<?php

namespace App\Http\Controllers;

use App\Models\AlokasiKecamatan;
use App\Models\AlternatifMusim;
use App\Models\AnpHasil;
use App\Models\Kecamatan;
use App\Models\Kriteria;
use App\Models\MusimTanam;
use App\Models\Periode;
use App\Models\Penyuluh;
use App\Models\VikorHasil;
use App\Models\VikorRiwayat;
use App\Services\AuditLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class VikorController extends Controller
{
    public function __construct(private readonly AuditLogger $audit)
    {
    }

    /*
    |--------------------------------------------------------------------------
    | HALAMAN UTAMA VIKOR
    |--------------------------------------------------------------------------
    */

    public function index(Request $request)
    {
        $musimTanam = MusimTanam::orderBy('kode_musim')->get();
        $periode = Periode::orderByDesc('tahun')->get();
        $kecamatan = Kecamatan::orderBy('nama_kecamatan')->get();

        return view(
            'vikor.index',
            compact('musimTanam', 'periode', 'kecamatan')
        );
    }


    /*
    |--------------------------------------------------------------------------
    | VALIDASI SCOPE
    |--------------------------------------------------------------------------
    */

    private function validateScope(
        Request $request
    ): array {

        $validated = $request->validate([
            'musim_tanam_id' => [
                'required',
                'exists:musim_tanam,id',
            ],

            'periode_id' => [
                'required',
                'exists:periode,id',
            ],

            'kecamatan_id' => [
                'required',
                'exists:kecamatan,id',
            ],
        ], [
            'musim_tanam_id.required' =>
                'Musim Tanam wajib dipilih.',

            'periode_id.required' =>
                'Tahun/Periode wajib dipilih.',

            'kecamatan_id.required' =>
                'Kecamatan wajib dipilih.',
        ]);


        return [
            'musim_tanam_id' =>
                (int) $validated['musim_tanam_id'],

            'periode_id' =>
                (int) $validated['periode_id'],

            'kecamatan_id' =>
                (int) $validated['kecamatan_id'],
        ];
    }


    /*
    |--------------------------------------------------------------------------
    | PROSES / PREVIEW VIKOR
    |--------------------------------------------------------------------------
    */

    public function proses(Request $request)
    {
        if ($request->isMethod('get')) {

            return redirect()
                ->route('vikor.index');
        }


        $scope =
            $this->validateScope($request);


        try {

            $hasilPerhitungan =
                $this->hitungVikorData(
                    $scope['musim_tanam_id'],
                    $scope['periode_id'],
                    $scope['kecamatan_id']
                );


            return view(
                'vikor.proses',
                $hasilPerhitungan
            );

        } catch (\Throwable $e) {

            return redirect()
                ->route(
                    'vikor.index',
                    $scope
                )
                ->with(
                    'error',
                    $e->getMessage()
                );
        }
    }


    /*
    |--------------------------------------------------------------------------
    | HITUNG & SIMPAN
    |--------------------------------------------------------------------------
    */

    public function hitung(Request $request)
    {
        $scope =
            $this->validateScope($request);


        DB::beginTransaction();


        try {

            $hasilPerhitungan =
                $this->hitungVikorData(
                    $scope['musim_tanam_id'],
                    $scope['periode_id'],
                    $scope['kecamatan_id']
                );


            /*
            |--------------------------------------------------------------------------
            | HAPUS HASIL HANYA UNTUK SCOPE YANG DIHITUNG
            |--------------------------------------------------------------------------
            |
            | Tidak lagi VikorHasil::query()->delete()
            |
            | Jadi hasil Musim/Tahun/Kecamatan lain tetap aman.
            |
            */

            $alternatifIds =
                collect(
                    $hasilPerhitungan['alternatif']
                )
                ->map(
                    fn ($item) =>
                        $item['alternatif']->id
                )
                ->values()
                ->all();


            if (!empty($alternatifIds)) {

                VikorHasil::whereIn(
                    'alternatif_musim_id',
                    $alternatifIds
                )->delete();
            }


            /*
            |--------------------------------------------------------------------------
            | SIMPAN HASIL
            |--------------------------------------------------------------------------
            */

            foreach (
                $hasilPerhitungan['alternatif']
                as $item
            ) {

                VikorHasil::create([
                    'petani_id' =>
                        $item['petani']->id,

                    'alternatif_musim_id' =>
                        $item['alternatif']->id,

                    'nilai_s' =>
                        $item['nilai_s'],

                    'nilai_r' =>
                        $item['nilai_r'],

                    'nilai_qs' =>
                        $item['nilai_qs'],

                    'nilai_qr' =>
                        $item['nilai_qr'],

                    'nilai_q' =>
                        $item['nilai_q'],

                    'ranking' =>
                        $item['ranking'],
                ]);
            }

            /*
            |--------------------------------------------------------------------------
            | SIMPAN SNAPSHOT RIWAYAT PERHITUNGAN
            |--------------------------------------------------------------------------
            | Hasil aktif tetap berada di vikor_hasil. Setiap proses juga disalin
            | ke vikor_riwayat agar perhitungan lama tidak hilang saat dihitung ulang.
            */
            $runUuid = (string) Str::uuid();
            $calculatedAt = now();

            foreach ($hasilPerhitungan['alternatif'] as $item) {
                VikorRiwayat::create([
                    'run_uuid' => $runUuid,
                    'user_id' => Auth::id(),
                    'musim_tanam_id' => $scope['musim_tanam_id'],
                    'periode_id' => $scope['periode_id'],
                    'kecamatan_id' => $scope['kecamatan_id'],
                    'alternatif_musim_id' => $item['alternatif']->id,
                    'petani_id' => $item['petani']->id,
                    'kode_alternatif' => $item['alternatif']->kode_alternatif,
                    'nik' => $item['petani']->nik,
                    'nama_petani' => $item['petani']->nama_petani,
                    'nama_poktan' => optional($item['alternatif']->poktan)->nama_poktan,
                    'nilai_s' => $item['nilai_s'],
                    'nilai_r' => $item['nilai_r'],
                    'nilai_qs' => $item['nilai_qs'],
                    'nilai_qr' => $item['nilai_qr'],
                    'nilai_q' => $item['nilai_q'],
                    'ranking' => $item['ranking'],
                    'kuota_penerima' => (int) $hasilPerhitungan['kuota'],
                    'status_kuota' => $item['status_kuota'],
                    'calculated_at' => $calculatedAt,
                ]);
            }

            $this->audit->log(
                'vikor',
                'calculate',
                null,
                'Perhitungan VIKOR disimpan untuk ' .
                    $hasilPerhitungan['musimTanam']->kode_musim . ' / ' .
                    $hasilPerhitungan['periode']->tahun . ' / Kecamatan ' .
                    $hasilPerhitungan['kecamatan']->nama_kecamatan .
                    '. Run: ' . $runUuid,
                null,
                [
                    'run_uuid' => $runUuid,
                    'jumlah_alternatif' => count($hasilPerhitungan['alternatif']),
                    'kuota' => (int) $hasilPerhitungan['kuota'],
                ]
            );

            DB::commit();


            return redirect()
                ->route(
                    'ranking.index',
                    [
                        'musim_tanam_id' =>
                            $scope['musim_tanam_id'],

                        'periode_id' =>
                            $scope['periode_id'],

                        'kecamatan_id' =>
                            $scope['kecamatan_id'],
                    ]
                )
                ->with(
                    'success',
                    'Perhitungan VIKOR berhasil disimpan. Ranking telah disesuaikan dengan kuota Kecamatan.'
                );

        } catch (\Throwable $e) {

            DB::rollBack();


            return redirect()
                ->route(
                    'vikor.index',
                    $scope
                )
                ->with(
                    'error',
                    'Perhitungan VIKOR gagal: ' .
                    $e->getMessage()
                );
        }
    }


    /*
    |--------------------------------------------------------------------------
    | HASIL RANKING
    |--------------------------------------------------------------------------
    |
    | Admin:
    | melihat seluruh hasil scope Kecamatan.
    |
    | Penyuluh:
    | hanya melihat petani Poktan binaannya,
    | tetapi nomor ranking tetap Ranking Kecamatan.
    |
    */

    public function ranking(Request $request)
    {
        $request->validate([
            'musim_tanam_id' => ['nullable', 'exists:musim_tanam,id'],
            'periode_id' => ['nullable', 'exists:periode,id'],
            'kecamatan_id' => ['nullable', 'exists:kecamatan,id'],
        ]);

        $isPenyuluh = Auth::user()->role === 'penyuluh';
        $musimTanam = MusimTanam::orderBy('kode_musim')->get();
        $periode = Periode::orderByDesc('tahun')->get();
        $penyuluh = null;
        $wilayahPenyuluh = null;

        if ($isPenyuluh) {
            $penyuluh = Penyuluh::with('penugasanAktif.kecamatan')
                ->where('user_id', Auth::id())
                ->first();
            $wilayahPenyuluh = $penyuluh?->penugasanAktif?->kecamatan;
            $kecamatan = $wilayahPenyuluh ? collect([$wilayahPenyuluh]) : collect();
        } else {
            $kecamatan = Kecamatan::orderBy('nama_kecamatan')->get();
        }

        $hasil = collect();
        $alokasi = null;
        $musimDipilih = null;
        $periodeDipilih = null;
        $kecamatanDipilih = null;
        $totalKandidatKecamatan = 0;

        // Penyuluh tidak memilih Kecamatan. Wilayah selalu mengikuti penugasan aktif.
        $scopeLengkap = $request->filled('musim_tanam_id')
            && $request->filled('periode_id')
            && ($isPenyuluh ? (bool) $wilayahPenyuluh : $request->filled('kecamatan_id'));

        if ($scopeLengkap) {
            $kecamatanId = $isPenyuluh
                ? (int) $wilayahPenyuluh->id
                : (int) $request->kecamatan_id;

            $musimDipilih = MusimTanam::find($request->musim_tanam_id);
            $periodeDipilih = Periode::find($request->periode_id);
            $kecamatanDipilih = Kecamatan::find($kecamatanId);

            $alokasi = AlokasiKecamatan::where('musim_tanam_id', $request->musim_tanam_id)
                ->where('periode_id', $request->periode_id)
                ->where('kecamatan_id', $kecamatanId)
                ->first();

            $baseQuery = VikorHasil::whereNotNull('alternatif_musim_id')
                ->whereHas('alternatifMusim', function ($query) use ($request, $kecamatanId) {
                    $query->where('musim_tanam_id', $request->musim_tanam_id)
                        ->where('periode_id', $request->periode_id)
                        ->whereHas('poktan', fn ($poktanQuery) => $poktanQuery->where('kecamatan_id', $kecamatanId));
                });

            $totalKandidatKecamatan = (clone $baseQuery)->count();

            if ($isPenyuluh && $penyuluh) {
                $baseQuery->whereHas('alternatifMusim.poktan', fn ($query) =>
                    $query->where('penyuluh_id', $penyuluh->id));
            }

            $hasil = $baseQuery
                ->with([
                    'petani',
                    'alternatifMusim.petani',
                    'alternatifMusim.poktan.kecamatan',
                    'alternatifMusim.musimTanam',
                    'alternatifMusim.periode',
                ])
                ->orderBy('ranking')
                ->paginate(10)
                ->withQueryString();
        }

        return view('vikor.ranking', compact(
            'hasil', 'musimTanam', 'periode', 'kecamatan', 'alokasi',
            'musimDipilih', 'periodeDipilih', 'kecamatanDipilih',
            'totalKandidatKecamatan', 'scopeLengkap', 'wilayahPenyuluh'
        ));
    }

    public function riwayat(Request $request)
    {
        $runs = VikorRiwayat::query()
            ->join('musim_tanam as mt', 'mt.id', '=', 'vikor_riwayat.musim_tanam_id')
            ->join('periode as p', 'p.id', '=', 'vikor_riwayat.periode_id')
            ->join('kecamatan as k', 'k.id', '=', 'vikor_riwayat.kecamatan_id')
            ->select([
                'vikor_riwayat.run_uuid',
                'vikor_riwayat.musim_tanam_id',
                'vikor_riwayat.periode_id',
                'vikor_riwayat.kecamatan_id',
                'mt.kode_musim',
                'p.tahun',
                'k.nama_kecamatan',
            ])
            ->selectRaw('COUNT(*) as total_alternatif')
            ->selectRaw('MAX(vikor_riwayat.kuota_penerima) as kuota_penerima')
            ->selectRaw('MAX(vikor_riwayat.calculated_at) as calculated_at')
            ->groupBy(
                'vikor_riwayat.run_uuid',
                'vikor_riwayat.musim_tanam_id',
                'vikor_riwayat.periode_id',
                'vikor_riwayat.kecamatan_id',
                'mt.kode_musim',
                'p.tahun',
                'k.nama_kecamatan'
            )
            ->orderByDesc('calculated_at')
            ->paginate(20)
            ->withQueryString();

        return view('vikor.riwayat', compact('runs'));
    }


    public function hapusRiwayat(string $runUuid)
    {
        DB::transaction(function () use ($runUuid) {
            VikorRiwayat::where('run_uuid', $runUuid)->delete();
        });

        return redirect()
            ->route('vikor.riwayat')
            ->with('success', 'Riwayat VIKOR berhasil dihapus.');
    }

    public function riwayatDetail(string $runUuid)
    {
        $first = VikorRiwayat::where('run_uuid', $runUuid)->firstOrFail();
        $first->load(['alternatifMusim.musimTanam', 'alternatifMusim.periode', 'alternatifMusim.poktan.kecamatan']);

        $hasil = VikorRiwayat::where('run_uuid', $runUuid)
            ->with(['petani', 'alternatifMusim.poktan.kecamatan'])
            ->orderBy('ranking')
            ->paginate(20)
            ->withQueryString();

        return view('vikor.riwayat_detail', compact('first', 'hasil', 'runUuid'));
    }

    /*
    |--------------------------------------------------------------------------
    | PRIVATE: PERHITUNGAN VIKOR
    |--------------------------------------------------------------------------
    |
    | Metode tetap mengikuti perhitungan lama:
    |
    | 1. C1-C5 dikonversi ke skor subkriteria
    | 2. Hitung MAX, MIN, MAX-MIN
    | 3. Normalisasi:
    |
    | COST:
    | (x - min) / (max - min)
    |
    | BENEFIT:
    | (max - x) / (max - min)
    |
    | 4. Normalisasi × Bobot ANP
    | 5. S = SUM
    | 6. R = MAX
    | 7. v = 0.5
    | 8. QS
    | 9. QR
    | 10. Q = QS + QR
    | 11. Q terkecil = ranking terbaik
    |
    */

    private function hitungVikorData(
        int $musimTanamId,
        int $periodeId,
        int $kecamatanId
    ): array {

        /*
        |--------------------------------------------------------------------------
        | MASTER SCOPE
        |--------------------------------------------------------------------------
        */

        $musimTanam =
            MusimTanam::findOrFail(
                $musimTanamId
            );


        $periode =
            Periode::findOrFail(
                $periodeId
            );


        $kecamatan =
            Kecamatan::findOrFail(
                $kecamatanId
            );


        /*
        |--------------------------------------------------------------------------
        | KUOTA KECAMATAN
        |--------------------------------------------------------------------------
        */

        $alokasi =
            AlokasiKecamatan::where(
                'musim_tanam_id',
                $musimTanamId
            )
            ->where(
                'periode_id',
                $periodeId
            )
            ->where(
                'kecamatan_id',
                $kecamatanId
            )
            ->first();


        if (!$alokasi) {

            throw new \Exception(
                'Kuota penerima untuk Kecamatan ' .
                $kecamatan->nama_kecamatan .
                ', ' .
                $musimTanam->nama_musim .
                ' Tahun ' .
                $periode->tahun .
                ' belum diatur pada menu Periode & Wilayah.'
            );
        }


        $kuota =
            (int) $alokasi->kuota_penerima;


        /*
        |--------------------------------------------------------------------------
        | KRITERIA C1-C5
        |--------------------------------------------------------------------------
        */

        $kriteria =
            Kriteria::with('subkriteria')
            ->whereIn(
                'kode_kriteria',
                [
                    'C1',
                    'C2',
                    'C3',
                    'C4',
                    'C5',
                ]
            )
            ->orderBy('id')
            ->get();


        if ($kriteria->count() !== 5) {

            throw new \Exception(
                'Kriteria C1 sampai C5 belum lengkap.'
            );
        }


        /*
        |--------------------------------------------------------------------------
        | BOBOT ANP
        |--------------------------------------------------------------------------
        */

        $hasilANP =
            AnpHasil::all()
            ->keyBy('kriteria_id');


        $bobot = [];


        foreach ($kriteria as $krit) {

            if (
                !isset(
                    $hasilANP[$krit->id]
                )
            ) {

                throw new \Exception(
                    'Bobot ANP untuk ' .
                    $krit->kode_kriteria .
                    ' belum tersedia. Hitung ANP terlebih dahulu.'
                );
            }


            $bobot[
                $krit->kode_kriteria
            ] =
                (float)
                $hasilANP[$krit->id]
                    ->bobot;
        }


        if (array_sum($bobot) <= 0) {

            throw new \Exception(
                'Total bobot ANP tidak valid.'
            );
        }


        /*
        |--------------------------------------------------------------------------
        | ALTERNATIF SESUAI SCOPE
        |--------------------------------------------------------------------------
        */

        $dataAlternatif =
            AlternatifMusim::with([
                'petani',
                'poktan.kecamatan',
            ])
            ->where(
                'musim_tanam_id',
                $musimTanamId
            )
            ->where(
                'periode_id',
                $periodeId
            )
            ->whereHas(
                'poktan',
                function ($query) use ($kecamatanId) {

                    $query->where(
                        'kecamatan_id',
                        $kecamatanId
                    );
                }
            )
            ->orderBy('id')
            ->get();


        if ($dataAlternatif->isEmpty()) {

            throw new \Exception(
                'Belum ada Alternatif Musim untuk ' .
                $kecamatan->nama_kecamatan .
                ', ' .
                $musimTanam->nama_musim .
                ' Tahun ' .
                $periode->tahun .
                '.'
            );
        }


        /*
        |--------------------------------------------------------------------------
        | KONVERSI C1-C5 KE SKOR
        |--------------------------------------------------------------------------
        */

        $alternatif = [];


        foreach (
            $dataAlternatif as
            $index => $item
        ) {

            if (!$item->petani) {

                throw new \Exception(
                    'Alternatif ' .
                    $item->kode_alternatif .
                    ' tidak memiliki data Petani.'
                );
            }


            $nilaiAsli = [];

            $skor = [];


            foreach ($kriteria as $krit) {

                $field =
                    $this->fieldAlternatif(
                        $krit->kode_kriteria
                    );


                if (!$field) {

                    throw new \Exception(
                        'Mapping untuk ' .
                        $krit->kode_kriteria .
                        ' tidak ditemukan.'
                    );
                }


                $nilai =
                    $item->{$field};


                $nilaiAsli[
                    $krit->kode_kriteria
                ] =
                    $nilai;


                $skor[
                    $krit->kode_kriteria
                ] =
                    $this->ambilSkorSubkriteria(
                        $krit,
                        $nilai
                    );
            }


            $alternatif[] = [
                'urutan_asli' =>
                    $index,

                'alternatif' =>
                    $item,

                'petani' =>
                    $item->petani,

                'kode' =>
                    $item->kode_alternatif,

                'nilai_asli' =>
                    $nilaiAsli,

                'skor' =>
                    $skor,

                'normalisasi' =>
                    [],

                'terbobot' =>
                    [],

                'nilai_s' =>
                    0.0,

                'nilai_r' =>
                    0.0,

                'nilai_qs' =>
                    0.0,

                'nilai_qr' =>
                    0.0,

                'nilai_q' =>
                    0.0,

                'ranking' =>
                    null,

                'status_kuota' =>
                    null,
            ];
        }


        /*
        |--------------------------------------------------------------------------
        | MAX, MIN, SELISIH
        |--------------------------------------------------------------------------
        */

        $nilaiMax = [];

        $nilaiMin = [];

        $selisih = [];


        foreach ($kriteria as $krit) {

            $kode =
                $krit->kode_kriteria;


            $daftarNilai =
                array_map(
                    fn ($item) =>
                        $item['skor'][$kode],
                    $alternatif
                );


            $nilaiMax[$kode] =
                max($daftarNilai);


            $nilaiMin[$kode] =
                min($daftarNilai);


            $selisih[$kode] =
                $nilaiMax[$kode] -
                $nilaiMin[$kode];
        }


        /*
        |--------------------------------------------------------------------------
        | NORMALISASI + TERBOBOT + S + R
        |--------------------------------------------------------------------------
        */

        foreach (
            $alternatif as
            $index => $item
        ) {

            $nilaiTerbobot = [];


            foreach ($kriteria as $krit) {

                $kode =
                    $krit->kode_kriteria;


                $x =
                    $item['skor'][$kode];


                $range =
                    $selisih[$kode];


                if ($range == 0) {

                    $r = 0.0;

                } elseif (
                    strtolower(
                        trim(
                            $krit->atribut
                        )
                    ) === 'cost'
                ) {

                    $r =
                        ($x - $nilaiMin[$kode])
                        /
                        $range;

                } else {

                    $r =
                        ($nilaiMax[$kode] - $x)
                        /
                        $range;
                }


                $alternatif[
                    $index
                ]['normalisasi'][$kode] =
                    $r;


                $nilaiBobot =
                    $r *
                    $bobot[$kode];


                $alternatif[
                    $index
                ]['terbobot'][$kode] =
                    $nilaiBobot;


                $nilaiTerbobot[] =
                    $nilaiBobot;
            }


            $alternatif[
                $index
            ]['nilai_s'] =
                array_sum(
                    $nilaiTerbobot
                );


            $alternatif[
                $index
            ]['nilai_r'] =
                max(
                    $nilaiTerbobot
                );
        }


        /*
        |--------------------------------------------------------------------------
        | S MAX / MIN DAN R MAX / MIN
        |--------------------------------------------------------------------------
        */

        $daftarS =
            array_column(
                $alternatif,
                'nilai_s'
            );


        $daftarR =
            array_column(
                $alternatif,
                'nilai_r'
            );


        $sPlus =
            max($daftarS);


        $sMinus =
            min($daftarS);


        $rPlus =
            max($daftarR);


        $rMinus =
            min($daftarR);


        /*
        |--------------------------------------------------------------------------
        | HITUNG Q
        |--------------------------------------------------------------------------
        */

        $v = 0.5;


        foreach (
            $alternatif as
            $index => $item
        ) {

            if (
                ($sPlus - $sMinus) == 0
            ) {

                $qs = 0.0;

            } else {

                $qs =
                    $v *
                    (
                        (
                            $item['nilai_s'] -
                            $sMinus
                        )
                        /
                        (
                            $sPlus -
                            $sMinus
                        )
                    );
            }


            if (
                ($rPlus - $rMinus) == 0
            ) {

                $qr = 0.0;

            } else {

                $qr =
                    (1 - $v) *
                    (
                        (
                            $item['nilai_r'] -
                            $rMinus
                        )
                        /
                        (
                            $rPlus -
                            $rMinus
                        )
                    );
            }


            $alternatif[
                $index
            ]['nilai_qs'] =
                $qs;


            $alternatif[
                $index
            ]['nilai_qr'] =
                $qr;


            $alternatif[
                $index
            ]['nilai_q'] =
                $qs + $qr;
        }


        /*
        |--------------------------------------------------------------------------
        | RANKING
        |--------------------------------------------------------------------------
        */

        $urutanRanking =
            array_keys(
                $alternatif
            );


        usort(
            $urutanRanking,
            function ($a, $b) use ($alternatif) {

                $qA =
                    $alternatif[$a]['nilai_q'];

                $qB =
                    $alternatif[$b]['nilai_q'];


                /*
                | Jika Q sama, gunakan urutan data awal.
                */
                if (
                    abs($qA - $qB)
                    <
                    0.000000000001
                ) {

                    return
                        $alternatif[$a]['urutan_asli']
                        <=>
                        $alternatif[$b]['urutan_asli'];
                }


                return $qA <=> $qB;
            }
        );


        foreach (
            $urutanRanking as
            $posisi =>
            $indexAlternatif
        ) {

            $ranking =
                $posisi + 1;


            $alternatif[
                $indexAlternatif
            ]['ranking'] =
                $ranking;


            /*
            |--------------------------------------------------------------------------
            | KUOTA HANYA SEBAGAI CUTOFF
            |--------------------------------------------------------------------------
            |
            | VIKOR tidak diubah.
            |
            | Ranking <= Kuota = Masuk Kuota.
            |
            */

            $alternatif[
                $indexAlternatif
            ]['status_kuota'] =
                $ranking <= $kuota
                    ? 'masuk_kuota'
                    : 'di_luar_kuota';
        }


        /*
        |--------------------------------------------------------------------------
        | RANKING TERURUT
        |--------------------------------------------------------------------------
        */

        $ranking =
            $alternatif;


        usort(
            $ranking,
            fn ($a, $b) =>
                $a['ranking']
                <=>
                $b['ranking']
        );


        return [
            'kriteria' =>
                $kriteria,

            'bobot' =>
                $bobot,

            'alternatif' =>
                $alternatif,

            'nilaiMax' =>
                $nilaiMax,

            'nilaiMin' =>
                $nilaiMin,

            'selisih' =>
                $selisih,

            'sPlus' =>
                $sPlus,

            'sMinus' =>
                $sMinus,

            'rPlus' =>
                $rPlus,

            'rMinus' =>
                $rMinus,

            'v' =>
                $v,

            'ranking' =>
                $ranking,

            'musimTanam' =>
                $musimTanam,

            'periode' =>
                $periode,

            'kecamatan' =>
                $kecamatan,

            'alokasi' =>
                $alokasi,

            'kuota' =>
                $kuota,
        ];
    }


    /*
    |--------------------------------------------------------------------------
    | MAPPING C1-C5
    |--------------------------------------------------------------------------
    */

    private function fieldAlternatif(
        string $kodeKriteria
    ): ?string {

        $mapping = [
            'C1' =>
                'luas_lahan',

            'C2' =>
                'jenis_komoditas',

            'C3' =>
                'kelengkapan_berkas',

            'C4' =>
                'kebutuhan_pupuk',

            'C5' =>
                'subsektor_pertanian',
        ];


        return $mapping[
            strtoupper(
                trim(
                    $kodeKriteria
                )
            )
        ] ?? null;
    }


    /*
    |--------------------------------------------------------------------------
    | AMBIL SKOR SUBKRITERIA
    |--------------------------------------------------------------------------
    */

    private function ambilSkorSubkriteria(
        Kriteria $kriteria,
        $nilaiAlternatif
    ): float {

        if (
            $nilaiAlternatif === null ||
            trim(
                (string)
                $nilaiAlternatif
            ) === ''
        ) {

            throw new \Exception(
                'Nilai ' .
                $kriteria->kode_kriteria .
                ' pada salah satu alternatif masih kosong.'
            );
        }


        $nilaiNormal =
            $this->normalisasiTeks(
                $nilaiAlternatif
            );


        /*
        |--------------------------------------------------------------------------
        | EXACT MATCH
        |--------------------------------------------------------------------------
        */

        foreach (
            $kriteria->subkriteria
            as $sub
        ) {

            $namaNormal =
                $this->normalisasiTeks(
                    $sub->nama_subkriteria
                );


            if (
                $nilaiNormal ===
                $namaNormal
            ) {

                if (
                    $sub->bobot === null
                ) {

                    throw new \Exception(
                        'Bobot subkriteria "' .
                        $sub->nama_subkriteria .
                        '" belum tersedia.'
                    );
                }


                return
                    (float)
                    $sub->bobot;
            }
        }


        /*
        |--------------------------------------------------------------------------
        | FALLBACK TEXT MATCH
        |--------------------------------------------------------------------------
        */

        foreach (
            $kriteria->subkriteria
            as $sub
        ) {

            $namaNormal =
                $this->normalisasiTeks(
                    $sub->nama_subkriteria
                );


            if (
                str_contains(
                    $nilaiNormal,
                    $namaNormal
                )
                ||
                str_contains(
                    $namaNormal,
                    $nilaiNormal
                )
            ) {

                if (
                    $sub->bobot === null
                ) {

                    throw new \Exception(
                        'Bobot subkriteria "' .
                        $sub->nama_subkriteria .
                        '" belum tersedia.'
                    );
                }


                return
                    (float)
                    $sub->bobot;
            }
        }


        throw new \Exception(
            'Nilai "' .
            $nilaiAlternatif .
            '" pada ' .
            $kriteria->kode_kriteria .
            ' tidak cocok dengan Subkriteria.'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | NORMALISASI TEKS
    |--------------------------------------------------------------------------
    */

    private function normalisasiTeks(
        $text
    ): string {

        $text =
            mb_strtolower(
                trim(
                    (string)
                    $text
                )
            );


        $text =
            str_replace(
                [
                    '–',
                    '—',
                    '−',
                ],
                '-',
                $text
            );


        $text =
            preg_replace(
                '/\s+/u',
                ' ',
                $text
            );


        $text =
            preg_replace(
                '/\s*-\s*/u',
                '-',
                $text
            );


        return trim($text);
    }
}