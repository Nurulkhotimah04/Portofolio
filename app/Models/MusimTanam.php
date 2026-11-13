<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MusimTanam extends Model
{
    use HasFactory;

    protected $table = 'musim_tanam';

    protected $fillable = [
        'kode_musim',
        'nama_musim',
    ];

    public function pengajuanPetani()
    {
        return $this->hasMany(PengajuanPetani::class, 'musim_tanam_id');
    }

    public function alternatifMusim()
    {
        return $this->hasMany(AlternatifMusim::class, 'musim_tanam_id');
    }

    public function alokasiKecamatan()
    {
        return $this->hasMany(AlokasiKecamatan::class, 'musim_tanam_id');
    }

    public function batchImportPengajuan()
    {
        return $this->hasMany(BatchImportPengajuan::class, 'musim_tanam_id');
    }
}