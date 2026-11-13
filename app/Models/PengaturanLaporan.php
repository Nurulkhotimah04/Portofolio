<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PengaturanLaporan extends Model
{
    use HasFactory;

    protected $table = 'pengaturan_laporan';

    protected $fillable = [
        'nama_kepala_dinas',
        'nip_kepala_dinas',
    ];
}
