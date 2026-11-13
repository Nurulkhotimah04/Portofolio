<?php

namespace App\Http\Controllers;

use App\Models\AlternatifMusim;
use App\Models\JenisPupuk;
use App\Models\Kecamatan;
use App\Models\MusimTanam;
use App\Models\PengajuanPetani;
use App\Models\Periode;
use App\Models\Petani;
use App\Models\Penyuluh;
use App\Models\Poktan;
use App\Services\AuditLogger;
use App\Services\KebutuhanPupukService;
use App\Services\PenilaianKriteriaService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class PengajuanPetaniController extends Controller
{
    public function __construct(
        private readonly PenilaianKriteriaService $penilaian,
        private readonly KebutuhanPupukService $pupukService,
        private readonly AuditLogger $audit,
    ) {
    }

    private function currentPenyuluh(): Penyuluh
    {
        $penyuluh = Penyuluh::with('penugasanAktif.kecamatan')
            ->where('user_id', Auth::id())
            ->first();

        if (!$penyuluh) {
            abort(403, 'Akun ini belum terhubung dengan data Penyuluh.');
        }

        return $penyuluh;
    }

    private function ensureOwnedByPenyuluh(PengajuanPetani $pengajuan): void
    {
        if (Auth::user()->role !== 'penyuluh') {
            return;
        }

        if ((int) $pengajuan->penyuluh_id !== (int) $this->currentPenyuluh()->id) {
            abort(403, 'Anda tidak memiliki akses ke pengajuan tersebut.');
        }
    }

    private function allowedPoktanIds(Penyuluh $penyuluh)
    {
        return Poktan::where('penyuluh_id', $penyuluh->id)
            ->where('status', 'aktif')
            ->pluck('id');
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

    private function validateInput(Request $request, ?PengajuanPetani $existing = null): array
    {
        $options = $this->criteriaOptions();

        $rules = [
            'musim_tanam_id' => ['required', 'exists:musim_tanam,id'],
            'periode_id' => ['required', 'exists:periode,id'],
            'poktan_id' => ['required', 'exists:poktan,id'],
            'nik' => ['required', 'digits:16'],
            'nama_petani' => ['required', 'string', 'max:255'],
            'luas_lahan_ha' => ['required', 'numeric', 'gt:0', 'lte:2'],
            'jenis_komoditas' => ['required', Rule::in($options['C2'])],
            'kelengkapan_berkas' => ['required', Rule::in($options['C3'])],
            'subsektor_pertanian' => ['required', Rule::in($options['C5'])],
            'pupuk' => ['required', 'array'],
            'pupuk.*' => ['nullable', 'numeric', 'min:0'],
            'catatan_penyuluh' => ['nullable', 'string', 'max:2000'],
        ];

        if ($existing && $existing->berkas_persyaratan_path) {
            $rules['berkas_pdf'] = ['nullable', 'file', 'mimes:pdf', 'max:10240'];
        } else {
            $rules['berkas_pdf'] = ['required', 'file', 'mimes:pdf', 'max:10240'];
        }

        $data = $request->validate($rules, [
            'luas_lahan_ha.required' => 'Luas lahan wajib diisi dalam satuan Ha.',
            'luas_lahan_ha.lte' => 'Luas lahan maksimal 2 Ha sesuai subkriteria C1 yang digunakan.',
            'berkas_pdf.required' => 'Berkas persyaratan PDF wajib diunggah.',
            'berkas_pdf.mimes' => 'Berkas persyaratan harus berupa PDF.',
            'berkas_pdf.max' => 'Ukuran PDF maksimal 10 MB.',
        ]);

        $data['luas_lahan'] = $this->penilaian->resolveLuasLahan((float) $data['luas_lahan_ha']);
        $data['jenis_komoditas'] = $this->penilaian->canonical('C2', $data['jenis_komoditas']);
        $data['kelengkapan_berkas'] = $this->penilaian->canonical('C3', $data['kelengkapan_berkas']);
        $data['subsektor_pertanian'] = $this->penilaian->canonical('C5', $data['subsektor_pertanian']);
        $data['pupuk_rows'] = $this->pupukService->fromRequestArray($data['pupuk'], true);
        $data['kebutuhan_pupuk'] = $this->penilaian->resolveKebutuhanPupuk(count($data['pupuk_rows']));

        return $data;
    }

    private function ensurePoktanValid(Penyuluh $penyuluh, int $poktanId): Poktan
    {
        $poktan = Poktan::with('kecamatan')->findOrFail($poktanId);

        if ((int) $poktan->penyuluh_id !== (int) $penyuluh->id) {
            abort(403, 'Anda hanya dapat memilih Poktan binaan Anda.');
        }

        if ($poktan->status !== 'aktif') {
            throw ValidationException::withMessages([
                'poktan_id' => 'Poktan yang dipilih sedang nonaktif.',
            ]);
        }

        if (!$poktan->kecamatan_id || !$penyuluh->penugasanAktif) {
            throw ValidationException::withMessages([
                'poktan_id' => 'Wilayah tugas Penyuluh/Poktan belum lengkap. Hubungi Admin.',
            ]);
        }

        if ((int) $poktan->kecamatan_id !== (int) $penyuluh->penugasanAktif->kecamatan_id) {
            throw ValidationException::withMessages([
                'poktan_id' => 'Kecamatan Poktan tidak sesuai dengan penugasan aktif Penyuluh.',
            ]);
        }

        return $poktan;
    }

    private function duplicateExists(
        string $nik,
        int $musimTanamId,
        int $periodeId,
        ?int $exceptPengajuanId = null
    ): bool {
        $pengajuan = PengajuanPetani::where('nik', $nik)
            ->where('musim_tanam_id', $musimTanamId)
            ->where('periode_id', $periodeId)
            ->whereIn('status', ['menunggu_verifikasi', 'perlu_perbaikan', 'disetujui']);

        if ($exceptPengajuanId) {
            $pengajuan->where('id', '!=', $exceptPengajuanId);
        }

        if ($pengajuan->exists()) {
            return true;
        }

        return AlternatifMusim::where('musim_tanam_id', $musimTanamId)
            ->where('periode_id', $periodeId)
            ->whereHas('petani', fn ($q) => $q->where('nik', $nik))
            ->exists();
    }

    private function storePdf(Request $request, string $nik, int $musimId, int $periodeId, ?string $oldPath = null): ?string
    {
        if (!$request->hasFile('berkas_pdf')) {
            return $oldPath;
        }

        $filename = $nik . '_' . now()->format('YmdHis') . '.pdf';
        $path = $request->file('berkas_pdf')->storeAs(
            "berkas-pengajuan/{$periodeId}/{$musimId}",
            $filename,
            'local'
        );

        if ($oldPath && $oldPath !== $path && Storage::disk('local')->exists($oldPath)) {
            Storage::disk('local')->delete($oldPath);
        }

        return $path;
    }

    public function index(Request $request)
    {
        $query = PengajuanPetani::with([
            'penyuluh',
            'poktan.kecamatan',
            'musimTanam',
            'periode',
            'batchImport',
            'verifier',
            'c3Verifier',
            'alternatifMusim',
            'pupuk.jenisPupuk',
        ]);

        $penyuluhAktif = null;
        $wilayahPenyuluh = null;

        if (Auth::user()->role === 'penyuluh') {
            $penyuluhAktif = $this->currentPenyuluh();
            $query->where('penyuluh_id', $penyuluhAktif->id);
            $wilayahPenyuluh = $penyuluhAktif->penugasanAktif?->kecamatan;
        }

        if ($request->filled('search')) {
            $search = trim((string) $request->search);
            $query->where(function ($q) use ($search) {
                $q->where('nik', 'like', '%' . $search . '%')
                    ->orWhere('nama_petani', 'like', '%' . $search . '%')
                    ->orWhereHas('poktan', fn ($p) => $p->where('nama_poktan', 'like', '%' . $search . '%'));
            });
        }

        foreach (['status', 'musim_tanam_id', 'periode_id', 'asal_input', 'poktan_id'] as $field) {
            if ($request->filled($field)) {
                $query->where($field, $request->{$field});
            }
        }

        if (Auth::user()->role === 'admin') {
            if ($request->filled('kecamatan_id')) {
                $query->whereHas('poktan', fn ($q) => $q->where('kecamatan_id', $request->kecamatan_id));
            }
            if ($request->filled('penyuluh_id')) {
                $query->where('penyuluh_id', $request->penyuluh_id);
            }
        }

        $pengajuan = $query->orderByDesc('tanggal_pengajuan')->orderByDesc('id')->paginate(10)->withQueryString();
        $musimTanam = MusimTanam::orderBy('kode_musim')->get();
        $periode = Periode::orderByDesc('tahun')->get();
        $kecamatan = Auth::user()->role === 'admin' ? Kecamatan::orderBy('nama_kecamatan')->get() : collect();

        if (Auth::user()->role === 'admin') {
            $poktanOptions = Poktan::with('kecamatan')->where('status', 'aktif')->orderBy('nama_poktan')->get();
            $penyuluhOptions = Penyuluh::where('status', 'aktif')->orderBy('nama_penyuluh')->get();
        } else {
            $poktanOptions = Poktan::with('kecamatan')
                ->where('penyuluh_id', $penyuluhAktif->id)
                ->where('status', 'aktif')
                ->orderBy('nama_poktan')
                ->get();
            $penyuluhOptions = collect();
        }

        $statusBase = Auth::user()->role === 'penyuluh'
            ? PengajuanPetani::where('penyuluh_id', $penyuluhAktif->id)
            : PengajuanPetani::query();

        $statusCounts = [
            'semua' => (clone $statusBase)->count(),
            'menunggu_verifikasi' => (clone $statusBase)->where('status', 'menunggu_verifikasi')->count(),
            'perlu_perbaikan' => (clone $statusBase)->where('status', 'perlu_perbaikan')->count(),
            'disetujui' => (clone $statusBase)->where('status', 'disetujui')->count(),
            'ditolak' => (clone $statusBase)->where('status', 'ditolak')->count(),
        ];

        return view('pengajuan.index', compact(
            'pengajuan', 'musimTanam', 'periode', 'kecamatan', 'statusCounts', 'wilayahPenyuluh',
            'poktanOptions', 'penyuluhOptions'
        ));
    }

    public function create(Request $request)
    {
        $penyuluh = $this->currentPenyuluh();
        $poktan = Poktan::with('kecamatan')
            ->whereIn('id', $this->allowedPoktanIds($penyuluh))
            ->orderBy('nama_poktan')->get();
        $musimTanam = MusimTanam::orderBy('kode_musim')->get();
        $periode = Periode::orderByDesc('tahun')->get();
        $criteriaOptions = $this->criteriaOptions();
        $jenisPupuk = JenisPupuk::where('is_active', true)->orderBy('nama_pupuk')->get();
        $prefill = null;

        if ($request->filled('alternatif_id')) {
            $alternatif = AlternatifMusim::with(['petani', 'pupuk.jenisPupuk', 'poktan'])
                ->findOrFail($request->integer('alternatif_id'));

            if ((int) $alternatif->poktan?->penyuluh_id !== (int) $penyuluh->id) {
                abort(403, 'Alternatif tersebut bukan petani binaan Anda.');
            }

            $prefill = $alternatif;
        }

        return view('pengajuan.create', compact(
            'poktan', 'musimTanam', 'periode', 'criteriaOptions', 'jenisPupuk', 'prefill'
        ));
    }

    public function store(Request $request)
    {
        $penyuluh = $this->currentPenyuluh();
        $data = $this->validateInput($request);
        $this->ensurePoktanValid($penyuluh, (int) $data['poktan_id']);

        if ($this->duplicateExists($data['nik'], (int) $data['musim_tanam_id'], (int) $data['periode_id'])) {
            return back()->withInput()->withErrors([
                'nik' => 'Petani ini sudah memiliki pengajuan/alternatif pada Musim Tanam dan Tahun yang sama.',
            ]);
        }

        $pengajuan = DB::transaction(function () use ($request, $data, $penyuluh) {
            $path = $this->storePdf(
                $request,
                $data['nik'],
                (int) $data['musim_tanam_id'],
                (int) $data['periode_id']
            );

            $pengajuan = PengajuanPetani::create([
                'penyuluh_id' => $penyuluh->id,
                'poktan_id' => $data['poktan_id'],
                'musim_tanam_id' => $data['musim_tanam_id'],
                'periode_id' => $data['periode_id'],
                'batch_import_id' => null,
                'asal_input' => 'manual',
                'nik' => $data['nik'],
                'nama_petani' => $data['nama_petani'],
                'luas_lahan_ha' => $data['luas_lahan_ha'],
                'luas_lahan' => $data['luas_lahan'],
                'jenis_komoditas' => $data['jenis_komoditas'],
                'kelengkapan_berkas' => $data['kelengkapan_berkas'],
                'kelengkapan_berkas_verifikasi' => null,
                'berkas_persyaratan_path' => $path,
                'catatan_verifikasi_berkas' => null,
                'c3_verified_by' => null,
                'c3_verified_at' => null,
                'kebutuhan_pupuk' => $data['kebutuhan_pupuk'],
                'subsektor_pertanian' => $data['subsektor_pertanian'],
                'catatan_penyuluh' => $data['catatan_penyuluh'] ?? null,
                'status' => 'menunggu_verifikasi',
                'tanggal_pengajuan' => now(),
            ]);

            $this->pupukService->syncPengajuan($pengajuan, $data['pupuk_rows']);

            return $pengajuan;
        });

        return redirect()->route('pengajuan.index')
            ->with('success', 'Pengajuan berhasil dikirim. Nilai C1, C2, C4, dan C5 ditentukan otomatis dari data faktual.');
    }

    public function show(PengajuanPetani $pengajuan)
    {
        $this->ensureOwnedByPenyuluh($pengajuan);
        $pengajuan->load([
            'penyuluh.user', 'poktan.kecamatan', 'musimTanam', 'periode', 'batchImport',
            'verifier', 'c3Verifier', 'alternatifMusim.petani', 'pupuk.jenisPupuk',
        ]);

        $criteriaOptions = $this->criteriaOptions();
        return view('pengajuan.show', compact('pengajuan', 'criteriaOptions'));
    }

    public function edit(PengajuanPetani $pengajuan)
    {
        $this->ensureOwnedByPenyuluh($pengajuan);

        if (!in_array($pengajuan->status, ['menunggu_verifikasi', 'perlu_perbaikan'], true)) {
            return redirect()->route('pengajuan.show', $pengajuan)
                ->with('error', 'Pengajuan yang sudah disetujui atau ditolak tidak dapat diedit.');
        }

        $penyuluh = $this->currentPenyuluh();
        $poktan = Poktan::with('kecamatan')->whereIn('id', $this->allowedPoktanIds($penyuluh))->orderBy('nama_poktan')->get();
        $musimTanam = MusimTanam::orderBy('kode_musim')->get();
        $periode = Periode::orderByDesc('tahun')->get();
        $criteriaOptions = $this->criteriaOptions();
        $jenisPupuk = JenisPupuk::where('is_active', true)->orderBy('nama_pupuk')->get();
        $pengajuan->load('pupuk');

        return view('pengajuan.edit', compact('pengajuan', 'poktan', 'musimTanam', 'periode', 'criteriaOptions', 'jenisPupuk'));
    }

    public function update(Request $request, PengajuanPetani $pengajuan)
    {
        $this->ensureOwnedByPenyuluh($pengajuan);

        if (!in_array($pengajuan->status, ['menunggu_verifikasi', 'perlu_perbaikan'], true)) {
            abort(422, 'Pengajuan ini tidak dapat diedit lagi.');
        }

        $penyuluh = $this->currentPenyuluh();
        $data = $this->validateInput($request, $pengajuan);
        $this->ensurePoktanValid($penyuluh, (int) $data['poktan_id']);

        if ($this->duplicateExists($data['nik'], (int) $data['musim_tanam_id'], (int) $data['periode_id'], $pengajuan->id)) {
            return back()->withInput()->withErrors(['nik' => 'NIK ini sudah memiliki pengajuan/alternatif lain pada Musim/Tahun tersebut.']);
        }

        DB::transaction(function () use ($request, $pengajuan, $data) {
            $path = $this->storePdf(
                $request,
                $data['nik'],
                (int) $data['musim_tanam_id'],
                (int) $data['periode_id'],
                $pengajuan->berkas_persyaratan_path
            );

            $pengajuan->update([
                'poktan_id' => $data['poktan_id'],
                'musim_tanam_id' => $data['musim_tanam_id'],
                'periode_id' => $data['periode_id'],
                'nik' => $data['nik'],
                'nama_petani' => $data['nama_petani'],
                'luas_lahan_ha' => $data['luas_lahan_ha'],
                'luas_lahan' => $data['luas_lahan'],
                'jenis_komoditas' => $data['jenis_komoditas'],
                'kelengkapan_berkas' => $data['kelengkapan_berkas'],
                'kelengkapan_berkas_verifikasi' => null,
                'berkas_persyaratan_path' => $path,
                'catatan_verifikasi_berkas' => null,
                'c3_verified_by' => null,
                'c3_verified_at' => null,
                'kebutuhan_pupuk' => $data['kebutuhan_pupuk'],
                'subsektor_pertanian' => $data['subsektor_pertanian'],
                'catatan_penyuluh' => $data['catatan_penyuluh'] ?? null,
                'status' => 'menunggu_verifikasi',
                'catatan_admin' => null,
                'verified_by' => null,
                'tanggal_pengajuan' => now(),
                'tanggal_verifikasi' => null,
            ]);

            $this->pupukService->syncPengajuan($pengajuan, $data['pupuk_rows']);
        });

        return redirect()->route('pengajuan.show', $pengajuan)
            ->with('success', 'Pengajuan berhasil diperbarui dan dikirim kembali untuk verifikasi.');
    }

    public function berkas(PengajuanPetani $pengajuan)
    {
        $this->ensureOwnedByPenyuluh($pengajuan);

        if (!$pengajuan->berkas_persyaratan_path || !Storage::disk('local')->exists($pengajuan->berkas_persyaratan_path)) {
            abort(404, 'Berkas PDF belum tersedia.');
        }

        return response()->file(Storage::disk('local')->path($pengajuan->berkas_persyaratan_path), [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="' . $pengajuan->nik . '.pdf"',
        ]);
    }

    private function nextKodeAlternatif(int $musimTanamId, int $periodeId): string
    {
        $max = AlternatifMusim::where('musim_tanam_id', $musimTanamId)
            ->where('periode_id', $periodeId)
            ->lockForUpdate()
            ->pluck('kode_alternatif')
            ->map(function ($kode) {
                return preg_match('/^A(\d+)$/i', (string) $kode, $m) ? (int) $m[1] : 0;
            })->max();

        return 'A' . (($max ?? 0) + 1);
    }

    private function approveLocked(
        PengajuanPetani $pengajuan,
        ?string $catatanAdmin = null
    ): AlternatifMusim {
        if (!in_array($pengajuan->status, ['menunggu_verifikasi', 'perlu_perbaikan'], true)) {
            throw ValidationException::withMessages(['pengajuan' => "Pengajuan {$pengajuan->nik} sudah diproses sebelumnya."]);
        }

        if (!$pengajuan->musim_tanam_id || !$pengajuan->periode_id || !$pengajuan->poktan_id) {
            throw ValidationException::withMessages(['pengajuan' => "Pengajuan {$pengajuan->nik} belum memiliki Musim, Tahun, atau Poktan."]);
        }

        $poktan = Poktan::with('kecamatan')->find($pengajuan->poktan_id);
        if (!$poktan || !$poktan->kecamatan_id) {
            throw ValidationException::withMessages(['pengajuan' => "Poktan pengajuan {$pengajuan->nik} tidak memiliki Kecamatan yang valid."]);
        }

        if (!$pengajuan->c3_terverifikasi) {
            throw ValidationException::withMessages([
                'pengajuan' => "Berkas C3 untuk NIK {$pengajuan->nik} belum diverifikasi Admin. Buka detail pengajuan dan simpan verifikasi berkas terlebih dahulu.",
            ]);
        }

        $kelengkapanBerkasFinal = $this->penilaian->canonical(
            'C3',
            $pengajuan->kelengkapan_berkas_verifikasi
        );

        $existingAlternative = AlternatifMusim::where('musim_tanam_id', $pengajuan->musim_tanam_id)
            ->where('periode_id', $pengajuan->periode_id)
            ->whereHas('petani', fn ($q) => $q->where('nik', $pengajuan->nik))
            ->exists();

        if ($existingAlternative) {
            throw ValidationException::withMessages(['pengajuan' => "Petani {$pengajuan->nik} sudah menjadi alternatif pada Musim/Tahun tersebut."]);
        }

        $petani = Petani::where('nik', $pengajuan->nik)->lockForUpdate()->first();

        if (!$petani) {
            $petani = Petani::create([
                'nik' => $pengajuan->nik,
                'nama_petani' => $pengajuan->nama_petani,
                'poktan_id' => $pengajuan->poktan_id,
                'luas_lahan' => $pengajuan->luas_lahan,
                'jenis_komoditas' => $pengajuan->jenis_komoditas,
                'kelengkapan_berkas' => $kelengkapanBerkasFinal,
                'kebutuhan_pupuk' => $pengajuan->kebutuhan_pupuk,
                'subsektor_pertanian' => $pengajuan->subsektor_pertanian,
            ]);
        } else {
            $petani->update([
                'nama_petani' => $pengajuan->nama_petani,
                'poktan_id' => $pengajuan->poktan_id,
            ]);
        }

        $alternatif = AlternatifMusim::create([
            'petani_id' => $petani->id,
            'poktan_id' => $pengajuan->poktan_id,
            'musim_tanam_id' => $pengajuan->musim_tanam_id,
            'periode_id' => $pengajuan->periode_id,
            'pengajuan_petani_id' => $pengajuan->id,
            'kode_alternatif' => $this->nextKodeAlternatif($pengajuan->musim_tanam_id, $pengajuan->periode_id),
            'luas_lahan_ha' => $pengajuan->luas_lahan_ha,
            'luas_lahan' => $pengajuan->luas_lahan,
            'jenis_komoditas' => $pengajuan->jenis_komoditas,
            'kelengkapan_berkas' => $kelengkapanBerkasFinal,
            'berkas_persyaratan_path' => $pengajuan->berkas_persyaratan_path,
            'kebutuhan_pupuk' => $pengajuan->kebutuhan_pupuk,
            'subsektor_pertanian' => $pengajuan->subsektor_pertanian,
        ]);

        $pengajuan->loadMissing('pupuk');
        $rows = $pengajuan->pupuk->pluck('jumlah_kg', 'jenis_pupuk_id')->map(fn ($v) => (float) $v)->all();
        if ($rows) {
            $this->pupukService->syncAlternatif($alternatif, $rows);
        }

        $pengajuan->update([
            'status' => 'disetujui',
            'catatan_admin' => $catatanAdmin,
            'verified_by' => Auth::id(),
            'tanggal_verifikasi' => now(),
        ]);

        $this->audit->log(
            'pengajuan',
            'approve',
            $pengajuan,
            'Pengajuan disetujui dan dibuat menjadi Alternatif Musim.',
            ['status' => 'menunggu_verifikasi/perlu_perbaikan'],
            ['status' => 'disetujui', 'alternatif_musim_id' => $alternatif->id, 'c3_final' => $kelengkapanBerkasFinal]
        );

        return $alternatif;
    }

    public function verifyC3(Request $request, PengajuanPetani $pengajuan)
    {
        if (!in_array($pengajuan->status, ['menunggu_verifikasi', 'perlu_perbaikan'], true)) {
            abort(422, 'C3 hanya dapat diverifikasi pada pengajuan yang masih dalam proses verifikasi.');
        }

        if (!$pengajuan->berkas_persyaratan_path || !Storage::disk('local')->exists($pengajuan->berkas_persyaratan_path)) {
            throw ValidationException::withMessages([
                'kelengkapan_berkas_verifikasi' => 'PDF persyaratan belum tersedia sehingga C3 belum dapat diverifikasi.',
            ]);
        }

        $options = $this->criteriaOptions();
        $validated = $request->validate([
            'kelengkapan_berkas_verifikasi' => ['required', Rule::in($options['C3'])],
            'catatan_verifikasi_berkas' => ['nullable', 'string', 'max:2000'],
        ]);

        $old = [
            'kelengkapan_berkas_verifikasi' => $pengajuan->kelengkapan_berkas_verifikasi,
            'c3_verified_by' => $pengajuan->c3_verified_by,
            'c3_verified_at' => optional($pengajuan->c3_verified_at)->toDateTimeString(),
        ];

        $pengajuan->update([
            'kelengkapan_berkas_verifikasi' => $this->penilaian->canonical('C3', $validated['kelengkapan_berkas_verifikasi']),
            'catatan_verifikasi_berkas' => $validated['catatan_verifikasi_berkas'] ?? null,
            'c3_verified_by' => Auth::id(),
            'c3_verified_at' => now(),
        ]);

        $this->audit->log(
            'pengajuan',
            'verify_c3',
            $pengajuan,
            'Admin memverifikasi kelengkapan berkas (C3).',
            $old,
            [
                'kelengkapan_berkas_diajukan' => $pengajuan->kelengkapan_berkas,
                'kelengkapan_berkas_verifikasi' => $pengajuan->kelengkapan_berkas_verifikasi,
            ]
        );

        return redirect()->route('pengajuan.show', $pengajuan)
            ->with('success', 'Verifikasi C3 berhasil disimpan. Pengajuan sekarang dapat disetujui.');
    }

    public function approve(Request $request, PengajuanPetani $pengajuan)
    {
        $validated = $request->validate([
            'catatan_admin' => ['nullable', 'string', 'max:2000'],
        ]);

        DB::transaction(function () use ($pengajuan, $validated) {
            $locked = PengajuanPetani::whereKey($pengajuan->id)->lockForUpdate()->firstOrFail();
            $this->approveLocked(
                $locked,
                $validated['catatan_admin'] ?? null
            );
        });

        return redirect()->route('pengajuan.show', $pengajuan)
            ->with('success', 'Pengajuan disetujui dan masuk ke Data Alternatif. Data tersebut sudah sah untuk dihitung VIKOR.');
    }

    public function requestRevision(Request $request, PengajuanPetani $pengajuan)
    {
        $data = $request->validate(['catatan_admin' => ['required', 'string', 'max:2000']]);

        if ($pengajuan->status !== 'menunggu_verifikasi') {
            abort(422, 'Hanya pengajuan yang menunggu verifikasi yang dapat dikembalikan.');
        }

        $pengajuan->update([
            'status' => 'perlu_perbaikan',
            'catatan_admin' => $data['catatan_admin'],
            'verified_by' => Auth::id(),
            'tanggal_verifikasi' => now(),
        ]);

        $this->audit->log('pengajuan', 'request_revision', $pengajuan, 'Pengajuan dikembalikan untuk perbaikan.', null, ['catatan_admin' => $data['catatan_admin']]);

        return redirect()->route('pengajuan.show', $pengajuan)->with('success', 'Pengajuan dikembalikan untuk perbaikan.');
    }

    public function reject(Request $request, PengajuanPetani $pengajuan)
    {
        $data = $request->validate(['catatan_admin' => ['required', 'string', 'max:2000']]);

        if (!in_array($pengajuan->status, ['menunggu_verifikasi', 'perlu_perbaikan'], true)) {
            abort(422, 'Pengajuan ini sudah diproses sebelumnya.');
        }

        $pengajuan->update([
            'status' => 'ditolak',
            'catatan_admin' => $data['catatan_admin'],
            'verified_by' => Auth::id(),
            'tanggal_verifikasi' => now(),
        ]);

        $this->audit->log('pengajuan', 'reject', $pengajuan, 'Pengajuan ditolak.', null, ['catatan_admin' => $data['catatan_admin']]);

        return redirect()->route('pengajuan.show', $pengajuan)->with('success', 'Pengajuan petani ditolak.');
    }


}
