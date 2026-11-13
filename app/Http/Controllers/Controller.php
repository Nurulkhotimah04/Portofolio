<?php

namespace App\Http\Controllers;
use PhpOffice\PhpSpreadsheet\IOFactory;

use App\Models\Petani; 
use App\Models\Kriteria;
use App\Models\Penilaian;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;

class Controller extends BaseController
{
    use AuthorizesRequests, ValidatesRequests;
}
