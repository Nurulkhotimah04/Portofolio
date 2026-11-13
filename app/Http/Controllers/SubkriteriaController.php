<?php

namespace App\Http\Controllers;

use App\Models\Subkriteria;
use App\Models\Kriteria;
use Illuminate\Http\Request;

class SubkriteriaController extends Controller
{
    /**
     * Menampilkan data subkriteria
     */
    public function index()
    {
        $kriteria = Kriteria::with('subkriteria')
            ->orderBy('id', 'asc')
            ->get();

        return view('subkriteria.index', compact('kriteria'));
    }

    /**
     * Form tambah subkriteria
     */
    public function create()
    {
        $kriteria = Kriteria::orderBy('id', 'asc')->get();

        return view('subkriteria.create', compact('kriteria'));
    }

    /**
     * Simpan subkriteria baru
     */
    public function store(Request $request)
    {
        $request->validate([
            'kriteria_id' => 'required|exists:kriteria,id',
            'kode_subkriteria' => 'required|string|max:20',
            'nama_subkriteria' => 'required|string|max:255',
            'bobot' => 'required|numeric',
        ]);

        Subkriteria::create([
            'kriteria_id' => $request->kriteria_id,
            'kode_subkriteria' => strtoupper(trim($request->kode_subkriteria)),
            'nama_subkriteria' => $request->nama_subkriteria,
            'bobot' => $request->bobot,
        ]);

        return redirect()
            ->route('subkriteria.index')
            ->with('success', 'Subkriteria berhasil ditambahkan.');
    }

        
    /**
     * Form edit subkriteria
     */
    public function edit($id)
    {
        $subkriteria = Subkriteria::findOrFail($id);

        $kriteria = Kriteria::orderBy('id', 'asc')->get();

        return view('subkriteria.edit', compact(
            'subkriteria',
            'kriteria'
        ));
    }

    /**
     * Update subkriteria
     */
    public function update(Request $request, $id)
    {
        $subkriteria = Subkriteria::findOrFail($id);

        $request->validate([
            'kriteria_id' => 'required|exists:kriteria,id',
            'kode_subkriteria' => 'required|string|max:20',
            'nama_subkriteria' => 'required|string|max:255',
            'bobot' => 'required|numeric',
        ]);

        $subkriteria->update([
            'kriteria_id' => $request->kriteria_id,
            'kode_subkriteria' => strtoupper(trim($request->kode_subkriteria)),
            'nama_subkriteria' => $request->nama_subkriteria,
            'bobot' => $request->bobot,
        ]);

        return redirect()
            ->route('subkriteria.index')
            ->with('success', 'Subkriteria berhasil diperbarui.');
    }

    /**
     * Hapus subkriteria
     */
    public function destroy($id)
    {
        $subkriteria = Subkriteria::findOrFail($id);

        $subkriteria->delete();

        return redirect()
            ->route('subkriteria.index')
            ->with('success', 'Subkriteria berhasil dihapus.');
    }
}