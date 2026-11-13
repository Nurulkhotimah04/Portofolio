<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('musim_tanam', function (Blueprint $table) {
            $table->id();
            $table->string('kode_musim')->unique();
            $table->string('nama_musim');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('musim_tanam');
    }
};