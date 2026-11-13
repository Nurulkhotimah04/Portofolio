<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('vikor_hasil', function (Blueprint $table) {
            $table->foreignId('alternatif_musim_id')
                ->nullable()
                ->after('petani_id')
                ->constrained('alternatif_musim')
                ->cascadeOnDelete();

            $table->unique(
                'alternatif_musim_id',
                'uniq_vikor_alt_musim'
            );
        });
    }

    public function down(): void
    {
        Schema::table('vikor_hasil', function (Blueprint $table) {
            $table->dropForeign(['alternatif_musim_id']);
            $table->dropUnique('uniq_vikor_alt_musim');
            $table->dropColumn('alternatif_musim_id');
        });
    }
};