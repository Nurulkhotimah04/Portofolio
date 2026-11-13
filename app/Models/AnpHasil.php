<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AnpHasil extends Model
{
    use HasFactory;

    protected $table = 'anp_hasil';

    protected $fillable = [
        'kriteria_id',
        'bobot',
    ];

    protected $casts = [
        'bobot' => 'float',
    ];

    public function kriteria()
    {
        return $this->belongsTo(
            Kriteria::class,
            'kriteria_id'
        );
    }
}