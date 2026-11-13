<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\AuthController;
use App\Http\Controllers\PetaniController;
use App\Http\Controllers\PenyuluhController;
use App\Http\Controllers\KriteriaController;
use App\Http\Controllers\SubkriteriaController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\AnpController;
use App\Http\Controllers\VikorController;
use App\Http\Controllers\LaporanController;
use App\Http\Controllers\PoktanController;
use App\Http\Controllers\PengajuanPetaniController;
use App\Http\Controllers\MusimTanamController;
use App\Http\Controllers\PeriodeController;
use App\Http\Controllers\KecamatanController;
use App\Http\Controllers\AlokasiKecamatanController;
use App\Http\Controllers\PengaturanPeriodeWilayahController;
use App\Http\Controllers\PengajuanImportController;
use App\Http\Controllers\AlternatifMusimController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PengaturanController;
use App\Http\Controllers\JenisPupukController;
use App\Http\Controllers\AuditLogController;


/*
|--------------------------------------------------------------------------
| LOGIN & LOGOUT
|--------------------------------------------------------------------------
*/

Route::get(
    '/login',
    [AuthController::class, 'index']
)->name('login');


Route::post(
    '/login',
    [AuthController::class, 'login']
)->name('login.process');


Route::post(
    '/logout',
    [AuthController::class, 'logout']
)->name('logout');


Route::get('/', function () {

    return redirect()
        ->route('login');

});


/*
|--------------------------------------------------------------------------
| DASHBOARD
|--------------------------------------------------------------------------
|
| Admin dan Penyuluh dapat membuka Dashboard.
|
*/

Route::middleware([
    'auth'
])->group(function () {

    Route::get(
        '/dashboard',
        [DashboardController::class, 'index']
    )->name('dashboard');

});


/*
|--------------------------------------------------------------------------
| PROFIL AKUN
|--------------------------------------------------------------------------
| Admin dan Penyuluh mengelola akun sendiri melalui navbar kanan atas.
*/

Route::middleware([
    'auth',
    'role:admin,penyuluh'
])->group(function () {

    Route::get(
        '/profil',
        [ProfileController::class, 'show']
    )->name('profile.show');

    Route::put(
        '/profil',
        [ProfileController::class, 'update']
    )->name('profile.update');

    Route::put(
        '/profil/password',
        [ProfileController::class, 'updatePassword']
    )->name('profile.password.update');

});


/*
|--------------------------------------------------------------------------
| ADMIN + PENYULUH
|--------------------------------------------------------------------------
|
| Route yang dapat dibuka oleh kedua role.
|
*/

