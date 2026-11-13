<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PenugasanPenyuluh extends Model
{
    use HasFactory;

    protected $table = 'penugasan_penyuluh';

    protected $fillable = [
        'penyuluh_id',
        'kecamatan_id',
        'tanggal_mulai',
        'tanggal_selesai',
        'status',
        'catatan',
    ];

    protected $casts = [
        'tanggal_mulai' => 'date',
        'tanggal_selesai' => 'date',
    ];

    public function penyuluh()
    {
        return $this->belongsTo(Penyuluh::class, 'penyuluh_id');
    }

    public function kecamatan()
    {
        return $this->belongsTo(Kecamatan::class, 'kecamatan_id');
    }
}
