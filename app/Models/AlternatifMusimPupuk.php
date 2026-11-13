<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AlternatifMusimPupuk extends Model
{
    use HasFactory;

    protected $table = 'alternatif_musim_pupuk';

    protected $fillable = [
        'alternatif_musim_id',
        'jenis_pupuk_id',
        'jumlah_kg',
    ];

    protected $casts = [
        'jumlah_kg' => 'decimal:2',
    ];

    public function alternatifMusim()
    {
        return $this->belongsTo(AlternatifMusim::class, 'alternatif_musim_id');
    }

    public function jenisPupuk()
    {
        return $this->belongsTo(JenisPupuk::class, 'jenis_pupuk_id');
    }
}
