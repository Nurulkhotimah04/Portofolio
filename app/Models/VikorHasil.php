<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VikorHasil extends Model
{
    use HasFactory;

    protected $table = 'vikor_hasil';


    protected $fillable = [
        'petani_id',
        'alternatif_musim_id',
        'nilai_s',
        'nilai_r',
        'nilai_qs',
        'nilai_qr',
        'nilai_q',
        'ranking',
    ];


    protected $casts = [
        'nilai_s' => 'float',
        'nilai_r' => 'float',
        'nilai_qs' => 'float',
        'nilai_qr' => 'float',
        'nilai_q' => 'float',
        'ranking' => 'integer',
    ];


    /*
    |--------------------------------------------------------------------------
    | PETANI MASTER
    |--------------------------------------------------------------------------
    */

    public function petani()
    {
        return $this->belongsTo(
            Petani::class,
            'petani_id'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | ALTERNATIF MUSIM
    |--------------------------------------------------------------------------
    */

    public function alternatifMusim()
    {
        return $this->belongsTo(
            AlternatifMusim::class,
            'alternatif_musim_id'
        );
    }
}