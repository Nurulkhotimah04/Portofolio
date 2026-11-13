<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AnpFeedback extends Model
{
    use HasFactory;

    protected $table = 'anp_feedback';

    protected $fillable = [
        'kriteria_asal_id',
        'kriteria_tujuan_id',
        'nilai',
    ];

    public function kriteriaAsal()
    {
        return $this->belongsTo(
            Kriteria::class,
            'kriteria_asal_id'
        );
    }

    public function kriteriaTujuan()
    {
        return $this->belongsTo(
            Kriteria::class,
            'kriteria_tujuan_id'
        );
    }
}