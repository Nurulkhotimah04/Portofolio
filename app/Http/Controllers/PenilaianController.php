<?php

namespace App\Http\Controllers;

use App\Models\Petani;
use App\Models\Kriteria;
use App\Models\Subkriteria;
use App\Models\Penilaian;
use Illuminate\Http\Request;

class PenilaianController extends Controller
{
    // Menampilkan daftar penilaian
    public function index()
    {
        $petani = Petani::paginate(10);

        $totalPetani = Petani::count();
        $sudahDinilai = Penilaian::distinct('petani_id')->count('petani_id');
        $belumDinilai = $totalPetani - $sudahDinilai;
        $adaPengajuan = Petani::whereNotNull('luas_lahan')->count();

        return view('penilaian.index', compact(
            'petani',
            'totalPetani',
            'sudahDinilai',
            'belumDinilai',
            'adaPengajuan'
        ));
    }

    // Menampilkan form penilaian
    public function create()
    {
        $petani = Petani::all();
        $kriteria = Kriteria::with('subkriteria')->get();

        return view('penilaian.create', compact('petani', 'kriteria'));
    }

    // Form penilaian untuk petani tertentu
    public function createFor(string $id)
    {
        $petani = Petani::all();
        $selectedPetani = Petani::findOrFail($id);
        $kriteria = Kriteria::with('subkriteria')->get();

        return view('penilaian.create', compact(
            'petani',
            'kriteria',
            'selectedPetani'
        ));
    }

    // Menyimpan penilaian
    public function store(Request $request)
    {
        $request->validate([
            'petani_id' => 'required|exists:petani,id',
            'subkriteria' => 'required|array',
        ]);

        // Hapus penilaian lama
        Penilaian::where('petani_id', $request->petani_id)->delete();

        foreach ($request->subkriteria as $kriteria_id => $input_sub) {
            
            // CEK: Apakah input berupa Array? (Berarti ini dari Checkbox / C4)
            if (is_array($input_sub)) {
                // Ambil semua data subkriteria yang dicentang
                $subkriterias = Subkriteria::whereIn('id', $input_sub)->get();
                
                // Hitung RATA-RATA bobotnya
                $rata_rata_bobot = $subkriterias->avg('bobot');

                // Simpan setiap pilihan ke database, tapi nilainya menggunakan nilai rata-rata
                foreach ($input_sub as $sub_id) {
                    Penilaian::create([
                        'petani_id' => $request->petani_id,
                        'kriteria_id' => $kriteria_id,
                        'subkriteria_id' => $sub_id,
                        'nilai' => $rata_rata_bobot,
                    ]);
                }
            } else {
                // JIKA BUKAN ARRAY (Berarti dari Dropdown biasa)
                $sub = Subkriteria::findOrFail($input_sub);

                Penilaian::create([
                    'petani_id' => $request->petani_id,
                    'kriteria_id' => $kriteria_id,
                    'subkriteria_id' => $input_sub,
                    'nilai' => $sub->bobot,
                ]);
            }
        }

        return redirect('/penilaian')
            ->with('success', 'Penilaian berhasil disimpan.');
    }

    // Menampilkan detail penilaian
    public function show(string $id)
    {
        $petani = Petani::findOrFail($id);

        $penilaian = Penilaian::with(['kriteria', 'subkriteria'])
            ->where('petani_id', $id)
            ->get();

        return view('penilaian.show', compact('petani', 'penilaian'));
    }

    // Form edit penilaian
    public function edit(string $id)
    {
        $petani = Petani::findOrFail($id);
        $kriteria = Kriteria::with('subkriteria')->get();

        // AMBIL DATA LAMA: Diubah agar bisa menampung banyak pilihan (Array) untuk 1 Kriteria
        $dataPenilaian = Penilaian::where('petani_id', $id)->get();
        $penilaianLama = [];
        
        foreach ($dataPenilaian as $p) {
            $penilaianLama[$p->kriteria_id][] = $p->subkriteria_id;
        }

        return view('penilaian.edit', compact(
            'petani',
            'kriteria',
            'penilaianLama'
        ));
    }

