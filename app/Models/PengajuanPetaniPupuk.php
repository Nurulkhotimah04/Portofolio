<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PengajuanPetaniPupuk extends Model
{
    use HasFactory;

    protected $table = 'pengajuan_petani_pupuk';

    protected $fillable = [
        'pengajuan_petani_id',
        'jenis_pupuk_id',
        'jumlah_kg',
    ];

    protected $casts = [
        'jumlah_kg' => 'decimal:2',
    ];

    public function pengajuanPetani()
    {
        return $this->belongsTo(PengajuanPetani::class, 'pengajuan_petani_id');
    }

    public function jenisPupuk()
    {
        return $this->belongsTo(JenisPupuk::class, 'jenis_pupuk_id');
    }
}
