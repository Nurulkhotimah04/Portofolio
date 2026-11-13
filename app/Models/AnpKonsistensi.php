<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AnpKonsistensi extends Model
{
    use HasFactory;

    protected $table = 'anp_konsistensi';

    protected $fillable = [
        'kriteria_dipengaruhi_id',
        'lambda_max',
        'ci',
        'cr',
        'status_konsisten',
    ];

    protected $casts = [
        'lambda_max' => 'float',
        'ci' => 'float',
        'cr' => 'float',
        'status_konsisten' => 'boolean',
    ];

    public function kriteria()
    {
        return $this->belongsTo(
            Kriteria::class,
            'kriteria_dipengaruhi_id'
        );
    }
}