    // Update penilaian
    public function update(Request $request, string $id)
    {
        $request->validate([
            'subkriteria' => 'required|array',
        ]);

        Penilaian::where('petani_id', $id)->delete();

        foreach ($request->subkriteria as $kriteria_id => $input_sub) {
            
            // CEK: Apakah input berupa Array? (Berarti ini dari Checkbox / C4)
            if (is_array($input_sub)) {
                $subkriterias = Subkriteria::whereIn('id', $input_sub)->get();
                
                // Hitung RATA-RATA bobotnya
                $rata_rata_bobot = $subkriterias->avg('bobot');

                foreach ($input_sub as $sub_id) {
                    Penilaian::create([
                        'petani_id' => $id,
                        'kriteria_id' => $kriteria_id,
                        'subkriteria_id' => $sub_id,
                        'nilai' => $rata_rata_bobot,
                    ]);
                }
            } else {
                // JIKA BUKAN ARRAY (Berarti dari Dropdown biasa)
                $sub = Subkriteria::findOrFail($input_sub);

                Penilaian::create([
                    'petani_id' => $id,
                    'kriteria_id' => $kriteria_id,
                    'subkriteria_id' => $input_sub,
                    'nilai' => $sub->bobot,
                ]);
            }
        }

        return redirect('/penilaian')
            ->with('success', 'Penilaian berhasil diperbarui.');
    }

    // Menghapus penilaian
    public function destroy(string $id)
    {
        Penilaian::where('petani_id', $id)->delete();

        return redirect('/penilaian')
            ->with('success', 'Penilaian berhasil dihapus.');
    }

    // Generate Penilaian Otomatis dari Tabel Petani
    public function generatePenilaianOtomatis()
    {
        // Ambil semua data petani yang BELUM punya penilaian sama sekali
        $petaniBelumDinilai = Petani::doesntHave('penilaian')->get();

        // Ambil data kriteria berdasarkan kodenya agar ID kriteria tidak tertukar
        $kriteria = Kriteria::all()->keyBy('kode_kriteria');
        
        $c1 = $kriteria['C1']->id ?? null;
        $c2 = $kriteria['C2']->id ?? null;
        $c3 = $kriteria['C3']->id ?? null;
        $c4 = $kriteria['C4']->id ?? null;
        $c5 = $kriteria['C5']->id ?? null;

        foreach ($petaniBelumDinilai as $p) {
            
            // C1 - Luas Lahan (Pencocokan Teks Persis)
            if ($p->luas_lahan && $c1) {
                $sub = Subkriteria::where('kriteria_id', $c1)->where('nama_subkriteria', $p->luas_lahan)->first();
                if ($sub) {
                    Penilaian::create(['petani_id' => $p->id, 'kriteria_id' => $c1, 'subkriteria_id' => $sub->id, 'nilai' => $sub->bobot]);
                }
            }

            // C2 - Jenis Komoditas
            if ($p->jenis_komoditas && $c2) {
                $sub = Subkriteria::where('kriteria_id', $c2)->where('nama_subkriteria', $p->jenis_komoditas)->first();
                if ($sub) {
                    Penilaian::create(['petani_id' => $p->id, 'kriteria_id' => $c2, 'subkriteria_id' => $sub->id, 'nilai' => $sub->bobot]);
                }
            }

            // C3 - Kelengkapan Berkas
            if ($p->kelengkapan_berkas && $c3) {
                $sub = Subkriteria::where('kriteria_id', $c3)->where('nama_subkriteria', $p->kelengkapan_berkas)->first();
                if ($sub) {
                    Penilaian::create(['petani_id' => $p->id, 'kriteria_id' => $c3, 'subkriteria_id' => $sub->id, 'nilai' => $sub->bobot]);
                }
            }

            // C5 - Subsektor Pertanian
            if ($p->subsektor_pertanian && $c5) {
                $sub = Subkriteria::where('kriteria_id', $c5)->where('nama_subkriteria', $p->subsektor_pertanian)->first();
                if ($sub) {
                    Penilaian::create(['petani_id' => $p->id, 'kriteria_id' => $c5, 'subkriteria_id' => $sub->id, 'nilai' => $sub->bobot]);
                }
            }

            // C4 - Kebutuhan Pupuk (TIPE ARRAY / BANYAK PILIHAN)
            if ($p->kebutuhan_pupuk && $c4) {
                // Pecah teks "Pupuk NPK, Pupuk ZA" menjadi array
                $nama_pupuk_array = array_map('trim', explode(',', $p->kebutuhan_pupuk));
                
                // Cari subkriteria yang namanya cocok dengan array tersebut
                $subkriterias = Subkriteria::where('kriteria_id', $c4)
                                           ->whereIn('nama_subkriteria', $nama_pupuk_array)
                                           ->get();
                
                if ($subkriterias->count() > 0) {
                    // Hitung rata-ratanya sama seperti saat input manual
                    $rata_rata_bobot = $subkriterias->avg('bobot');
                    
                    foreach ($subkriterias as $sub) {
                        Penilaian::create([
                            'petani_id' => $p->id, 
                            'kriteria_id' => $c4, 
                            'subkriteria_id' => $sub->id, 
                            'nilai' => $rata_rata_bobot
                        ]);
                    }
                }
            }
        }

        return redirect('/penilaian')->with('success', 'Penilaian otomatis berhasil di-generate!');
    }
}