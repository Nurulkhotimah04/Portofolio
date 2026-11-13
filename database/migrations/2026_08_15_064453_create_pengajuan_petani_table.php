<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
   public function up(): void
{
    Schema::create('pengajuan_petani', function (Blueprint $table) {
        $table->id();

        // Penyuluh yang mengajukan
        $table->foreignId('penyuluh_id')
            ->constrained('penyuluh')
            ->cascadeOnDelete();

        // Data calon petani
        $table->string('nik');
        $table->string('nama_petani');

        // Data yang nantinya masuk ke tabel petani / alternatif
        $table->string('luas_lahan')->nullable();
        $table->string('jenis_komoditas')->nullable();
        $table->string('kelengkapan_berkas')->nullable();
        $table->string('kebutuhan_pupuk')->nullable();
        $table->string('subsektor_pertanian')->nullable();

        $table->foreignId('poktan_id')
            ->nullable()
            ->constrained('poktan')
            ->nullOnDelete();

        // Status pengajuan
        $table->enum('status', [
            'menunggu_verifikasi',
            'perlu_perbaikan',
            'disetujui',
            'ditolak'
        ])->default('menunggu_verifikasi');

        // Catatan
        $table->text('catatan_penyuluh')->nullable();
        $table->text('catatan_admin')->nullable();

        // Admin yang memverifikasi
        $table->foreignId('verified_by')
            ->nullable()
            ->constrained('users')
            ->nullOnDelete();

        $table->timestamp('tanggal_pengajuan')->nullable();
        $table->timestamp('tanggal_verifikasi')->nullable();

        $table->timestamps();
    });
}
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pengajuan_petani');
    }
};
