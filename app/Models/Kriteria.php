<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Kriteria extends Model
{
    use HasFactory;
    // Nama tabel
    protected $table = 'kriteria';

    protected $casts = [
        'bobot' => 'double', // Memaksa format data menjadi desimal
    ];

    // Kolom yang boleh diisi
    protected $fillable = [
        'kode_kriteria',
        'nama_kriteria',
        'bobot',
        'atribut',
    ];

    // Relasi satu kriteria memiliki banyak subkriteria
    public function subkriteria()
    {
        return $this->hasMany(Subkriteria::class, 'kriteria_id');
    }
}