<?php

namespace App\Http\Controllers;

use App\Models\Kecamatan;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class KecamatanController extends Controller
{
    public function index()
    {
        $kecamatan = Kecamatan::withCount('poktan')
            ->orderBy('nama_kecamatan', 'asc')
            ->get();

        return view('kecamatan.index', compact('kecamatan'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama_kecamatan' => [
                'required',
                'string',
                'max:100',
                'unique:kecamatan,nama_kecamatan',
            ],
        ], [
            'nama_kecamatan.required' =>
                'Nama Kecamatan wajib diisi.',

            'nama_kecamatan.unique' =>
                'Kecamatan tersebut sudah tersedia.',
        ]);

        Kecamatan::create([
            'nama_kecamatan' => strtoupper(
                trim($validated['nama_kecamatan'])
            ),
        ]);

        return redirect()
            ->route('kecamatan.index')
            ->with('success', 'Kecamatan berhasil ditambahkan.');
    }

    public function update(
        Request $request,
        Kecamatan $kecamatan
    ) {
        $validated = $request->validate([
            'nama_kecamatan' => [
                'required',
                'string',
                'max:100',
                Rule::unique('kecamatan', 'nama_kecamatan')
                    ->ignore($kecamatan->id),
            ],
        ]);

        $kecamatan->update([
            'nama_kecamatan' => strtoupper(
                trim($validated['nama_kecamatan'])
            ),
        ]);

        return redirect()
            ->route('kecamatan.index')
            ->with('success', 'Kecamatan berhasil diperbarui.');
    }

    public function destroy(Kecamatan $kecamatan)
        {
            // Tambahkan pengecekan vikorRiwayat() di dalam kondisi IF ini
            if (
                $kecamatan->poktan()->exists() ||
                $kecamatan->alokasiKecamatan()->exists() ||
                $kecamatan->vikorRiwayat()->exists() 
            ) {
                return back()->with(
                    'error',
                    'Kecamatan tidak dapat dihapus karena sudah digunakan oleh Poktan, Alokasi Kecamatan, atau Riwayat Vikor.'
                );
            }

            $kecamatan->delete();

            return redirect()
                ->route('pengaturan-periode-wilayah.index', ['tab' => 'kecamatan'])
                ->with('success', 'Kecamatan berhasil dihapus.');
        }

}