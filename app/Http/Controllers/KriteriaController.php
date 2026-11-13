<?php

namespace App\Http\Controllers;

use App\Models\Kriteria;
use Illuminate\Http\Request;

class KriteriaController extends Controller
{
    // Berfungsi untuk menampilkan semua data kriteria
    public function index()
    {
        // Mengambil semua data kriteria dari database
        $kriteria = Kriteria::all();

        // Mengirim data ke halaman index
        return view('kriteria.index', compact('kriteria'));
    }

    // Untuk menampilkan form data kriteria
    public function create()
    {
        return view('kriteria.create');
    }

    // Berfungsi untuk menyimpan data kriteria yang baru
    public function store(Request $request)
    {
        // Validasi input
        $request->validate([
            'kode_kriteria' => 'required',
            'nama_kriteria' => 'required',
            'bobot' => 'required|numeric',
            'atribut' => 'required',
        ]);

        // Menyimpan data ke tabel kriteria
        Kriteria::create([
            'kode_kriteria' => $request->kode_kriteria,
            'nama_kriteria' => $request->nama_kriteria,
            'bobot' => $request->bobot,
            'atribut' => $request->atribut,
        ]);

        // Kembali ke halaman kriteria
        return redirect('/kriteria');
    }

    // Untuk tampilan form edit
    public function edit(string $id)
    {
        // Mengambil data berdasarkan id
        $kriteria = Kriteria::findOrFail($id);

        return view('kriteria.edit', compact('kriteria'));
    }

        // Untuk megupdate data kriteria
    public function update(Request $request, string $id)
    {
        // Lakukan validasi terlebih dahulu
        $request->validate([
            'kode_kriteria' => 'required',
            'nama_kriteria' => 'required',
            'bobot' => 'required|numeric',
            'atribut' => 'required',
        ]);

        // Mengambil data kriteria
        $kriteria = Kriteria::findOrFail($id);

        // Mengupdate data dengan inputan dari form ($request->bobot)
        $kriteria->update([
            'kode_kriteria' => $request->kode_kriteria,
            'nama_kriteria' => $request->nama_kriteria,
            'bobot' => $request->bobot, // <--- Sudah diperbaiki menjadi ini
            'atribut' => $request->atribut,
        ]);

        // Kembali ke halaman kriteria
        return redirect('/kriteria');
    }

    // Untuk menghapus data kriteria
    public function destroy(string $id)
    {
        // Mengambil data kriteria
        $kriteria = Kriteria::findOrFail($id);

        // Menghapus data
        $kriteria->delete();

        // Kembali ke halaman kriteria
        return redirect('/kriteria');
    }
}