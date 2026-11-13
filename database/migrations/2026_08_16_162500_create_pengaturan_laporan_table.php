<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pengaturan_laporan', function (Blueprint $table) {
            $table->id();
            $table->string('nama_kepala_dinas', 150)->nullable();
            $table->string('nip_kepala_dinas', 50)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pengaturan_laporan');
    }
};
