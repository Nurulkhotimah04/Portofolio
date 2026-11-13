<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PenugasanPoktan extends Model
{
    use HasFactory;

    protected $table = 'penugasan_poktan';

    protected $fillable = [
        'poktan_id',
        'penyuluh_id',
        'tanggal_mulai',
        'tanggal_selesai',
        'status',
        'catatan',
    ];

    protected $casts = [
        'tanggal_mulai' => 'date',
        'tanggal_selesai' => 'date',
    ];

    public function poktan()
    {
        return $this->belongsTo(Poktan::class, 'poktan_id');
    }

    public function penyuluh()
    {
        return $this->belongsTo(Penyuluh::class, 'penyuluh_id');
    }
}
