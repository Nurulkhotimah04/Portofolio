<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Kriteria;
use App\Models\Subkriteria;

class KriteriaSeeder extends Seeder
{
    public function run(): void
    {
        // Menghapus data lama agar tidak dobel
        Subkriteria::truncate();
        Kriteria::truncate();

        // Data kriteria
        $kriteria = [
            [
                'kode_kriteria' => 'C1',
                'nama_kriteria' => 'Luas Lahan',
                'atribut' => 'benefit',
                'subkriteria' => [
                    ['nama_subkriteria' => '≤ 0,5 Ha'],
                    ['nama_subkriteria' => '0,6 – 1 Ha'],
                    ['nama_subkriteria' => '1,1 – 2 Ha'],
                ],
            ],
            [
                'kode_kriteria' => 'C2',
                'nama_kriteria' => 'Jenis Komoditas',
                'atribut' => 'benefit',
                'subkriteria' => [
                    ['nama_subkriteria' => 'Padi'],
                    ['nama_subkriteria' => 'Jagung'],
                    ['nama_subkriteria' => 'Tebu Rakyat'],
                    ['nama_subkriteria' => 'Bawang Merah'],
                ],
            ],
            [
                'kode_kriteria' => 'C3',
                'nama_kriteria' => 'Kelengkapan Berkas',
                'atribut' => 'benefit',
                'subkriteria' => [
                    ['nama_subkriteria' => 'Lengkap'],
                    ['nama_subkriteria' => 'Kurang Lengkap'],
                    ['nama_subkriteria' => 'Tidak Lengkap'],
                ],
            ],
            [
                'kode_kriteria' => 'C4',
                'nama_kriteria' => 'Kebutuhan Pupuk',
                'atribut' => 'benefit',
                'subkriteria' => [
                    ['nama_subkriteria' => 'Pupuk Urea'],
                    ['nama_subkriteria' => 'Pupuk NPK'],
                    ['nama_subkriteria' => 'Pupuk Organik'],
                    ['nama_subkriteria' => 'Pupuk ZA'],
                ],
            ],
            [
                'kode_kriteria' => 'C5',
                'nama_kriteria' => 'Subsektor Pertanian',
                'atribut' => 'benefit',
                'subkriteria' => [
                    ['nama_subkriteria' => 'Tanaman Pangan'],
                    ['nama_subkriteria' => 'Hortikultura'],
                    ['nama_subkriteria' => 'Perkebunan'],
                ],
            ],
        ];

        // Menyimpan kriteria dan subkriteria
        foreach ($kriteria as $data) {
            $subkriteria = $data['subkriteria'];

            unset($data['data subkriteria']); // membersihkan array subkriteria sebelum insert kriteria
            unset($data['subkriteria']);

            $kriteriaBaru = Kriteria::create($data);

            foreach ($subkriteria as $sub) {
                // Insert data subkriteria tanpa kolom bobot
                Subkriteria::create([
                    'kriteria_id' => $kriteriaBaru->id,
                    'nama_subkriteria' => $sub['nama_subkriteria'],
                ]);
            }
        }
    }
}