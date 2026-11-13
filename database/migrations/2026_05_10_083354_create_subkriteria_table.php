<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{

    // Membuat tabel subkriteria
    public function up(): void
    {
        Schema::create('subkriteria', function (Blueprint $table) {

            // Primary key
            $table->id();

            // Relasi untuk ke tabel kriteria
            $table->foreignId('kriteria_id')
                  ->constrained('kriteria')
                  ->onDelete('cascade');

            // Nama subkriteria
            $table->string('nama_subkriteria');

            // Bobot subkriteria
            $table->integer('bobot');

            // created_at dan updated_at
            $table->timestamps();
        });
    }

    // Untuk menghapus tabel
    public function down(): void
    {
        Schema::dropIfExists('subkriteria');
    }
};