<?php

namespace App\Http\Controllers;

use App\Models\Poktan;
use App\Models\Penyuluh;
use App\Models\PenugasanPoktan;
use App\Services\AuditLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class PoktanController extends Controller
{
    public function __construct(private readonly AuditLogger $audit)
    {
    }

    public function index(Request $request)
    {
        $this->checkViewerAccess();

        $query = Poktan::with([
            'penyuluh.penugasanAktif.kecamatan',
            'kecamatan',
        ])->withCount(['petani', 'pengajuanPetani']);

        if (Auth::user()->role === 'penyuluh') {
            $penyuluh = $this->currentPenyuluh();
            $query->where('penyuluh_id', $penyuluh->id);
        }

        if ($request->filled('search')) {
            $search = trim((string) $request->search);
            $query->where(function ($q) use ($search) {
                $q->where('nama_poktan', 'like', '%' . $search . '%')
                    ->orWhereHas('penyuluh', fn ($p) => $p->where('nama_penyuluh', 'like', '%' . $search . '%'))
                    ->orWhereHas('kecamatan', fn ($k) => $k->where('nama_kecamatan', 'like', '%' . $search . '%'));
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $poktan = $query->orderBy('nama_poktan')->paginate(10)->withQueryString();

        return view('poktan.index', compact('poktan'));
    }

    public function create()
    {
        $this->checkViewerAccess();
        $penyuluhLogin = Auth::user()->role === 'penyuluh' ? $this->currentPenyuluh() : null;

        $penyuluhs = Penyuluh::with('penugasanAktif.kecamatan')
            ->where('status', 'aktif')
            ->whereHas('penugasanAktif')
            ->orderBy('nama_penyuluh')
            ->get();

        return view('poktan.create', compact('penyuluhs', 'penyuluhLogin'));
    }

    public function store(Request $request)
    {
        $this->checkViewerAccess();

        if (Auth::user()->role === 'penyuluh') {
            $penyuluh = $this->currentPenyuluh();
            $penyuluhId = $penyuluh->id;
        } else {
            $validatedPenyuluh = $request->validate([
                'penyuluh_id' => ['required', 'exists:penyuluh,id'],
            ]);
            $penyuluhId = (int) $validatedPenyuluh['penyuluh_id'];
            $penyuluh = Penyuluh::with('penugasanAktif.kecamatan')->findOrFail($penyuluhId);
        }

        $data = $request->validate([
            'nama_poktan' => ['required', 'string', 'max:255'],
        ], ['nama_poktan.required' => 'Nama Poktan wajib diisi.']);

        $penugasan = $penyuluh->penugasanAktif;
        if ($penyuluh->status !== 'aktif' || !$penugasan) {
            throw ValidationException::withMessages([
                'penyuluh_id' => 'Penyuluh belum memiliki penugasan Kecamatan aktif.',
            ]);
        }

        DB::transaction(function () use ($data, $penyuluhId, $penugasan) {
            $poktan = Poktan::create([
                'nama_poktan' => strtoupper(trim($data['nama_poktan'])),
                'penyuluh_id' => $penyuluhId,
                'kecamatan_id' => $penugasan->kecamatan_id,
                'status' => 'aktif',
            ]);

            PenugasanPoktan::create([
                'poktan_id' => $poktan->id,
                'penyuluh_id' => $penyuluhId,
                'tanggal_mulai' => now()->toDateString(),
                'status' => 'aktif',
                'catatan' => 'Pembinaan Poktan dimulai.',
            ]);

            $this->audit->log(
                'poktan',
                'create',
                $poktan,
                'Poktan ditambahkan. Kecamatan otomatis mengikuti wilayah tugas Penyuluh.',
                null,
                ['nama_poktan' => $poktan->nama_poktan, 'penyuluh_id' => $penyuluhId, 'kecamatan_id' => $poktan->kecamatan_id]
            );
        });

        return redirect()->route('poktan.index')
            ->with('success', 'Poktan berhasil ditambahkan. Kecamatan otomatis mengikuti wilayah tugas Penyuluh.');
    }

    public function show(Poktan $poktan)
    {
        $this->checkOwnedForPenyuluh($poktan);

        $poktan->load(['penyuluh.penugasanAktif.kecamatan', 'kecamatan'])->loadCount('petani');
        $petani = $poktan->petani()->orderBy('nama_petani')->paginate(10)->withQueryString();

        return view('poktan.show', compact('poktan', 'petani'));
    }

    public function edit(Poktan $poktan)
    {
        $this->checkOwnedForPenyuluh($poktan);

        $penyuluhLogin = Auth::user()->role === 'penyuluh' ? $this->currentPenyuluh() : null;
        $penyuluhs = Penyuluh::with('penugasanAktif.kecamatan')
            ->where('status', 'aktif')
            ->whereHas('penugasanAktif', function ($q) use ($poktan) {
                $q->where('kecamatan_id', $poktan->kecamatan_id);
            })
            ->orderBy('nama_penyuluh')
            ->get();

        return view('poktan.edit', compact('poktan', 'penyuluhs', 'penyuluhLogin'));
    }

    public function update(Request $request, Poktan $poktan)
    {
        $this->checkOwnedForPenyuluh($poktan);

        $data = $request->validate([
            'nama_poktan' => ['required', 'string', 'max:255'],
            'status' => ['required', Rule::in(['aktif', 'nonaktif'])],
            'penyuluh_id' => [Auth::user()->role === 'admin' ? 'required' : 'nullable', 'nullable', 'exists:penyuluh,id'],
        ]);

        $targetPenyuluhId = Auth::user()->role === 'admin'
            ? (int) $data['penyuluh_id']
            : (int) $poktan->penyuluh_id;

        $target = Penyuluh::with('penugasanAktif')->findOrFail($targetPenyuluhId);

        if ($target->status !== 'aktif' || !$target->penugasanAktif) {
            throw ValidationException::withMessages([
                'penyuluh_id' => 'Penyuluh tujuan tidak memiliki penugasan Kecamatan aktif.',
            ]);
        }

        if ((int) $target->penugasanAktif->kecamatan_id !== (int) $poktan->kecamatan_id) {
            throw ValidationException::withMessages([
                'penyuluh_id' => 'Poktan tetap berada di Kecamatan asal. Pilih Penyuluh lain yang sedang bertugas pada Kecamatan yang sama.',
            ]);
        }

        $auditOld = [
            'nama_poktan' => $poktan->nama_poktan,
            'penyuluh_id' => $poktan->penyuluh_id,
            'kecamatan_id' => $poktan->kecamatan_id,
            'status' => $poktan->status,
        ];

        $oldPenyuluhId = (int) $poktan->penyuluh_id;
        $oldStatus = $poktan->status;
        $newStatus = $data['status'];
        $penyuluhBerubah = $oldPenyuluhId !== $targetPenyuluhId;

        DB::transaction(function () use (
            $poktan, $data, $targetPenyuluhId, $oldStatus, $newStatus, $penyuluhBerubah, $auditOld
        ) {
            $pembinaanAktif = PenugasanPoktan::where('poktan_id', $poktan->id)
                ->where('status', 'aktif')
                ->whereNull('tanggal_selesai')
                ->latest('tanggal_mulai')
                ->first();

            if ($pembinaanAktif && ($penyuluhBerubah || $newStatus !== 'aktif')) {
                $pembinaanAktif->update([
                    'tanggal_selesai' => now()->toDateString(),
                    'status' => 'selesai',
                    'catatan' => $penyuluhBerubah
                        ? 'Pembinaan dialihkan ke Penyuluh lain.'
                        : 'Poktan dinonaktifkan.',
                ]);
            }

            $poktan->update([
                'nama_poktan' => strtoupper(trim($data['nama_poktan'])),
                'penyuluh_id' => $targetPenyuluhId,
                // Kecamatan tidak pernah ikut berpindah saat Penyuluh berganti.
                'status' => $newStatus,
            ]);

            $perluPembinaanBaru = $newStatus === 'aktif'
                && ($penyuluhBerubah || $oldStatus !== 'aktif' || !$pembinaanAktif);

            if ($perluPembinaanBaru) {
                PenugasanPoktan::create([
                    'poktan_id' => $poktan->id,
                    'penyuluh_id' => $targetPenyuluhId,
                    'tanggal_mulai' => now()->toDateString(),
                    'status' => 'aktif',
                    'catatan' => $penyuluhBerubah
                        ? 'Pembinaan dialihkan dari Penyuluh sebelumnya.'
                        : 'Pembinaan Poktan diaktifkan kembali.',
                ]);
            }

            $this->audit->log(
                'poktan',
                'update',
                $poktan,
                $penyuluhBerubah ? 'Pembina Poktan dialihkan; Kecamatan Poktan tetap.' : 'Data Poktan diperbarui.',
                $auditOld,
                [
                    'nama_poktan' => $poktan->nama_poktan,
                    'penyuluh_id' => $poktan->penyuluh_id,
                    'kecamatan_id' => $poktan->kecamatan_id,
                    'status' => $poktan->status,
                ]
            );
        });

        return redirect()->route('poktan.index')->with(
            'success',
            $penyuluhBerubah
                ? 'Poktan berhasil dialihkan. Kecamatan tetap sama dan riwayat pembinaan tersimpan.'
                : 'Data Poktan berhasil diperbarui.'
        );
    }

    public function destroy(Poktan $poktan)
    {
        $this->checkOwnedForPenyuluh($poktan);

        if (
            $poktan->petani()->exists() ||
            $poktan->pengajuanPetani()->exists() ||
            $poktan->alternatifMusim()->exists() ||
            $poktan->batchImportPengajuan()->exists()
        ) {
            return back()->with(
                'error',
                'Poktan sudah memiliki histori dan tidak boleh dihapus. Ubah status menjadi Nonaktif agar histori tetap tersimpan.'
            );
        }

        $this->audit->log(
            'poktan',
            'delete',
            $poktan,
            'Poktan tanpa histori dihapus.',
            ['nama_poktan' => $poktan->nama_poktan, 'kecamatan_id' => $poktan->kecamatan_id, 'penyuluh_id' => $poktan->penyuluh_id],
            null
        );
        $poktan->delete();
        return redirect()->route('poktan.index')->with('success', 'Poktan yang belum memiliki histori berhasil dihapus.');
    }

    private function currentPenyuluh(): Penyuluh
    {
        $penyuluh = Penyuluh::with('penugasanAktif.kecamatan')
            ->where('user_id', Auth::id())
            ->first();

        if (!$penyuluh) {
            abort(403, 'Akun belum terhubung dengan data Penyuluh.');
        }

        return $penyuluh;
    }

    private function checkViewerAccess(): void
    {
        if (!Auth::check() || !in_array(Auth::user()->role, ['admin', 'penyuluh'], true)) {
            abort(403);
        }
    }

    private function checkOwnedForPenyuluh(Poktan $poktan): void
    {
        $this->checkViewerAccess();

        if (Auth::user()->role === 'penyuluh') {
            $penyuluh = $this->currentPenyuluh();
            if ((int) $poktan->penyuluh_id !== (int) $penyuluh->id) {
                abort(403, 'Poktan ini bukan binaan Anda.');
            }
        }
    }
}
