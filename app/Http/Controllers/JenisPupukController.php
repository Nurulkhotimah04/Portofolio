<?php

namespace App\Http\Controllers;

use App\Models\JenisPupuk;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class JenisPupukController extends Controller
{
    public function index(Request $request)
    {
        $query = JenisPupuk::query();

        if ($request->filled('search')) {
            $query->where('nama_pupuk', 'like', '%' . trim((string) $request->search) . '%');
        }

        $jenisPupuk = $query->orderBy('nama_pupuk')->paginate(10)->withQueryString();

        return view('jenis_pupuk.index', compact('jenisPupuk'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'nama_pupuk' => ['required', 'string', 'max:100', 'unique:jenis_pupuk,nama_pupuk'],
        ]);

        JenisPupuk::create([
            'nama_pupuk' => trim($data['nama_pupuk']),
            'is_active' => true,
        ]);

        return back()->with('success', 'Jenis pupuk berhasil ditambahkan.');
    }

    public function update(Request $request, JenisPupuk $jenisPupuk)
    {
        $data = $request->validate([
            'nama_pupuk' => [
                'required', 'string', 'max:100',
                Rule::unique('jenis_pupuk', 'nama_pupuk')->ignore($jenisPupuk->id),
            ],
            'is_active' => ['required', 'boolean'],
        ]);

        $jenisPupuk->update($data);

        return back()->with('success', 'Jenis pupuk berhasil diperbarui.');
    }

    public function destroy(JenisPupuk $jenisPupuk)
    {
        if ($jenisPupuk->pengajuanPupuk()->exists() || $jenisPupuk->alternatifPupuk()->exists()) {
            $jenisPupuk->update(['is_active' => false]);
            return back()->with('success', 'Jenis pupuk sudah pernah digunakan, sehingga dinonaktifkan dan tidak dihapus dari histori.');
        }

        $jenisPupuk->delete();
        return back()->with('success', 'Jenis pupuk berhasil dihapus.');
    }
}
