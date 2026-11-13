<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('penugasan_poktan')) {
            Schema::create('penugasan_poktan', function (Blueprint $table) {
                $table->id();
                $table->foreignId('poktan_id')
                    ->constrained('poktan')
                    ->cascadeOnDelete();
                $table->foreignId('penyuluh_id')
                    ->constrained('penyuluh')
                    ->restrictOnDelete();
                $table->date('tanggal_mulai');
                $table->date('tanggal_selesai')->nullable();
                $table->string('status', 20)->default('aktif');
                $table->text('catatan')->nullable();
                $table->timestamps();

                $table->index(['poktan_id', 'status'], 'idx_penugasan_poktan_status');
                $table->index(['penyuluh_id', 'status'], 'idx_pembinaan_penyuluh_status');
            });
        }

        // Backfill riwayat pembinaan dari relasi Poktan yang sudah ada.
        $poktans = DB::table('poktan')
            ->whereNotNull('penyuluh_id')
            ->select('id', 'penyuluh_id', 'status')
            ->get();

        foreach ($poktans as $poktan) {
            $exists = DB::table('penugasan_poktan')
                ->where('poktan_id', $poktan->id)
                ->exists();

            if ($exists) {
                continue;
            }

            DB::table('penugasan_poktan')->insert([
                'poktan_id' => $poktan->id,
                'penyuluh_id' => $poktan->penyuluh_id,
                'tanggal_mulai' => now()->toDateString(),
                'tanggal_selesai' => ($poktan->status ?? 'aktif') === 'aktif' ? null : now()->toDateString(),
                'status' => ($poktan->status ?? 'aktif') === 'aktif' ? 'aktif' : 'selesai',
                'catatan' => 'Riwayat awal hasil migrasi dari relasi Poktan yang sudah ada.',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('penugasan_poktan');
    }
};
