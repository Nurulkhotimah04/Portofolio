<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KriteriaNetwork extends Model
{
    use HasFactory;

    protected $table = 'kriteria_network';

    protected $fillable = [
        'kriteria_asal_id',
        'kriteria_tujuan_id',
        'is_related',
    ];

    // Relasi untuk mengambil data kriteria baris (yang dipengaruhi)
    public function kriteriaAsal()
    {
        return $this->belongsTo(Kriteria::class, 'kriteria_asal_id');
    }

    // Relasi untuk mengambil data kriteria kolom (yang mempengaruhi)
    public function kriteriaTujuan()
    {
        return $this->belongsTo(Kriteria::class, 'kriteria_tujuan_id');
    }
}