Route::middleware([
    'auth',
    'role:admin,penyuluh'
])->group(function () {


    /*
    |--------------------------------------------------------------------------
    | DATA PETANI LAMA - KOMPATIBILITAS
    |--------------------------------------------------------------------------
    | Seluruh penilaian per periode sekarang dikelola dari Data Alternatif.
    | URL lama diarahkan agar tidak ada dua jalur edit C1-C5.
    */

    Route::get('/petani', function () {
        return redirect()->route('alternatif-musim.index');
    })->name('petani.index');


    /*
    |--------------------------------------------------------------------------
    | PENGAJUAN PETANI - INDEX
    |--------------------------------------------------------------------------
    |
    | Admin:
    | - Melihat seluruh pengajuan.
    |
    | Penyuluh:
    | - Melihat pengajuan miliknya sendiri.
    |
    */

    Route::get(
        '/pengajuan-petani',
        [PengajuanPetaniController::class, 'index']
    )->name('pengajuan.index');


    /*
    |--------------------------------------------------------------------------
    | DATA POKTAN - LIHAT
    |--------------------------------------------------------------------------
    */

    Route::get(
        '/poktan',
        [PoktanController::class, 'index']
    )->name('poktan.index');


    Route::get(
        '/poktan/{poktan}',
        [PoktanController::class, 'show']
    )
        ->whereNumber('poktan')
        ->name('poktan.show');


    /* CRUD POKTAN - Admin dan Penyuluh (dibatasi controller) */
    Route::get('/poktan/create', [PoktanController::class, 'create'])
        ->name('poktan.create');
    Route::post('/poktan', [PoktanController::class, 'store'])
        ->name('poktan.store');
    Route::get('/poktan/{poktan}/edit', [PoktanController::class, 'edit'])
        ->whereNumber('poktan')->name('poktan.edit');
    Route::put('/poktan/{poktan}', [PoktanController::class, 'update'])
        ->whereNumber('poktan')->name('poktan.update');
    Route::delete('/poktan/{poktan}', [PoktanController::class, 'destroy'])
        ->whereNumber('poktan')->name('poktan.destroy');

    Route::get('/pengajuan-petani/{pengajuan}/berkas', [PengajuanPetaniController::class, 'berkas'])
        ->whereNumber('pengajuan')->name('pengajuan.berkas');
    Route::get('/alternatif-musim/{alternatifMusim}/berkas', [AlternatifMusimController::class, 'berkas'])
        ->whereNumber('alternatifMusim')->name('alternatif-musim.berkas');


    /*
    |--------------------------------------------------------------------------
    | DATA ALTERNATIF MUSIM
    |--------------------------------------------------------------------------
    |
    | Data alternatif baru berdasarkan:
    | - Petani
    | - Musim Tanam
    | - Tahun / Periode
    | - Poktan / Kecamatan
    |
    */

    Route::get(
        '/alternatif-musim',
        [AlternatifMusimController::class, 'index']
    )->name('alternatif-musim.index');


    /*
    |--------------------------------------------------------------------------
    | HASIL RANKING
    |--------------------------------------------------------------------------
    |
    | Admin:
    | - Melihat hasil seluruh Kecamatan.
    |
    | Penyuluh:
    | - Hanya hasil petani binaannya.
    |
    */

    Route::get(
        '/ranking',
        [VikorController::class, 'ranking']
    )->name('ranking.index');


    /*
    |--------------------------------------------------------------------------
    | LAPORAN - ADMIN + PENYULUH
    |--------------------------------------------------------------------------
    */

    Route::get(
        '/laporan',
        [LaporanController::class, 'index']
    )->name('laporan.index');


    /*
    |--------------------------------------------------------------------------
    | LAPORAN PRIORITAS
    |--------------------------------------------------------------------------
    |
    | Admin:
    | - Seluruh petani dalam Kecamatan.
    |
    | Penyuluh:
    | - Hanya petani binaannya.
    |
    */

    Route::get(
        '/laporan/prioritas',
        [LaporanController::class, 'prioritas']
    )->name('laporan.prioritas');


    Route::get(
        '/laporan/prioritas/pdf',
        [LaporanController::class, 'prioritasPdf']
    )->name('laporan.prioritas.pdf');


    Route::get(
        '/laporan/prioritas/excel',
        [LaporanController::class, 'prioritasExcel']
    )->name('laporan.prioritas.excel');


    /*
    |--------------------------------------------------------------------------
    | LAPORAN PENGAJUAN
    |--------------------------------------------------------------------------
    |
    | Admin:
    | - Seluruh Pengajuan & Verifikasi.
    |
    | Penyuluh:
    | - Pengajuan petani binaannya sendiri.
    |
    */

    Route::get(
        '/laporan/pengajuan',
        [LaporanController::class, 'pengajuan']
    )->name('laporan.pengajuan');


    Route::get(
        '/laporan/pengajuan/pdf',
        [LaporanController::class, 'pengajuanPdf']
    )->name('laporan.pengajuan.pdf');


    Route::get(
        '/laporan/pengajuan/excel',
        [LaporanController::class, 'pengajuanExcel']
    )->name('laporan.pengajuan.excel');

});


/*
|--------------------------------------------------------------------------
| KHUSUS PENYULUH
|--------------------------------------------------------------------------
*/

