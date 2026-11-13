<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Petani;
use App\Models\Poktan;
use App\Models\AlternatifMusim;

class TambahAlternatifMT22026Seeder extends Seeder
{
    public function run(): void
    {
        $mt1Codes = ['A52','A64','A74','A142','A178'];

        $mt2Codes = collect(range(195,294))
            ->map(fn($i) => 'A'.$i)
            ->toArray();

        // Nama asli tanpa angka tambahan
        $names = [
            'Marjuki','Santoso','Suparman','Haryanto','Ahmad Fauzan',
            'Suyanto','Mulyadi','Slamet Riyadi','Rudi Hartono',
            'Wahyudi','Sutrisno','Joko Susanto','Darmadi',
            'Taufik Hidayat','Agus Setiawan','Bambang Irawan',
            'Yusuf Kurniawan','Hasan Basri','Rizal Maulana',
            'Fahrudin','Sukardi','Widodo','Kusnadi','Ramli',
            'Abdul Rahman','Dedi Kurniawan','Rahmat Hidayat',
            'Suharto','Irawan','Gunawan'
        ];

        $codes = array_merge($mt1Codes, $mt2Codes);

        foreach ($codes as $index => $kode) {

            if (AlternatifMusim::where('kode_alternatif', $kode)->exists()) {
                continue;
            }

            $poktan = Poktan::inRandomOrder()->first();

            $petani = Petani::create([
                'nama_petani' => $names[$index % count($names)],
                'nik' => '998877660000' . str_pad($index + 1, 4, '0', STR_PAD_LEFT),
                'poktan_id' => $poktan->id,
            ]);

            AlternatifMusim::create([
                'petani_id' => $petani->id,
                'poktan_id' => $poktan->id,
                'musim_tanam_id' => in_array($kode, $mt1Codes) ? 2 : 3,
                'periode_id' => 3,
                'kode_alternatif' => $kode,
                'luas_lahan_ha' => 0.50,
                'luas_lahan' => '≤ 0,5 Ha',
                'jenis_komoditas' => 'Padi',
                'kelengkapan_berkas' => 'Lengkap',
                'kebutuhan_pupuk' => 'Membutuhkan 2 Pupuk',
                'subsektor_pertanian' => 'Tanaman Pangan',
            ]);
        }
    }
}
