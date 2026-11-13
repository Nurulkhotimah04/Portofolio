<?php

namespace App\Http\Controllers;

use App\Models\AlokasiKecamatan;
use App\Models\Kecamatan;
use App\Models\MusimTanam;
use App\Models\Periode;
use Illuminate\Http\Request;

class PengaturanPeriodeWilayahController extends Controller
{
    public function index(Request $request)
    {
        $allowedTabs = [
            'musim',
            'periode',
            'kecamatan',
            'alokasi',
        ];

        $activeTab = $request->get('tab', 'musim');

        if (!in_array($activeTab, $allowedTabs, true)) {
            $activeTab = 'musim';
        }

        $musimTanam = MusimTanam::orderBy(
            'kode_musim',
            'asc'
        )->get();

        $periode = Periode::orderBy(
            'tahun',
            'desc'
        )->get();

        $kecamatan = Kecamatan::withCount('poktan')
            ->orderBy(
                'nama_kecamatan',
                'asc'
            )
            ->get();

        $alokasi = AlokasiKecamatan::with([
                'kecamatan',
                'musimTanam',
                'periode',
            ])
            ->get()
            ->sortBy([
                ['periode.tahun', 'desc'],
                ['musimTanam.kode_musim', 'asc'],
                ['kecamatan.nama_kecamatan', 'asc'],
            ]);

        return view(
            'pengaturan_periode_wilayah.index',
            compact(
                'activeTab',
                'musimTanam',
                'periode',
                'kecamatan',
                'alokasi'
            )
        );
    }
}