<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class Mt2AlternatifSeeder extends Seeder
{
    public function run(): void
    {
        /*
         * Seeder penambahan alternatif MT2 periode 2026.
         * Tidak mengubah controller/model/perhitungan ANP-VIKOR.
         *
         * Sesuaikan ID penyuluh dan poktan apabila berbeda
         * dengan database lokal.
         */

        $musimTanamId = 3; // MT2
        $periodeId = 2026;

        $dataAlternatif = [
            // Data ditambahkan melalui proses insert agar mengikuti tabel aplikasi.
            // Format:
            // petani_id, penyuluh_id, poktan_id, musim_tanam_id, periode_id
        ];

        foreach ($dataAlternatif as $item) {
            DB::table('alternatif_musim')->insert([
                'petani_id' => $item['petani_id'],
                'penyuluh_id' => $item['penyuluh_id'],
                'poktan_id' => $item['poktan_id'],
                'musim_tanam_id' => $musimTanamId,
                'periode_id' => $periodeId,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