Route::middleware([
    'auth',
    'role:penyuluh'
])->group(function () {


    /*
    |--------------------------------------------------------------------------
    | IMPORT PENGAJUAN PETANI
    |--------------------------------------------------------------------------
    |
    | Route import harus berada sebelum route parameter {pengajuan}.
    |
    */

    Route::get(
        '/pengajuan-petani/import',
        [PengajuanImportController::class, 'form']
    )->name('pengajuan.import.form');


    Route::get(
        '/pengajuan-petani/import/template',
        [PengajuanImportController::class, 'template']
    )->name('pengajuan.import.template');


    Route::post(
        '/pengajuan-petani/import/preview',
        [PengajuanImportController::class, 'preview']
    )->name('pengajuan.import.preview');


    Route::post(
        '/pengajuan-petani/import/commit',
        [PengajuanImportController::class, 'commit']
    )->name('pengajuan.import.commit');


    /*
    |--------------------------------------------------------------------------
    | INPUT MANUAL PENGAJUAN
    |--------------------------------------------------------------------------
    */

    Route::get(
        '/pengajuan-petani/create',
        [PengajuanPetaniController::class, 'create']
    )->name('pengajuan.create');


    Route::post(
        '/pengajuan-petani',
        [PengajuanPetaniController::class, 'store']
    )->name('pengajuan.store');


    /*
    |--------------------------------------------------------------------------
    | EDIT / PERBAIKAN PENGAJUAN
    |--------------------------------------------------------------------------
    */

    Route::get(
        '/pengajuan-petani/{pengajuan}/edit',
        [PengajuanPetaniController::class, 'edit']
    )
        ->whereNumber('pengajuan')
        ->name('pengajuan.edit');


    Route::put(
        '/pengajuan-petani/{pengajuan}',
        [PengajuanPetaniController::class, 'update']
    )
        ->whereNumber('pengajuan')
        ->name('pengajuan.update');

});


/*
|--------------------------------------------------------------------------
| DETAIL PENGAJUAN
|--------------------------------------------------------------------------
|
| Admin dan Penyuluh bisa membuka detail.
|
| Penyuluh tetap dibatasi oleh controller agar hanya dapat melihat
| pengajuan miliknya sendiri.
|
*/

Route::middleware([
    'auth',
    'role:admin,penyuluh'
])->group(function () {

    Route::get(
        '/pengajuan-petani/{pengajuan}',
        [PengajuanPetaniController::class, 'show']
    )
        ->whereNumber('pengajuan')
        ->name('pengajuan.show');

});


/*
|--------------------------------------------------------------------------
| KHUSUS ADMIN
|--------------------------------------------------------------------------
*/

