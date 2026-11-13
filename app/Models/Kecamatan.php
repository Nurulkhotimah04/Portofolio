<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Kecamatan extends Model
{
    use HasFactory;

    protected $table = 'kecamatan';

    protected $fillable = [
        'nama_kecamatan',
    ];

    public function poktan()
    {
        return $this->hasMany(Poktan::class, 'kecamatan_id');
    }

    public function alokasiKecamatan()
    {
        return $this->hasMany(AlokasiKecamatan::class, 'kecamatan_id');
    }

// Tambahkan di dalam class Kecamatan
    public function vikorRiwayat()
    {
        // Sesuaikan nama class Model VikorRiwayat dengan yang kamu gunakan
        return $this->hasMany(VikorRiwayat::class, 'kecamatan_id'); 
    }
}