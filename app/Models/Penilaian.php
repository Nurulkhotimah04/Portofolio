<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Penilaian extends Model
{
    // Nama nama dari tabelnya
    protected $table = 'penilaian';

    // Kolom kolom yang akan diisi
    protected $fillable = [
        'petani_id',
        'kriteria_id',
        'subkriteria_id',
        'nilai',
    ];

    // Relasi ke tabel petani
    public function petani()
    {
        return $this->belongsTo(Petani::class, 'petani_id');
    }

    // Relasi ke tabel kriteria
    public function kriteria()
    {
        return $this->belongsTo(Kriteria::class, 'kriteria_id');
    }

    // Relasi ke tabel subkriteria
    public function subkriteria()
    {
        return $this->belongsTo(Subkriteria::class, 'subkriteria_id');
    }
}