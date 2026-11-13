<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('penyuluh', 'status')) {
            Schema::table('penyuluh', function (Blueprint $table) {
                $table->string('status', 30)
                    ->default('aktif')
                    ->after('nama_penyuluh');
            });
        }

        if (!Schema::hasColumn('poktan', 'status')) {
            Schema::table('poktan', function (Blueprint $table) {
                $table->string('status', 20)
                    ->default('aktif')
                    ->after('kecamatan_id');
            });
        }

        if (!Schema::hasTable('penugasan_penyuluh')) {
            Schema::create('penugasan_penyuluh', function (Blueprint $table) {
                $table->id();
                $table->foreignId('penyuluh_id')
                    ->constrained('penyuluh')
                    ->cascadeOnDelete();
                $table->foreignId('kecamatan_id')
                    ->constrained('kecamatan')
                    ->restrictOnDelete();
                $table->date('tanggal_mulai');
                $table->date('tanggal_selesai')->nullable();
                $table->string('status', 20)->default('aktif');
                $table->text('catatan')->nullable();
                $table->timestamps();

                $table->index(['penyuluh_id', 'status'], 'idx_penugasan_penyuluh_status');
                $table->index(['kecamatan_id', 'status'], 'idx_penugasan_kecamatan_status');
            });
        }

        // Backfill penugasan aktif dari Poktan lama jika seluruh Poktan
        // seorang Penyuluh memang berada pada satu Kecamatan yang sama.
        $penyuluhIds = DB::table('penyuluh')->pluck('id');

        foreach ($penyuluhIds as $penyuluhId) {
            $sudahAda = DB::table('penugasan_penyuluh')
                ->where('penyuluh_id', $penyuluhId)
                ->exists();

            if ($sudahAda) {
                continue;
            }

            $kecamatanIds = DB::table('poktan')
                ->where('penyuluh_id', $penyuluhId)
                ->whereNotNull('kecamatan_id')
                ->distinct()
                ->pluck('kecamatan_id');

            if ($kecamatanIds->count() === 1) {
                DB::table('penugasan_penyuluh')->insert([
                    'penyuluh_id' => $penyuluhId,
                    'kecamatan_id' => $kecamatanIds->first(),
                    'tanggal_mulai' => now()->toDateString(),
                    'tanggal_selesai' => null,
                    'status' => 'aktif',
                    'catatan' => 'Penugasan awal hasil migrasi dari data Poktan yang sudah ada.',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('penugasan_penyuluh');

        if (Schema::hasColumn('poktan', 'status')) {
            Schema::table('poktan', function (Blueprint $table) {
                $table->dropColumn('status');
            });
        }

        if (Schema::hasColumn('penyuluh', 'status')) {
            Schema::table('penyuluh', function (Blueprint $table) {
                $table->dropColumn('status');
            });
        }
    }
};
