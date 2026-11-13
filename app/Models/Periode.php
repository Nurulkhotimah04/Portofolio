<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Periode extends Model
{
    use HasFactory;

    protected $table = 'periode';

    protected $fillable = [
        'tahun',
    ];

    protected $casts = [
        'tahun' => 'integer',
    ];

    public function pengajuanPetani()
    {
        return $this->hasMany(PengajuanPetani::class, 'periode_id');
    }

    public function alternatifMusim()
    {
        return $this->hasMany(AlternatifMusim::class, 'periode_id');
    }

    public function alokasiKecamatan()
    {
        return $this->hasMany(AlokasiKecamatan::class, 'periode_id');
    }

    public function batchImportPengajuan()
    {
        return $this->hasMany(BatchImportPengajuan::class, 'periode_id');
    }
}