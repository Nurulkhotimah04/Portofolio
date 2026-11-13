<?php

namespace App\Http\Controllers;

use App\Models\Petani;
use App\Models\Subkriteria;
use App\Models\Poktan;
use App\Models\Penyuluh;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Auth;
use PhpOffice\PhpSpreadsheet\IOFactory;

class PetaniController extends Controller
{
    /**
     * ============================================================
     * MENGAMBIL DATA POKTAN SESUAI ROLE USER
     * ============================================================
     *
     * Admin:
     *   -> semua Poktan
     *
     * Penyuluh:
     *   -> hanya Poktan yang dibinanya
     */
    private function getPoktanUser()
    {
        $user = Auth::user();

        /*
        |--------------------------------------------------------------------------
        | ADMIN
        |--------------------------------------------------------------------------
        */

        if ($user->role === 'admin') {

            return Poktan::orderBy('nama_poktan')->get();
        }


        /*
        |--------------------------------------------------------------------------
        | PENYULUH
        |--------------------------------------------------------------------------
        */

        if ($user->role === 'penyuluh') {

            $penyuluh = Penyuluh::where('user_id', $user->id)->first();

            if (!$penyuluh) {
                return collect();
            }

            return Poktan::where('penyuluh_id', $penyuluh->id)
                ->orderBy('nama_poktan')
                ->get();
        }


        return collect();
    }


    /**
     * ============================================================
     * CEK APAKAH POKTAN BOLEH DIAKSES USER
     * ============================================================
     */
    private function isPoktanAllowed($poktanId)
    {
        $user = Auth::user();


        /*
        |--------------------------------------------------------------------------
        | ADMIN
        |--------------------------------------------------------------------------
        */

        if ($user->role === 'admin') {

            return Poktan::where('id', $poktanId)->exists();
        }


        /*
        |--------------------------------------------------------------------------
        | PENYULUH
        |--------------------------------------------------------------------------
        */

        if ($user->role === 'penyuluh') {

            $penyuluh = Penyuluh::where('user_id', $user->id)->first();

            if (!$penyuluh) {
                return false;
            }

            return Poktan::where('id', $poktanId)
                ->where('penyuluh_id', $penyuluh->id)
                ->exists();
        }


        return false;
    }


    /**
     * ============================================================
     * MENAMPILKAN DATA PETANI
     * ============================================================
     *
     * Admin:
     *   -> semua petani
     *
     * Penyuluh:
     *   -> hanya petani dari Poktan binaannya
     */
    public function index()
    {
        $user = Auth::user();


        /*
        |--------------------------------------------------------------------------
        | ADMIN
        |--------------------------------------------------------------------------
        */

        if ($user->role === 'admin') {

            $petani = Petani::with('poktan')
                ->orderBy('id', 'desc')
                ->get();
        }


        /*
        |--------------------------------------------------------------------------
        | PENYULUH
        |--------------------------------------------------------------------------
        */

        elseif ($user->role === 'penyuluh') {

            $penyuluh = Penyuluh::where('user_id', $user->id)->first();

            if ($penyuluh) {

                /*
                | Ambil ID Poktan yang dibina penyuluh
                */

                $poktanIds = Poktan::where(
                    'penyuluh_id',
                    $penyuluh->id
                )->pluck('id');


                /*
                | Ambil petani yang berada
                | di Poktan tersebut
                */

                $petani = Petani::with('poktan')
                    ->whereIn('poktan_id', $poktanIds)
                    ->orderBy('id', 'desc')
                    ->get();
            } else {

                $petani = collect();
            }
        }


        /*
        |--------------------------------------------------------------------------
        | ROLE LAIN
        |--------------------------------------------------------------------------
        */

        else {

            abort(403, 'Anda tidak memiliki akses ke data petani.');
        }


        return view(
            'petani.index',
            compact('petani')
        );
    }


