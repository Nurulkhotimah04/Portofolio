<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('subkriteria', function (Blueprint $table) {
            $table->string('kode_subkriteria')->after('kriteria_id');
        });
    }

    public function down(): void
    {
        Schema::table('subkriteria', function (Blueprint $table) {
            $table->dropColumn('kode_subkriteria');
        });
    }
};