<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class FinalApplicationStructureTest extends TestCase
{
    public function test_final_named_routes_are_registered(): void
    {
        $routes = [
            'dashboard',
            'poktan.index',
            'pengajuan.index',
            'pengajuan.verify-c3',
            'alternatif-musim.index',
            'vikor.index',
            'ranking.index',
            'vikor.riwayat',
            'laporan.index',
            'jenis-pupuk.index',
            'audit.index',
            'audit.destroy-selected',
            'audit.destroy-all',
            'profile.show',
        ];

        foreach ($routes as $routeName) {
            $this->assertTrue(Route::has($routeName), "Route [{$routeName}] harus tersedia pada versi final.");
        }
    }

    public function test_guest_cannot_open_operational_pages(): void
    {
        $this->get('/alternatif-musim')->assertRedirect('/login');
        $this->get('/pengajuan-petani')->assertRedirect('/login');
        $this->get('/laporan')->assertRedirect('/login');
    }

    public function test_public_debug_scripts_have_been_removed(): void
    {
        $dangerousFiles = [
            'debug.php',
            'debug_db.php',
            'debug_vikor.php',
            'add_bobot.php',
            'clear_opcache.php',
            'delete_adji.php',
            'fix_db.php',
            'format_blade.php',
            'format3.php',
        ];

        foreach ($dangerousFiles as $file) {
            $this->assertFileDoesNotExist(public_path($file), "File public/{$file} harus dihapus sebelum final testing.");
        }
    }
}
