<?php

namespace App\Http\Controllers;

use App\Models\AlokasiKecamatan;
use App\Models\AlternatifMusim;
use App\Models\PengajuanPetani;
use App\Models\Penyuluh;
use App\Models\Poktan;
use App\Models\VikorHasil;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | DASHBOARD
    |--------------------------------------------------------------------------
    */

    public function index(Request $request)
    {
        $user = Auth::user();


        /*
        |--------------------------------------------------------------------------
        | ADMIN
        |--------------------------------------------------------------------------
        */

        if ($user->role === 'admin') {

            return $this->dashboardAdmin();
        }


        /*
        |--------------------------------------------------------------------------
        | PENYULUH
        |--------------------------------------------------------------------------
        */

        if ($user->role === 'penyuluh') {

            return $this->dashboardPenyuluh();
        }


        abort(
            403,
            'Anda tidak memiliki akses ke dashboard.'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | DASHBOARD ADMIN
    |--------------------------------------------------------------------------
    */

    private function dashboardAdmin()
    {
        /*
        |--------------------------------------------------------------------------
        | CARD UTAMA
        |--------------------------------------------------------------------------
        */

        $totalAlternatif =
            AlternatifMusim::count();


        $totalPenyuluh =
            Penyuluh::count();


        $pengajuanMenunggu =
            PengajuanPetani::where(
                'status',
                'menunggu_verifikasi'
            )->count();


        $pengajuanDisetujui =
            PengajuanPetani::where(
                'status',
                'disetujui'
            )->count();


        /*
        |--------------------------------------------------------------------------
        | STATUS PENGAJUAN
        |--------------------------------------------------------------------------
        */

        $statusPengajuan = [

            'menunggu' =>
                PengajuanPetani::where(
                    'status',
                    'menunggu_verifikasi'
                )->count(),

            'perbaikan' =>
                PengajuanPetani::where(
                    'status',
                    'perlu_perbaikan'
                )->count(),

            'disetujui' =>
                PengajuanPetani::where(
                    'status',
                    'disetujui'
                )->count(),

            'ditolak' =>
                PengajuanPetani::where(
                    'status',
                    'ditolak'
                )->count(),
        ];


        /*
        |--------------------------------------------------------------------------
        | ALTERNATIF BERDASARKAN KOMODITAS
        |--------------------------------------------------------------------------
        |
        | Sekarang menggunakan alternatif_musim,
        | bukan lagi tabel petani lama.
        |
        */

        $dataKomoditas =
            AlternatifMusim::select(
                    'jenis_komoditas'
                )
                ->selectRaw(
                    'COUNT(*) AS total'
                )
                ->whereNotNull(
                    'jenis_komoditas'
                )
                ->where(
                    'jenis_komoditas',
                    '!=',
                    ''
                )
                ->groupBy(
                    'jenis_komoditas'
                )
                ->orderByDesc(
                    'total'
                )
                ->get();


        /*
        |--------------------------------------------------------------------------
        | SCOPE VIKOR TERBARU
        |--------------------------------------------------------------------------
        */

        [
            $topRanking,
            $rankingScope,
        ] =
            $this->latestRankingScope();


        /*
        |--------------------------------------------------------------------------
        | PENGAJUAN TERBARU
        |--------------------------------------------------------------------------
        */

        $pengajuanTerbaru =
            PengajuanPetani::with([
                'penyuluh',
                'poktan.kecamatan',
                'musimTanam',
                'periode',
            ])
                ->orderByDesc(
                    'tanggal_pengajuan'
                )
                ->orderByDesc('id')
                ->take(5)
                ->get();


        /*
        |--------------------------------------------------------------------------
        | HASIL VIKOR
        |--------------------------------------------------------------------------
        */

        $totalHasilVikor =
            VikorHasil::whereNotNull(
                'alternatif_musim_id'
            )->count();


        return view(
            'dashboard.index',
            compact(
                'totalAlternatif',
                'totalPenyuluh',
                'pengajuanMenunggu',
                'pengajuanDisetujui',
                'statusPengajuan',
                'dataKomoditas',
                'topRanking',
                'rankingScope',
                'pengajuanTerbaru',
                'totalHasilVikor'
            )
        );
    }


    /*
    |--------------------------------------------------------------------------
    | DASHBOARD PENYULUH
    |--------------------------------------------------------------------------
    */

    private function dashboardPenyuluh()
    {
        /*
        |--------------------------------------------------------------------------
        | CARI PENYULUH LOGIN
        |--------------------------------------------------------------------------
        */

        $penyuluh =
            Penyuluh::with('penugasanAktif.kecamatan')->where(
                'user_id',
                Auth::id()
            )->first();


        /*
        |--------------------------------------------------------------------------
        | JIKA BELUM TERHUBUNG DATA PENYULUH
        |--------------------------------------------------------------------------
        */

        if (!$penyuluh) {

            return view(
                'dashboard.index',
                [
                    'penyuluh' =>
                        null,

                    'totalPoktan' =>
                        0,

                    'totalPetaniBinaan' =>
                        0,

                    'totalAlternatifBinaan' =>
                        0,

                    'pengajuanMenunggu' =>
                        0,

                    'pengajuanPerbaikan' =>
                        0,

                    'statusPengajuan' => [
                        'menunggu' =>
                            0,

                        'perbaikan' =>
                            0,

                        'disetujui' =>
                            0,

                        'ditolak' =>
                            0,
                    ],

                    'pengajuanPerluTindakan' =>
                        collect(),

                    'pengajuanTerbaru' =>
                        collect(),

                    'topRanking' =>
                        collect(),

                    'rankingScope' =>
                        null,
                ]
            );
        }


        /*
        |--------------------------------------------------------------------------
        | POKTAN BINAAN
        |--------------------------------------------------------------------------
        */

        $poktanIds =
            Poktan::where(
                'penyuluh_id',
                $penyuluh->id
            )
                ->where('status', 'aktif')
                ->pluck('id');


        $totalPoktan =
            $poktanIds->count();


        /*
        |--------------------------------------------------------------------------
        | PETANI BINAAN
        |--------------------------------------------------------------------------
        |
        | Hitung petani unik dari alternatif_musim.
        |
        | Petani yang muncul pada MT berbeda tetap hanya dihitung sekali
        | pada kartu Petani Binaan.
        |
        */

        $totalPetaniBinaan =
            AlternatifMusim::whereIn(
                'poktan_id',
                $poktanIds
            )
                ->distinct()
                ->count(
                    'petani_id'
                );


        /*
        |--------------------------------------------------------------------------
        | TOTAL ALTERNATIF MUSIM BINAAN
        |--------------------------------------------------------------------------
        */

        $totalAlternatifBinaan =
            AlternatifMusim::whereIn(
                'poktan_id',
                $poktanIds
            )->count();


        /*
        |--------------------------------------------------------------------------
        | PENGAJUAN MENUNGGU
        |--------------------------------------------------------------------------
        */

        $pengajuanMenunggu =
            PengajuanPetani::where(
                'penyuluh_id',
                $penyuluh->id
            )
                ->where(
                    'status',
                    'menunggu_verifikasi'
                )
                ->count();


        /*
        |--------------------------------------------------------------------------
        | PENGAJUAN PERLU PERBAIKAN
        |--------------------------------------------------------------------------
        */

        $pengajuanPerbaikan =
            PengajuanPetani::where(
                'penyuluh_id',
                $penyuluh->id
            )
                ->where(
                    'status',
                    'perlu_perbaikan'
                )
                ->count();


        /*
        |--------------------------------------------------------------------------
        | STATUS PENGAJUAN PENYULUH
        |--------------------------------------------------------------------------
        */

        $statusPengajuan = [

            'menunggu' =>
                PengajuanPetani::where(
                    'penyuluh_id',
                    $penyuluh->id
                )
                    ->where(
                        'status',
                        'menunggu_verifikasi'
                    )
                    ->count(),

            'perbaikan' =>
                PengajuanPetani::where(
                    'penyuluh_id',
                    $penyuluh->id
                )
                    ->where(
                        'status',
                        'perlu_perbaikan'
                    )
                    ->count(),

            'disetujui' =>
                PengajuanPetani::where(
                    'penyuluh_id',
                    $penyuluh->id
                )
                    ->where(
                        'status',
                        'disetujui'
                    )
                    ->count(),

            'ditolak' =>
                PengajuanPetani::where(
                    'penyuluh_id',
                    $penyuluh->id
                )
                    ->where(
                        'status',
                        'ditolak'
                    )
                    ->count(),
        ];


        /*
        |--------------------------------------------------------------------------
        | PENGAJUAN YANG HARUS DIPERBAIKI
        |--------------------------------------------------------------------------
        */

        $pengajuanPerluTindakan =
            PengajuanPetani::with([
                'poktan.kecamatan',
                'musimTanam',
                'periode',
            ])
                ->where(
                    'penyuluh_id',
                    $penyuluh->id
                )
                ->where(
                    'status',
                    'perlu_perbaikan'
                )
                ->orderByDesc('id')
                ->take(5)
                ->get();


        /*
        |--------------------------------------------------------------------------
        | PENGAJUAN TERBARU
        |--------------------------------------------------------------------------
        */

        $pengajuanTerbaru =
            PengajuanPetani::with([
                'poktan.kecamatan',
                'musimTanam',
                'periode',
            ])
                ->where(
                    'penyuluh_id',
                    $penyuluh->id
                )
                ->orderByDesc(
                    'tanggal_pengajuan'
                )
                ->orderByDesc('id')
                ->take(5)
                ->get();


        /*
        |--------------------------------------------------------------------------
        | HASIL PRIORITAS TERBARU PENYULUH
        |--------------------------------------------------------------------------
        |
        | Ranking tetap menggunakan ranking Kecamatan.
        |
        | Yang difilter hanya petani yang berada pada Poktan
        | milik Penyuluh.
        |
        */

        [
            $topRanking,
            $rankingScope,
        ] =
            $this->latestRankingScope(
                $penyuluh->id
            );


        return view(
            'dashboard.index',
            compact(
                'penyuluh',
                'totalPoktan',
                'totalPetaniBinaan',
                'totalAlternatifBinaan',
                'pengajuanMenunggu',
                'pengajuanPerbaikan',
                'statusPengajuan',
                'pengajuanPerluTindakan',
                'pengajuanTerbaru',
                'topRanking',
                'rankingScope'
            )
        );
    }


    /*
    |--------------------------------------------------------------------------
    | AMBIL SCOPE RANKING TERBARU
    |--------------------------------------------------------------------------
    |
    | Masalah bila langsung mengambil:
    |
    | orderBy('ranking')->take(5)
    |
    | adalah ranking dari beberapa Musim/Tahun/Kecamatan bisa tercampur.
    |
    | Karena ranking VIKOR sekarang bersifat per:
    |
    | Musim + Tahun + Kecamatan
    |
    | maka Dashboard hanya menampilkan 5 ranking dari scope
    | perhitungan terbaru.
    |
    */

    private function latestRankingScope(
        ?int $penyuluhId = null
    ): array {

        /*
        |--------------------------------------------------------------------------
        | CARI HASIL VIKOR TERBARU
        |--------------------------------------------------------------------------
        */

        $latestQuery =
            VikorHasil::query()
                ->whereNotNull(
                    'alternatif_musim_id'
                )
                ->with([
                    'alternatifMusim.poktan.kecamatan',
                    'alternatifMusim.musimTanam',
                    'alternatifMusim.periode',
                ]);


        /*
        |--------------------------------------------------------------------------
        | JIKA PENYULUH
        |--------------------------------------------------------------------------
        */

        if ($penyuluhId !== null) {

            $latestQuery->whereHas(
                'alternatifMusim.poktan',
                function ($query) use ($penyuluhId) {

                    $query->where(
                        'penyuluh_id',
                        $penyuluhId
                    );
                }
            );
        }


        $latest =
            $latestQuery
                ->orderByDesc(
                    'updated_at'
                )
                ->orderByDesc('id')
                ->first();


        /*
        |--------------------------------------------------------------------------
        | BELUM ADA HASIL
        |--------------------------------------------------------------------------
        */

        if (
            !$latest ||
            !$latest->alternatifMusim ||
            !$latest->alternatifMusim->poktan
        ) {

            return [
                collect(),
                null,
            ];
        }


        $alternatif =
            $latest->alternatifMusim;


        $musimTanamId =
            (int)
            $alternatif->musim_tanam_id;


        $periodeId =
            (int)
            $alternatif->periode_id;


        $kecamatanId =
            (int)
            $alternatif
                ->poktan
                ->kecamatan_id;


        if (!$kecamatanId) {

            return [
                collect(),
                null,
            ];
        }


        /*
        |--------------------------------------------------------------------------
        | QUERY HASIL PADA SCOPE YANG SAMA
        |--------------------------------------------------------------------------
        */

        $rankingQuery =
            VikorHasil::query()
                ->whereNotNull(
                    'alternatif_musim_id'
                )
                ->whereHas(
                    'alternatifMusim',
                    function ($query) use (
                        $musimTanamId,
                        $periodeId,
                        $kecamatanId
                    ) {

                        $query->where(
                            'musim_tanam_id',
                            $musimTanamId
                        )
                            ->where(
                                'periode_id',
                                $periodeId
                            )
                            ->whereHas(
                                'poktan',
                                function ($poktanQuery) use ($kecamatanId) {

                                    $poktanQuery->where(
                                        'kecamatan_id',
                                        $kecamatanId
                                    );
                                }
                            );
                    }
                );


        /*
        |--------------------------------------------------------------------------
        | BATASI PENYULUH
        |--------------------------------------------------------------------------
        */

        if ($penyuluhId !== null) {

            $rankingQuery->whereHas(
                'alternatifMusim.poktan',
                function ($query) use ($penyuluhId) {

                    $query->where(
                        'penyuluh_id',
                        $penyuluhId
                    );
                }
            );
        }


        /*
        |--------------------------------------------------------------------------
        | TOP 5
        |--------------------------------------------------------------------------
        */

        $topRanking =
            $rankingQuery
                ->with([
                    'petani',
                    'alternatifMusim.petani',
                    'alternatifMusim.poktan.kecamatan',
                    'alternatifMusim.musimTanam',
                    'alternatifMusim.periode',
                ])
                ->orderBy(
                    'ranking'
                )
                ->take(5)
                ->get();


        /*
        |--------------------------------------------------------------------------
        | KUOTA
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


        /*
        |--------------------------------------------------------------------------
        | DATA SCOPE
        |--------------------------------------------------------------------------
        */

        $rankingScope = [

            'musim_tanam_id' =>
                $musimTanamId,

            'periode_id' =>
                $periodeId,

            'kecamatan_id' =>
                $kecamatanId,

            'musim' =>
                optional(
                    $alternatif->musimTanam
                )->kode_musim
                ?? '-',

            'nama_musim' =>
                optional(
                    $alternatif->musimTanam
                )->nama_musim
                ?? '-',

            'tahun' =>
                optional(
                    $alternatif->periode
                )->tahun
                ?? '-',

            'kecamatan' =>
                optional(
                    $alternatif
                        ->poktan
                        ->kecamatan
                )->nama_kecamatan
                ?? '-',

            'kuota' =>
                $alokasi
                    ? (int)
                      $alokasi
                        ->kuota_penerima
                    : null,
        ];


        return [
            $topRanking,
            $rankingScope,
        ];
    }
}