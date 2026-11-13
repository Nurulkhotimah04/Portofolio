<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\AlternatifMusim;
use App\Models\Petani;

class UpdateNamaAlternatifMT22026Seeder extends Seeder
{
    public function run(): void
    {
        $namaPetani = [
            'Marjuki','Santoso','Suparman','Haryanto','Ahmad Fauzan',
            'Suyanto','Mulyadi','Slamet Riyadi','Rudi Hartono','Wahyudi',
            'Sutrisno','Joko Susanto','Darmadi','Taufik Hidayat','Agus Setiawan',
            'Bambang Irawan','Yusuf Kurniawan','Hasan Basri','Rizal Maulana','Fahrudin',
            'Sukardi','Widodo','Kusnadi','Ramli','Abdul Rahman',
            'Dedi Kurniawan','Rahmat Hidayat','Suharto','Irawan','Gunawan',
            'Suyono','Parman','Kasim','Herman','Sutopo',
            'Wahidin','Rohman','Sugianto','Basuki','Daryanto',
            'Narto','Karyadi','Subandi','Slamet','Purnomo',
            'Yanto','Junaidi','Asep Setiawan','Rohadi','Mustofa',
            'Rohmat','Fauzi Rahman','Imam Syafii','Agung Pratama','Fajar Nugroho',
            'Bayu Saputra','Roni Setiawan','Dedi Irawan','Arif Rahman','Rizki Hidayat',
            'Hendra Gunawan','Budi Santoso','Eko Prasetyo','Cahyo Widodo','Guntur Pratama',
            'Andi Kurniawan','Rizal Haryanto','Fikri Maulana','Hadi Susanto','Yudi Hartono',
            'Teguh Pratama','Wawan Setiawan','Aminudin','Darsono','Kusnanto',
            'Rohim','Sarman','Suwandi','Darmawan','Soleh',
            'Nurdin','Feri Irawan','Maman Suherman','Ari Setiawan','Rudi Kurniawan',
            'Dadan Ramadhan','Agus Salim','Yusuf Hidayat','Rangga Pratama','Doni Saputra',
            'Ilham Fauzi','Rendi Kurniawan','Samsul Bahri','Fauzan Akbar','Rizwan Maulana',
            'Hasanuddin','Amin Rahman','Fadli Setiawan','Rian Saputra','Bagus Santoso'
        ];

        $alternatif = AlternatifMusim::whereBetween('kode_alternatif', ['A195','A294'])
            ->orderByRaw('CAST(SUBSTRING(kode_alternatif,2) AS UNSIGNED)')
            ->get();

        foreach ($alternatif as $index => $item) {
            $petani = Petani::find($item->petani_id);

            if ($petani && isset($namaPetani[$index])) {
                $petani->update([
                    'nama_petani' => $namaPetani[$index],
                ]);
            }
        }
    }
}
