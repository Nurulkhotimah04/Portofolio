public function up(): void
{
    Schema::table('petani', function (Blueprint $table) {
        // Kode alternatif, contoh A1, A2, A3
        $table->string('kode_alternatif')->nullable()->after('id');

        // Hapus kolom lama jika sudah tidak dipakai
        $table->dropColumn(['nik', 'alamat']);

        // Data alternatif sesuai kriteria
        $table->string('luas_lahan')->nullable();
        $table->string('jenis_komoditas')->nullable();
        $table->string('kelengkapan_berkas')->nullable();
        $table->string('kebutuhan_pupuk')->nullable();
        $table->string('subsektor_pertanian')->nullable();
    });
}

public function down(): void
{
    Schema::table('petani', function (Blueprint $table) {
        $table->string('nik')->nullable();
        $table->text('alamat')->nullable();

        $table->dropColumn([
            'kode_alternatif',
            'luas_lahan',
            'jenis_komoditas',
            'kelengkapan_berkas',
            'kebutuhan_pupuk',
            'subsektor_pertanian',
        ]);
    });
}