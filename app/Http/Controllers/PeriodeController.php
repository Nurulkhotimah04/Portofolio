<?php

namespace App\Http\Controllers;

use App\Models\Periode;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class PeriodeController extends Controller
{
    public function index()
    {
        $periode = Periode::orderBy('tahun', 'desc')->get();

        return view('periode.index', compact('periode'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'tahun' => [
                'required',
                'integer',
                'min:2020',
                'max:2100',
                'unique:periode,tahun',
            ],
        ], [
            'tahun.required' => 'Tahun/Periode wajib diisi.',
            'tahun.unique' => 'Tahun/Periode sudah tersedia.',
        ]);

        Periode::create([
            'tahun' => $validated['tahun'],
        ]);

        return redirect()
            ->route('periode.index')
            ->with('success', 'Periode berhasil ditambahkan.');
    }

    public function update(
        Request $request,
        Periode $periode
    ) {
        $validated = $request->validate([
            'tahun' => [
                'required',
                'integer',
                'min:2020',
                'max:2100',
                Rule::unique('periode', 'tahun')
                    ->ignore($periode->id),
            ],
        ]);

        $periode->update([
            'tahun' => $validated['tahun'],
        ]);

        return redirect()
            ->route('periode.index')
            ->with('success', 'Periode berhasil diperbarui.');
    }

    public function destroy(Periode $periode)
    {
        if (
            $periode->pengajuanPetani()->exists() ||
            $periode->alternatifMusim()->exists() ||
            $periode->alokasiKecamatan()->exists() ||
            $periode->batchImportPengajuan()->exists()
        ) {
            return back()->with(
                'error',
                'Periode tidak dapat dihapus karena sudah digunakan.'
            );
        }

        $periode->delete();

        return redirect()
        ->route
            ('pengaturan-periode-wilayah.index',
            ['tab' => 'periode']
        )
            ->with('success', 'Periode berhasil dihapus.');
    }
}