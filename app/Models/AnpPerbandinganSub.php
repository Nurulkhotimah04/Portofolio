<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AnpPerbandinganSub extends Model
{
    protected $table = 'anp_perbandingan_sub';

    protected $fillable = [
        'kriteria_id',
        'subkriteria_1',
        'subkriteria_2',
        'nilai'
    ];
}