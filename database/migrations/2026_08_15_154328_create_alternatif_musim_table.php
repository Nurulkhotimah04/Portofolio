<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('alternatif_musim', function (Blueprint $table) {
            $table->id();

            $table->foreignId('petani_id')
                ->constrained('petani')
                ->restrictOnDelete();

            $table->foreignId('poktan_id')
                ->constrained('poktan')
                ->restrictOnDelete();

            $table->foreignId('musim_tanam_id')
                ->constrained('musim_tanam')
                ->restrictOnDelete();

            $table->foreignId('periode_id')
                ->constrained('periode')
                ->restrictOnDelete();

            $table->foreignId('pengajuan_petani_id')
                ->nullable()
                ->constrained('pengajuan_petani')
                ->nullOnDelete();

            $table->string('kode_alternatif', 20);

            $table->string('luas_lahan');
            $table->string('jenis_komoditas');
            $table->string('kelengkapan_berkas');
            $table->string('kebutuhan_pupuk');
            $table->string('subsektor_pertanian');

            $table->timestamps();

            $table->unique(
                ['petani_id', 'musim_tanam_id', 'periode_id'],
                'uniq_alt_petani_musim_periode'
            );

            $table->unique(
                ['musim_tanam_id', 'periode_id', 'kode_alternatif'],
                'uniq_alt_kode_musim_periode'
            );

            $table->index(
                ['musim_tanam_id', 'periode_id', 'poktan_id'],
                'idx_alt_filter'
            );

            $table->unique(
                'pengajuan_petani_id',
                'uniq_alt_pengajuan'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('alternatif_musim');
    }
};