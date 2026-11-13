<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    // Menambahkan kolom role ke kolom user

    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {

            // Kolom role yaitu admin, penyuluh, dan petani
            $table->string('role')->default('petani');
        });
    }

    // Untuk menghapus kolom role

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {

            $table->dropColumn('role');

        });
    }
};