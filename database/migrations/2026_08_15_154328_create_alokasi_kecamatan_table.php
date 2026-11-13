<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('alokasi_kecamatan', function (Blueprint $table) {
            $table->id();

            $table->foreignId('kecamatan_id')
                ->constrained('kecamatan')
                ->restrictOnDelete();

            $table->foreignId('musim_tanam_id')
                ->constrained('musim_tanam')
                ->restrictOnDelete();

            $table->foreignId('periode_id')
                ->constrained('periode')
                ->restrictOnDelete();

            $table->unsignedInteger('kuota_penerima');

            $table->timestamps();

            $table->unique(
                ['kecamatan_id', 'musim_tanam_id', 'periode_id'],
                'uniq_alokasi_kec_musim_periode'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('alokasi_kecamatan');
    }
};