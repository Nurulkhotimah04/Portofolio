<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Petani extends Model
{
    use HasFactory;

    protected $table = 'petani';

    protected $fillable = [
        'user_id',
        'nik',
        'nama_petani',
        'poktan_id',

        // SEMENTARA MASIH DIPERTAHANKAN
        // karena data alternatif lama A1-A30 masih berada di tabel petani.
        'kode_alternatif',
        'luas_lahan',
        'jenis_komoditas',
        'kelengkapan_berkas',
        'kebutuhan_pupuk',
        'subsektor_pertanian',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function poktan()
    {
        return $this->belongsTo(Poktan::class, 'poktan_id');
    }

    public function penilaian()
    {
        return $this->hasMany(Penilaian::class, 'petani_id');
    }

    public function alternatifMusim()
    {
        return $this->hasMany(AlternatifMusim::class, 'petani_id');
    }

    public function vikorHasil()
    {
        return $this->hasMany(VikorHasil::class, 'petani_id');
    }
}