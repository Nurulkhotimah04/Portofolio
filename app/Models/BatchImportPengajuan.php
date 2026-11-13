<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BatchImportPengajuan extends Model
{
    use HasFactory;

    protected $table = 'batch_import_pengajuan';

    protected $fillable = [
        'kode_batch',
        'penyuluh_id',
        'poktan_id',
        'musim_tanam_id',
        'periode_id',
        'nama_file',
        'total_data',
        'total_valid',
        'total_gagal',
        'status',
    ];

    protected $casts = [
        'total_data' => 'integer',
        'total_valid' => 'integer',
        'total_gagal' => 'integer',
    ];

    public function penyuluh()
    {
        return $this->belongsTo(Penyuluh::class, 'penyuluh_id');
    }

    public function poktan()
    {
        return $this->belongsTo(Poktan::class, 'poktan_id');
    }

    public function musimTanam()
    {
        return $this->belongsTo(MusimTanam::class, 'musim_tanam_id');
    }

    public function periode()
    {
        return $this->belongsTo(Periode::class, 'periode_id');
    }

    public function pengajuanPetani()
    {
        return $this->hasMany(PengajuanPetani::class, 'batch_import_id');
    }
}