    /**
     * ============================================================
     * FORM TAMBAH PETANI
     * ============================================================
     *
     * Admin dan Penyuluh boleh menambahkan petani.
     *
     * Penyuluh hanya dapat memilih Poktan binaannya.
     */
    public function create()
    {
        $lastPetani = Petani::orderBy('id', 'desc')->first();


        /*
        |--------------------------------------------------------------------------
        | Membuat kode alternatif otomatis
        |--------------------------------------------------------------------------
        */

        if ($lastPetani && $lastPetani->kode_alternatif) {

            $lastNumber = (int) substr(
                $lastPetani->kode_alternatif,
                1
            );

            $newNumber = $lastNumber + 1;

        } else {

            $newNumber = 1;
        }


        $kode_alternatif = 'A' . $newNumber;


        /*
        |--------------------------------------------------------------------------
        | Subkriteria C4
        |--------------------------------------------------------------------------
        */

        $subkriteria_c4 = Subkriteria::whereHas(
            'kriteria',
            function ($query) {
                $query->where(
                    'kode_kriteria',
                    'C4'
                );
            }
        )->get();


        /*
        |--------------------------------------------------------------------------
        | Poktan sesuai role
        |--------------------------------------------------------------------------
        */

        $poktan = $this->getPoktanUser();


        return view(
            'petani.create',
            compact(
                'kode_alternatif',
                'subkriteria_c4',
                'poktan'
            )
        );
    }


    /**
     * ============================================================
     * MENYIMPAN DATA PETANI
     * ============================================================
     */
    public function store(Request $request)
    {
        /*
        |--------------------------------------------------------------------------
        | VALIDASI
        |--------------------------------------------------------------------------
        */

        $request->validate([
            'kode_alternatif' => 'required',

            'nik' => [
                'required',
                'digits:16',
                Rule::unique(
                    Petani::class,
                    'nik'
                ),
            ],

            'nama_petani' => 'required',

            'poktan_id' => [
                'required',
                'exists:poktan,id',
            ],

            'luas_lahan' => 'required',

            'jenis_komoditas' => 'required',

            'kelengkapan_berkas' => 'required',

            'subkriteria_pupuk' => 'required|array',

            'subsektor_pertanian' => 'required',

        ], [

            'nik.required' =>
                'NIK wajib diisi.',

            'nik.unique' =>
                'Gagal! Petani dengan NIK ini sudah terdaftar.',

            'nik.digits' =>
                'NIK harus berupa 16 digit angka.',

            'poktan_id.required' =>
                'Poktan wajib dipilih.',

            'poktan_id.exists' =>
                'Poktan tidak valid.',

            'subkriteria_pupuk.required' =>
                'Kebutuhan pupuk wajib diisi.',
        ]);


        /*
        |--------------------------------------------------------------------------
        | CEK HAK AKSES POKTAN
        |--------------------------------------------------------------------------
        |
        | Bagian ini sangat penting.
        |
        | Penyuluh tidak dapat memanipulasi ID Poktan
        | milik penyuluh lain melalui URL/request.
        |
        */

        if (!$this->isPoktanAllowed(
            $request->poktan_id
        )) {

            abort(
                403,
                'Anda tidak memiliki akses ke Poktan tersebut.'
            );
        }


        /*
        |--------------------------------------------------------------------------
        | GABUNGKAN DATA KEBUTUHAN PUPUK
        |--------------------------------------------------------------------------
        */

        $namaPupukGabungan = Subkriteria::whereIn(
            'id',
            $request->subkriteria_pupuk
        )
            ->pluck('nama_subkriteria')
            ->implode(', ');


        /*
        |--------------------------------------------------------------------------
        | SIMPAN DATA
        |--------------------------------------------------------------------------
        */

        Petani::create([

            'kode_alternatif' =>
                $request->kode_alternatif,

            'nik' =>
                $request->nik,

            'nama_petani' =>
                $request->nama_petani,

            'poktan_id' =>
                $request->poktan_id,

            'luas_lahan' =>
                $request->luas_lahan,

            'jenis_komoditas' =>
                $request->jenis_komoditas,

            'kelengkapan_berkas' =>
                $request->kelengkapan_berkas,

            'kebutuhan_pupuk' =>
                $namaPupukGabungan,

            'subsektor_pertanian' =>
                $request->subsektor_pertanian,
        ]);


        return redirect()
            ->route('petani.index')
            ->with(
                'success',
                'Data petani berhasil ditambahkan.'
            );
    }


