<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AlternatifMusim extends Model
{
    use HasFactory;

    protected $table = 'alternatif_musim';


    protected $fillable = [
        'petani_id',
        'poktan_id',
        'musim_tanam_id',
        'periode_id',
        'pengajuan_petani_id',
        'kode_alternatif',
        'luas_lahan_ha',
        'luas_lahan',
        'jenis_komoditas',
        'kelengkapan_berkas',
        'berkas_persyaratan_path',
        'kebutuhan_pupuk',
        'subsektor_pertanian',
    ];


    /*
    |--------------------------------------------------------------------------
    | PETANI MASTER
    |--------------------------------------------------------------------------
    */

    public function petani()
    {
        return $this->belongsTo(
            Petani::class,
            'petani_id'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | POKTAN
    |--------------------------------------------------------------------------
    */

    public function poktan()
    {
        return $this->belongsTo(
            Poktan::class,
            'poktan_id'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | MUSIM TANAM
    |--------------------------------------------------------------------------
    */

    public function musimTanam()
    {
        return $this->belongsTo(
            MusimTanam::class,
            'musim_tanam_id'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | PERIODE / TAHUN
    |--------------------------------------------------------------------------
    */

    public function periode()
    {
        return $this->belongsTo(
            Periode::class,
            'periode_id'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | PENGAJUAN PETANI
    |--------------------------------------------------------------------------
    */

    public function pengajuanPetani()
    {
        return $this->belongsTo(
            PengajuanPetani::class,
            'pengajuan_petani_id'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | HASIL VIKOR
    |--------------------------------------------------------------------------
    */

    public function vikorHasil()
    {
        return $this->hasOne(
            VikorHasil::class,
            'alternatif_musim_id'
        );
    }



    public function pupuk()
    {
        return $this->hasMany(
            AlternatifMusimPupuk::class,
            'alternatif_musim_id'
        );
    }

    public function getRingkasanPupukAttribute(): string
    {
        if (!$this->relationLoaded('pupuk')) {
            $this->load('pupuk.jenisPupuk');
        }

        if ($this->pupuk->isEmpty()) {
            return $this->kebutuhan_pupuk
                ? 'Rincian belum tersedia (' . $this->kebutuhan_pupuk . ')'
                : '-';
        }

        return $this->pupuk
            ->filter(fn ($item) => (float) $item->jumlah_kg > 0)
            ->map(function ($item) {
                $nama = $item->jenisPupuk?->nama_pupuk ?? 'Pupuk';
                $kg = rtrim(rtrim(number_format((float) $item->jumlah_kg, 2, ',', '.'), '0'), ',');
                return $nama . ' ' . $kg . ' kg';
            })
            ->implode(', ');
    }

    /*
    |--------------------------------------------------------------------------
    | KECAMATAN
    |--------------------------------------------------------------------------
    |
    | Kecamatan tidak disimpan langsung di alternatif_musim.
    | Kecamatan mengikuti Poktan.
    |
    */

    public function getKecamatanAttribute()
    {
        return $this->poktan
            ? $this->poktan->kecamatan
            : null;
    }


    /*
    |--------------------------------------------------------------------------
    | STATUS SUDAH VIKOR
    |--------------------------------------------------------------------------
    */

    public function getSudahDihitungVikorAttribute(): bool
    {
        return $this->vikorHasil !== null;
    }


    /*
    |--------------------------------------------------------------------------
    | LABEL PERIODE
    |--------------------------------------------------------------------------
    |
    | Contoh:
    | MT1 / 2026
    |
    */

    public function getLabelPeriodeAttribute(): string
    {
        $musim = $this->musimTanam
            ? $this->musimTanam->kode_musim
            : '-';

        $tahun = $this->periode
            ? $this->periode->tahun
            : '-';

        return $musim . ' / ' . $tahun;
    }


    /*
    |--------------------------------------------------------------------------
    | SCOPE MUSIM + PERIODE
    |--------------------------------------------------------------------------
    */

    public function scopePeriodeMusim(
        $query,
        $musimTanamId,
        $periodeId
    ) {
        return $query
            ->where(
                'musim_tanam_id',
                $musimTanamId
            )
            ->where(
                'periode_id',
                $periodeId
            );
    }


    /*
    |--------------------------------------------------------------------------
    | SCOPE KECAMATAN
    |--------------------------------------------------------------------------
    */

    public function scopeKecamatan(
        $query,
        $kecamatanId
    ) {
        return $query->whereHas(
            'poktan',
            function ($poktanQuery) use ($kecamatanId) {

                $poktanQuery->where(
                    'kecamatan_id',
                    $kecamatanId
                );
            }
        );
    }
}