<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    // Untuk membuat tabel kriteria
    public function up(): void
    {
        Schema::create('kriteria', function (Blueprint $table) {

            // Primary key
            $table->id();

            // Kode kriteria
            $table->string('kode_kriteria');

            // Nama kriteria
            $table->string('nama_kriteria');

            // Bobot kriteria
            $table->integer('bobot');

            // Jenis atribut
            $table->enum('atribut', ['benefit', 'cost'])
                  ->default('benefit');

            // created_at dan updated_at
            $table->timestamps();
        });
    }

    // Untuk  menghapus tabel
    public function down(): void
    {
        Schema::dropIfExists('kriteria');
    }
};