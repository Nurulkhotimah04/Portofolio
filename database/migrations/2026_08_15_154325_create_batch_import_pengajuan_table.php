<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('batch_import_pengajuan', function (Blueprint $table) {
            $table->id();
            $table->string('kode_batch')->unique();

            $table->foreignId('penyuluh_id')
                ->constrained('penyuluh')
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

            $table->string('nama_file')->nullable();

            $table->unsignedInteger('total_data')->default(0);
            $table->unsignedInteger('total_valid')->default(0);
            $table->unsignedInteger('total_gagal')->default(0);

            $table->enum('status', [
                'diproses',
                'selesai',
                'gagal',
            ])->default('diproses');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('batch_import_pengajuan');
    }
};