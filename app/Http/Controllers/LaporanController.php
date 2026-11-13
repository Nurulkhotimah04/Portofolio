<?php

namespace App\Http\Controllers;

use App\Models\AlokasiKecamatan;
use App\Models\AlternatifMusim;
use App\Models\AlternatifMusimPupuk;
use App\Models\Kecamatan;
use App\Models\MusimTanam;
use App\Models\PengajuanPetani;
use App\Models\PengaturanLaporan;
use App\Models\Periode;
use App\Models\Penyuluh;
use App\Models\VikorHasil;
use App\Services\AuditLogger;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class LaporanController extends Controller
{
    public function __construct(private AuditLogger $audit)
    {
    }

    private function paginateCollection(
        $items,
        Request $request,
        int $perPage = 10
    ): LengthAwarePaginator {
        $items = collect($items)->values();
        $page = LengthAwarePaginator::resolveCurrentPage();

        return new LengthAwarePaginator(
            $items->forPage($page, $perPage)->values(),
            $items->count(),
            $perPage,
            $page,
            [
                'path' => $request->url(),
                'query' => $request->query(),
            ]
        );
    }


    /*
    |--------------------------------------------------------------------------
    | PENYULUH LOGIN
    |--------------------------------------------------------------------------
    */

    private function currentPenyuluh(): ?Penyuluh
    {
        if (
            !Auth::check() ||
            Auth::user()->role !== 'penyuluh'
        ) {
            return null;
        }

        return Penyuluh::with('penugasanAktif.kecamatan')->where(
            'user_id',
            Auth::id()
        )->first();
    }


    /*
    |--------------------------------------------------------------------------
    | KECAMATAN BINAAN PENYULUH
    |--------------------------------------------------------------------------
    |
    | Aturan bisnis: satu Penyuluh hanya memiliki satu penugasan Kecamatan
    | yang aktif pada satu waktu. Kecamatan tidak dipilih dari filter laporan;
    | sistem mengambilnya langsung dari riwayat penugasan aktif Penyuluh.
    |
    */

    private function currentPenyuluhKecamatan(): ?Kecamatan
    {
        $penyuluh = $this->currentPenyuluh();

        if (!$penyuluh || $penyuluh->status !== 'aktif') {
            return null;
        }

        // Sumber kebenaran wilayah adalah penugasan aktif Penyuluh,
        // bukan pilihan filter dan bukan hasil tebak dari daftar Poktan.
        return $penyuluh->penugasanAktif?->kecamatan;
    }


    /*
    |--------------------------------------------------------------------------
    | MASTER FILTER
    |--------------------------------------------------------------------------
    */

    private function masterFilterData(): array
    {
        $musimTanam = MusimTanam::orderBy(
            'kode_musim'
        )->get();

        $periode = Periode::orderByDesc(
            'tahun'
        )->get();

        $isPenyuluh =
            Auth::user()->role === 'penyuluh';

        $kecamatanPenyuluh = null;

        if ($isPenyuluh) {
            $kecamatanPenyuluh =
                $this->currentPenyuluhKecamatan();

            /*
             * Tetap dikirim sebagai collection agar view lama yang membaca
             * variabel $kecamatan tidak rusak, tetapi isinya maksimal satu.
             */
            $kecamatan = $kecamatanPenyuluh
                ? collect([$kecamatanPenyuluh])
                : collect();
        } else {
            $kecamatan = Kecamatan::orderBy(
                'nama_kecamatan'
            )->get();
        }

        return compact(
            'musimTanam',
            'periode',
            'kecamatan',
            'kecamatanPenyuluh',
            'isPenyuluh'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | HALAMAN UTAMA LAPORAN
    |--------------------------------------------------------------------------
    */

    public function index()
    {
        return view(
            'laporan.index',
            array_merge(
                $this->masterFilterData(),
                [
                    'penandatangan' => PengaturanLaporan::first(),
                ]
            )
        );
    }


    /*
    |--------------------------------------------------------------------------
    | PENANDATANGAN LAPORAN
    |--------------------------------------------------------------------------
    |
    | Data penandatangan disimpan satu kali oleh Admin dan digunakan otomatis
    | pada seluruh laporan PDF, termasuk laporan yang dicetak Penyuluh.
    |
    */

    public function simpanPenandatangan(Request $request)
    {
        abort_unless(
            Auth::user()->role === 'admin',
            403
        );

        $validated = $request->validate([
            'nama_kepala_dinas' => [
                'required',
                'string',
                'max:150',
            ],
            'nip_kepala_dinas' => [
                'required',
                'string',
                'max:50',
            ],
        ]);

        $old = PengaturanLaporan::first()?->only(['nama_kepala_dinas', 'nip_kepala_dinas']);

        $setting = PengaturanLaporan::updateOrCreate(
            ['id' => 1],
            $validated
        );

        $this->audit->log(
            'laporan',
            'update_signer',
            $setting,
            'Penandatangan laporan diperbarui.',
            $old,
            $setting->only(['nama_kepala_dinas', 'nip_kepala_dinas'])
        );

        return redirect()
            ->route('laporan.index')
            ->with(
                'success',
                'Nama dan NIP Kepala Dinas berhasil disimpan dan akan digunakan pada seluruh laporan PDF.'
            );
    }


    private function penandatanganLaporan(): array
    {
        $setting = PengaturanLaporan::first();

        return [
            'namaKepalaDinas' =>
                $setting?->nama_kepala_dinas,

            'nipKepalaDinas' =>
                $setting?->nip_kepala_dinas,
        ];
    }


    /*
    |--------------------------------------------------------------------------
    | CEK AKSES KECAMATAN PENYULUH
    |--------------------------------------------------------------------------
    */

    private function ensureKecamatanAccess(
        int $kecamatanId
    ): void {

        if (Auth::user()->role !== 'penyuluh') {
            return;
        }

        $kecamatan = $this->currentPenyuluhKecamatan();

        if (!$kecamatan || (int) $kecamatan->id !== $kecamatanId) {
            abort(403, 'Anda tidak memiliki akses ke Kecamatan tersebut.');
        }
    }


    /*
    |--------------------------------------------------------------------------
    | VALIDASI SCOPE PRIORITAS
    |--------------------------------------------------------------------------
    */

    private function validatePrioritasScope(
        Request $request
    ): array {

        $rules = [
            'musim_tanam_id' => [
                'required',
                'exists:musim_tanam,id',
            ],

            'periode_id' => [
                'required',
                'exists:periode,id',
            ],
        ];

        /*
         * Admin memilih Kecamatan karena VIKOR dihitung per Kecamatan.
         * Penyuluh tidak memilih Kecamatan; wilayah diambil otomatis.
         */
        if (Auth::user()->role === 'admin') {
            $rules['kecamatan_id'] = [
                'required',
                'exists:kecamatan,id',
            ];
        }

        $data = $request->validate($rules);

        if (Auth::user()->role === 'penyuluh') {
            $kecamatan =
                $this->currentPenyuluhKecamatan();

            if (!$kecamatan) {
                throw ValidationException::withMessages([
                    'kecamatan_id' =>
                        'Kecamatan binaan belum tersedia. Pastikan Poktan Anda sudah memiliki Kecamatan.',
                ]);
            }

            $kecamatanId = (int) $kecamatan->id;
        } else {
            $kecamatanId =
                (int) $data['kecamatan_id'];
        }

        return [
            'musim_tanam_id' =>
                (int) $data['musim_tanam_id'],

            'periode_id' =>
                (int) $data['periode_id'],

            'kecamatan_id' =>
                $kecamatanId,
        ];
    }


    /*
    |--------------------------------------------------------------------------
    | DATA PRIORITAS
    |--------------------------------------------------------------------------
    */

    private function buildPrioritasData(
        int $musimTanamId,
        int $periodeId,
        int $kecamatanId
    ): array {

        $musimTanam = MusimTanam::findOrFail(
            $musimTanamId
        );

        $periode = Periode::findOrFail(
            $periodeId
        );

        $kecamatan = Kecamatan::findOrFail(
            $kecamatanId
        );


        $alokasi = AlokasiKecamatan::where(
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
        | JUMLAH ALTERNATIF DALAM KECAMATAN
        |--------------------------------------------------------------------------
        */

        $totalKandidat = AlternatifMusim::where(
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
            ->count();


        /*
        |--------------------------------------------------------------------------
        | HASIL VIKOR
        |--------------------------------------------------------------------------
        */

        $query = VikorHasil::whereNotNull(
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


        $totalHasilKecamatan =
            (clone $query)->count();


        /*
        |--------------------------------------------------------------------------
        | PENYULUH HANYA PETANI BINAANNYA
        |--------------------------------------------------------------------------
        */

        if (
            Auth::user()->role === 'penyuluh'
        ) {

            $penyuluh =
                $this->currentPenyuluh();


            $query->whereHas(
                'alternatifMusim.poktan',
                function ($q) use ($penyuluh) {

                    $q->where(
                        'penyuluh_id',
                        $penyuluh->id
                    );
                }
            );
        }


        $hasil = $query
            ->with([
                'petani',
                'alternatifMusim.petani',
                'alternatifMusim.poktan.kecamatan',
                'alternatifMusim.musimTanam',
                'alternatifMusim.periode',
                'alternatifMusim.pupuk.jenisPupuk',
            ])
            ->orderBy('ranking')
            ->get();


        $kuota = $alokasi
            ? (int) $alokasi->kuota_penerima
            : null;


        $jumlahMasuk = 0;

        $jumlahLuar = 0;


        if ($kuota !== null) {

            foreach ($hasil as $item) {

                if (
                    (int) $item->ranking <= $kuota
                ) {

                    $jumlahMasuk++;

                } else {

                    $jumlahLuar++;
                }
            }
        }


        return [
            'musimDipilih' =>
                $musimTanam,

            'periodeDipilih' =>
                $periode,

            'kecamatanDipilih' =>
                $kecamatan,

            'alokasi' =>
                $alokasi,

            'kuota' =>
                $kuota,

            'hasil' =>
                $hasil,

            'totalKandidat' =>
                $totalKandidat,

            'totalHasilKecamatan' =>
                $totalHasilKecamatan,

            'jumlahDitampilkan' =>
                $hasil->count(),

            'jumlahMasuk' =>
                $jumlahMasuk,

            'jumlahLuar' =>
                $jumlahLuar,

            'isPenyuluh' =>
                Auth::user()->role === 'penyuluh',

            'judulLaporan' =>
                Auth::user()->role === 'penyuluh'
                    ? 'LAPORAN HASIL PRIORITAS PETANI BINAAN'
                    : 'LAPORAN PRIORITAS PENERIMA SUBSIDI PUPUK',
        ];
    }


    /*
    |--------------------------------------------------------------------------
    | HALAMAN PRIORITAS
    |--------------------------------------------------------------------------
    */

    public function prioritas(
        Request $request
    ) {

        $master =
            $this->masterFilterData();

        $data = null;


        $scopeLengkap =
            $request->filled('musim_tanam_id') &&
            $request->filled('periode_id') &&
            (
                Auth::user()->role === 'penyuluh'
                ||
                $request->filled('kecamatan_id')
            );


        if ($scopeLengkap) {

            $scope =
                $this->validatePrioritasScope(
                    $request
                );


            $data =
                $this->buildPrioritasData(
                    $scope['musim_tanam_id'],
                    $scope['periode_id'],
                    $scope['kecamatan_id']
                );

            $data['hasil'] =
                $this->paginateCollection(
                    collect($data['hasil']),
                    $request,
                    10
                );
        }


        return view(
            'laporan.prioritas',
            array_merge(
                $master,
                [
                    'data' => $data,
                ]
            )
        );
    }


    /*
    |--------------------------------------------------------------------------
    | PDF PRIORITAS
    |--------------------------------------------------------------------------
    */

    public function prioritasPdf(
        Request $request
    ) {

        $scope =
            $this->validatePrioritasScope(
                $request
            );


        $data =
            $this->buildPrioritasData(
                $scope['musim_tanam_id'],
                $scope['periode_id'],
                $scope['kecamatan_id']
            );


        $data = array_merge(
            $data,
            $this->penandatanganLaporan()
        );

        $data['tanggalCetak'] =
            now();


        $pdf = Pdf::loadView(
            'laporan.pdf.prioritas',
            $data
        )
            ->setPaper(
                'a4',
                'landscape'
            );


        return $pdf->download(
            'Laporan_Prioritas_' .
            $data['periodeDipilih']->tahun .
            '_' .
            $data['kecamatanDipilih']->nama_kecamatan .
            '.pdf'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | EXCEL PRIORITAS
    |--------------------------------------------------------------------------
    */

    public function prioritasExcel(
        Request $request
    ) {

        $scope =
            $this->validatePrioritasScope(
                $request
            );


        $data =
            $this->buildPrioritasData(
                $scope['musim_tanam_id'],
                $scope['periode_id'],
                $scope['kecamatan_id']
            );


        $spreadsheet =
            new Spreadsheet();


        $sheet =
            $spreadsheet->getActiveSheet();


        $sheet->setTitle(
            'Prioritas Penerima'
        );


        $sheet->setCellValue(
            'A1',
            'LAPORAN PRIORITAS PENERIMA SUBSIDI PUPUK'
        );


        $sheet->mergeCells(
            'A1:P1'
        );


        $sheet->setCellValue(
            'A2',
            'Musim Tanam: ' .
            $data['musimDipilih']->nama_musim
        );


        $sheet->setCellValue(
            'A3',
            'Tahun: ' .
            $data['periodeDipilih']->tahun
        );


        $sheet->setCellValue(
            'A4',
            'Kecamatan: ' .
            $data['kecamatanDipilih']->nama_kecamatan
        );


        $sheet->setCellValue(
            'A5',
            'Kuota: ' .
            (
                $data['kuota'] !== null
                    ? $data['kuota']
                    : 'Belum Diatur'
            )
        );


        $headers = [
            'No',
            'Ranking',
            'Status Kuota',
            'Kode',
            'NIK',
            'Nama Petani',
            'Poktan',
            'Kecamatan',
            'C1 - Luas Lahan',
            'C2 - Komoditas',
            'C3 - Berkas',
            'C4 - Kebutuhan',
            'Kebutuhan Pupuk yang Diajukan',
            'C5 - Subsektor',
            'Nilai Q',
            'Keterangan',
        ];


        $headerRow = 7;


        foreach (
            $headers as
            $index => $header
        ) {

            $column = Coordinate::stringFromColumnIndex($index + 1);


            $sheet->setCellValue(
                $column .
                $headerRow,
                $header
            );
        }


        $row =
            $headerRow + 1;


        foreach (
            $data['hasil'] as
            $index => $item
        ) {

            $masuk =
                $data['kuota'] !== null
                &&
                (int) $item->ranking
                <=
                $data['kuota'];


            $alternatif =
                $item->alternatifMusim;


            $sheet->setCellValue(
                'A' . $row,
                $index + 1
            );

            $sheet->setCellValue(
                'B' . $row,
                $item->ranking
            );

            $sheet->setCellValue(
                'C' . $row,
                $data['kuota'] === null
                    ? 'Kuota Belum Diatur'
                    : (
                        $masuk
                            ? 'Masuk Kuota'
                            : 'Di Luar Kuota'
                    )
            );

            $sheet->setCellValue(
                'D' . $row,
                $alternatif->kode_alternatif
            );

            $sheet->setCellValueExplicit(
                'E' . $row,
                (string) $item->petani->nik,
                \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING
            );

            $sheet->setCellValue(
                'F' . $row,
                $item->petani->nama_petani
            );

            $sheet->setCellValue(
                'G' . $row,
                optional(
                    $alternatif->poktan
                )->nama_poktan ?? '-'
            );

            $sheet->setCellValue(
                'H' . $row,
                optional(
                    optional(
                        $alternatif->poktan
                    )->kecamatan
                )->nama_kecamatan ?? '-'
            );

            $sheet->setCellValue(
                'I' . $row,
                $alternatif->luas_lahan
            );

            $sheet->setCellValue(
                'J' . $row,
                $alternatif->jenis_komoditas
            );

            $sheet->setCellValue(
                'K' . $row,
                $alternatif->kelengkapan_berkas
            );

            $sheet->setCellValue(
                'L' . $row,
                $alternatif->kebutuhan_pupuk
            );

            $sheet->setCellValue(
                'M' . $row,
                $alternatif->ringkasan_pupuk
            );

            $sheet->setCellValue(
                'N' . $row,
                $alternatif->subsektor_pertanian
            );

            $sheet->setCellValue(
                'O' . $row,
                $item->nilai_q
            );

            $sheet->setCellValue(
                'P' . $row,
                'Ranking Kecamatan'
            );


            $row++;
        }


        $this->styleSpreadsheet(
            $sheet,
            $headerRow,
            'A',
            'P',
            $row - 1
        );


        return $this->downloadSpreadsheet(
            $spreadsheet,
            'Laporan_Prioritas_' .
            $data['periodeDipilih']->tahun .
            '.xlsx'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | REKAP KECAMATAN
    |--------------------------------------------------------------------------
    */

    public function rekap(
        Request $request
    ) {

        abort_unless(
            Auth::user()->role === 'admin',
            403
        );


        $master =
            $this->masterFilterData();

        $data = null;


        if (
            $request->filled('musim_tanam_id') &&
            $request->filled('periode_id')
        ) {

            $validated =
                $request->validate([
                    'musim_tanam_id' => [
                        'required',
                        'exists:musim_tanam,id',
                    ],

                    'periode_id' => [
                        'required',
                        'exists:periode,id',
                    ],
                ]);


            $data =
                $this->buildRekapData(
                    (int)
                    $validated['musim_tanam_id'],

                    (int)
                    $validated['periode_id']
                );

            $data['rekap'] =
                $this->paginateCollection(
                    collect($data['rekap']),
                    $request,
                    10
                );
        }


        return view(
            'laporan.rekap',
            array_merge(
                $master,
                [
                    'data' => $data,
                ]
            )
        );
    }


    /*
    |--------------------------------------------------------------------------
    | BUILD REKAP
    |--------------------------------------------------------------------------
    */

    private function buildRekapData(
        int $musimTanamId,
        int $periodeId
    ): array {

        $musimTanam =
            MusimTanam::findOrFail(
                $musimTanamId
            );


        $periode =
            Periode::findOrFail(
                $periodeId
            );


        $daftarKecamatan =
            Kecamatan::orderBy(
                'nama_kecamatan'
            )->get();


        $rekap = collect();


        foreach (
            $daftarKecamatan as
            $kecamatan
        ) {

            $alternatifIds =
                AlternatifMusim::where(
                    'musim_tanam_id',
                    $musimTanamId
                )
                ->where(
                    'periode_id',
                    $periodeId
                )
                ->whereHas(
                    'poktan',
                    function ($query) use ($kecamatan) {

                        $query->where(
                            'kecamatan_id',
                            $kecamatan->id
                        );
                    }
                )
                ->pluck('id');


            $kandidat =
                $alternatifIds->count();


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
                    $kecamatan->id
                )
                ->first();


            $hasilQuery =
                VikorHasil::whereIn(
                    'alternatif_musim_id',
                    $alternatifIds
                );


            $sudahDihitung =
                (clone $hasilQuery)
                ->count();


            $masukKuota = 0;

            $diLuarKuota = 0;


            if ($alokasi) {

                $masukKuota =
                    (clone $hasilQuery)
                    ->where(
                        'ranking',
                        '<=',
                        $alokasi->kuota_penerima
                    )
                    ->count();


                $diLuarKuota =
                    max(
                        0,
                        $sudahDihitung -
                        $masukKuota
                    );
            }


            $belumDihitung =
                max(
                    0,
                    $kandidat -
                    $sudahDihitung
                );

            $detailPupuk = AlternatifMusimPupuk::with('jenisPupuk')
                ->whereIn('alternatif_musim_id', $alternatifIds)
                ->get();

            $jumlahAlternatifDenganDetailPupuk = $detailPupuk
                ->pluck('alternatif_musim_id')
                ->unique()
                ->count();

            $rekapPupuk = $detailPupuk
                ->groupBy(fn ($row) => $row->jenisPupuk?->nama_pupuk ?? 'Pupuk')
                ->map(fn ($rows) => round((float) $rows->sum('jumlah_kg'), 2))
                ->sortKeys();

            $ringkasanPupuk = $rekapPupuk->isEmpty()
                ? 'Belum ada rincian'
                : $rekapPupuk->map(function ($kg, $nama) {
                    $jumlah = rtrim(rtrim(number_format((float) $kg, 2, ',', '.'), '0'), ',');
                    return $nama . ' ' . $jumlah . ' kg';
                })->implode(', ');


            $rekap->push([
                'kecamatan' =>
                    $kecamatan,

                'kandidat' =>
                    $kandidat,

                'kuota' =>
                    $alokasi
                        ? (int)
                          $alokasi->kuota_penerima
                        : null,

                'sudah_dihitung' =>
                    $sudahDihitung,

                'masuk_kuota' =>
                    $masukKuota,

                'di_luar_kuota' =>
                    $diLuarKuota,

                'belum_dihitung' =>
                    $belumDihitung,

                'ringkasan_pupuk' =>
                    $ringkasanPupuk,

                'detail_pupuk_tercatat' =>
                    $jumlahAlternatifDenganDetailPupuk,

                'detail_pupuk_belum_tercatat' =>
                    max(0, $kandidat - $jumlahAlternatifDenganDetailPupuk),
            ]);
        }


        $semuaAlternatifIds = AlternatifMusim::where('musim_tanam_id', $musimTanamId)
            ->where('periode_id', $periodeId)
            ->whereHas('poktan', fn ($q) => $q->whereNotNull('kecamatan_id'))
            ->pluck('id');

        $totalPupukRows = AlternatifMusimPupuk::with('jenisPupuk')
            ->whereIn('alternatif_musim_id', $semuaAlternatifIds)
            ->get();

        $totalRekapPupuk = $totalPupukRows
            ->groupBy(fn ($row) => $row->jenisPupuk?->nama_pupuk ?? 'Pupuk')
            ->map(fn ($rows) => round((float) $rows->sum('jumlah_kg'), 2))
            ->sortKeys();

        $totalRingkasanPupuk = $totalRekapPupuk->isEmpty()
            ? 'Belum ada rincian'
            : $totalRekapPupuk->map(function ($kg, $nama) {
                $jumlah = rtrim(rtrim(number_format((float) $kg, 2, ',', '.'), '0'), ',');
                return $nama . ' ' . $jumlah . ' kg';
            })->implode(', ');


        return [
            'musimDipilih' =>
                $musimTanam,

            'periodeDipilih' =>
                $periode,

            'rekap' =>
                $rekap,

            'totalKandidat' =>
                $rekap->sum(
                    'kandidat'
                ),

            'totalKuota' =>
                $rekap
                ->whereNotNull('kuota')
                ->sum('kuota'),

            'totalMasuk' =>
                $rekap->sum(
                    'masuk_kuota'
                ),

            'totalLuar' =>
                $rekap->sum(
                    'di_luar_kuota'
                ),

            'totalBelumDihitung' =>
                $rekap->sum(
                    'belum_dihitung'
                ),

            'totalRingkasanPupuk' =>
                $totalRingkasanPupuk,

            'totalDetailPupukTercatat' =>
                $rekap->sum('detail_pupuk_tercatat'),

            'totalDetailPupukBelumTercatat' =>
                $rekap->sum('detail_pupuk_belum_tercatat'),
        ];
    }


    /*
    |--------------------------------------------------------------------------
    | PDF REKAP
    |--------------------------------------------------------------------------
    */

    public function rekapPdf(
        Request $request
    ) {

        abort_unless(
            Auth::user()->role === 'admin',
            403
        );


        $data =
            $request->validate([
                'musim_tanam_id' => [
                    'required',
                    'exists:musim_tanam,id',
                ],

                'periode_id' => [
                    'required',
                    'exists:periode,id',
                ],
            ]);


        $report =
            $this->buildRekapData(
                (int)
                $data['musim_tanam_id'],

                (int)
                $data['periode_id']
            );


        $report = array_merge(
            $report,
            $this->penandatanganLaporan()
        );

        $report['tanggalCetak'] =
            now();


        return Pdf::loadView(
            'laporan.pdf.rekap',
            $report
        )
            ->setPaper(
                'a4',
                'landscape'
            )
            ->download(
                'Rekap_Prioritas_' .
                $report['periodeDipilih']->tahun .
                '.pdf'
            );
    }


    /*
    |--------------------------------------------------------------------------
    | EXCEL REKAP
    |--------------------------------------------------------------------------
    */

    public function rekapExcel(
        Request $request
    ) {

        abort_unless(
            Auth::user()->role === 'admin',
            403
        );


        $data =
            $request->validate([
                'musim_tanam_id' => [
                    'required',
                    'exists:musim_tanam,id',
                ],

                'periode_id' => [
                    'required',
                    'exists:periode,id',
                ],
            ]);


        $report =
            $this->buildRekapData(
                (int)
                $data['musim_tanam_id'],

                (int)
                $data['periode_id']
            );


        $spreadsheet =
            new Spreadsheet();


        $sheet =
            $spreadsheet->getActiveSheet();


        $sheet->setTitle(
            'Rekap Kecamatan'
        );


        $sheet->setCellValue(
            'A1',
            'REKAP PRIORITAS PENERIMA SUBSIDI PUPUK PER KECAMATAN'
        );


        $sheet->mergeCells(
            'A1:I1'
        );


        $sheet->setCellValue(
            'A2',
            'Musim Tanam: ' .
            $report['musimDipilih']->nama_musim
        );


        $sheet->setCellValue(
            'A3',
            'Tahun: ' .
            $report['periodeDipilih']->tahun
        );

        $sheet->setCellValue(
            'A4',
            'Total Kebutuhan Pupuk yang Diajukan: ' . $report['totalRingkasanPupuk']
        );
        $sheet->mergeCells('A4:I4');


        $headers = [
            'No',
            'Kecamatan',
            'Kandidat',
            'Kuota',
            'Sudah Dihitung',
            'Masuk Kuota',
            'Di Luar Kuota',
            'Belum Dihitung',
            'Kebutuhan Pupuk yang Diajukan',
        ];


        $headerRow = 5;


        foreach (
            $headers as
            $index => $header
        ) {

            $column = Coordinate::stringFromColumnIndex($index + 1);


            $sheet->setCellValue(
                $column . $headerRow,
                $header
            );
        }


        $row =
            $headerRow + 1;


        foreach (
            $report['rekap'] as
            $index => $item
        ) {

            $sheet->setCellValue(
                'A' . $row,
                $index + 1
            );

            $sheet->setCellValue(
                'B' . $row,
                $item['kecamatan']
                    ->nama_kecamatan
            );

            $sheet->setCellValue(
                'C' . $row,
                $item['kandidat']
            );

            $sheet->setCellValue(
                'D' . $row,
                $item['kuota']
                ?? 'Belum Diatur'
            );

            $sheet->setCellValue(
                'E' . $row,
                $item['sudah_dihitung']
            );

            $sheet->setCellValue(
                'F' . $row,
                $item['masuk_kuota']
            );

            $sheet->setCellValue(
                'G' . $row,
                $item['di_luar_kuota']
            );

            $sheet->setCellValue(
                'H' . $row,
                $item['belum_dihitung']
            );

            $sheet->setCellValue(
                'I' . $row,
                $item['ringkasan_pupuk'] .
                ($item['detail_pupuk_belum_tercatat'] > 0
                    ? ' ('. $item['detail_pupuk_belum_tercatat'] .' alternatif belum memiliki rincian)'
                    : '')
            );


            $row++;
        }


        $this->styleSpreadsheet(
            $sheet,
            $headerRow,
            'A',
            'I',
            $row - 1
        );


        return $this->downloadSpreadsheet(
            $spreadsheet,
            'Rekap_Prioritas_' .
            $report['periodeDipilih']->tahun .
            '.xlsx'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | PENGAJUAN & VERIFIKASI
    |--------------------------------------------------------------------------
    */

    private function buildPengajuanQuery(
        Request $request
    ) {

        $query =
            PengajuanPetani::with([
                'penyuluh',
                'poktan.kecamatan',
                'musimTanam',
                'periode',
                'verifier',
                'c3Verifier',
                'batchImport',
                'pupuk.jenisPupuk',
            ]);


        /*
        |--------------------------------------------------------------------------
        | PENYULUH HANYA PENGAJUANNYA
        |--------------------------------------------------------------------------
        */

        if (
            Auth::user()->role === 'penyuluh'
        ) {

            $penyuluh =
                $this->currentPenyuluh();

            $kecamatanPenyuluh =
                $this->currentPenyuluhKecamatan();


            if (
                !$penyuluh ||
                !$kecamatanPenyuluh
            ) {

                return $query
                    ->whereRaw(
                        '1 = 0'
                    );
            }


            $query->where(
                'penyuluh_id',
                $penyuluh->id
            )
            ->whereHas(
                'poktan',
                function ($q) use ($kecamatanPenyuluh) {
                    $q->where(
                        'kecamatan_id',
                        $kecamatanPenyuluh->id
                    );
                }
            );
        }


        if (
            $request->filled(
                'musim_tanam_id'
            )
        ) {

            $query->where(
                'musim_tanam_id',
                $request->musim_tanam_id
            );
        }


        if (
            $request->filled(
                'periode_id'
            )
        ) {

            $query->where(
                'periode_id',
                $request->periode_id
            );
        }


        if (
            Auth::user()->role === 'admin' &&
            $request->filled(
                'kecamatan_id'
            )
        ) {

            $query->whereHas(
                'poktan',
                function ($q) use ($request) {

                    $q->where(
                        'kecamatan_id',
                        $request->kecamatan_id
                    );
                }
            );
        }


        if (
            $request->filled(
                'status'
            )
        ) {

            $query->where(
                'status',
                $request->status
            );
        }


        if (
            $request->filled(
                'asal_input'
            )
        ) {

            $query->where(
                'asal_input',
                $request->asal_input
            );
        }


        return $query
            ->orderByDesc(
                'tanggal_pengajuan'
            )
            ->orderByDesc('id');
    }


    private function validatePengajuanFilters(
        Request $request
    ): void {

        $rules = [
            'musim_tanam_id' => [
                'nullable',
                'exists:musim_tanam,id',
            ],

            'periode_id' => [
                'nullable',
                'exists:periode,id',
            ],

            'status' => [
                'nullable',
                'in:menunggu_verifikasi,perlu_perbaikan,disetujui,ditolak',
            ],

            'asal_input' => [
                'nullable',
                'in:manual,import',
            ],
        ];

        /* Kecamatan hanya merupakan filter untuk Admin. */
        if (Auth::user()->role === 'admin') {
            $rules['kecamatan_id'] = [
                'nullable',
                'exists:kecamatan,id',
            ];
        }

        $request->validate($rules);

        /*
         * Untuk Penyuluh, kecamatan_id dari URL/form lama sengaja diabaikan.
         * Scope wilayah selalu ditentukan oleh Poktan binaannya di server.
         */
        if (Auth::user()->role === 'penyuluh') {
            $this->currentPenyuluhKecamatan();
        }
    }


    /*
    |--------------------------------------------------------------------------
    | HALAMAN LAPORAN PENGAJUAN
    |--------------------------------------------------------------------------
    */

    public function pengajuan(
        Request $request
    ) {

        $this->validatePengajuanFilters(
            $request
        );


        $master =
            $this->masterFilterData();


        $pengajuan =
            $this->buildPengajuanQuery(
                $request
            )
                ->paginate(10)
                ->withQueryString();


        return view(
            'laporan.pengajuan',
            array_merge(
                $master,
                [
                    'pengajuan' =>
                        $pengajuan,

                    'isPenyuluh' =>
                        Auth::user()->role
                        ===
                        'penyuluh',
                ]
            )
        );
    }


    /*
    |--------------------------------------------------------------------------
    | PDF PENGAJUAN
    |--------------------------------------------------------------------------
    */

    public function pengajuanPdf(
        Request $request
    ) {

        $this->validatePengajuanFilters(
            $request
        );


        $pengajuan =
            $this->buildPengajuanQuery(
                $request
            )->get();


        $judul =
            Auth::user()->role === 'penyuluh'
                ? 'LAPORAN PENGAJUAN PETANI BINAAN'
                : 'LAPORAN PENGAJUAN DAN VERIFIKASI PETANI';


        return Pdf::loadView(
            'laporan.pdf.pengajuan',
            array_merge(
                [
                    'pengajuan' =>
                        $pengajuan,

                    'judulLaporan' =>
                        $judul,

                    'tanggalCetak' =>
                        now(),
                ],
                $this->penandatanganLaporan()
            )
        )
            ->setPaper(
                'a4',
                'landscape'
            )
            ->download(
                'Laporan_Pengajuan_Petani.pdf'
            );
    }


    /*
    |--------------------------------------------------------------------------
    | EXCEL PENGAJUAN
    |--------------------------------------------------------------------------
    */

    public function pengajuanExcel(
        Request $request
    ) {

        $this->validatePengajuanFilters(
            $request
        );


        $pengajuan =
            $this->buildPengajuanQuery(
                $request
            )->get();


        $spreadsheet =
            new Spreadsheet();


        $sheet =
            $spreadsheet->getActiveSheet();


        $sheet->setTitle(
            'Pengajuan'
        );


        $sheet->setCellValue(
            'A1',
            Auth::user()->role === 'penyuluh'
                ? 'LAPORAN PENGAJUAN PETANI BINAAN'
                : 'LAPORAN PENGAJUAN DAN VERIFIKASI PETANI'
        );


        $sheet->mergeCells(
            'A1:O1'
        );


        $headers = [
            'No',
            'Tanggal Pengajuan',
            'NIK',
            'Nama Petani',
            'Poktan',
            'Kecamatan',
            'Musim Tanam',
            'Tahun',
            'Penyuluh',
            'Asal Input',
            'C3 Diajukan',
            'C3 Hasil Verifikasi Admin',
            'Kebutuhan Pupuk yang Diajukan',
            'Status',
            'Catatan Admin',
        ];


        $headerRow = 3;


        foreach (
            $headers as
            $index => $header
        ) {

            $column = Coordinate::stringFromColumnIndex($index + 1);


            $sheet->setCellValue(
                $column .
                $headerRow,
                $header
            );
        }


        $row =
            $headerRow + 1;


        foreach (
            $pengajuan as
            $index => $item
        ) {

            $sheet->setCellValue(
                'A' . $row,
                $index + 1
            );

            $sheet->setCellValue(
                'B' . $row,
                $item->tanggal_pengajuan
                    ? $item
                        ->tanggal_pengajuan
                        ->format(
                            'd-m-Y H:i'
                        )
                    : '-'
            );

            $sheet->setCellValueExplicit(
                'C' . $row,
                (string) $item->nik,
                \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING
            );

            $sheet->setCellValue(
                'D' . $row,
                $item->nama_petani
            );

            $sheet->setCellValue(
                'E' . $row,
                optional(
                    $item->poktan
                )->nama_poktan ?? '-'
            );

            $sheet->setCellValue(
                'F' . $row,
                optional(
                    optional(
                        $item->poktan
                    )->kecamatan
                )->nama_kecamatan ?? '-'
            );

            $sheet->setCellValue(
                'G' . $row,
                optional(
                    $item->musimTanam
                )->kode_musim ?? '-'
            );

            $sheet->setCellValue(
                'H' . $row,
                optional(
                    $item->periode
                )->tahun ?? '-'
            );

            $sheet->setCellValue(
                'I' . $row,
                optional(
                    $item->penyuluh
                )->nama_penyuluh ?? '-'
            );

            $sheet->setCellValue(
                'J' . $row,
                $item->asal_input ===
                'import'
                    ? 'Import Excel'
                    : 'Manual'
            );

            $sheet->setCellValue(
                'K' . $row,
                $item->kelengkapan_berkas ?? '-'
            );

            $c3Hasil = $item->c3_terverifikasi
                ? $item->kelengkapan_berkas_verifikasi
                : ($item->status === 'disetujui'
                    ? 'Tidak tercatat (data lama)'
                    : 'Belum diverifikasi');

            $sheet->setCellValue(
                'L' . $row,
                $c3Hasil
            );

            $sheet->setCellValue(
                'M' . $row,
                $item->ringkasan_pupuk
            );

            $sheet->setCellValue(
                'N' . $row,
                $this->statusLabel(
                    $item->status
                )
            );

            $sheet->setCellValue(
                'O' . $row,
                $item->catatan_admin
                ?? '-'
            );


            $row++;
        }


        $this->styleSpreadsheet(
            $sheet,
            $headerRow,
            'A',
            'O',
            $row - 1
        );


        return $this->downloadSpreadsheet(
            $spreadsheet,
            'Laporan_Pengajuan_Petani.xlsx'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | LABEL STATUS
    |--------------------------------------------------------------------------
    */

    private function statusLabel(
        string $status
    ): string {

        return match ($status) {

            'menunggu_verifikasi' =>
                'Menunggu Verifikasi',

            'perlu_perbaikan' =>
                'Perlu Perbaikan',

            'disetujui' =>
                'Disetujui',

            'ditolak' =>
                'Ditolak',

            default =>
                $status,
        };
    }


    /*
    |--------------------------------------------------------------------------
    | STYLE EXCEL
    |--------------------------------------------------------------------------
    */

    private function styleSpreadsheet(
        $sheet,
        int $headerRow,
        string $startColumn,
        string $endColumn,
        int $lastRow
    ): void {

        $sheet->getStyle(
            $startColumn .
            $headerRow .
            ':' .
            $endColumn .
            $headerRow
        )
            ->getFont()
            ->setBold(true);


        $sheet->getStyle(
            $startColumn .
            $headerRow .
            ':' .
            $endColumn .
            $lastRow
        )
            ->getBorders()
            ->getAllBorders()
            ->setBorderStyle(
                Border::BORDER_THIN
            );


        $sheet->getStyle(
            $startColumn .
            $headerRow .
            ':' .
            $endColumn .
            $headerRow
        )
            ->getAlignment()
            ->setHorizontal(
                Alignment::HORIZONTAL_CENTER
            );


        $sheet->getStyle(
            $startColumn .
            $headerRow .
            ':' .
            $endColumn .
            $lastRow
        )
            ->getAlignment()
            ->setVertical(
                Alignment::VERTICAL_CENTER
            )
            ->setWrapText(true);


        foreach (
            range(
                $startColumn,
                $endColumn
            ) as $column
        ) {

            $sheet
                ->getColumnDimension(
                    $column
                )
                ->setAutoSize(true);
        }


        $sheet->freezePane(
            $startColumn .
            ($headerRow + 1)
        );
    }


    /*
    |--------------------------------------------------------------------------
    | DOWNLOAD XLSX
    |--------------------------------------------------------------------------
    */

    private function downloadSpreadsheet(
        Spreadsheet $spreadsheet,
        string $filename
    ) {

        return response()->streamDownload(
            function () use ($spreadsheet) {

                $writer =
                    new Xlsx(
                        $spreadsheet
                    );


                $writer->save(
                    'php://output'
                );
            },
            $filename,
            [
                'Content-Type' =>
                    'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            ]
        );
    }
}