<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('jenis_pupuk')) {
            Schema::create('jenis_pupuk', function (Blueprint $table) {
                $table->id();
                $table->string('nama_pupuk')->unique();
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });

            DB::table('jenis_pupuk')->insert([
                ['nama_pupuk' => 'Urea', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
                ['nama_pupuk' => 'ZA', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
                ['nama_pupuk' => 'NPK', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ]);
        }

        if (!Schema::hasTable('pengajuan_petani_pupuk')) {
            Schema::create('pengajuan_petani_pupuk', function (Blueprint $table) {
                $table->id();
                $table->foreignId('pengajuan_petani_id')
                    ->constrained('pengajuan_petani')
                    ->cascadeOnDelete();
                $table->foreignId('jenis_pupuk_id')
                    ->constrained('jenis_pupuk')
                    ->restrictOnDelete();
                $table->decimal('jumlah_kg', 12, 2);
                $table->timestamps();

                $table->unique(
                    ['pengajuan_petani_id', 'jenis_pupuk_id'],
                    'uniq_pengajuan_jenis_pupuk'
                );
            });
        }

        if (!Schema::hasTable('alternatif_musim_pupuk')) {
            Schema::create('alternatif_musim_pupuk', function (Blueprint $table) {
                $table->id();
                $table->foreignId('alternatif_musim_id')
                    ->constrained('alternatif_musim')
                    ->cascadeOnDelete();
                $table->foreignId('jenis_pupuk_id')
                    ->constrained('jenis_pupuk')
                    ->restrictOnDelete();
                $table->decimal('jumlah_kg', 12, 2);
                $table->timestamps();

                $table->unique(
                    ['alternatif_musim_id', 'jenis_pupuk_id'],
                    'uniq_alternatif_jenis_pupuk'
                );
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('alternatif_musim_pupuk');
        Schema::dropIfExists('pengajuan_petani_pupuk');
        Schema::dropIfExists('jenis_pupuk');
    }
};
