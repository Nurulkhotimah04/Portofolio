<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('petani', function (Blueprint $table) {
            // Tambah kolom user_id sebagai foreign key ke tabel users
            // nullable() agar data petani lama yg diinput admin tidak error
            $table->unsignedBigInteger('user_id')->nullable()->after('id');

            // Definisi foreign key constraint
            $table->foreign('user_id')
                  ->references('id')
                  ->on('users')
                  ->onDelete('set null'); // Jika user dihapus, user_id jadi null (data petani tetap ada)
        });
    }

    public function down(): void
    {
        Schema::table('petani', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropColumn('user_id');
        });
    }
};
