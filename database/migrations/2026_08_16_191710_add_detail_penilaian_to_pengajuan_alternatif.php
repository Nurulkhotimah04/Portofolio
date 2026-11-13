<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pengajuan_petani', function (Blueprint $table) {
            if (!Schema::hasColumn('pengajuan_petani', 'luas_lahan_ha')) {
                $table->decimal('luas_lahan_ha', 8, 2)->nullable()->after('nama_petani');
            }

            if (!Schema::hasColumn('pengajuan_petani', 'berkas_persyaratan_path')) {
                $table->string('berkas_persyaratan_path')->nullable()->after('kelengkapan_berkas');
            }
        });

        Schema::table('alternatif_musim', function (Blueprint $table) {
            if (!Schema::hasColumn('alternatif_musim', 'luas_lahan_ha')) {
                $table->decimal('luas_lahan_ha', 8, 2)->nullable()->after('kode_alternatif');
            }

            if (!Schema::hasColumn('alternatif_musim', 'berkas_persyaratan_path')) {
                $table->string('berkas_persyaratan_path')->nullable()->after('kelengkapan_berkas');
            }
        });
    }

    public function down(): void
    {
        Schema::table('alternatif_musim', function (Blueprint $table) {
            if (Schema::hasColumn('alternatif_musim', 'berkas_persyaratan_path')) {
                $table->dropColumn('berkas_persyaratan_path');
            }
            if (Schema::hasColumn('alternatif_musim', 'luas_lahan_ha')) {
                $table->dropColumn('luas_lahan_ha');
            }
        });

        Schema::table('pengajuan_petani', function (Blueprint $table) {
            if (Schema::hasColumn('pengajuan_petani', 'berkas_persyaratan_path')) {
                $table->dropColumn('berkas_persyaratan_path');
            }
            if (Schema::hasColumn('pengajuan_petani', 'luas_lahan_ha')) {
                $table->dropColumn('luas_lahan_ha');
            }
        });
    }
};
