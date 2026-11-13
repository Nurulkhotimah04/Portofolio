<?php

namespace App\Console\Commands;

use App\Models\AlternatifMusim;
use App\Models\PenugasanPenyuluh;
use App\Models\Penyuluh;
use App\Models\Poktan;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class CekKonsistensiFinal extends Command
{
    protected $signature = 'app:cek-konsistensi';

    protected $description = 'Memeriksa konsistensi Kecamatan-Penyuluh-Poktan dan kelengkapan C1-C5 tanpa mengubah data.';

    public function handle(): int
    {
        $issues = [];

        $multiAktif = DB::table('penugasan_penyuluh')
            ->select('penyuluh_id', DB::raw('COUNT(*) total'))
            ->where('status', 'aktif')
            ->whereNull('tanggal_selesai')
            ->groupBy('penyuluh_id')
            ->having('total', '>', 1)
            ->get();
        foreach ($multiAktif as $row) {
            $nama = Penyuluh::find($row->penyuluh_id)?->nama_penyuluh ?? $row->penyuluh_id;
            $issues[] = "Penyuluh {$nama} memiliki {$row->total} penugasan Kecamatan aktif.";
        }

        Poktan::with('penyuluh.penugasanAktif')->where('status', 'aktif')->chunkById(100, function ($poktans) use (&$issues) {
            foreach ($poktans as $poktan) {
                if (!$poktan->penyuluh_id) {
                    $issues[] = "Poktan {$poktan->nama_poktan} belum mempunyai Penyuluh.";
                    continue;
                }
                if (!$poktan->kecamatan_id) {
                    $issues[] = "Poktan {$poktan->nama_poktan} belum mempunyai Kecamatan.";
                    continue;
                }
                $aktif = $poktan->penyuluh?->penugasanAktif;
                if (!$aktif) {
                    $issues[] = "Penyuluh {$poktan->penyuluh?->nama_penyuluh} pada Poktan {$poktan->nama_poktan} tidak memiliki penugasan aktif.";
                    continue;
                }
                if ((int) $aktif->kecamatan_id !== (int) $poktan->kecamatan_id) {
                    $issues[] = "Poktan {$poktan->nama_poktan} tidak satu Kecamatan dengan penugasan aktif Penyuluh {$poktan->penyuluh?->nama_penyuluh}.";
                }
            }
        });

        AlternatifMusim::with('petani')->chunkById(100, function ($alternatifs) use (&$issues) {
            foreach ($alternatifs as $alt) {
                foreach (['luas_lahan','jenis_komoditas','kelengkapan_berkas','kebutuhan_pupuk','subsektor_pertanian'] as $field) {
                    if (blank($alt->{$field})) {
                        $issues[] = "Alternatif {$alt->kode_alternatif} ({$alt->petani?->nama_petani}) masih kosong pada {$field}.";
                    }
                }
            }
        });

        if (!$issues) {
            $this->info('Konsistensi data utama: OK. Tidak ditemukan masalah.');
            return self::SUCCESS;
        }

        $this->warn('Ditemukan ' . count($issues) . ' masalah:');
        foreach ($issues as $issue) {
            $this->line('- ' . $issue);
        }

        return self::FAILURE;
    }
}
