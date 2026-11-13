<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Poktan extends Model
{
    use HasFactory;

    protected $table = 'poktan';

    protected $fillable = [
        'nama_poktan',
        'penyuluh_id',
        'kecamatan_id',
        'status',
    ];

    public function penyuluh()
    {
        return $this->belongsTo(Penyuluh::class, 'penyuluh_id');
    }

    public function kecamatan()
    {
        return $this->belongsTo(Kecamatan::class, 'kecamatan_id');
    }

    public function riwayatPembinaan()
    {
        return $this->hasMany(PenugasanPoktan::class, 'poktan_id');
    }

    public function pembinaanAktif()
    {
        return $this->hasOne(PenugasanPoktan::class, 'poktan_id')
            ->where('status', 'aktif')
            ->whereNull('tanggal_selesai')
            ->latestOfMany('tanggal_mulai');
    }

    public function petani()
    {
        return $this->hasMany(Petani::class, 'poktan_id');
    }

    public function pengajuanPetani()
    {
        return $this->hasMany(PengajuanPetani::class, 'poktan_id');
    }

    public function alternatifMusim()
    {
        return $this->hasMany(AlternatifMusim::class, 'poktan_id');
    }

    public function batchImportPengajuan()
    {
        return $this->hasMany(BatchImportPengajuan::class, 'poktan_id');
    }
}