    /**
     * ============================================================
     * FORM EDIT PETANI
     * ============================================================
     */
    public function edit(string $id)
    {
        $petani = Petani::findOrFail($id);


        /*
        |--------------------------------------------------------------------------
        | CEK AKSES
        |--------------------------------------------------------------------------
        */

        if (!$this->isPoktanAllowed(
            $petani->poktan_id
        )) {

            abort(
                403,
                'Anda tidak memiliki akses ke data petani tersebut.'
            );
        }


        /*
        |--------------------------------------------------------------------------
        | DATA SUBKRITERIA C4
        |--------------------------------------------------------------------------
        */

        $subkriteria_c4 = Subkriteria::whereHas(
            'kriteria',
            function ($query) {
                $query->where(
                    'kode_kriteria',
                    'C4'
                );
            }
        )->get();


        /*
        |--------------------------------------------------------------------------
        | DATA POKTAN
        |--------------------------------------------------------------------------
        */

        $poktan = $this->getPoktanUser();


        return view(
            'petani.edit',
            compact(
                'petani',
                'subkriteria_c4',
                'poktan'
            )
        );
    }


    /**
     * ============================================================
     * UPDATE DATA PETANI
     * ============================================================
     */
    public function update(
        Request $request,
        string $id
    ) {
        $petani = Petani::findOrFail($id);


        /*
        |--------------------------------------------------------------------------
        | CEK AKSES PETANI LAMA
        |--------------------------------------------------------------------------
        */

        if (!$this->isPoktanAllowed(
            $petani->poktan_id
        )) {

            abort(
                403,
                'Anda tidak memiliki akses ke data petani tersebut.'
            );
        }


        /*
        |--------------------------------------------------------------------------
        | VALIDASI
        |--------------------------------------------------------------------------
        */

        $request->validate([

            'kode_alternatif' =>
                'required',

            'nik' => [
                'required',
                'digits:16',
                Rule::unique(
                    Petani::class,
                    'nik'
                )->ignore($id),
            ],

            'nama_petani' =>
                'required',

            'poktan_id' => [
                'required',
                'exists:poktan,id',
            ],

            'luas_lahan' =>
                'required',

            'jenis_komoditas' =>
                'required',

            'kelengkapan_berkas' =>
                'required',

            'subkriteria_pupuk' =>
                'required|array',

            'subsektor_pertanian' =>
                'required',

        ], [

            'nik.unique' =>
                'Gagal! NIK ini sudah digunakan oleh data petani lain.',

            'nik.digits' =>
                'NIK harus berupa 16 digit angka.',

            'poktan_id.required' =>
                'Poktan wajib dipilih.',

            'poktan_id.exists' =>
                'Poktan tidak valid.',

            'subkriteria_pupuk.required' =>
                'Kebutuhan pupuk wajib diisi.',
        ]);


        /*
        |--------------------------------------------------------------------------
        | CEK POKTAN TUJUAN
        |--------------------------------------------------------------------------
        |
        | Penyuluh tidak boleh memindahkan petani
        | ke Poktan milik penyuluh lain.
        |
        */

        if (!$this->isPoktanAllowed(
            $request->poktan_id
        )) {

            abort(
                403,
                'Anda tidak memiliki akses ke Poktan tersebut.'
            );
        }


        /*
        |--------------------------------------------------------------------------
        | GABUNGKAN KEBUTUHAN PUPUK
        |--------------------------------------------------------------------------
        */

        $namaPupukGabungan = Subkriteria::whereIn(
            'id',
            $request->subkriteria_pupuk
        )
            ->pluck('nama_subkriteria')
            ->implode(', ');


        /*
        |--------------------------------------------------------------------------
        | UPDATE DATA
        |--------------------------------------------------------------------------
        */

        $petani->update([

            'kode_alternatif' =>
                $request->kode_alternatif,

            'nik' =>
                $request->nik,

            'nama_petani' =>
                $request->nama_petani,

            'poktan_id' =>
                $request->poktan_id,

            'luas_lahan' =>
                $request->luas_lahan,

            'jenis_komoditas' =>
                $request->jenis_komoditas,

            'kelengkapan_berkas' =>
                $request->kelengkapan_berkas,

            'kebutuhan_pupuk' =>
                $namaPupukGabungan,

            'subsektor_pertanian' =>
                $request->subsektor_pertanian,
        ]);


        return redirect()
            ->route('petani.index')
            ->with(
                'success',
                'Data petani berhasil diperbarui.'
            );
    }


    /**
     * ============================================================
     * HAPUS DATA PETANI
     * ============================================================
     *
     * HANYA ADMIN.
     */
    public function destroy(string $id)
    {
        $user = Auth::user();


        if ($user->role !== 'admin') {

            abort(
                403,
                'Anda tidak memiliki hak untuk menghapus data petani.'
            );
        }


        $petani = Petani::findOrFail($id);

        $petani->delete();


        return redirect()
            ->route('petani.index')
            ->with(
                'success',
                'Data petani berhasil dihapus.'
            );
    }


