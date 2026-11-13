<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('kriteria_network', function (Blueprint $table) {
            $table->id();
            $table->foreignId('kriteria_asal_id')->constrained('kriteria')->onDelete('cascade');
            $table->foreignId('kriteria_tujuan_id')->constrained('kriteria')->onDelete('cascade');
            $table->boolean('is_related')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kriteria_network');
    }
};
