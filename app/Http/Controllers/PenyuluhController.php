<?php

namespace App\Http\Controllers;

use App\Models\Kecamatan;
use App\Models\PenugasanPenyuluh;
use App\Models\Penyuluh;
use App\Models\User;
use App\Services\AuditLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class PenyuluhController extends Controller
{
    public function __construct(private readonly AuditLogger $audit)
    {
    }

    public function index(Request $request)
    {
        $query = Penyuluh::with(['user', 'penugasanAktif.kecamatan'])
            ->withCount(['poktan', 'pengajuanPetani']);

        if ($request->filled('search')) {
            $search = trim((string) $request->search);
            $query->where(function ($q) use ($search) {
                $q->where('nama_penyuluh', 'like', '%' . $search . '%')
                    ->orWhereHas('user', function ($u) use ($search) {
                        $u->where('username', 'like', '%' . $search . '%')
                            ->orWhere('email', 'like', '%' . $search . '%');
                    })
                    ->orWhereHas('penugasanAktif.kecamatan', function ($k) use ($search) {
                        $k->where('nama_kecamatan', 'like', '%' . $search . '%');
                    });
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $penyuluh = $query->orderBy('nama_penyuluh')->paginate(10)->withQueryString();
        $kecamatans = Kecamatan::orderBy('nama_kecamatan')->get();

        return view('penyuluh.index', compact('penyuluh', 'kecamatans'));
    }

    public function create()
    {
        $kecamatans = Kecamatan::orderBy('nama_kecamatan')->get();
        return view('penyuluh.create', compact('kecamatans'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama_penyuluh' => ['required', 'string', 'max:255'],
            'username' => ['required', 'string', 'max:255', 'unique:users,username'],
            'password' => ['required', 'string', 'min:6', 'confirmed'],
            'kecamatan_id' => ['required', 'exists:kecamatan,id'],
            'tanggal_mulai' => ['required', 'date'],
        ], [
            'kecamatan_id.required' => 'Kecamatan tugas wajib dipilih.',
            'tanggal_mulai.required' => 'Tanggal mulai penugasan wajib diisi.',
        ]);

        DB::transaction(function () use ($validated) {
            $user = User::create([
                'name' => $validated['nama_penyuluh'],
                'username' => $validated['username'],
                'email' => $validated['username'] . '@penyuluh.com',
                'password' => Hash::make($validated['password']),
                'role' => 'penyuluh',
            ]);

            $penyuluh = Penyuluh::create([
                'user_id' => $user->id,
                'nama_penyuluh' => $validated['nama_penyuluh'],
                'status' => 'aktif',
            ]);

            PenugasanPenyuluh::create([
                'penyuluh_id' => $penyuluh->id,
                'kecamatan_id' => $validated['kecamatan_id'],
                'tanggal_mulai' => $validated['tanggal_mulai'],
                'status' => 'aktif',
            ]);

            $this->audit->log(
                'penyuluh',
                'create',
                $penyuluh,
                'Penyuluh ditambahkan dan ditempatkan pada satu Kecamatan tugas.',
                null,
                ['nama_penyuluh' => $penyuluh->nama_penyuluh, 'kecamatan_id' => $validated['kecamatan_id']]
            );
        });

        return redirect()->route('penyuluh.index')
            ->with('success', 'Penyuluh berhasil ditambahkan dan ditugaskan ke Kecamatan yang dipilih.');
    }

    public function edit(string $id)
    {
        $penyuluh = Penyuluh::with(['user', 'penugasanAktif.kecamatan', 'penugasan.kecamatan', 'poktan'])
            ->findOrFail($id);
        $kecamatans = Kecamatan::orderBy('nama_kecamatan')->get();

        return view('penyuluh.edit', compact('penyuluh', 'kecamatans'));
    }

    public function update(Request $request, string $id)
    {
        $penyuluh = Penyuluh::with(['user', 'penugasanAktif', 'poktan'])->findOrFail($id);
        $userId = optional($penyuluh->user)->id;

        $validated = $request->validate([
            'nama_penyuluh' => ['required', 'string', 'max:255'],
            'username' => [
                'required', 'string', 'max:255',
                Rule::unique('users', 'username')->ignore($userId),
            ],
            'password' => ['nullable', 'string', 'min:6', 'confirmed'],
            'status' => ['required', Rule::in(['aktif', 'nonaktif', 'mutasi_keluar'])],
            'kecamatan_id' => ['nullable', 'exists:kecamatan,id'],
            'tanggal_mulai' => ['nullable', 'date'],
            'catatan_penugasan' => ['nullable', 'string', 'max:1000'],
        ]);

        $current = $penyuluh->penugasanAktif;
        $auditOld = [
            'nama_penyuluh' => $penyuluh->nama_penyuluh,
            'username' => $penyuluh->user?->username,
            'status' => $penyuluh->status,
            'kecamatan_id' => $current?->kecamatan_id,
        ];
        $newStatus = $validated['status'];
        $newKecamatanId = !empty($validated['kecamatan_id']) ? (int) $validated['kecamatan_id'] : null;
        $changingKecamatan = $newStatus === 'aktif'
            && $newKecamatanId
            && (!$current || (int) $current->kecamatan_id !== $newKecamatanId);
        $closingAssignment = $newStatus !== 'aktif';

        if (($changingKecamatan || $closingAssignment) && $penyuluh->poktan()->where('status', 'aktif')->exists()) {
            throw ValidationException::withMessages([
                'kecamatan_id' => 'Penyuluh masih memiliki Poktan aktif. Alihkan Poktan kepada Penyuluh lain di Kecamatan lama terlebih dahulu sebelum memindahkan atau menonaktifkan Penyuluh.',
            ]);
        }

        if ($newStatus === 'aktif' && !$newKecamatanId) {
            throw ValidationException::withMessages([
                'kecamatan_id' => 'Kecamatan tugas wajib dipilih untuk Penyuluh aktif.',
            ]);
        }

        DB::transaction(function () use ($penyuluh, $validated, $current, $newStatus, $newKecamatanId, $changingKecamatan, $closingAssignment, $auditOld) {
            $penyuluh->update([
                'nama_penyuluh' => $validated['nama_penyuluh'],
                'status' => $newStatus,
            ]);

            if ($penyuluh->user) {
                $userData = [
                    'name' => $validated['nama_penyuluh'],
                    'username' => $validated['username'],
                    'email' => $validated['username'] . '@penyuluh.com',
                ];

                if (!empty($validated['password'])) {
                    $userData['password'] = Hash::make($validated['password']);
                }

                $penyuluh->user->update($userData);
            }

            if ($current && ($changingKecamatan || $closingAssignment)) {
                $current->update([
                    'tanggal_selesai' => now()->toDateString(),
                    'status' => 'selesai',
                    'catatan' => $validated['catatan_penugasan'] ?: $current->catatan,
                ]);
            }

            if ($newStatus === 'aktif' && (!$current || $changingKecamatan)) {
                PenugasanPenyuluh::create([
                    'penyuluh_id' => $penyuluh->id,
                    'kecamatan_id' => $newKecamatanId,
                    'tanggal_mulai' => $validated['tanggal_mulai'] ?: now()->toDateString(),
                    'status' => 'aktif',
                    'catatan' => $validated['catatan_penugasan'] ?? null,
                ]);
            }

            $this->audit->log(
                'penyuluh',
                'update',
                $penyuluh,
                $changingKecamatan ? 'Wilayah tugas Penyuluh diperbarui setelah Poktan aktif dialihkan.' : 'Data Penyuluh diperbarui.',
                $auditOld,
                [
                    'nama_penyuluh' => $penyuluh->nama_penyuluh,
                    'username' => $penyuluh->user?->username,
                    'status' => $penyuluh->status,
                    'kecamatan_id' => $newStatus === 'aktif' ? $newKecamatanId : null,
                ]
            );
        });

        return redirect()->route('penyuluh.index')
            ->with('success', 'Data dan penugasan Penyuluh berhasil diperbarui. Riwayat penugasan lama tetap tersimpan.');
    }

    public function destroy(string $id)
    {
        $penyuluh = Penyuluh::with('user')->findOrFail($id);

        $punyaRiwayatOperasional = $penyuluh->poktan()->exists()
            || $penyuluh->riwayatPembinaanPoktan()->exists()
            || $penyuluh->pengajuanPetani()->exists()
            || $penyuluh->batchImportPengajuan()->exists();

        if ($punyaRiwayatOperasional) {
            return redirect()->route('penyuluh.index')->with(
                'error',
                'Penyuluh tidak dapat dihapus karena sudah mempunyai riwayat Poktan/Pengajuan. Gunakan status Nonaktif atau Mutasi Keluar agar histori tetap terjaga.'
            );
        }

        DB::transaction(function () use ($penyuluh) {
            $this->audit->log(
                'penyuluh',
                'delete',
                $penyuluh,
                'Penyuluh tanpa histori operasional dihapus.',
                ['nama_penyuluh' => $penyuluh->nama_penyuluh, 'user_id' => $penyuluh->user_id],
                null
            );
            $penyuluh->penugasan()->delete();
            $user = $penyuluh->user;
            $penyuluh->delete();
            if ($user) {
                $user->delete();
            }
        });

        return redirect()->route('penyuluh.index')->with('success', 'Penyuluh yang belum memiliki riwayat berhasil dihapus.');
    }
}
