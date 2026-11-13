<?php

namespace App\Console\Commands;

use App\Models\AlternatifMusim;
use App\Models\MusimTanam;
use App\Models\Periode;
use App\Models\Petani;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class MigrasiAlternatifLama extends Command
{
    /**
     * Nama command.
     */
    protected $signature = 'alternatif:migrasi-lama';


    /**
     * Deskripsi command.
     */
    protected $description =
        'Memindahkan data Petani/Alternatif lama ke Alternatif Musim MT1 Tahun 2026';


    /**
     * Jalankan command.
     */
    public function handle()
    {
        $this->info(
            '=============================================='
        );

        $this->info(
            ' MIGRASI ALTERNATIF LAMA → MT1 TAHUN 2026'
        );

        $this->info(
            '=============================================='
        );


        /*
        |--------------------------------------------------------------------------
        | CARI MUSIM TANAM MT1
        |--------------------------------------------------------------------------
        */

        $musimTanam = MusimTanam::where(
            'kode_musim',
            'MT1'
        )->first();


        if (!$musimTanam) {

            $this->error(
                'Musim Tanam dengan kode MT1 tidak ditemukan.'
            );

            $this->line(
                'Buat terlebih dahulu MT1 pada menu Periode & Wilayah.'
            );

            return Command::FAILURE;
        }


        /*
        |--------------------------------------------------------------------------
        | CARI PERIODE 2026
        |--------------------------------------------------------------------------
        */

        $periode = Periode::where(
            'tahun',
            2026
        )->first();


        if (!$periode) {

            $this->error(
                'Periode/Tahun 2026 tidak ditemukan.'
            );

            $this->line(
                'Buat terlebih dahulu Tahun 2026 pada menu Periode & Wilayah.'
            );

            return Command::FAILURE;
        }


        /*
        |--------------------------------------------------------------------------
        | AMBIL DATA PETANI LAMA
        |--------------------------------------------------------------------------
        |
        | Hanya mengambil data yang mempunyai kode alternatif.
        |
        | Contoh:
        | A1
        | A2
        | ...
        | A30
        |
        */

        $petaniLama = Petani::whereNotNull(
                'kode_alternatif'
            )
            ->where(
                'kode_alternatif',
                '!=',
                ''
            )
            ->orderBy('id')
            ->get();


        if ($petaniLama->isEmpty()) {

            $this->warn(
                'Tidak ada data Petani lama yang memiliki kode alternatif.'
            );

            return Command::SUCCESS;
        }


        $this->info(
            'Musim Tanam : ' .
            $musimTanam->kode_musim .
            ' - ' .
            $musimTanam->nama_musim
        );

        $this->info(
            'Tahun       : ' .
            $periode->tahun
        );

        $this->info(
            'Data lama   : ' .
            $petaniLama->count()
        );


        $this->newLine();


        /*
        |--------------------------------------------------------------------------
        | COUNTER
        |--------------------------------------------------------------------------
        */

        $berhasil = 0;

        $sudahAda = 0;

        $gagal = 0;


        /*
        |--------------------------------------------------------------------------
        | TRANSACTION
        |--------------------------------------------------------------------------
        */

        DB::beginTransaction();


        try {

            foreach ($petaniLama as $petani) {

                /*
                |--------------------------------------------------------------------------
                | VALIDASI POKTAN
                |--------------------------------------------------------------------------
                */

                if (!$petani->poktan_id) {

                    $this->warn(
                        $petani->kode_alternatif .
                        ' - ' .
                        $petani->nama_petani .
                        ' dilewati karena belum memiliki Poktan.'
                    );

                    $gagal++;

                    continue;
                }


                /*
                |--------------------------------------------------------------------------
                | VALIDASI C1-C5
                |--------------------------------------------------------------------------
                */

                $fieldKosong = [];


                if (
                    $petani->luas_lahan === null ||
                    $petani->luas_lahan === ''
                ) {
                    $fieldKosong[] =
                        'Luas Lahan/C1';
                }


                if (
                    $petani->jenis_komoditas === null ||
                    $petani->jenis_komoditas === ''
                ) {
                    $fieldKosong[] =
                        'Jenis Komoditas/C2';
                }


                if (
                    $petani->kelengkapan_berkas === null ||
                    $petani->kelengkapan_berkas === ''
                ) {
                    $fieldKosong[] =
                        'Kelengkapan Berkas/C3';
                }


                if (
                    $petani->kebutuhan_pupuk === null ||
                    $petani->kebutuhan_pupuk === ''
                ) {
                    $fieldKosong[] =
                        'Kebutuhan Pupuk/C4';
                }


                if (
                    $petani->subsektor_pertanian === null ||
                    $petani->subsektor_pertanian === ''
                ) {
                    $fieldKosong[] =
                        'Subsektor Pertanian/C5';
                }


                if (!empty($fieldKosong)) {

                    $this->warn(
                        $petani->kode_alternatif .
                        ' - ' .
                        $petani->nama_petani .
                        ' dilewati. Data kosong: ' .
                        implode(
                            ', ',
                            $fieldKosong
                        )
                    );

                    $gagal++;

                    continue;
                }


                /*
                |--------------------------------------------------------------------------
                | CEK SUDAH ADA
                |--------------------------------------------------------------------------
                |
                | Jangan membuat alternatif yang sama dua kali
                | untuk Petani + Musim + Tahun yang sama.
                |
                */

                $existing =
                    AlternatifMusim::where(
                        'petani_id',
                        $petani->id
                    )
                        ->where(
                            'musim_tanam_id',
                            $musimTanam->id
                        )
                        ->where(
                            'periode_id',
                            $periode->id
                        )
                        ->first();


                if ($existing) {

                    $this->line(
                        '[SKIP] ' .
                        $petani->kode_alternatif .
                        ' - ' .
                        $petani->nama_petani .
                        ' sudah ada.'
                    );

                    $sudahAda++;

                    continue;
                }


                /*
                |--------------------------------------------------------------------------
                | SIMPAN KE ALTERNATIF MUSIM
                |--------------------------------------------------------------------------
                */

                AlternatifMusim::create([

                    'petani_id' =>
                        $petani->id,

                    'poktan_id' =>
                        $petani->poktan_id,

                    'musim_tanam_id' =>
                        $musimTanam->id,

                    'periode_id' =>
                        $periode->id,

                    /*
                    | Data lama bukan berasal dari
                    | pengajuan baru.
                    */
                    'pengajuan_petani_id' =>
                        null,

                    /*
                    | Pertahankan A1-A30.
                    */
                    'kode_alternatif' =>
                        $petani->kode_alternatif,

                    /*
                    | C1
                    */
                    'luas_lahan' =>
                        $petani->luas_lahan,

                    /*
                    | C2
                    */
                    'jenis_komoditas' =>
                        $petani->jenis_komoditas,

                    /*
                    | C3
                    */
                    'kelengkapan_berkas' =>
                        $petani->kelengkapan_berkas,

                    /*
                    | C4
                    */
                    'kebutuhan_pupuk' =>
                        $petani->kebutuhan_pupuk,

                    /*
                    | C5
                    */
                    'subsektor_pertanian' =>
                        $petani->subsektor_pertanian,
                ]);


                $this->info(
                    '[OK] ' .
                    $petani->kode_alternatif .
                    ' - ' .
                    $petani->nama_petani
                );


                $berhasil++;
            }


            DB::commit();


            /*
            |--------------------------------------------------------------------------
            | HASIL
            |--------------------------------------------------------------------------
            */

            $this->newLine();

            $this->info(
                '=============================================='
            );

            $this->info(
                ' MIGRASI SELESAI'
            );

            $this->info(
                '=============================================='
            );


            $this->table(
                [
                    'Keterangan',
                    'Jumlah',
                ],
                [
                    [
                        'Berhasil dimigrasikan',
                        $berhasil,
                    ],

                    [
                        'Sudah ada / dilewati',
                        $sudahAda,
                    ],

                    [
                        'Gagal / data tidak lengkap',
                        $gagal,
                    ],

                    [
                        'Total Petani Lama',
                        $petaniLama->count(),
                    ],
                ]
            );


            $this->newLine();


            $this->info(
                'Data sekarang dapat dilihat pada menu Data Alternatif.'
            );


            $this->line(
                'Scope: ' .
                $musimTanam->kode_musim .
                ' / ' .
                $periode->tahun
            );


            return Command::SUCCESS;

        } catch (\Throwable $e) {

            DB::rollBack();


            $this->error(
                'Migrasi gagal.'
            );


            $this->error(
                $e->getMessage()
            );


            return Command::FAILURE;
        }
    }
}