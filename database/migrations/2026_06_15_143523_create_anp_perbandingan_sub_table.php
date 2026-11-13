<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {

    public function up(): void
    {
        Schema::create('anp_perbandingan_sub', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('kriteria_id'); // Menandakan subkriteria ini milik kriteria mana
            $table->unsignedBigInteger('subkriteria_1');
            $table->unsignedBigInteger('subkriteria_2');
            $table->double('nilai');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('anp_perbandingan_sub');
    }
};