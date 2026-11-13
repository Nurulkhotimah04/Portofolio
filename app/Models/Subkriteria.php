<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Subkriteria extends Model
{
    use HasFactory;

    protected $table = 'subkriteria';

    protected $fillable = [
        'kriteria_id',
        'kode_subkriteria',
        'nama_subkriteria',
        'bobot',
    ];


    protected $casts = [
        'bobot' => 'float',
    ];


    public function kriteria()
    {
        return $this->belongsTo(
            Kriteria::class,
            'kriteria_id'
        );
    }
}