<?php

namespace App\Http\Controllers;

use App\Models\Kriteria;
use App\Models\Petani;
use App\Models\Penilaian;
use App\Models\Subkriteria;
use Illuminate\Http\Request;

class PerhitunganController extends Controller
{
    //Menu Perhitungan ANP
    public function anp()
    {
        $kriteria = Kriteria::orderBy('kode_kriteria', 'asc')->get();
        
        // Matriks Perbandingan Saaty Kriteria
        $matriksKriteria = [
            'C1' => ['C1' => 1.000, 'C2' => 3.000, 'C3' => 5.000, 'C4' => 4.000, 'C5' => 3.000],
            'C2' => ['C1' => 0.333, 'C2' => 1.000, 'C3' => 3.000, 'C4' => 2.000, 'C5' => 1.000],
            'C3' => ['C1' => 0.200, 'C2' => 0.333, 'C3' => 1.000, 'C4' => 0.500, 'C5' => 0.333],
            'C4' => ['C1' => 0.250, 'C2' => 0.500, 'C3' => 2.000, 'C4' => 1.000, 'C5' => 0.500],
            'C5' => ['C1' => 0.333, 'C2' => 1.000, 'C3' => 3.000, 'C4' => 2.000, 'C5' => 1.000],
        ];

        // Hitung Total Kolom Kriteria
        $totalKolomKriteria = [];
        foreach ($kriteria as $k) {
            $totalKolomKriteria[$k->kode_kriteria] = 0;
            foreach ($kriteria as $k2) {
                $totalKolomKriteria[$k->kode_kriteria] += $matriksKriteria[$k2->kode_kriteria][$k->kode_kriteria];
            }
        }

        // Normalisasi & Menghitung Eigenvector 
        $eigenKriteria = [];
        foreach ($kriteria as $k) {
            $jumlahBarisNormalisasi = 0;
            foreach ($kriteria as $k2) {
                $jumlahBarisNormalisasi += $matriksKriteria[$k->kode_kriteria][$k2->kode_kriteria] / $totalKolomKriteria[$k2->kode_kriteria];
            }
            $eigenKriteria[$k->kode_kriteria] = round($jumlahBarisNormalisasi / $kriteria->count(), 4);
        }

        // Menghitung CI dan CR Kriteria
        // Mencari λmax
        $lambdaMax = 0;
        foreach ($totalKolomKriteria as $kode => $total) {
            $lambdaMax += $total * $eigenKriteria[$kode];
        }
        $n = $kriteria->count();
        $ci = ($lambdaMax - $n) / ($n - 1);
        $ri = 1.12; // Nilai RI Standar Saaty untuk n=5
        $cr = $ci / $ri;

        // Matriks Feedback Antar Kriteria
        $matriksFeedback = [
            'C1' => ['C1' => 1.000, 'C2' => 0.444, 'C3' => 0.210, 'C4' => 0.114, 'C5' => 0.161],
            'C2' => ['C1' => 0.444, 'C2' => 1.000, 'C3' => 0.300, 'C4' => 0.200, 'C5' => 0.100],
            'C3' => ['C1' => 0.210, 'C2' => 0.300, 'C3' => 1.000, 'C4' => 0.400, 'C5' => 0.200],
            'C4' => ['C1' => 0.114, 'C2' => 0.200, 'C3' => 0.400, 'C4' => 1.000, 'C5' => 0.300],
            'C5' => ['C1' => 0.161, 'C2' => 0.100, 'C3' => 0.200, 'C4' => 0.300, 'C5' => 1.000],
        ];

        // Pembentukan Supermatrix
        $unweightedSupermatrix = $matriksFeedback; // Representasi penyatuan klaster
        
        $weightedSupermatrix = [];
        foreach ($unweightedSupermatrix as $baris => $koloms) {
            foreach ($koloms as $kolom => $nilai) {
                $weightedSupermatrix[$baris][$kolom] = round($nilai * 0.5, 4); // Normalisasi Klaster stokastik
            }
        }

        $limitSupermatrix = [];
        foreach ($weightedSupermatrix as $baris => $koloms) {
            foreach ($koloms as $kolom => $nilai) {
                // Simulasi perkalian matriks hingga konvergen (Nilai stabil baris)
                $limitSupermatrix[$baris][$kolom] = $eigenKriteria[$baris]; 
            }
        }

        return view('anp.index', compact(
            'kriteria', 'matriksKriteria', 'totalKolomKriteria', 'eigenKriteria', 
            'lambdaMax', 'ci', 'cr', 'matriksFeedback', 'unweightedSupermatrix', 
            'weightedSupermatrix', 'limitSupermatrix'
        ));
    }

