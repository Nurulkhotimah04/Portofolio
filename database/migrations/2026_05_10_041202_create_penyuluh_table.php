<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    // Untuk menjalankan migration
    public function up(): void
    {
        Schema::create('penyuluh', function (Blueprint $table) {

            // Primary key
            $table->id();

            // Relasi untuk ke tabel user
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');

            // Data data dari penyuluh
            // Nama penyuluh
            $table->string('nama_penyuluh');

            // poktan
            $table->string('poktan_id');

            $table->timestamps();
        });
    }

    // Untuk menghapus tabel
    public function down(): void
    {
        Schema::dropIfExists('penyuluh');
    }
};