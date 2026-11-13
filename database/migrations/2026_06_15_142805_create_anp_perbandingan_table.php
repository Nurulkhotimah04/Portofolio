<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {

    public function up(): void
    {
        Schema::create('anp_perbandingan', function (Blueprint $table) {

            $table->id();

            $table->unsignedBigInteger('kriteria_1');

            $table->unsignedBigInteger('kriteria_2');

            $table->double('nilai');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('anp_perbandingan');
    }
};