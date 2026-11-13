<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class FinalisasiAplikasi extends Command
{
    protected $signature = 'app:finalize {--dry-run : Tampilkan file yang akan dihapus tanpa menghapusnya}';

    protected $description = 'Membersihkan file debug/test yang tidak boleh berada pada versi final aplikasi.';

    public function handle(): int
    {
        $publicPatterns = [
            'debug*.php',
            'debug*.json',
            'add_bobot.php',
            'clear_opcache.php',
            'delete_adji.php',
            'fix_db.php',
            'format_blade.php',
            'format3.php',
        ];

        $rootFiles = [
            base_path('db_test.php'),
            base_path('scratch.php'),
            base_path('penyuluh'),
        ];

        $files = collect($rootFiles)->filter(fn ($file) => is_file($file));

        foreach ($publicPatterns as $pattern) {
            foreach (glob(public_path($pattern)) ?: [] as $file) {
                if (is_file($file)) {
                    $files->push($file);
                }
            }
        }

        $files = $files->unique()->sort()->values();

        if ($files->isEmpty()) {
            $this->info('Tidak ada file debug/test yang perlu dibersihkan.');
            return self::SUCCESS;
        }

        foreach ($files as $file) {
            $relative = str_replace(base_path() . DIRECTORY_SEPARATOR, '', $file);

            if ($this->option('dry-run')) {
                $this->line('[DRY RUN] ' . $relative);
                continue;
            }

            if (!@unlink($file)) {
                $this->error('Gagal menghapus: ' . $relative);
                return self::FAILURE;
            }

            $this->line('Dihapus: ' . $relative);
        }

        $this->info($this->option('dry-run')
            ? 'Dry-run selesai. Tidak ada file yang dihapus.'
            : 'Pembersihan file debug/test selesai.');

        return self::SUCCESS;
    }
}
