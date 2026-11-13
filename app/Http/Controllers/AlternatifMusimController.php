<?php

namespace App\Http\Controllers;

use App\Models\AlternatifMusim;
use App\Models\JenisPupuk;
use App\Models\Kecamatan;
use App\Models\MusimTanam;
use App\Models\Periode;
use App\Models\Petani;
use App\Models\Penyuluh;
use App\Models\Poktan;
use App\Models\VikorHasil;
use App\Services\AuditLogger;
use App\Services\KebutuhanPupukService;
use App\Services\PenilaianKriteriaService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class AlternatifMusimController extends Controller
{
    public function __construct(
        private readonly PenilaianKriteriaService $penilaian,
        private readonly KebutuhanPupukService $pupukService,
        private readonly AuditLogger $audit,
    ) {
    }

    private function currentPenyuluh(): ?Penyuluh
    {
        if (!Auth::check() || Auth::user()->role !== 'penyuluh') {
            return null;
        }

        return Penyuluh::with('penugasanAktif.kecamatan')
            ->where('user_id', Auth::id())
            ->first();
    }

    private function criteriaOptions(): array
    {
        return [
            'C1' => $this->penilaian->options('C1'),
            'C2' => $this->penilaian->options('C2'),
            'C3' => $this->penilaian->options('C3'),
            'C4' => $this->penilaian->options('C4'),
            'C5' => $this->penilaian->options('C5'),
        ];
    }

    private function validateInput(Request $request, ?AlternatifMusim $existing = null): array
    {
        $options = $this->criteriaOptions();

        $rules = [
            'nik' => ['required', 'digits:16'],
            'nama_petani' => ['required', 'string', 'max:255'],
            'musim_tanam_id' => ['required', 'exists:musim_tanam,id'],
            'periode_id' => ['required', 'exists:periode,id'],
            'poktan_id' => ['required', 'exists:poktan,id'],
            'jenis_komoditas' => ['required', Rule::in($options['C2'])],
            'kelengkapan_berkas' => ['required', Rule::in($options['C3'])],
            'subsektor_pertanian' => ['required', Rule::in($options['C5'])],
            'berkas_pdf' => ['nullable', 'file', 'mimes:pdf', 'max:10240'],
            'pupuk' => ['nullable', 'array'],
            'pupuk.*' => ['nullable', 'numeric', 'min:0'],
        ];

        $rules['luas_lahan_ha'] = $existing
            ? ['nullable', 'numeric', 'gt:0', 'lte:2']
            : ['required', 'numeric', 'gt:0', 'lte:2'];

        $data = $request->validate($rules);

        if ($request->filled('luas_lahan_ha')) {
            $data['luas_lahan'] = $this->penilaian->resolveLuasLahan((float) $data['luas_lahan_ha']);
        } elseif ($existing) {
            $data['luas_lahan_ha'] = $existing->luas_lahan_ha;
            $data['luas_lahan'] = $existing->luas_lahan;
        }

        $data['jenis_komoditas'] = $this->penilaian->canonical('C2', $data['jenis_komoditas']);
        $data['kelengkapan_berkas'] = $this->penilaian->canonical('C3', $data['kelengkapan_berkas']);
        $data['subsektor_pertanian'] = $this->penilaian->canonical('C5', $data['subsektor_pertanian']);

        $rows = $this->pupukService->fromRequestArray($data['pupuk'] ?? [], !$existing);
        $data['pupuk_rows'] = $rows;

        if (count($rows) > 0) {
            $data['kebutuhan_pupuk'] = $this->penilaian->resolveKebutuhanPupuk(count($rows));
        } elseif ($existing) {
            $data['kebutuhan_pupuk'] = $existing->kebutuhan_pupuk;
        }

        return $data;
    }

    private function formMasterData(): array
    {
        $criteria = $this->criteriaOptions();

        return [
            'musimTanam' => MusimTanam::orderBy('kode_musim')->get(),
            'periode' => Periode::orderByDesc('tahun')->get(),
            'poktan' => Poktan::with(['kecamatan', 'penyuluh'])
                ->where('status', 'aktif')
                ->orderBy('nama_poktan')->get(),
            'c1' => $criteria['C1'],
            'c2' => $criteria['C2'],
            'c3' => $criteria['C3'],
            'c4' => $criteria['C4'],
            'c5' => $criteria['C5'],
            'jenisPupuk' => JenisPupuk::where('is_active', true)->orderBy('nama_pupuk')->get(),
        ];
    }

    private function getPoktanOrFail(int $poktanId): Poktan
    {
        $poktan = Poktan::with('kecamatan')->findOrFail($poktanId);
        if (!$poktan->kecamatan_id) {
            throw ValidationException::withMessages(['poktan_id' => 'Poktan belum memiliki Kecamatan.']);
        }
        return $poktan;
    }

    private function ensureNoDuplicate(string $nik, int $musimId, int $periodeId, ?int $ignoreId = null): void
    {
        $query = AlternatifMusim::where('musim_tanam_id', $musimId)
            ->where('periode_id', $periodeId)
            ->whereHas('petani', fn ($q) => $q->where('nik', $nik));

        if ($ignoreId !== null) {
            $query->where('id', '!=', $ignoreId);
        }

        if ($query->exists()) {
            throw ValidationException::withMessages([
                'nik' => 'Petani dengan NIK ini sudah menjadi alternatif pada Musim Tanam dan Tahun tersebut.',
            ]);
        }
    }

    private function nextKodeAlternatif(int $musimId, int $periodeId): string
    {
        $max = AlternatifMusim::where('musim_tanam_id', $musimId)
            ->where('periode_id', $periodeId)
            ->lockForUpdate()
            ->pluck('kode_alternatif')
            ->map(fn ($kode) => preg_match('/^A(\d+)$/i', (string) $kode, $m) ? (int) $m[1] : 0)
            ->max();

        return 'A' . (($max ?? 0) + 1);
    }

    private function invalidateVikorScope(int $musimId, int $periodeId, int $kecamatanId): int
    {
        $ids = AlternatifMusim::where('musim_tanam_id', $musimId)
            ->where('periode_id', $periodeId)
            ->whereHas('poktan', fn ($q) => $q->where('kecamatan_id', $kecamatanId))
            ->pluck('id');

        if ($ids->isEmpty()) {
            return 0;
        }

        return VikorHasil::whereIn('alternatif_musim_id', $ids)->delete();
    }

    private function savePdf(Request $request, AlternatifMusim $alternatif, ?string $oldPath = null): ?string
    {
        if (!$request->hasFile('berkas_pdf')) {
            return $oldPath;
        }

        $filename = ($alternatif->petani?->nik ?: 'alternatif') . '_' . now()->format('YmdHis') . '.pdf';
        $path = $request->file('berkas_pdf')->storeAs(
            "berkas-alternatif/{$alternatif->periode_id}/{$alternatif->musim_tanam_id}",
            $filename,
            'local'
        );

        if ($oldPath && $oldPath !== $path && Storage::disk('local')->exists($oldPath)) {
            // Jangan hapus jika path sama-sama dipakai oleh pengajuan terkait.
            if (!$alternatif->pengajuanPetani || $alternatif->pengajuanPetani->berkas_persyaratan_path !== $oldPath) {
                Storage::disk('local')->delete($oldPath);
            }
        }

        return $path;
    }

    public function index(Request $request)
    {
        $query = AlternatifMusim::with([
            'petani', 'poktan.kecamatan', 'poktan.penyuluh', 'musimTanam', 'periode',
            'pengajuanPetani', 'vikorHasil', 'pupuk.jenisPupuk',
        ]);

        $wilayahPenyuluh = null;
        if (Auth::user()->role === 'penyuluh') {
            $penyuluh = $this->currentPenyuluh();
            if (!$penyuluh) {
                $query->whereRaw('1 = 0');
            } else {
                $query->whereHas('poktan', fn ($q) => $q->where('penyuluh_id', $penyuluh->id));
                $wilayahPenyuluh = $penyuluh->penugasanAktif?->kecamatan;
            }
        }

        foreach (['musim_tanam_id', 'periode_id', 'poktan_id'] as $field) {
            if ($request->filled($field)) {
                $query->where($field, $request->{$field});
            }
        }

        if (Auth::user()->role === 'admin' && $request->filled('kecamatan_id')) {
            $query->whereHas('poktan', fn ($q) => $q->where('kecamatan_id', $request->kecamatan_id));
        }

        if ($request->filled('search')) {
            $search = trim((string) $request->search);
            $query->where(function ($q) use ($search) {
                $q->where('kode_alternatif', 'like', '%' . $search . '%')
                    ->orWhereHas('petani', function ($p) use ($search) {
                        $p->where('nik', 'like', '%' . $search . '%')
                            ->orWhere('nama_petani', 'like', '%' . $search . '%');
                    });
            });
        }

        $alternatif = $query
            ->orderByDesc('periode_id')
            ->orderBy('musim_tanam_id')
            ->orderByRaw("CAST(SUBSTRING(kode_alternatif, 2) AS UNSIGNED) ASC")
            ->orderBy('kode_alternatif')
            ->paginate(10)->withQueryString();

        $musimTanam = MusimTanam::orderBy('kode_musim')->get();
        $periode = Periode::orderByDesc('tahun')->get();

        if (Auth::user()->role === 'penyuluh' && ($penyuluh = $this->currentPenyuluh())) {
            $poktan = Poktan::with('kecamatan')->where('penyuluh_id', $penyuluh->id)->orderBy('nama_poktan')->get();
            $kecamatan = collect();
        } else {
            $poktan = Poktan::with('kecamatan')->orderBy('nama_poktan')->get();
            $kecamatan = Kecamatan::orderBy('nama_kecamatan')->get();
        }

        return view('alternatif_musim.index', compact(
            'alternatif', 'musimTanam', 'periode', 'kecamatan', 'poktan', 'wilayahPenyuluh'
        ));
    }

    public function create()
    {
        return view('alternatif_musim.create', $this->formMasterData());
    }

    public function store(Request $request)
    {
        $data = $this->validateInput($request);
        $poktan = $this->getPoktanOrFail((int) $data['poktan_id']);
        $this->ensureNoDuplicate($data['nik'], (int) $data['musim_tanam_id'], (int) $data['periode_id']);

        DB::transaction(function () use ($request, $data, $poktan) {
            $petani = Petani::where('nik', $data['nik'])->lockForUpdate()->first();
            if (!$petani) {
                $petani = Petani::create([
                    'nik' => $data['nik'], 'nama_petani' => $data['nama_petani'], 'poktan_id' => $data['poktan_id'],
                    'luas_lahan' => $data['luas_lahan'], 'jenis_komoditas' => $data['jenis_komoditas'],
                    'kelengkapan_berkas' => $data['kelengkapan_berkas'], 'kebutuhan_pupuk' => $data['kebutuhan_pupuk'],
                    'subsektor_pertanian' => $data['subsektor_pertanian'],
                ]);
            } else {
                $petani->update(['nama_petani' => $data['nama_petani'], 'poktan_id' => $data['poktan_id']]);
            }

            $alternatif = AlternatifMusim::create([
                'petani_id' => $petani->id,
                'poktan_id' => $data['poktan_id'],
                'musim_tanam_id' => $data['musim_tanam_id'],
                'periode_id' => $data['periode_id'],
                'pengajuan_petani_id' => null,
                'kode_alternatif' => $this->nextKodeAlternatif((int) $data['musim_tanam_id'], (int) $data['periode_id']),
                'luas_lahan_ha' => $data['luas_lahan_ha'],
                'luas_lahan' => $data['luas_lahan'],
                'jenis_komoditas' => $data['jenis_komoditas'],
                'kelengkapan_berkas' => $data['kelengkapan_berkas'],
                'kebutuhan_pupuk' => $data['kebutuhan_pupuk'],
                'subsektor_pertanian' => $data['subsektor_pertanian'],
            ]);

            $alternatif->load('petani');
            $path = $this->savePdf($request, $alternatif);
            if ($path) {
                $alternatif->update(['berkas_persyaratan_path' => $path]);
            }

            $this->pupukService->syncAlternatif($alternatif, $data['pupuk_rows']);
            $this->invalidateVikorScope((int) $data['musim_tanam_id'], (int) $data['periode_id'], (int) $poktan->kecamatan_id);

            $this->audit->log(
                'alternatif',
                'create',
                $alternatif,
                'Admin menambahkan alternatif secara manual.',
                null,
                ['kode' => $alternatif->kode_alternatif, 'nik' => $petani->nik]
            );
        });

        return redirect()->route('alternatif-musim.index')
            ->with('success', 'Alternatif berhasil ditambahkan. Scope terkait perlu dihitung ulang karena kandidat bertambah.');
    }

    public function edit(AlternatifMusim $alternatifMusim)
    {
        $alternatifMusim->load(['petani', 'poktan.kecamatan', 'musimTanam', 'periode', 'vikorHasil', 'pupuk.jenisPupuk', 'pengajuanPetani']);
        return view('alternatif_musim.edit', array_merge($this->formMasterData(), compact('alternatifMusim')));
    }

    public function update(Request $request, AlternatifMusim $alternatifMusim)
    {
        $alternatifMusim->load(['poktan', 'petani', 'pupuk', 'pengajuanPetani']);
        $data = $this->validateInput($request, $alternatifMusim);
        $poktanBaru = $this->getPoktanOrFail((int) $data['poktan_id']);
        $this->ensureNoDuplicate($data['nik'], (int) $data['musim_tanam_id'], (int) $data['periode_id'], $alternatifMusim->id);

        $auditOld = [
            'nik' => $alternatifMusim->petani?->nik,
            'nama_petani' => $alternatifMusim->petani?->nama_petani,
            'poktan_id' => $alternatifMusim->poktan_id,
            'musim_tanam_id' => $alternatifMusim->musim_tanam_id,
            'periode_id' => $alternatifMusim->periode_id,
            'luas_lahan' => $alternatifMusim->luas_lahan,
            'jenis_komoditas' => $alternatifMusim->jenis_komoditas,
            'kelengkapan_berkas' => $alternatifMusim->kelengkapan_berkas,
            'kebutuhan_pupuk' => $alternatifMusim->kebutuhan_pupuk,
            'subsektor_pertanian' => $alternatifMusim->subsektor_pertanian,
        ];

        $old = [
            'musim' => (int) $alternatifMusim->musim_tanam_id,
            'periode' => (int) $alternatifMusim->periode_id,
            'kecamatan' => (int) $alternatifMusim->poktan?->kecamatan_id,
            'criteria' => [
                $alternatifMusim->luas_lahan,
                $alternatifMusim->jenis_komoditas,
                $alternatifMusim->kelengkapan_berkas,
                $alternatifMusim->kebutuhan_pupuk,
                $alternatifMusim->subsektor_pertanian,
            ],
        ];

        DB::transaction(function () use ($request, $data, $alternatifMusim, $poktanBaru, $old, $auditOld) {
            $petani = Petani::where('nik', $data['nik'])->lockForUpdate()->first();
            if (!$petani) {
                $petani = Petani::create([
                    'nik' => $data['nik'], 'nama_petani' => $data['nama_petani'], 'poktan_id' => $data['poktan_id'],
                    'luas_lahan' => $data['luas_lahan'], 'jenis_komoditas' => $data['jenis_komoditas'],
                    'kelengkapan_berkas' => $data['kelengkapan_berkas'], 'kebutuhan_pupuk' => $data['kebutuhan_pupuk'],
                    'subsektor_pertanian' => $data['subsektor_pertanian'],
                ]);
            } else {
                $petani->update(['nama_petani' => $data['nama_petani'], 'poktan_id' => $data['poktan_id']]);
            }

            $scopeChanged = $old['musim'] !== (int) $data['musim_tanam_id']
                || $old['periode'] !== (int) $data['periode_id']
                || $old['kecamatan'] !== (int) $poktanBaru->kecamatan_id;

            $newCriteria = [
                $data['luas_lahan'], $data['jenis_komoditas'], $data['kelengkapan_berkas'],
                $data['kebutuhan_pupuk'], $data['subsektor_pertanian'],
            ];
            $criteriaChanged = $old['criteria'] !== $newCriteria;

            $kode = $alternatifMusim->kode_alternatif;
            if ($old['musim'] !== (int) $data['musim_tanam_id'] || $old['periode'] !== (int) $data['periode_id']) {
                $kode = $this->nextKodeAlternatif((int) $data['musim_tanam_id'], (int) $data['periode_id']);
            }

            $alternatifMusim->update([
                'petani_id' => $petani->id,
                'poktan_id' => $data['poktan_id'],
                'musim_tanam_id' => $data['musim_tanam_id'],
                'periode_id' => $data['periode_id'],
                'kode_alternatif' => $kode,
                'luas_lahan_ha' => $data['luas_lahan_ha'],
                'luas_lahan' => $data['luas_lahan'],
                'jenis_komoditas' => $data['jenis_komoditas'],
                'kelengkapan_berkas' => $data['kelengkapan_berkas'],
                'kebutuhan_pupuk' => $data['kebutuhan_pupuk'],
                'subsektor_pertanian' => $data['subsektor_pertanian'],
            ]);

            $alternatifMusim->load('petani');
            $path = $this->savePdf($request, $alternatifMusim, $alternatifMusim->berkas_persyaratan_path);
            if ($path !== $alternatifMusim->berkas_persyaratan_path) {
                $alternatifMusim->update(['berkas_persyaratan_path' => $path]);
                if ($alternatifMusim->pengajuanPetani) {
                    $alternatifMusim->pengajuanPetani->update(['berkas_persyaratan_path' => $path]);
                }
            }

            // Detail pupuk boleh diperbarui tanpa invalidasi jika jumlah jenis (C4) tidak berubah.
            if (count($data['pupuk_rows']) > 0) {
                $this->pupukService->syncAlternatif($alternatifMusim, $data['pupuk_rows']);
            }

            if ($scopeChanged) {
                if ($old['kecamatan']) {
                    $this->invalidateVikorScope($old['musim'], $old['periode'], $old['kecamatan']);
                }
                $this->invalidateVikorScope((int) $data['musim_tanam_id'], (int) $data['periode_id'], (int) $poktanBaru->kecamatan_id);
            } elseif ($criteriaChanged) {
                $this->invalidateVikorScope((int) $data['musim_tanam_id'], (int) $data['periode_id'], (int) $poktanBaru->kecamatan_id);
            }

            $this->audit->log(
                'alternatif',
                'update',
                $alternatifMusim,
                $scopeChanged || $criteriaChanged
                    ? 'Data alternatif diperbarui dan hasil VIKOR scope terkait diinvalidasi.'
                    : 'Data pendukung alternatif diperbarui tanpa mengubah nilai C1-C5.',
                $auditOld,
                [
                    'nik' => $petani->nik,
                    'nama_petani' => $petani->nama_petani,
                    'poktan_id' => $alternatifMusim->poktan_id,
                    'musim_tanam_id' => $alternatifMusim->musim_tanam_id,
                    'periode_id' => $alternatifMusim->periode_id,
                    'luas_lahan' => $alternatifMusim->luas_lahan,
                    'jenis_komoditas' => $alternatifMusim->jenis_komoditas,
                    'kelengkapan_berkas' => $alternatifMusim->kelengkapan_berkas,
                    'kebutuhan_pupuk' => $alternatifMusim->kebutuhan_pupuk,
                    'subsektor_pertanian' => $alternatifMusim->subsektor_pertanian,
                    'vikor_invalidated' => $scopeChanged || $criteriaChanged,
                ]
            );
        });

        return redirect()->route('alternatif-musim.index')
            ->with('success', 'Data alternatif berhasil diperbarui. Hasil VIKOR hanya dikosongkan jika scope atau nilai C1–C5 berubah.');
    }

    public function destroy(AlternatifMusim $alternatifMusim)
    {
        $alternatifMusim->load(['poktan', 'pengajuanPetani']);
        $musim = (int) $alternatifMusim->musim_tanam_id;
        $periode = (int) $alternatifMusim->periode_id;
        $kecamatan = (int) $alternatifMusim->poktan?->kecamatan_id;
        $kode = $alternatifMusim->kode_alternatif;

        DB::transaction(function () use ($alternatifMusim, $musim, $periode, $kecamatan) {
            if ($kecamatan) {
                $this->invalidateVikorScope($musim, $periode, $kecamatan);
            }

            if ($alternatifMusim->pengajuanPetani) {
                $alternatifMusim->pengajuanPetani->update([
                    'status' => 'ditolak',
                    'catatan_admin' => trim(($alternatifMusim->pengajuanPetani->catatan_admin ? $alternatifMusim->pengajuanPetani->catatan_admin . "\n" : '') . 'Alternatif dihapus oleh Admin.'),
                    'verified_by' => Auth::id(),
                    'tanggal_verifikasi' => now(),
                ]);
            }

            $this->audit->log(
                'alternatif',
                'delete',
                $alternatifMusim,
                'Alternatif dihapus oleh Admin; Petani master tetap dipertahankan.',
                ['kode' => $alternatifMusim->kode_alternatif, 'petani_id' => $alternatifMusim->petani_id],
                null
            );

            $alternatifMusim->delete();
        });

        return redirect()->route('alternatif-musim.index')
            ->with('success', "Alternatif {$kode} berhasil dihapus. Petani master tidak ikut dihapus.");
    }

    public function berkas(AlternatifMusim $alternatifMusim)
    {
        if (Auth::user()->role === 'penyuluh') {
            $penyuluh = $this->currentPenyuluh();
            $alternatifMusim->load('poktan');
            if (!$penyuluh || (int) $alternatifMusim->poktan?->penyuluh_id !== (int) $penyuluh->id) {
                abort(403);
            }
        }

        if (!$alternatifMusim->berkas_persyaratan_path || !Storage::disk('local')->exists($alternatifMusim->berkas_persyaratan_path)) {
            abort(404, 'Berkas PDF belum tersedia untuk alternatif ini.');
        }

        $alternatifMusim->loadMissing('petani');
        return response()->file(Storage::disk('local')->path($alternatifMusim->berkas_persyaratan_path), [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="' . ($alternatifMusim->petani?->nik ?: $alternatifMusim->kode_alternatif) . '.pdf"',
        ]);
    }
}
