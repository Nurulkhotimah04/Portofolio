<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VikorRiwayat extends Model
{
    protected $table = 'vikor_riwayat';

    protected $fillable = [
        'run_uuid', 'user_id', 'musim_tanam_id', 'periode_id', 'kecamatan_id',
        'alternatif_musim_id', 'petani_id', 'kode_alternatif', 'nik', 'nama_petani', 'nama_poktan',
        'nilai_s', 'nilai_r', 'nilai_qs',
        'nilai_qr', 'nilai_q', 'ranking', 'kuota_penerima', 'status_kuota', 'calculated_at',
    ];

    protected $casts = [
        'nilai_s' => 'float',
        'nilai_r' => 'float',
        'nilai_qs' => 'float',
        'nilai_qr' => 'float',
        'nilai_q' => 'float',
        'ranking' => 'integer',
        'kuota_penerima' => 'integer',
        'calculated_at' => 'datetime',
    ];

    public function alternatifMusim()
    {
        return $this->belongsTo(AlternatifMusim::class, 'alternatif_musim_id');
    }

    public function petani()
    {
        return $this->belongsTo(Petani::class, 'petani_id');
    }
}
