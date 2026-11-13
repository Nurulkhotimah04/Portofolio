<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Jadikan semua kolom petani yang bermasalah (NOT NULL tanpa default) menjadi nullable
     * agar petani bisa submit form tanpa harus mengisi semua field sekaligus.
     */
    public function up(): void
    {
        Schema::table('petani', function (Blueprint $table) {
            $table->string('nik')->nullable()->default(null)->change();
            $table->string('kode_alternatif')->nullable()->default(null)->change();
            $table->string('nama_petani')->nullable()->default(null)->change();
            $table->string('poktan_id')->nullable()->default(null)->change();
            $table->string('luas_lahan')->nullable()->default(null)->change();
            $table->string('jenis_komoditas')->nullable()->default(null)->change();
            $table->string('kelengkapan_berkas')->nullable()->default(null)->change();
            $table->string('kebutuhan_pupuk')->nullable()->default(null)->change();
            $table->string('subsektor_pertanian')->nullable()->default(null)->change();
        });
    }

    public function down(): void
    {
        Schema::table('petani', function (Blueprint $table) {
            $table->string('nik')->nullable(false)->change();
            $table->string('kode_alternatif')->nullable(false)->change();
        });
    }
};

