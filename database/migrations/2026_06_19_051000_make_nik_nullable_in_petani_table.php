<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('petani', function (Blueprint $table) {
            // Jadikan nik nullable karena petani yang daftar via form tidak perlu mengisi NIK manual
            // NIK bisa diisi oleh admin/penyuluh saat verifikasi lapangan
            $table->string('nik')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('petani', function (Blueprint $table) {
            $table->string('nik')->nullable(false)->change();
        });
    }
};