Route::middleware([
    'auth',
    'role:admin'
])->group(function () {


    /*
    |--------------------------------------------------------------------------
    | PUSAT PENGATURAN
    |--------------------------------------------------------------------------
    */

    Route::get(
        '/pengaturan',
        [PengaturanController::class, 'index']
    )->name('pengaturan.index');


    Route::resource('jenis-pupuk', JenisPupukController::class)
        ->parameters(['jenis-pupuk' => 'jenisPupuk'])
        ->only(['index', 'store', 'update', 'destroy']);

    Route::get('/audit-log', [AuditLogController::class, 'index'])->name('audit.index');
    Route::delete('/audit-log/terpilih', [AuditLogController::class, 'destroySelected'])->name('audit.destroy-selected');
    Route::delete('/audit-log/semua', [AuditLogController::class, 'destroyAll'])->name('audit.destroy-all');


    /*
    |--------------------------------------------------------------------------
    | CRUD DATA ALTERNATIF MUSIM - ADMIN
    |--------------------------------------------------------------------------
    */

    Route::get(
        '/alternatif-musim/create',
        [AlternatifMusimController::class, 'create']
    )->name('alternatif-musim.create');

    Route::post(
        '/alternatif-musim',
        [AlternatifMusimController::class, 'store']
    )->name('alternatif-musim.store');

    Route::get(
        '/alternatif-musim/{alternatifMusim}/edit',
        [AlternatifMusimController::class, 'edit']
    )
        ->whereNumber('alternatifMusim')
        ->name('alternatif-musim.edit');

    Route::put(
        '/alternatif-musim/{alternatifMusim}',
        [AlternatifMusimController::class, 'update']
    )
        ->whereNumber('alternatifMusim')
        ->name('alternatif-musim.update');

    Route::delete(
        '/alternatif-musim/{alternatifMusim}',
        [AlternatifMusimController::class, 'destroy']
    )
        ->whereNumber('alternatifMusim')
        ->name('alternatif-musim.destroy');


    /*
    |--------------------------------------------------------------------------
    | VERIFIKASI C3 / BERKAS PENGAJUAN
    |--------------------------------------------------------------------------
    */

    Route::post(
        '/pengajuan-petani/{pengajuan}/verifikasi-c3',
        [PengajuanPetaniController::class, 'verifyC3']
    )
        ->whereNumber('pengajuan')
        ->name('pengajuan.verify-c3');


    /*
    |--------------------------------------------------------------------------
    | SETUJUI PENGAJUAN
    |--------------------------------------------------------------------------
    */

    Route::post(
        '/pengajuan-petani/{pengajuan}/setujui',
        [PengajuanPetaniController::class, 'approve']
    )
        ->whereNumber('pengajuan')
        ->name('pengajuan.approve');


    /*
    |--------------------------------------------------------------------------
    | MINTA PERBAIKAN
    |--------------------------------------------------------------------------
    */

    Route::post(
        '/pengajuan-petani/{pengajuan}/perbaikan',
        [PengajuanPetaniController::class, 'requestRevision']
    )
        ->whereNumber('pengajuan')
        ->name('pengajuan.revision');


    /*
    |--------------------------------------------------------------------------
    | TOLAK PENGAJUAN
    |--------------------------------------------------------------------------
    */

    Route::post(
        '/pengajuan-petani/{pengajuan}/tolak',
        [PengajuanPetaniController::class, 'reject']
    )
        ->whereNumber('pengajuan')
        ->name('pengajuan.reject');


    /*
    |--------------------------------------------------------------------------
    | PENGATURAN PERIODE & WILAYAH
    |--------------------------------------------------------------------------
    */

    Route::get(
        '/pengaturan-periode-wilayah',
        [PengaturanPeriodeWilayahController::class, 'index']
    )->name('pengaturan-periode-wilayah.index');


    /*
    |--------------------------------------------------------------------------
    | MASTER MUSIM TANAM
    |--------------------------------------------------------------------------
    */

    Route::resource(
        'musim-tanam',
        MusimTanamController::class
    )
        ->parameters([
            'musim-tanam' => 'musimTanam',
        ])
        ->only([
            'index',
            'store',
            'update',
            'destroy',
        ]);


    /*
    |--------------------------------------------------------------------------
    | MASTER PERIODE / TAHUN
    |--------------------------------------------------------------------------
    */

    Route::resource(
        'periode',
        PeriodeController::class
    )
        ->only([
            'index',
            'store',
            'update',
            'destroy',
        ]);


    /*
    |--------------------------------------------------------------------------
    | MASTER KECAMATAN
    |--------------------------------------------------------------------------
    */

    Route::resource(
        'kecamatan',
        KecamatanController::class
    )
        ->only([
            'index',
            'store',
            'update',
            'destroy',
        ]);


    /*
    |--------------------------------------------------------------------------
    | ALOKASI / KUOTA KECAMATAN
    |--------------------------------------------------------------------------
    */

    Route::resource(
        'alokasi-kecamatan',
        AlokasiKecamatanController::class
    )
        ->parameters([
            'alokasi-kecamatan' => 'alokasiKecamatan',
        ])
        ->only([
            'index',
            'store',
            'update',
            'destroy',
        ]);


    /*
    |--------------------------------------------------------------------------
    | DATA ADMIN
    |--------------------------------------------------------------------------
    */

    Route::resource(
        'admin',
        AdminController::class
    )->except(['show']);


    /*
    |--------------------------------------------------------------------------
    | DATA PENYULUH
    |--------------------------------------------------------------------------
    */

    Route::resource(
        'penyuluh',
        PenyuluhController::class
    )->except(['show']);


    /*
    |--------------------------------------------------------------------------
    | KRITERIA
    |--------------------------------------------------------------------------
    */

    Route::resource(
        'kriteria',
        KriteriaController::class
    );


    /*
    |--------------------------------------------------------------------------
    | SUBKRITERIA
    |--------------------------------------------------------------------------
    */

    Route::resource(
        'subkriteria',
        SubkriteriaController::class
    );


    /*
    |--------------------------------------------------------------------------
    | ANP
    |--------------------------------------------------------------------------
    */

    Route::get(
        '/anp',
        [AnpController::class, 'index']
    )->name('anp.index');


    Route::get(
        '/anp/hasil',
        [AnpController::class, 'hasil']
    )->name('anp.hasil');


    Route::get(
        '/anp/network',
        [AnpController::class, 'network']
    )->name('anp.network');


    Route::post(
        '/anp/network',
        [AnpController::class, 'saveNetwork']
    )->name('anp.network.save');


    Route::post(
        '/anp/network/simpan',
        [AnpController::class, 'simpan_network']
    )->name('anp.simpan_network');


    Route::get(
        '/anp/pairwise',
        [AnpController::class, 'pairwiseKriteria']
    )->name('anp.pairwise.kriteria');


    Route::post(
        '/anp/hitung',
        [AnpController::class, 'hitung']
    )->name('anp.hitung');


    Route::get(
        '/anp/feedback',
        [AnpController::class, 'feedback']
    )->name('anp.feedback');


    /*
    |--------------------------------------------------------------------------
    | ANP SUBKRITERIA
    |--------------------------------------------------------------------------
    */

    Route::get(
        '/anp/subkriteria',
        [AnpController::class, 'indexSubkriteria']
    )->name('anp.index_subkriteria');


    Route::post(
        '/anp/subkriteria/proses',
        [AnpController::class, 'prosesSubkriteria']
    )->name('anp.proses_subkriteria');


    Route::get(
        '/anp/hasil-subkriteria',
        [AnpController::class, 'hasilSubkriteria']
    )->name('anp.hasil_subkriteria');


    Route::post(
        '/anp/subkriteria/simpan-bobot',
        [AnpController::class, 'simpanBobotSubkriteria']
    )->name('anp.simpan_bobot_subkriteria');


    /*
    |--------------------------------------------------------------------------
    | VIKOR
    |--------------------------------------------------------------------------
    |
    | Perhitungan hanya dilakukan oleh Admin.
    |
    | Scope:
    | - Musim Tanam
    | - Tahun / Periode
    | - Kecamatan
    |
    */

    Route::get(
        '/vikor',
        [VikorController::class, 'index']
    )->name('vikor.index');


    Route::match(
        [
            'get',
            'post',
        ],
        '/vikor/proses',
        [VikorController::class, 'proses']
    )->name('vikor.proses');


    Route::post(
        '/vikor/hitung',
        [VikorController::class, 'hitung']
    )->name('vikor.hitung');

    Route::get(
        '/vikor/riwayat',
        [VikorController::class, 'riwayat']
    )->name('vikor.riwayat');

    Route::delete(
        '/vikor/riwayat/{runUuid}',
        [VikorController::class, 'hapusRiwayat']
    )->whereUuid('runUuid')->name('vikor.riwayat.hapus');

    Route::get(
        '/vikor/riwayat/{runUuid}',
        [VikorController::class, 'riwayatDetail']
    )->whereUuid('runUuid')->name('vikor.riwayat.detail');


    /*
    |--------------------------------------------------------------------------
    | REDIRECT PERHITUNGAN ANP
    |--------------------------------------------------------------------------
    */

    Route::get(
        '/perhitungan/anp/hitung',
        function () {

            return redirect()
                ->route('anp.index');

        }
    )->name('perhitungan.anp.hitung');


    /*
    |--------------------------------------------------------------------------
    | LAPORAN REKAP PER KECAMATAN
    |--------------------------------------------------------------------------
    |
    | Khusus Admin.
    |
    */

    Route::get(
        '/laporan/rekap-kecamatan',
        [LaporanController::class, 'rekap']
    )->name('laporan.rekap');


    Route::get(
        '/laporan/rekap-kecamatan/pdf',
        [LaporanController::class, 'rekapPdf']
    )->name('laporan.rekap.pdf');


    Route::get(
        '/laporan/rekap-kecamatan/excel',
        [LaporanController::class, 'rekapExcel']
    )->name('laporan.rekap.excel');


    /*
    |--------------------------------------------------------------------------
    | PENANDATANGAN LAPORAN
    |--------------------------------------------------------------------------
    */

    Route::post(
        '/laporan/penandatangan',
        [LaporanController::class, 'simpanPenandatangan']
    )->name('laporan.penandatangan.update');

});