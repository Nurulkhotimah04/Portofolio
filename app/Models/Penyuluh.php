<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Penyuluh extends Model
{
    use HasFactory;

    protected $table = 'penyuluh';

    protected $fillable = [
        'user_id',
        'nama_penyuluh',
        'status',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function poktan()
    {
        return $this->hasMany(Poktan::class, 'penyuluh_id');
    }

    public function riwayatPembinaanPoktan()
    {
        return $this->hasMany(PenugasanPoktan::class, 'penyuluh_id');
    }

    public function pengajuanPetani()
    {
        return $this->hasMany(PengajuanPetani::class, 'penyuluh_id');
    }

    public function batchImportPengajuan()
    {
        return $this->hasMany(BatchImportPengajuan::class, 'penyuluh_id');
    }

    public function penugasan()
    {
        return $this->hasMany(PenugasanPenyuluh::class, 'penyuluh_id');
    }

    public function penugasanAktif()
    {
        return $this->hasOne(PenugasanPenyuluh::class, 'penyuluh_id')
            ->where('status', 'aktif')
            ->whereNull('tanggal_selesai')
            ->latestOfMany('tanggal_mulai');
    }

    public function getKecamatanAktifAttribute()
    {
        if (!$this->relationLoaded('penugasanAktif')) {
            $this->load('penugasanAktif.kecamatan');
        }

        return $this->penugasanAktif?->kecamatan;
    }

    public function getAktifAttribute(): bool
    {
        return $this->status === 'aktif' && $this->penugasanAktif !== null;
    }
}
