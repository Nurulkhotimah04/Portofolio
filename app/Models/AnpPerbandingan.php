<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AnpPerbandingan extends Model
{
    use HasFactory;

    protected $table = 'anp_perbandingan';

    protected $fillable = [
        'kriteria_dipengaruhi_id',
        'kriteria_baris_id',
        'kriteria_kolom_id',
        'nilai',
    ];

    protected $casts = [
        'nilai' => 'float',
    ];

    public function kriteriaDipengaruhi()
    {
        return $this->belongsTo(
            Kriteria::class,
            'kriteria_dipengaruhi_id'
        );
    }

    public function kriteriaBaris()
    {
        return $this->belongsTo(
            Kriteria::class,
            'kriteria_baris_id'
        );
    }

    public function kriteriaKolom()
    {
        return $this->belongsTo(
            Kriteria::class,
            'kriteria_kolom_id'
        );
    }
}