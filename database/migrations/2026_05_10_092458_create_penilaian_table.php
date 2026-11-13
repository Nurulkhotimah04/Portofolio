<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    // Untuk membuat tabel penilaian untuk petani
    public function up(): void
    {
        Schema::create('penilaian', function (Blueprint $table) {

            // Primary key
            $table->id();

            // Relasi untuk ke tabel petani yang digunakan untuk mengetahui petani mana yang sedang dinilai
        
            $table->foreignId('petani_id')
                  ->constrained('petani')
                  ->onDelete('cascade');

            // Relasi untuk ke tabel kriteria yang digunakan untuk mengetahui kriteria apa yang dinilai.
            $table->foreignId('kriteria_id')
                  ->constrained('kriteria')
                  ->onDelete('cascade');

            // Relasi untuk ke tabel subkriteria yang digunakan untuk mengetahui pilihan subkriteria dari petani.
            
            $table->foreignId('subkriteria_id')
                  ->constrained('subkriteria')
                  ->onDelete('cascade');

            // Nilai ini diambil dari bobot subkriteria.
        
            $table->float('nilai', 8, 4);

            // created_at dan updated_at
            $table->timestamps();
        });
    }

    // Untk menghapus tabel penilaian
    public function down(): void
    {
        Schema::dropIfExists('penilaian');
    }
};