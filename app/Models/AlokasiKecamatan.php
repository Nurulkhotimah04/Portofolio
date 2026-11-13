<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AlokasiKecamatan extends Model
{
    use HasFactory;

    protected $table = 'alokasi_kecamatan';

    protected $fillable = [
        'kecamatan_id',
        'musim_tanam_id',
        'periode_id',
        'kuota_penerima',
    ];

    protected $casts = [
        'kuota_penerima' => 'integer',
    ];

    public function kecamatan()
    {
        return $this->belongsTo(Kecamatan::class, 'kecamatan_id');
    }

    public function musimTanam()
    {
        return $this->belongsTo(MusimTanam::class, 'musim_tanam_id');
    }

    public function periode()
    {
        return $this->belongsTo(Periode::class, 'periode_id');
    }
}