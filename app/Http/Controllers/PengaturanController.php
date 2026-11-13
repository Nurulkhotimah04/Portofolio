<?php

namespace App\Http\Controllers;

use App\Models\AlokasiKecamatan;
use App\Models\Kecamatan;
use App\Models\Kriteria;
use App\Models\JenisPupuk;
use App\Models\MusimTanam;
use App\Models\Periode;
use App\Models\Poktan;
use App\Models\Subkriteria;
use App\Models\AuditLog;

class PengaturanController extends Controller
{
    public function index()
    {
        $ringkasan = [
            'musim_tanam' => MusimTanam::count(),
            'periode' => Periode::count(),
            'kecamatan' => Kecamatan::count(),
            'alokasi' => AlokasiKecamatan::count(),
            'poktan' => Poktan::count(),
            'kriteria' => Kriteria::count(),
            'subkriteria' => Subkriteria::count(),
            'jenis_pupuk' => JenisPupuk::where('is_active', true)->count(),
            'audit_log' => AuditLog::count(),
        ];

        return view('pengaturan.index', compact('ringkasan'));
    }
}
