<?php

namespace App\Http\Controllers;

use App\Models\Kriteria;
use App\Models\Subkriteria;
use App\Models\AnpFeedback;
use App\Models\AnpPerbandingan;
use App\Models\AnpKonsistensi;
use App\Models\AnpHasil;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AnpController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | HALAMAN UTAMA ANP
    |--------------------------------------------------------------------------
    */

    public function index()
    {
        $kriteria = Kriteria::orderBy('id', 'asc')->get();

        $feedback = AnpFeedback::where('nilai', 1)
            ->orderBy('kriteria_asal_id')
            ->orderBy('kriteria_tujuan_id')
            ->get();

        /*
        | Format network:
        | network[kriteria_yang_memengaruhi][kriteria_yang_dipengaruhi] = 1
        */
        $network = [];

        foreach ($feedback as $item) {
            $network[$item->kriteria_asal_id][$item->kriteria_tujuan_id] = 1;
        }

        /*
        | Kelompok kriteria yang dibandingkan untuk setiap kriteria acuan.
        | Contoh Excel:
        | - C1 memengaruhi C2 dan C3
        | - C2 memengaruhi C1, C4 dan C5
        */
        $kelompokFeedback = [];

        foreach ($kriteria as $sumber) {
            $targetIds = $feedback
                ->where('kriteria_asal_id', $sumber->id)
                ->pluck('kriteria_tujuan_id')
                ->all();

            $kelompokFeedback[$sumber->id] = $kriteria
                ->whereIn('id', $targetIds)
                ->values();
        }

        $perbandingan = AnpPerbandingan::all();

        $konsistensi = AnpKonsistensi::all()
            ->keyBy('kriteria_dipengaruhi_id');

        return view(
            'anp.index',
            compact(
                'kriteria',
                'feedback',
                'network',
                'kelompokFeedback',
                'perbandingan',
                'konsistensi'
            )
        );
    }


    /*
    |--------------------------------------------------------------------------
    | HASIL BOBOT KRITERIA ANP
    |--------------------------------------------------------------------------
    |
    | Tidak lagi menggunakan bobot hardcoded.
    | Bobot dibaca dari tabel anp_hasil.
    |
    */

    public function hasil()
    {
        $kriteria = Kriteria::orderBy('id', 'asc')->get();

        $hasil = AnpHasil::all()
            ->keyBy('kriteria_id');

        $bobotANP = [];

        foreach ($kriteria as $item) {
            $bobotANP[$item->kode_kriteria] =
                isset($hasil[$item->id])
                    ? (float) $hasil[$item->id]->bobot
                    : null;
        }

        return view(
            'anp.hasil',
            compact(
                'kriteria',
                'bobotANP'
            )
        );
    }


    /*
    |--------------------------------------------------------------------------
    | SIMPAN NETWORK / FEEDBACK KRITERIA
    |--------------------------------------------------------------------------
    |
    | kriteria_asal_id   = kriteria yang memengaruhi
    | kriteria_tujuan_id = kriteria yang dipengaruhi
    |
    */

    public function simpan_network(Request $request)
    {
        $network = $request->input('network', []);

        DB::beginTransaction();

        try {

            /*
            | Karena network berubah, data perbandingan dan hasil lama
            | tidak lagi valid.
            */
            AnpFeedback::query()->delete();
            AnpPerbandingan::query()->delete();
            AnpKonsistensi::query()->delete();
            AnpHasil::query()->delete();

            foreach ($network as $asalId => $tujuanList) {

                if (!is_array($tujuanList)) {
                    continue;
                }

                foreach ($tujuanList as $tujuanId => $value) {

                    if (
                        (int) $value === 1 &&
                        (int) $asalId !== (int) $tujuanId
                    ) {
                        AnpFeedback::create([
                            'kriteria_asal_id' => $asalId,
                            'kriteria_tujuan_id' => $tujuanId,
                            'nilai' => 1,
                        ]);
                    }
                }
            }

            DB::commit();

            return redirect()
                ->route('anp.index')
                ->with(
                    'success',
                    'Matriks feedback antar kriteria berhasil disimpan.'
                );

        } catch (\Throwable $e) {

            DB::rollBack();

            return redirect()
                ->route('anp.index')
                ->with(
                    'error',
                    'Gagal menyimpan matriks feedback: ' . $e->getMessage()
                );
        }
    }


    /*
    |--------------------------------------------------------------------------
    | HITUNG ANP
    |--------------------------------------------------------------------------
    |
    | Alur sesuai Excel:
    | 1. Pairwise comparison
    | 2. Normalisasi dan Bobot E.V
    | 3. Lambda Max, CI, CR
    | 4. Unweighted Supermatrix
    | 5. Weighted Supermatrix
    | 6. Limiting Supermatrix
    | 7. Bobot akhir kriteria
    |
    */

    public function hitung(Request $request)
    {
        DB::beginTransaction();

        try {

            /*
            | Jika form mengirim nilai pairwise,
            | simpan terlebih dahulu.
            */
            if ($request->has('pairwise')) {
                $this->simpanPairwise(
                    $request->input('pairwise', [])
                );
            }

            $kriteria = Kriteria::orderBy('id', 'asc')->get();

            if ($kriteria->isEmpty()) {
                throw new \Exception(
                    'Data kriteria belum tersedia.'
                );
            }

            $jumlahKriteria = $kriteria->count();

            $indexKriteria = [];

            foreach ($kriteria as $index => $item) {
                $indexKriteria[$item->id] = $index;
            }

            /*
            |--------------------------------------------------------------------------
            | UNWEIGHTED SUPERMATRIX
            |--------------------------------------------------------------------------
            */

            $unweighted = array_fill(
                0,
                $jumlahKriteria,
                array_fill(0, $jumlahKriteria, 0.0)
            );

            AnpKonsistensi::query()->delete();

            foreach ($kriteria as $sumber) {

                /*
                | Cari kriteria yang dipengaruhi oleh kriteria sumber.
                */
                $targetIds = AnpFeedback::where(
                    'kriteria_asal_id',
                    $sumber->id
                )
                    ->where('nilai', 1)
                    ->pluck('kriteria_tujuan_id')
                    ->map(fn ($id) => (int) $id)
                    ->all();

                if (empty($targetIds)) {
                    throw new \Exception(
                        'Kriteria ' .
                        $sumber->kode_kriteria .
                        ' belum memiliki hubungan feedback.'
                    );
                }

                /*
                | Jika hanya satu kriteria yang dipengaruhi,
                | bobot lokal otomatis = 1.
                */
                if (count($targetIds) === 1) {

                    $bobotLokal = [
                        $targetIds[0] => 1.0
                    ];

                    $lambdaMax = 1.0;
                    $ci = 0.0;
                    $cr = 0.0;

                } else {

                    $matriksPairwise = $this->buatMatriksPairwise(
                        $sumber->id,
                        $targetIds
                    );

                    $hasilPairwise = $this->hitungPairwise(
                        $matriksPairwise
                    );

                    $bobotLokal = [];

                    foreach ($targetIds as $index => $targetId) {
                        $bobotLokal[$targetId] =
                            $hasilPairwise['bobot'][$index];
                    }

                    $lambdaMax = $hasilPairwise['lambda_max'];
                    $ci = $hasilPairwise['ci'];
                    $cr = $hasilPairwise['cr'];
                }

                /*
                | Simpan hasil konsistensi.
                |
                | Nama kolom database tetap
                | kriteria_dipengaruhi_id sesuai struktur tabel
                | yang sudah dibuat, tetapi isinya digunakan
                | sebagai kriteria acuan/sumber pairwise.
                */
                AnpKonsistensi::updateOrCreate(
                    [
                        'kriteria_dipengaruhi_id' => $sumber->id,
                    ],
                    [
                        'lambda_max' => $lambdaMax,
                        'ci' => $ci,
                        'cr' => $cr,
                        'status_konsisten' => $cr <= 0.10 ? 1 : 0,
                    ]
                );

                /*
                | Masukkan bobot lokal ke kolom supermatrix
                | milik kriteria sumber.
                */
                $kolom = $indexKriteria[$sumber->id];

                foreach ($bobotLokal as $targetId => $bobot) {

                    if (!isset($indexKriteria[$targetId])) {
                        continue;
                    }

                    $baris = $indexKriteria[$targetId];

                    $unweighted[$baris][$kolom] = $bobot;
                }
            }


            /*
            |--------------------------------------------------------------------------
            | CEK KONSISTENSI
            |--------------------------------------------------------------------------
            */

            $tidakKonsisten = AnpKonsistensi::where(
                'status_konsisten',
                0
            )->exists();

            if ($tidakKonsisten) {
                throw new \Exception(
                    'Terdapat perbandingan dengan CR > 0,10. ' .
                    'Silakan perbaiki nilai perbandingan terlebih dahulu.'
                );
            }


            /*
            |--------------------------------------------------------------------------
            | WEIGHTED SUPERMATRIX
            |--------------------------------------------------------------------------
            |
            | Pada Excel hanya terdapat satu cluster kriteria dan
            | setiap kolom Unweighted Supermatrix sudah berjumlah 1.
            | Oleh karena itu Weighted Supermatrix = Unweighted Supermatrix.
            |
            */

            $weighted = $unweighted;


            /*
            |--------------------------------------------------------------------------
            | LIMITING SUPERMATRIX
            |--------------------------------------------------------------------------
            |
            | Workbook Excel menunjukkan pola periodik genap-ganjil.
            | Matriks akhir diperoleh dari rata-rata limit matriks
            | pangkat genap dan pangkat ganjil.
            |
            */

            $limiting = $this->hitungLimitingSupermatrix(
                $weighted
            );

            $matriksAkhir = $limiting['final'];


            /*
            |--------------------------------------------------------------------------
            | BOBOT AKHIR KRITERIA
            |--------------------------------------------------------------------------
            |
            | Pada matriks limiting akhir, setiap kolom memiliki nilai
            | yang sama. Rata-rata setiap baris digunakan agar stabil
            | terhadap pembulatan numerik.
            |
            */

            $bobotAkhir = [];

            for ($i = 0; $i < $jumlahKriteria; $i++) {

                $totalBaris = array_sum(
                    $matriksAkhir[$i]
                );

                $bobotAkhir[$i] =
                    $totalBaris / $jumlahKriteria;
            }

            /*
            | Normalisasi agar total bobot tepat = 1.
            */
            $totalBobot = array_sum($bobotAkhir);

            if ($totalBobot <= 0) {
                throw new \Exception(
                    'Bobot akhir ANP tidak dapat dihitung.'
                );
            }

            foreach ($bobotAkhir as $index => $nilai) {
                $bobotAkhir[$index] =
                    $nilai / $totalBobot;
            }


            /*
            |--------------------------------------------------------------------------
            | SIMPAN HASIL ANP
            |--------------------------------------------------------------------------
            */

            AnpHasil::query()->delete();

            foreach ($kriteria as $index => $item) {

                AnpHasil::create([
                    'kriteria_id' => $item->id,
                    'bobot' => $bobotAkhir[$index],
                ]);
            }

            DB::commit();

            return redirect()
                ->route('anp.hasil')
                ->with(
                    'success',
                    'Perhitungan ANP berhasil dilakukan.'
                );

        } catch (\Throwable $e) {

            DB::rollBack();

            return redirect()
                ->route('anp.index')
                ->with(
                    'error',
                    'Perhitungan ANP gagal: ' .
                    $e->getMessage()
                );
        }
    }


    /*
    |--------------------------------------------------------------------------
    | DATA SUBKRITERIA
    |--------------------------------------------------------------------------
    */

    public function indexSubkriteria()
    {
        $kriteria = Kriteria::with('subkriteria')
            ->orderBy('id', 'asc')
            ->get();

        return view(
            'anp.index_subkriteria',
            compact('kriteria')
        );
    }


    /*
    |--------------------------------------------------------------------------
    | HASIL BOBOT / SKOR SUBKRITERIA
    |--------------------------------------------------------------------------
    |
    | Skor subkriteria diambil langsung dari database.
    | Berdasarkan Excel nilainya berupa skor seperti 5, 4, 3, 2, 1.
    |
    */

    public function hasilSubkriteria()
    {
        $kriteria = Kriteria::with('subkriteria')
            ->orderBy('id', 'asc')
            ->get();

        return view(
            'anp.hasil_subkriteria',
            compact('kriteria')
        );
    }


    public function prosesSubkriteria(Request $request)
    {
        return redirect()
            ->route('anp.hasil_subkriteria');
    }


    /*
    |--------------------------------------------------------------------------
    | BOBOT SUBKRITERIA
    |--------------------------------------------------------------------------
    |
    | Tidak lagi menulis bobot desimal ANP ke subkriteria.bobot,
    | karena pada Excel kolom ini merupakan skor subkriteria untuk
    | konversi alternatif VIKOR.
    |
    | Nilai dikelola melalui CRUD Subkriteria.
    |
    */

    public function simpanBobotSubkriteria(Request $request)
    {
        return redirect()
            ->route('anp.hasil_subkriteria')
            ->with(
                'success',
                'Bobot subkriteria menggunakan nilai yang tersimpan pada database.'
            );
    }


    /*
    |--------------------------------------------------------------------------
    | METHOD ROUTE LAMA
    |--------------------------------------------------------------------------
    */

    public function network()
    {
        return redirect()
            ->route('anp.index');
    }


    public function saveNetwork(Request $request)
    {
        return $this->simpan_network($request);
    }


    public function pairwiseKriteria()
    {
        return redirect()
            ->route('anp.index');
    }


    public function feedback()
    {
        return redirect()
            ->route('anp.index');
    }


    /*
    |--------------------------------------------------------------------------
    | PRIVATE: SIMPAN PAIRWISE
    |--------------------------------------------------------------------------
    |
    | Format request yang akan dipakai oleh Blade:
    |
    | pairwise[sumber_id][baris_id][kolom_id] = nilai
    |
    | Contoh:
    | pairwise[C2][C5][C4] = 0.5
    |
    */

    private function simpanPairwise(array $pairwise): void
    {
        AnpPerbandingan::query()->delete();

        foreach ($pairwise as $sumberId => $barisList) {

            if (!is_array($barisList)) {
                continue;
            }

            foreach ($barisList as $barisId => $kolomList) {

                if (!is_array($kolomList)) {
                    continue;
                }

                foreach ($kolomList as $kolomId => $nilaiInput) {

                    if (
                        (int) $barisId === (int) $kolomId ||
                        $nilaiInput === null ||
                        $nilaiInput === ''
                    ) {
                        continue;
                    }

                    $nilai = $this->parseNilaiSaaty(
                        $nilaiInput
                    );

                    if ($nilai <= 0) {
                        throw new \Exception(
                            'Nilai perbandingan harus lebih besar dari 0.'
                        );
                    }

                    /*
                    | Simpan nilai yang diinput.
                    */
                    AnpPerbandingan::updateOrCreate(
                        [
                            'kriteria_dipengaruhi_id' => $sumberId,
                            'kriteria_baris_id' => $barisId,
                            'kriteria_kolom_id' => $kolomId,
                        ],
                        [
                            'nilai' => $nilai,
                        ]
                    );

                    /*
                    | Simpan nilai reciprocal otomatis.
                    */
                    AnpPerbandingan::updateOrCreate(
                        [
                            'kriteria_dipengaruhi_id' => $sumberId,
                            'kriteria_baris_id' => $kolomId,
                            'kriteria_kolom_id' => $barisId,
                        ],
                        [
                            'nilai' => 1 / $nilai,
                        ]
                    );
                }
            }
        }
    }


    /*
    |--------------------------------------------------------------------------
    | PRIVATE: BUAT MATRIKS PAIRWISE
    |--------------------------------------------------------------------------
    */

    private function buatMatriksPairwise(
        int $sumberId,
        array $targetIds
    ): array {

        $jumlah = count($targetIds);

        $matriks = array_fill(
            0,
            $jumlah,
            array_fill(0, $jumlah, 1.0)
        );

        for ($i = 0; $i < $jumlah; $i++) {

            for ($j = 0; $j < $jumlah; $j++) {

                if ($i === $j) {
                    $matriks[$i][$j] = 1.0;
                    continue;
                }

                $nilai = AnpPerbandingan::where(
                    'kriteria_dipengaruhi_id',
                    $sumberId
                )
                    ->where(
                        'kriteria_baris_id',
                        $targetIds[$i]
                    )
                    ->where(
                        'kriteria_kolom_id',
                        $targetIds[$j]
                    )
                    ->value('nilai');

                /*
                | Jika sisi langsung belum ada,
                | cek nilai kebalikannya.
                */
                if ($nilai === null) {

                    $nilaiBalik = AnpPerbandingan::where(
                        'kriteria_dipengaruhi_id',
                        $sumberId
                    )
                        ->where(
                            'kriteria_baris_id',
                            $targetIds[$j]
                        )
                        ->where(
                            'kriteria_kolom_id',
                            $targetIds[$i]
                        )
                        ->value('nilai');

                    if ($nilaiBalik !== null) {
                        $nilai = 1 / (float) $nilaiBalik;
                    }
                }

                if ($nilai === null) {
                    throw new \Exception(
                        'Nilai pairwise belum lengkap untuk salah satu kelompok kriteria.'
                    );
                }

                $matriks[$i][$j] = (float) $nilai;
            }
        }

        return $matriks;
    }


    /*
    |--------------------------------------------------------------------------
    | PRIVATE: HITUNG PAIRWISE + CI CR
    |--------------------------------------------------------------------------
    |
    | Mengikuti metode pada Excel:
    | - jumlah setiap kolom
    | - normalisasi kolom
    | - rata-rata baris = Bobot E.V
    | - lambda max = Σ(total kolom × bobot E.V)
    |
    */

    private function hitungPairwise(
        array $matriks
    ): array {

        $n = count($matriks);

        $jumlahKolom = array_fill(
            0,
            $n,
            0.0
        );

        for ($j = 0; $j < $n; $j++) {

            for ($i = 0; $i < $n; $i++) {
                $jumlahKolom[$j] +=
                    $matriks[$i][$j];
            }

            if ($jumlahKolom[$j] == 0) {
                throw new \Exception(
                    'Jumlah kolom matriks pairwise bernilai 0.'
                );
            }
        }

        $normalisasi = array_fill(
            0,
            $n,
            array_fill(0, $n, 0.0)
        );

        $bobot = array_fill(
            0,
            $n,
            0.0
        );

        for ($i = 0; $i < $n; $i++) {

            $totalBaris = 0.0;

            for ($j = 0; $j < $n; $j++) {

                $normalisasi[$i][$j] =
                    $matriks[$i][$j] /
                    $jumlahKolom[$j];

                $totalBaris +=
                    $normalisasi[$i][$j];
            }

            $bobot[$i] =
                $totalBaris / $n;
        }

        /*
        | Lambda Max sesuai tabel Excel.
        */
        $lambdaMax = 0.0;

        for ($j = 0; $j < $n; $j++) {
            $lambdaMax +=
                $jumlahKolom[$j] *
                $bobot[$j];
        }

        if ($n <= 1) {

            $ci = 0.0;
            $cr = 0.0;

        } else {

            $ci =
                ($lambdaMax - $n) /
                ($n - 1);

            $ri = $this->nilaiRI($n);

            $cr =
                $ri > 0
                    ? $ci / $ri
                    : 0.0;
        }

        /*
        | Hindari angka sangat kecil negatif akibat floating point.
        */
        if (abs($ci) < 0.0000000001) {
            $ci = 0.0;
        }

        if (abs($cr) < 0.0000000001) {
            $cr = 0.0;
        }

        return [
            'jumlah_kolom' => $jumlahKolom,
            'normalisasi' => $normalisasi,
            'bobot' => $bobot,
            'lambda_max' => $lambdaMax,
            'ci' => $ci,
            'cr' => $cr,
        ];
    }


    /*
    |--------------------------------------------------------------------------
    | PRIVATE: LIMITING SUPERMATRIX
    |--------------------------------------------------------------------------
    */

    private function hitungLimitingSupermatrix(
        array $matrix
    ): array {

        /*
        | Mulai dari M².
        */
        $genap = $this->kaliMatriks(
            $matrix,
            $matrix
        );

        /*
        | Repeated squaring:
        | M², M⁴, M⁸, M¹⁶, ...
        |
        | Ini mengikuti pola tabel limiting pada Excel.
        */
        for ($iterasi = 0; $iterasi < 20; $iterasi++) {

            $berikutnya = $this->kaliMatriks(
                $genap,
                $genap
            );

            if (
                $this->selisihMaksimum(
                    $genap,
                    $berikutnya
                ) < 0.000000000001
            ) {
                $genap = $berikutnya;
                break;
            }

            $genap = $berikutnya;
        }

        /*
        | Matriks pangkat ganjil.
        */
        $ganjil = $this->kaliMatriks(
            $genap,
            $matrix
        );

        /*
        | Pada Excel hasil akhir merupakan rata-rata
        | limit genap dan limit ganjil.
        */
        $jumlah = count($matrix);

        $final = array_fill(
            0,
            $jumlah,
            array_fill(0, $jumlah, 0.0)
        );

        for ($i = 0; $i < $jumlah; $i++) {

            for ($j = 0; $j < $jumlah; $j++) {

                $final[$i][$j] =
                    (
                        $genap[$i][$j] +
                        $ganjil[$i][$j]
                    ) / 2;
            }
        }

        return [
            'even' => $genap,
            'odd' => $ganjil,
            'final' => $final,
        ];
    }


    /*
    |--------------------------------------------------------------------------
    | PRIVATE: PERKALIAN MATRIKS
    |--------------------------------------------------------------------------
    */

    private function kaliMatriks(
        array $a,
        array $b
    ): array {

        $barisA = count($a);
        $kolomA = count($a[0]);
        $kolomB = count($b[0]);

        $hasil = array_fill(
            0,
            $barisA,
            array_fill(0, $kolomB, 0.0)
        );

        for ($i = 0; $i < $barisA; $i++) {

            for ($j = 0; $j < $kolomB; $j++) {

                $nilai = 0.0;

                for ($k = 0; $k < $kolomA; $k++) {

                    $nilai +=
                        $a[$i][$k] *
                        $b[$k][$j];
                }

                $hasil[$i][$j] = $nilai;
            }
        }

        return $hasil;
    }


    /*
    |--------------------------------------------------------------------------
    | PRIVATE: SELISIH MATRIKS
    |--------------------------------------------------------------------------
    */

    private function selisihMaksimum(
        array $a,
        array $b
    ): float {

        $maksimum = 0.0;

        for ($i = 0; $i < count($a); $i++) {

            for ($j = 0; $j < count($a[$i]); $j++) {

                $selisih = abs(
                    $a[$i][$j] -
                    $b[$i][$j]
                );

                if ($selisih > $maksimum) {
                    $maksimum = $selisih;
                }
            }
        }

        return $maksimum;
    }


    /*
    |--------------------------------------------------------------------------
    | PRIVATE: PARSE SKALA SAATY
    |--------------------------------------------------------------------------
    |
    | Menerima:
    | 3
    | 0.5
    | 1/2
    | 1/3
    |
    */

    private function parseNilaiSaaty(
        $nilai
    ): float {

        if (is_numeric($nilai)) {
            return (float) $nilai;
        }

        $nilai = trim((string) $nilai);

        if (str_contains($nilai, '/')) {

            [$atas, $bawah] = array_pad(
                explode('/', $nilai, 2),
                2,
                null
            );

            if (
                is_numeric($atas) &&
                is_numeric($bawah) &&
                (float) $bawah != 0
            ) {
                return
                    (float) $atas /
                    (float) $bawah;
            }
        }

        throw new \Exception(
            'Format nilai perbandingan tidak valid.'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | PRIVATE: RANDOM INDEX (RI)
    |--------------------------------------------------------------------------
    */

    private function nilaiRI(
        int $n
    ): float {

        $ri = [
            1 => 0.00,
            2 => 0.00,
            3 => 0.58,
            4 => 0.90,
            5 => 1.12,
            6 => 1.24,
            7 => 1.32,
            8 => 1.41,
            9 => 1.45,
            10 => 1.49,
        ];

        return $ri[$n] ?? 1.49;
    }
}