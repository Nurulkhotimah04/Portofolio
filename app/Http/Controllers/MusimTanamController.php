<?php

namespace App\Http\Controllers;

use App\Models\MusimTanam;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class MusimTanamController extends Controller
{
    public function index()
    {
        $musimTanam = MusimTanam::orderBy('kode_musim', 'asc')->get();

        return view('musim_tanam.index', compact('musimTanam'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'kode_musim' => [
                'required',
                'string',
                'max:20',
                'unique:musim_tanam,kode_musim',
            ],

            'nama_musim' => [
                'required',
                'string',
                'max:100',
                'unique:musim_tanam,nama_musim',
            ],
        ], [
            'kode_musim.required' => 'Kode musim wajib diisi.',
            'kode_musim.unique' => 'Kode musim sudah digunakan.',
            'nama_musim.required' => 'Nama musim wajib diisi.',
            'nama_musim.unique' => 'Nama musim sudah tersedia.',
        ]);

        MusimTanam::create([
            'kode_musim' => strtoupper($validated['kode_musim']),
            'nama_musim' => $validated['nama_musim'],
        ]);

        return redirect()
            ->route('musim-tanam.index')
            ->with('success', 'Musim Tanam berhasil ditambahkan.');
    }

    public function update(
        Request $request,
        MusimTanam $musimTanam
    ) {
        $validated = $request->validate([
            'kode_musim' => [
                'required',
                'string',
                'max:20',
                Rule::unique('musim_tanam', 'kode_musim')
                    ->ignore($musimTanam->id),
            ],

            'nama_musim' => [
                'required',
                'string',
                'max:100',
                Rule::unique('musim_tanam', 'nama_musim')
                    ->ignore($musimTanam->id),
            ],
        ]);

        $musimTanam->update([
            'kode_musim' => strtoupper($validated['kode_musim']),
            'nama_musim' => $validated['nama_musim'],
        ]);

        return redirect()
            ->route('musim-tanam.index')
            ->with('success', 'Musim Tanam berhasil diperbarui.');
    }

    public function destroy(MusimTanam $musimTanam)
    {
        if (
            $musimTanam->pengajuanPetani()->exists() ||
            $musimTanam->alternatifMusim()->exists() ||
            $musimTanam->alokasiKecamatan()->exists() ||
            $musimTanam->batchImportPengajuan()->exists()
        ) {
            return back()->with(
                'error',
                'Musim Tanam tidak dapat dihapus karena sudah digunakan.'
            );
        }

        $musimTanam->delete();

        return redirect()
        ->route
            ('pengaturan-periode-wilayah.index',
            ['tab' => 'musim']
            )
            ->with('success', 'Musim Tanam berhasil dihapus.');
    }
}