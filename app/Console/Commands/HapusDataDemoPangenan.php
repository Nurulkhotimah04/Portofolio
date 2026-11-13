<?php

namespace App\Console\Commands;

use App\Models\AlternatifMusim;
use App\Models\Petani;
use App\Models\Penyuluh;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class HapusDataDemoPangenan extends Command
{
    protected $signature = 'data:hapus-demo-pangenan {--preview : Tampilkan data yang akan dihapus tanpa menyimpan perubahan}';

    protected $description = 'Menghapus data demo Pangenan yang dibuat oleh command pengujian, tanpa menyentuh A1-A30/data asli.';

    public function handle(): int
    {
        $preview = (bool) $this->option('preview');
        $nikPrefix = '320999000000%';
        $usernames = ['demo_pangenan_01', 'demo_pangenan_02', 'demo_pangenan_03'];

        $petaniIds = Petani::where('nik', 'like', $nikPrefix)->pluck('id');
        $alternatifs = AlternatifMusim::whereIn('petani_id', $petaniIds)->with('petani')->get();

        $this->line('Alternatif demo: ' . $alternatifs->count());
        foreach ($alternatifs as $alt) {
            $this->line("- {$alt->kode_alternatif} / {$alt->petani?->nama_petani}");
        }

        $penyuluhs = Penyuluh::whereHas('user', fn ($q) => $q->whereIn('username', $usernames))->with(['user','poktan'])->get();
        $this->line('Penyuluh demo: ' . $penyuluhs->count());

        if ($preview) {
            $this->warn('PREVIEW selesai. Tidak ada data yang dihapus.');
            return self::SUCCESS;
        }

        DB::transaction(function () use ($alternatifs, $petaniIds, $penyuluhs) {
            foreach ($alternatifs as $alt) {
                $alt->vikorHasil()?->delete();
                $alt->delete();
            }

            Petani::whereIn('id', $petaniIds)->whereDoesntHave('alternatifMusim')->delete();

            foreach ($penyuluhs as $penyuluh) {
                // Hanya hapus Poktan milik akun demo jika sudah tidak punya data operasional.
                foreach ($penyuluh->poktan as $poktan) {
                    if (!$poktan->petani()->exists() && !$poktan->pengajuanPetani()->exists() && !$poktan->alternatifMusim()->exists()) {
                        $poktan->riwayatPembinaan()->delete();
                        $poktan->delete();
                    }
                }

                if (!$penyuluh->poktan()->exists() && !$penyuluh->pengajuanPetani()->exists()) {
                    $user = $penyuluh->user;
                    $penyuluh->penugasan()->delete();
                    $penyuluh->delete();
                    $user?->delete();
                }
            }
        });

        $this->info('Data demo Pangenan selesai dibersihkan.');
        return self::SUCCESS;
    }
}
