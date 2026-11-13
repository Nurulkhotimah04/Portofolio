<?php

namespace App\Http\Controllers;

use App\Models\AlokasiKecamatan;
use App\Models\Kecamatan;
use App\Models\MusimTanam;
use App\Models\Periode;
use Illuminate\Http\Request;

class AlokasiKecamatanController extends Controller
{
    public function index()
    {
        $alokasi = AlokasiKecamatan::with([
                'kecamatan',
                'musimTanam',
                'periode',
            ])
            ->orderByDesc('periode_id')
            ->orderBy('musim_tanam_id')
            ->orderBy('kecamatan_id')
            ->get();

        $kecamatan = Kecamatan::orderBy(
            'nama_kecamatan',
            'asc'
        )->get();

        $musimTanam = MusimTanam::orderBy(
            'kode_musim',
            'asc'
        )->get();

        $periode = Periode::orderBy(
            'tahun',
            'desc'
        )->get();

        return view(
            'alokasi_kecamatan.index',
            compact(
                'alokasi',
                'kecamatan',
                'musimTanam',
                'periode'
            )
        );
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'kecamatan_id' => [
                'required',
                'exists:kecamatan,id',
            ],

            'musim_tanam_id' => [
                'required',
                'exists:musim_tanam,id',
            ],

            'periode_id' => [
                'required',
                'exists:periode,id',
            ],

            'kuota_penerima' => [
                'required',
                'integer',
                'min:0',
            ],
        ]);

        $sudahAda = AlokasiKecamatan::where(
                'kecamatan_id',
                $validated['kecamatan_id']
            )
            ->where(
                'musim_tanam_id',
                $validated['musim_tanam_id']
            )
            ->where(
                'periode_id',
                $validated['periode_id']
            )
            ->exists();

        if ($sudahAda) {
            return back()
                ->withInput()
                ->with(
                    'error',
                    'Alokasi untuk Kecamatan, Musim Tanam, dan Tahun tersebut sudah tersedia.'
                );
        }

        AlokasiKecamatan::create($validated);

        return redirect()
            ->route('alokasi-kecamatan.index')
            ->with('success', 'Alokasi Kecamatan berhasil ditambahkan.');
    }

    public function update(
        Request $request,
        AlokasiKecamatan $alokasiKecamatan
    ) {
        $validated = $request->validate([
            'kecamatan_id' => [
                'required',
                'exists:kecamatan,id',
            ],

            'musim_tanam_id' => [
                'required',
                'exists:musim_tanam,id',
            ],

            'periode_id' => [
                'required',
                'exists:periode,id',
            ],

            'kuota_penerima' => [
                'required',
                'integer',
                'min:0',
            ],
        ]);

        $sudahAda = AlokasiKecamatan::where(
                'kecamatan_id',
                $validated['kecamatan_id']
            )
            ->where(
                'musim_tanam_id',
                $validated['musim_tanam_id']
            )
            ->where(
                'periode_id',
                $validated['periode_id']
            )
            ->where(
                'id',
                '!=',
                $alokasiKecamatan->id
            )
            ->exists();

        if ($sudahAda) {
            return back()->with(
                'error',
                'Kombinasi Kecamatan, Musim Tanam, dan Tahun tersebut sudah digunakan.'
            );
        }

        $alokasiKecamatan->update($validated);

        return redirect()
            ->route('alokasi-kecamatan.index')
            ->with('success', 'Alokasi Kecamatan berhasil diperbarui.');
    }

    public function destroy(
        AlokasiKecamatan $alokasiKecamatan
    ) {
        $alokasiKecamatan->delete();

        return redirect()
            ->route
            ('pengaturan-periode-wilayah.index',
            ['tab' => 'alokasi']
        )
            ->with('success', 'Alokasi Kecamatan berhasil dihapus.');
    }
}