    /**
     * ============================================================
     * IMPORT EXCEL
     * ============================================================
     *
     * HANYA ADMIN.
     */
    public function importExcel(Request $request)
    {
        $user = Auth::user();


        if ($user->role !== 'admin') {

            abort(
                403,
                'Import data hanya dapat dilakukan oleh Admin.'
            );
        }


        /*
        |--------------------------------------------------------------------------
        | VALIDASI FILE
        |--------------------------------------------------------------------------
        */

        $request->validate([

            'file_excel' =>
                'required|mimes:xlsx,xls',

        ], [

            'file_excel.required' =>
                'Silakan pilih file Excel terlebih dahulu.',

            'file_excel.mimes' =>
                'Format file harus berupa .xlsx atau .xls.',
        ]);


        $file = $request->file(
            'file_excel'
        );


        /*
        |--------------------------------------------------------------------------
        | BACA EXCEL
        |--------------------------------------------------------------------------
        */

        $spreadsheet = IOFactory::load(
            $file->getRealPath()
        );

        $sheet =
            $spreadsheet->getActiveSheet();

        $rows =
            $sheet->toArray();


        $dataDilewati = 0;
        $dataDisimpan = 0;


        /*
        |--------------------------------------------------------------------------
        | PROSES SETIAP BARIS
        |--------------------------------------------------------------------------
        */

        foreach (
            $rows as $index => $row
        ) {


            /*
            |--------------------------------------------------------------------------
            | LEWATI HEADER
            |--------------------------------------------------------------------------
            */

            if (
                $index === 0 ||
                empty($row[1])
            ) {

                continue;
            }


            /*
            |--------------------------------------------------------------------------
            | DATA EXCEL
            |--------------------------------------------------------------------------
            |
            | 0 = Kode Alternatif
            | 1 = NIK
            | 2 = Nama Petani
            | 3 = Luas Lahan
            | 4 = Jenis Komoditas
            | 5 = Kelengkapan Berkas
            | 6 = Kebutuhan Pupuk
            | 7 = Subsektor Pertanian
            | 8 = ID Poktan
            |
            */


            $kode_alternatif =
                trim($row[0] ?? '');

            $nik =
                trim($row[1] ?? '');

            $nama_petani =
                trim($row[2] ?? '');


            /*
            |--------------------------------------------------------------------------
            | CEK NIK DUPLIKAT
            |--------------------------------------------------------------------------
            */

            $cekAda = Petani::where(
                'nik',
                $nik
            )->first();


            if ($cekAda) {

                $dataDilewati++;

                continue;
            }


            /*
            |--------------------------------------------------------------------------
            | AMBIL ID POKTAN
            |--------------------------------------------------------------------------
            */

            $poktanId =
                !empty($row[8])
                    ? trim($row[8])
                    : null;


            if (!$poktanId) {

                $dataDilewati++;

                continue;
            }


            /*
            |--------------------------------------------------------------------------
            | CEK POKTAN
            |--------------------------------------------------------------------------
            */

            if (
                !Poktan::where(
                    'id',
                    $poktanId
                )->exists()
            ) {

                $dataDilewati++;

                continue;
            }


            /*
            |--------------------------------------------------------------------------
            | SIMPAN DATA
            |--------------------------------------------------------------------------
            */

            Petani::create([

                'kode_alternatif' =>
                    $kode_alternatif,

                'nik' =>
                    $nik,

                'nama_petani' =>
                    $nama_petani,

                'poktan_id' =>
                    $poktanId,

                'luas_lahan' =>
                    trim($row[3] ?? ''),

                'jenis_komoditas' =>
                    trim($row[4] ?? ''),

                'kelengkapan_berkas' =>
                    trim($row[5] ?? ''),

                'kebutuhan_pupuk' =>
                    trim($row[6] ?? ''),

                'subsektor_pertanian' =>
                    trim($row[7] ?? ''),
            ]);


            $dataDisimpan++;
        }


        /*
        |--------------------------------------------------------------------------
        | KEMBALI KE DATA PETANI
        |--------------------------------------------------------------------------
        */

        return redirect()
            ->route('petani.index')
            ->with(
                'success',
                "Proses selesai! {$dataDisimpan} data petani berhasil di-import, {$dataDilewati} data dilewati."
            );
    }
}