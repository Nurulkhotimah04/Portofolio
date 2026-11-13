<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pengajuan_petani', function (Blueprint $table) {

            $table->foreignId('musim_tanam_id')
                ->nullable()
                ->after('poktan_id')
                ->constrained('musim_tanam')
                ->restrictOnDelete();

            $table->foreignId('periode_id')
                ->nullable()
                ->after('musim_tanam_id')
                ->constrained('periode')
                ->restrictOnDelete();

            $table->foreignId('batch_import_id')
                ->nullable()
                ->after('periode_id')
                ->constrained('batch_import_pengajuan')
                ->nullOnDelete();

            $table->enum('asal_input', [
                'manual',
                'import',
            ])
                ->default('manual')
                ->after('batch_import_id');
        });
    }

    public function down(): void
    {
        Schema::table('pengajuan_petani', function (Blueprint $table) {

            $table->dropForeign(['batch_import_id']);
            $table->dropForeign(['periode_id']);
            $table->dropForeign(['musim_tanam_id']);

            $table->dropColumn([
                'asal_input',
                'batch_import_id',
                'periode_id',
                'musim_tanam_id',
            ]);
        });
    }
};