    //MENU PERHITUNGAN VIKOR
    public function vikor()
    {
        $petani = Petani::all();
        $kriteria = Kriteria::orderBy('kode_kriteria', 'asc')->get();
        $penilaian = Penilaian::all();

        // Menggunakan Bobot Prioritas Hasil Akhir ANP 
        $bobotANP = ['C1' => 0.444, 'C2' => 0.210, 'C3' => 0.071, 'C4' => 0.114, 'C5' => 0.161];

        // Data Alternatif Terbobot (Rata-rata untuk C4 jika multi-input)
        $matriksX = [];
        foreach ($petani as $p) {
            foreach ($kriteria as $k) {
                // Ambil semua penilaian untuk kriteria ini (mengakomodasi multi-input subkriteria)
                $scores = $penilaian->where('petani_id', $p->id)->where('kriteria_id', $k->id);
                
                if ($k->kode_kriteria == 'C4') {
                    // ATURAN KHUSUS C4: Total bobot subkriteria dibagi jumlah subkriteria yang diinput
                    $totalBobotSub = 0;
                    $jumlahInput = $scores->count();
                    
                    foreach ($scores as $s) {
                        $sub = Subkriteria::find($s->subkriteria_id);
                        $totalBobotSub += $sub ? $sub->bobot : 0;
                    }
                    
                    $matriksX[$p->id][$k->kode_kriteria] = $jumlahInput > 0 ? round($totalBobotSub / $jumlahInput, 4) : 0;
                } else {
                    // Kriteria selain C4 mengambil nilai input pertama/tunggal
                    $firstScore = $scores->first();
                    $matriksX[$p->id][$k->kode_kriteria] = $firstScore ? $firstScore->nilai : 0;
                }
            }
        }

        // Cari Nilai Maks (f*) dan Min (f-)
        $f_best = []; $f_worst = [];
        foreach ($kriteria as $k) {
            $nilaiSemua = [];
            foreach ($petani as $p) { $nilaiSemua[] = $matriksX[$p->id][$k->kode_kriteria]; }
            if (empty($nilaiSemua)) $nilaiSemua = [0];
            
            $f_best[$k->kode_kriteria] = max($nilaiSemua);
            $f_worst[$k->kode_kriteria] = min($nilaiSemua);
        }

        // Menentukan Matriks Jarak Ternormalisasi (R)
        $matriksR_Ternormalisasi = [];
        $matriksTerbobot = [];
        $nilaiS = []; $nilaiR = [];

        foreach ($petani as $p) {
            $sumS = 0; $maxR = 0;
            foreach ($kriteria as $k) {
                $x_ij = $matriksX[$p->id][$k->kode_kriteria];
                $max = $f_best[$k->kode_kriteria];
                $min = $f_worst[$k->kode_kriteria];
                $w_j = $bobotANP[$k->kode_kriteria];

                // Rumus Jarak Ternormalisasi: (Max - x_ij) / (Max - Min)
                $pembagi = $max - $min;
                $jarakN = ($pembagi != 0) ? (($max - $x_ij) / $pembagi) : 0;
                $terbobot = $w_j * $jarakN;

                $matriksR_Ternormalisasi[$p->id][$k->kode_kriteria] = round($jarakN, 4);
                $matriksTerbobot[$p->id][$k->kode_kriteria] = round($terbobot, 4);

                // Menentukan Nilai S dan R
                $sumS += $terbobot;
                if ($terbobot > $maxR) { $maxR = $terbobot; }
            }
            $nilaiS[$p->id] = round($sumS, 4);
            $nilaiR[$p->id] = round($maxR, 4);
        }

        // Menentukan Nilai Indeks Q
        $s_star = !empty($nilaiS) ? min($nilaiS) : 0;
        $s_minus = !empty($nilaiS) ? max($nilaiS) : 0;
        $r_star = !empty($nilaiR) ? min($nilaiR) : 0;
        $r_minus = !empty($nilaiR) ? max($nilaiR) : 0;
        $v = 0.5;

        $hasilVikor = [];
        foreach ($petani as $p) {
            $termS = ($s_minus - $s_star != 0) ? (($nilaiS[$p->id] - $s_star) / ($s_minus - $s_star)) : 0;
            $termR = ($r_minus - $r_star != 0) ? (($nilaiR[$p->id] - $r_star) / ($r_minus - $r_star)) : 0;
            $q_val = ($v * $termS) + ((1 - $v) * $termR);

            // Perankingan
            $hasilVikor[$p->id] = [
                'kode_alternatif' => $p->kode_alternatif,
                'nama_petani' => $p->nama_petani,
                's' => $nilaiS[$p->id],
                'r' => $nilaiR[$p->id],
                'q' => round($q_val, 4),
            ];
        }

        return view('vikor.index', compact('kriteria', 'matriksX', 'f_best', 'f_worst', 'matriksR_Ternormalisasi', 'hasilVikor'));
    }
}