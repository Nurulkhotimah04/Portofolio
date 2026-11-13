<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pengajuan_petani', function (Blueprint $table) {
            if (!Schema::hasColumn('pengajuan_petani', 'kelengkapan_berkas_verifikasi')) {
                $table->string('kelengkapan_berkas_verifikasi')->nullable()->after('kelengkapan_berkas');
            }
            if (!Schema::hasColumn('pengajuan_petani', 'catatan_verifikasi_berkas')) {
                $table->text('catatan_verifikasi_berkas')->nullable()->after('berkas_persyaratan_path');
            }
            if (!Schema::hasColumn('pengajuan_petani', 'c3_verified_by')) {
                $table->foreignId('c3_verified_by')->nullable()->after('catatan_verifikasi_berkas')
                    ->constrained('users')->nullOnDelete();
            }
            if (!Schema::hasColumn('pengajuan_petani', 'c3_verified_at')) {
                $table->timestamp('c3_verified_at')->nullable()->after('c3_verified_by');
            }
        });

        if (!Schema::hasTable('audit_log')) {
            Schema::create('audit_log', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
                $table->string('module', 80);
                $table->string('action', 80);
                $table->string('subject_type', 150)->nullable();
                $table->unsignedBigInteger('subject_id')->nullable();
                $table->text('description')->nullable();
                $table->json('old_values')->nullable();
                $table->json('new_values')->nullable();
                $table->string('ip_address', 45)->nullable();
                $table->text('user_agent')->nullable();
                $table->timestamps();

                $table->index(['module', 'action']);
                $table->index(['subject_type', 'subject_id']);
                $table->index('created_at');
            });
        }

        if (!Schema::hasTable('vikor_riwayat')) {
            Schema::create('vikor_riwayat', function (Blueprint $table) {
                $table->id();
                $table->uuid('run_uuid')->index();
                $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
                $table->foreignId('musim_tanam_id')->constrained('musim_tanam')->restrictOnDelete();
                $table->foreignId('periode_id')->constrained('periode')->restrictOnDelete();
                $table->foreignId('kecamatan_id')->constrained('kecamatan')->restrictOnDelete();
                $table->foreignId('alternatif_musim_id')->nullable()->constrained('alternatif_musim')->nullOnDelete();
                $table->foreignId('petani_id')->nullable()->constrained('petani')->nullOnDelete();
                // Snapshot identitas agar riwayat tetap terbaca walaupun data master berubah/dihapus.
                $table->string('kode_alternatif', 20)->nullable();
                $table->string('nik', 30)->nullable();
                $table->string('nama_petani')->nullable();
                $table->string('nama_poktan')->nullable();
                $table->decimal('nilai_s', 18, 10)->nullable();
                $table->decimal('nilai_r', 18, 10)->nullable();
                $table->decimal('nilai_qs', 18, 10)->nullable();
                $table->decimal('nilai_qr', 18, 10)->nullable();
                $table->decimal('nilai_q', 18, 10)->nullable();
                $table->unsignedInteger('ranking')->nullable();
                $table->unsignedInteger('kuota_penerima')->nullable();
                $table->string('status_kuota', 30)->nullable();
                $table->timestamp('calculated_at');
                $table->timestamps();

                $table->index(['musim_tanam_id', 'periode_id', 'kecamatan_id'], 'idx_vikor_riwayat_scope');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('vikor_riwayat');
        Schema::dropIfExists('audit_log');

        Schema::table('pengajuan_petani', function (Blueprint $table) {
            if (Schema::hasColumn('pengajuan_petani', 'c3_verified_by')) {
                try { $table->dropForeign(['c3_verified_by']); } catch (\Throwable $e) {}
            }
            foreach ([
                'kelengkapan_berkas_verifikasi',
                'catatan_verifikasi_berkas',
                'c3_verified_by',
                'c3_verified_at',
            ] as $column) {
                if (Schema::hasColumn('pengajuan_petani', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
