<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class JenisPupuk extends Model
{
    use HasFactory;

    protected $table = 'jenis_pupuk';

    protected $fillable = [
        'nama_pupuk',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function pengajuanPupuk()
    {
        return $this->hasMany(PengajuanPetaniPupuk::class, 'jenis_pupuk_id');
    }

    public function alternatifPupuk()
    {
        return $this->hasMany(AlternatifMusimPupuk::class, 'jenis_pupuk_id');
    }
}
