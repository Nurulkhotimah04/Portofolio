<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('poktan', function (Blueprint $table) {
            $table->foreignId('kecamatan_id')
                ->nullable()
                ->after('penyuluh_id')
                ->constrained('kecamatan')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('poktan', function (Blueprint $table) {
            $table->dropForeign(['kecamatan_id']);
            $table->dropColumn('kecamatan_id');
        });
    }
};