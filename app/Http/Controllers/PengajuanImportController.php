<?php

namespace App\Http\Controllers;

use App\Models\AlternatifMusim;
use App\Models\BatchImportPengajuan;
use App\Models\JenisPupuk;
use App\Models\MusimTanam;
use App\Models\PengajuanPetani;
use App\Models\Periode;
use App\Models\Penyuluh;
use App\Models\Poktan;
use App\Services\KebutuhanPupukService;
use App\Services\PenilaianKriteriaService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Cell\DataValidation;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use ZipArchive;

class PengajuanImportController extends Controller
{
    public function __construct(
        private readonly PenilaianKriteriaService $penilaian,
        private readonly KebutuhanPupukService $pupukService,
    ) {
    }

    private function currentPenyuluh(): Penyuluh
    {
        $penyuluh = Penyuluh::with('penugasanAktif.kecamatan')
            ->where('user_id', Auth::id())
            ->first();

        if (!$penyuluh) {
            abort(403, 'Akun ini belum terhubung dengan data Penyuluh.');
        }

        return $penyuluh;
    }

    private function canonical(?string $value, string $kode): ?string
    {
        try {
            return $this->penilaian->canonical($kode, $value);
        } catch (ValidationException) {
            return null;
        }
    }

    private function normalizeHeader(?string $value): string
    {
        $value = mb_strtolower(trim((string) $value));
        $value = str_replace(['–', '—', '−'], '-', $value);
        $value = preg_replace('/[^\pL\pN]+/u', ' ', $value);
        return trim(preg_replace('/\s+/u', ' ', $value));
    }

    private function normalizeName(?string $value): string
    {
        $value = mb_strtolower(trim((string) $value));
        $value = preg_replace('/[^\pL\pN]+/u', ' ', $value);
        return trim(preg_replace('/\s+/u', ' ', $value));
    }

    private function headerAliases(): array
    {
        return [
            'nik' => ['nik', 'no nik', 'nomor nik'],
            'nama_petani' => ['nama petani', 'nama'],
            'poktan' => ['poktan', 'nama poktan', 'kelompok tani', 'nama kelompok tani'],
            'luas_lahan_ha' => ['luas lahan ha', 'luas lahan', 'luas ha'],
            'jenis_komoditas' => ['jenis komoditas', 'komoditas'],
            'kelengkapan_berkas' => ['kelengkapan berkas', 'status berkas', 'berkas'],
            'subsektor_pertanian' => ['subsektor pertanian', 'subsektor'],
            'penyuluh' => ['penyuluh', 'nama penyuluh'],
        ];
    }

    private function detectHeader($sheet, $jenisPupuk): array
    {
        $highestColumn = Coordinate::columnIndexFromString($sheet->getHighestDataColumn());
        $lastRow = min(15, max(1, $sheet->getHighestDataRow()));
        $aliases = $this->headerAliases();
        $best = ['row' => null, 'map' => [], 'score' => -1];

        for ($row = 1; $row <= $lastRow; $row++) {
            $cells = [];
            for ($col = 1; $col <= $highestColumn; $col++) {
                $value = $this->normalizeHeader((string) $sheet->getCell(Coordinate::stringFromColumnIndex($col) . $row)->getFormattedValue());
                if ($value !== '') {
                    $cells[$col] = $value;
                }
            }

            $map = [];
            foreach ($aliases as $key => $options) {
                foreach ($cells as $col => $value) {
                    if (in_array($value, $options, true)) {
                        $map[$key] = $col;
                        break;
                    }
                }
            }

            foreach ($jenisPupuk as $pupuk) {
                $name = $this->normalizeHeader($pupuk->nama_pupuk);
                $options = [$name, $name . ' kg', 'pupuk ' . $name, 'pupuk ' . $name . ' kg', 'kebutuhan ' . $name, 'kebutuhan ' . $name . ' kg'];
                foreach ($cells as $col => $value) {
                    if (in_array($value, $options, true)) {
                        $map['pupuk_' . $pupuk->id] = $col;
                        break;
                    }
                }
            }

            $score = count($map);
            if ($score > $best['score']) {
                $best = ['row' => $row, 'map' => $map, 'score' => $score];
            }
        }

        $required = [
            'nik' => 'NIK',
            'nama_petani' => 'Nama Petani',
            'poktan' => 'Poktan',
            'luas_lahan_ha' => 'Luas Lahan (Ha)',
            'jenis_komoditas' => 'Jenis Komoditas',
            'kelengkapan_berkas' => 'Kelengkapan Berkas',
            'subsektor_pertanian' => 'Subsektor Pertanian',
        ];

        foreach ($jenisPupuk as $pupuk) {
            $required['pupuk_' . $pupuk->id] = $pupuk->nama_pupuk . ' (kg)';
        }

        $missing = [];
        foreach ($required as $key => $label) {
            if (!isset($best['map'][$key])) {
                $missing[] = $label;
            }
        }

        return [$best['row'], $best['map'], $missing];
    }

    private function cell($sheet, array $map, string $key, int $row): string
    {
        if (!isset($map[$key])) {
            return '';
        }

        return trim((string) $sheet->getCell(Coordinate::stringFromColumnIndex($map[$key]) . $row)->getFormattedValue());
    }

    private function parsePupukValue(?string $raw): ?float
    {
        $value = mb_strtolower(trim((string) $raw));

        if ($value === '' || in_array($value, ['-', '–', '—', '0', '0 kg', '0kg'], true)) {
            return 0.0;
        }

        $value = str_replace(['kg', ' '], '', $value);

        if (preg_match('/^\d{1,3}(\.\d{3})+(,\d+)?$/', $value)) {
            $value = str_replace('.', '', $value);
            $value = str_replace(',', '.', $value);
        } else {
            $value = str_replace(',', '.', $value);
        }

        if (!is_numeric($value)) {
            return null;
        }

        $number = (float) $value;
        return $number >= 0 ? round($number, 2) : null;
    }

    private function readPdfZip(Request $request, string $tempDirectory): array
    {
        $zip = new ZipArchive();
        if ($zip->open($request->file('file_zip')->getRealPath()) !== true) {
            throw ValidationException::withMessages(['file_zip' => 'File ZIP tidak dapat dibuka.']);
        }

        if ($zip->numFiles > 1000) {
            $zip->close();
            throw ValidationException::withMessages(['file_zip' => 'Isi ZIP terlalu banyak. Maksimal 1000 file.']);
        }

        $pdfMap = [];
        $pdfErrors = [];
        $warnings = [];
        $totalSize = 0;
        $maxPdf = 10 * 1024 * 1024;
        $maxTotal = 100 * 1024 * 1024;

        for ($i = 0; $i < $zip->numFiles; $i++) {
            $entry = $zip->getNameIndex($i);
            $basename = basename($entry);

            if (!$basename || str_ends_with($entry, '/')) {
                continue;
            }

            if (strtolower(pathinfo($basename, PATHINFO_EXTENSION)) !== 'pdf') {
                continue;
            }

            $nik = pathinfo($basename, PATHINFO_FILENAME);
            if (!preg_match('/^\d{16}$/', $nik)) {
                $warnings[] = "File {$basename} diabaikan karena nama file bukan NIK 16 digit.";
                continue;
            }

            if (isset($pdfMap[$nik]) || isset($pdfErrors[$nik])) {
                $pdfErrors[$nik] = 'PDF dengan NIK yang sama terdapat lebih dari satu kali.';
                continue;
            }

            $stat = $zip->statIndex($i);
            $size = (int) ($stat['size'] ?? 0);
            $totalSize += $size;

            if ($totalSize > $maxTotal) {
                $zip->close();
                throw ValidationException::withMessages(['file_zip' => 'Total ukuran PDF setelah diekstrak melebihi 100 MB.']);
            }

            if ($size <= 0 || $size > $maxPdf) {
                $pdfErrors[$nik] = 'PDF kosong atau melebihi 10 MB.';
                continue;
            }

            $stream = $zip->getStream($entry);
            if (!$stream) {
                $pdfErrors[$nik] = 'PDF tidak dapat dibaca dari ZIP.';
                continue;
            }

            $content = stream_get_contents($stream);
            fclose($stream);

            if (!is_string($content) || !str_starts_with($content, '%PDF-')) {
                $pdfErrors[$nik] = 'File bukan PDF yang valid.';
                continue;
            }

            $path = $tempDirectory . '/' . $nik . '.pdf';
            Storage::disk('local')->put($path, $content);
            $pdfMap[$nik] = $path;
        }

        $zip->close();

        return [$pdfMap, $pdfErrors, $warnings];
    }

    public function form()
    {
        $penyuluh = $this->currentPenyuluh();
        $musimTanam = MusimTanam::orderBy('kode_musim')->get();
        $periode = Periode::orderByDesc('tahun')->get();
        $wilayahPenyuluh = $penyuluh->penugasanAktif?->kecamatan;

        return view('pengajuan.import', compact('musimTanam', 'periode', 'wilayahPenyuluh'));
    }

    public function template()
    {
        $jenisPupuk = JenisPupuk::where('is_active', true)->orderBy('nama_pupuk')->get();
        $c2 = $this->penilaian->options('C2');
        $c3 = $this->penilaian->options('C3');
        $c5 = $this->penilaian->options('C5');

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('PENGAJUAN');

        $headers = ['NIK', 'Nama Petani', 'Poktan', 'Luas Lahan (Ha)', 'Jenis Komoditas', 'Kelengkapan Berkas'];
        foreach ($jenisPupuk as $pupuk) {
            $headers[] = $pupuk->nama_pupuk . ' (kg)';
        }
        $headers[] = 'Subsektor Pertanian';

        $lastColumn = Coordinate::stringFromColumnIndex(count($headers));
        $sheet->mergeCells("A1:{$lastColumn}1");
        $sheet->setCellValue('A1', 'PERHATIAN: Baris CONTOH hanya panduan. Hapus baris contoh sebelum mengisi/upload.');
        $sheet->getStyle("A1:{$lastColumn}1")->getFont()->setBold(true);

        foreach ($headers as $i => $header) {
            $column = Coordinate::stringFromColumnIndex($i + 1);
            $sheet->setCellValue($column . '2', $header);
            $sheet->getColumnDimension($column)->setWidth(max(18, min(32, strlen($header) + 4)));
        }
        $sheet->getStyle("A2:{$lastColumn}2")->getFont()->setBold(true);

        $example = ['CONTOH-1', 'Arifin Maulana', 'MADU TANI II', 0.45, $c2[0] ?? 'Padi', $c3[0] ?? 'Lengkap'];
        foreach ($jenisPupuk as $index => $pupuk) {
            $example[] = $index === 0 ? 100 : ($index === 1 ? 50 : 0);
        }
        $example[] = $c5[0] ?? 'Tanaman Pangan';

        foreach ($example as $i => $value) {
            $column = Coordinate::stringFromColumnIndex($i + 1);
            $sheet->setCellValue($column . '3', $value);
        }

        $sheet->getStyle("A3:{$lastColumn}3")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFF3CD');
        $sheet->getStyle("A3:{$lastColumn}3")->getFont()->setItalic(true);
        $sheet->freezePane('A4');
        $sheet->setAutoFilter("A2:{$lastColumn}2");
        $sheet->getStyle('A4:A503')->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_TEXT);

        $listValidation = function (string $column, array $options) use ($sheet) {
            if (!$options) {
                return;
            }

            $formula = '"' . implode(',', array_map(fn ($v) => str_replace('"', '', $v), $options)) . '"';
            for ($row = 4; $row <= 503; $row++) {
                $validation = new DataValidation();
                $validation->setType(DataValidation::TYPE_LIST);
                $validation->setAllowBlank(false);
                $validation->setShowErrorMessage(true);
                $validation->setErrorTitle('Nilai tidak valid');
                $validation->setError('Silakan pilih nilai dari daftar.');
                $validation->setFormula1($formula);
                $sheet->getCell($column . $row)->setDataValidation($validation);
            }
        };

        $listValidation('E', $c2);
        $listValidation('F', $c3);
        $listValidation(Coordinate::stringFromColumnIndex(count($headers)), $c5);

        return response()->streamDownload(function () use ($spreadsheet) {
            (new Xlsx($spreadsheet))->save('php://output');
        }, 'template_pengajuan_petani.xlsx', [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }

    public function preview(Request $request)
    {
        $validated = $request->validate([
            'musim_tanam_id' => ['required', 'exists:musim_tanam,id'],
            'periode_id' => ['required', 'exists:periode,id'],
            'file_excel' => ['required', 'file', 'mimes:xlsx,xls', 'max:5120'],
            'file_zip' => ['required', 'file', 'mimes:zip', 'max:51200'],
        ], [
            'file_excel.max' => 'Ukuran Excel maksimal 5 MB.',
            'file_zip.required' => 'ZIP berisi PDF persyaratan wajib diunggah.',
            'file_zip.mimes' => 'Berkas persyaratan massal harus berupa ZIP.',
            'file_zip.max' => 'Ukuran ZIP maksimal 50 MB.',
        ]);

        $penyuluh = $this->currentPenyuluh();
        $jenisPupuk = JenisPupuk::where('is_active', true)->orderBy('nama_pupuk')->get();

        if ($jenisPupuk->isEmpty()) {
            throw ValidationException::withMessages(['file_excel' => 'Master Jenis Pupuk belum tersedia. Hubungi Admin.']);
        }

        try {
            $reader = IOFactory::createReaderForFile($request->file('file_excel')->getRealPath());
            $reader->setReadDataOnly(true);
            $spreadsheet = $reader->load($request->file('file_excel')->getRealPath());
        } catch (\Throwable) {
            return back()->withInput()->withErrors(['file_excel' => 'File Excel tidak dapat dibaca.']);
        }

        $sheet = $spreadsheet->getSheet(0);
        [$headerRow, $headerMap, $missingHeaders] = $this->detectHeader($sheet, $jenisPupuk);

        if (!$headerRow || $missingHeaders) {
            return back()->withInput()->withErrors([
                'file_excel' => 'Kolom wajib tidak ditemukan: ' . implode(', ', $missingHeaders) . '.',
            ]);
        }

        $ownedPoktan = Poktan::with('kecamatan')
            ->where('penyuluh_id', $penyuluh->id)
            ->where('status', 'aktif')
            ->get();

        $poktanMap = [];
        foreach ($ownedPoktan as $poktan) {
            $poktanMap[$this->normalizeName($poktan->nama_poktan)][] = $poktan;
        }

        $token = Str::uuid()->toString();
        $tempDirectory = 'import-pengajuan/' . $token;
        Storage::disk('local')->makeDirectory($tempDirectory);

        try {
            [$pdfMap, $pdfErrors, $zipWarnings] = $this->readPdfZip($request, $tempDirectory);
        } catch (ValidationException $e) {
            Storage::disk('local')->deleteDirectory($tempDirectory);
            throw $e;
        }

        $candidates = [];
        $errorRows = [];
        $seenNik = [];
        $dataCount = 0;
        $highestRow = $sheet->getHighestDataRow();
        $penyuluhName = $this->normalizeName($penyuluh->nama_penyuluh);

        for ($row = $headerRow + 1; $row <= $highestRow; $row++) {
            $nikRaw = $this->cell($sheet, $headerMap, 'nik', $row);
            $nama = $this->cell($sheet, $headerMap, 'nama_petani', $row);
            $poktanRaw = $this->cell($sheet, $headerMap, 'poktan', $row);

            if ($nikRaw === '' && $nama === '' && $poktanRaw === '') {
                continue;
            }

            if (str_starts_with(mb_strtoupper($nikRaw), 'CONTOH')) {
                continue;
            }

            $dataCount++;
            if ($dataCount > 500) {
                Storage::disk('local')->deleteDirectory($tempDirectory);
                return back()->withInput()->withErrors(['file_excel' => 'Maksimal 500 petani dalam satu kali import.']);
            }

            $nik = preg_replace('/[^0-9]/', '', $nikRaw);
            $luasRaw = str_replace(',', '.', $this->cell($sheet, $headerMap, 'luas_lahan_ha', $row));
            $c2Raw = $this->cell($sheet, $headerMap, 'jenis_komoditas', $row);
            $c3Raw = $this->cell($sheet, $headerMap, 'kelengkapan_berkas', $row);
            $c5Raw = $this->cell($sheet, $headerMap, 'subsektor_pertanian', $row);
            $penyuluhExcel = $this->cell($sheet, $headerMap, 'penyuluh', $row);
            $errors = [];

            if (!preg_match('/^\d{16}$/', $nik)) {
                $errors[] = 'NIK harus 16 digit.';
            }
            if ($nama === '') {
                $errors[] = 'Nama Petani wajib diisi.';
            }
            if (isset($seenNik[$nik]) && $nik !== '') {
                $errors[] = 'NIK duplikat di file Excel.';
            }
            if ($nik !== '') {
                $seenNik[$nik] = true;
            }
            if (!is_numeric($luasRaw) || (float) $luasRaw <= 0 || (float) $luasRaw > 2) {
                $errors[] = 'Luas lahan harus angka lebih dari 0 sampai 2 Ha.';
            }

            $poktan = null;
            $poktanKey = $this->normalizeName($poktanRaw);
            if ($poktanKey === '') {
                $errors[] = 'Poktan wajib diisi.';
            } elseif (!isset($poktanMap[$poktanKey])) {
                $errors[] = "Poktan {$poktanRaw} bukan binaan Anda atau tidak aktif.";
            } elseif (count($poktanMap[$poktanKey]) > 1) {
                $errors[] = "Nama Poktan {$poktanRaw} tidak unik pada data binaan Anda.";
            } else {
                $poktan = $poktanMap[$poktanKey][0];
            }

            if ($penyuluhExcel !== '' && $this->normalizeName($penyuluhExcel) !== $penyuluhName) {
                $errors[] = "Penyuluh {$penyuluhExcel} bukan akun Penyuluh yang sedang melakukan import.";
            }

            $c2 = $this->canonical($c2Raw, 'C2');
            $c3 = $this->canonical($c3Raw, 'C3');
            $c5 = $this->canonical($c5Raw, 'C5');

            if (!$c2) {
                $errors[] = 'Jenis Komoditas tidak valid.';
            }
            if (!$c3) {
                $errors[] = 'Kelengkapan Berkas tidak valid.';
            }
            if (!$c5) {
                $errors[] = 'Subsektor Pertanian tidak valid.';
            }

            $pupukRows = [];
            foreach ($jenisPupuk as $pupuk) {
                $value = $this->parsePupukValue($this->cell($sheet, $headerMap, 'pupuk_' . $pupuk->id, $row));
                if ($value === null) {
                    $errors[] = "Jumlah {$pupuk->nama_pupuk} harus angka 0 atau lebih. Tanda '-' boleh digunakan untuk nilai 0.";
                    continue;
                }
                if ($value > 0) {
                    $pupukRows[$pupuk->id] = $value;
                }
            }

            if (!$pupukRows) {
                $errors[] = 'Minimal satu jenis pupuk harus lebih dari 0 kg.';
            }

            if ($nik && isset($pdfErrors[$nik])) {
                $errors[] = $pdfErrors[$nik];
            } elseif ($nik && !isset($pdfMap[$nik])) {
                $errors[] = 'PDF persyaratan tidak ditemukan di ZIP. Nama PDF harus menggunakan NIK.';
            }

            if ($errors) {
                $errorRows[] = [
                    'baris' => $row,
                    'nik' => $nik,
                    'nama_petani' => $nama,
                    'penyuluh_excel' => $penyuluhExcel,
                    'poktan' => $poktanRaw,
                    'poktan_id' => $poktan?->id,
                    'errors' => $errors,
                ];
                continue;
            }

            try {
                $c1 = $this->penilaian->resolveLuasLahan((float) $luasRaw);
                $c4 = $this->penilaian->resolveKebutuhanPupuk(count($pupukRows));
            } catch (ValidationException $e) {
                $errorRows[] = [
                    'baris' => $row,
                    'nik' => $nik,
                    'nama_petani' => $nama,
                    'penyuluh_excel' => $penyuluhExcel,
                    'poktan' => $poktanRaw,
                    'poktan_id' => $poktan?->id,
                    'errors' => collect($e->errors())->flatten()->all(),
                ];
                continue;
            }

            $candidates[] = [
                'baris' => $row,
                'nik' => $nik,
                'nama_petani' => $nama,
                'poktan_id' => $poktan->id,
                'nama_poktan' => $poktan->nama_poktan,
                'nama_kecamatan' => $poktan->kecamatan?->nama_kecamatan,
                'penyuluh_excel' => $penyuluhExcel,
                'luas_lahan_ha' => round((float) $luasRaw, 2),
                'luas_lahan' => $c1,
                'jenis_komoditas' => $c2,
                'kelengkapan_berkas' => $c3,
                'kebutuhan_pupuk' => $c4,
                'subsektor_pertanian' => $c5,
                'pupuk_rows' => $pupukRows,
                'temp_pdf_path' => $pdfMap[$nik],
            ];
        }

        $niks = collect($candidates)->pluck('nik')->unique()->values();
        $existingPengajuan = PengajuanPetani::whereIn('nik', $niks)
            ->where('musim_tanam_id', $validated['musim_tanam_id'])
            ->where('periode_id', $validated['periode_id'])
            ->whereIn('status', ['menunggu_verifikasi', 'perlu_perbaikan', 'disetujui'])
            ->pluck('nik')->all();

        $existingAlternatives = AlternatifMusim::with('petani:id,nik')
            ->where('musim_tanam_id', $validated['musim_tanam_id'])
            ->where('periode_id', $validated['periode_id'])
            ->whereHas('petani', fn ($q) => $q->whereIn('nik', $niks))
            ->get()->pluck('petani.nik')->filter()->all();

        $validRows = [];
        foreach ($candidates as $candidate) {
            $errors = [];
            if (in_array($candidate['nik'], $existingPengajuan, true)) {
                $errors[] = 'NIK sudah memiliki pengajuan aktif pada Musim/Tahun tersebut.';
            }
            if (in_array($candidate['nik'], $existingAlternatives, true)) {
                $errors[] = 'NIK sudah menjadi Alternatif pada Musim/Tahun tersebut.';
            }

            if ($errors) {
                $errorRows[] = [
                    'baris' => $candidate['baris'],
                    'nik' => $candidate['nik'],
                    'nama_petani' => $candidate['nama_petani'],
                    'penyuluh_excel' => $candidate['penyuluh_excel'],
                    'poktan' => $candidate['nama_poktan'],
                    'poktan_id' => $candidate['poktan_id'],
                    'errors' => $errors,
                ];
            } else {
                $validRows[] = $candidate;
            }
        }

        session()->put('pengajuan_import.' . $token, [
            'penyuluh_id' => $penyuluh->id,
            'musim_tanam_id' => (int) $validated['musim_tanam_id'],
            'periode_id' => (int) $validated['periode_id'],
            'nama_file' => $request->file('file_excel')->getClientOriginalName(),
            'valid_rows' => $validRows,
            'error_rows' => $errorRows,
            'temp_directory' => $tempDirectory,
            'created_at' => now()->toIso8601String(),
        ]);

        return view('pengajuan.preview_import', [
            'token' => $token,
            'musimTanam' => MusimTanam::findOrFail($validated['musim_tanam_id']),
            'periode' => Periode::findOrFail($validated['periode_id']),
            'validRows' => $validRows,
            'errorRows' => $errorRows,
            'namaFile' => $request->file('file_excel')->getClientOriginalName(),
            'jenisPupuk' => $jenisPupuk,
            'zipWarnings' => $zipWarnings,
            'penyuluh' => $penyuluh,
        ]);
    }

    public function commit(Request $request)
    {
        $request->validate(['token' => ['required', 'string']]);
        $key = 'pengajuan_import.' . $request->token;
        $preview = session()->get($key);

        if (!$preview) {
            return redirect()->route('pengajuan.import.form')->with('error', 'Preview sudah tidak tersedia. Silakan upload ulang.');
        }

        if (!empty($preview['created_at']) && now()->diffInMinutes(\Carbon\Carbon::parse($preview['created_at'])) > 60) {
            if (!empty($preview['temp_directory'])) {
                Storage::disk('local')->deleteDirectory($preview['temp_directory']);
            }
            session()->forget($key);
            return redirect()->route('pengajuan.import.form')->with('error', 'Preview sudah kedaluwarsa. Silakan upload ulang.');
        }

        $penyuluh = $this->currentPenyuluh();
        if ((int) $preview['penyuluh_id'] !== (int) $penyuluh->id) {
            abort(403);
        }

        $validRows = $preview['valid_rows'] ?? [];
        if (!$validRows) {
            return redirect()->route('pengajuan.import.form')->with('error', 'Tidak ada data valid yang dapat diimport.');
        }

        DB::transaction(function () use ($preview, $validRows, $penyuluh) {
            $poktanIds = collect($validRows)->pluck('poktan_id')->unique()->values();
            $ownedPoktan = Poktan::whereIn('id', $poktanIds)
                ->where('penyuluh_id', $penyuluh->id)
                ->where('status', 'aktif')
                ->lockForUpdate()
                ->get()
                ->keyBy('id');

            if ($ownedPoktan->count() !== $poktanIds->count()) {
                throw ValidationException::withMessages(['import' => 'Ada Poktan yang sudah bukan binaan aktif Anda. Preview ulang data.']);
            }

            foreach ($validRows as $row) {
                $exists = PengajuanPetani::where('nik', $row['nik'])
                    ->where('musim_tanam_id', $preview['musim_tanam_id'])
                    ->where('periode_id', $preview['periode_id'])
                    ->whereIn('status', ['menunggu_verifikasi', 'perlu_perbaikan', 'disetujui'])
                    ->exists();

                $altExists = AlternatifMusim::where('musim_tanam_id', $preview['musim_tanam_id'])
                    ->where('periode_id', $preview['periode_id'])
                    ->whereHas('petani', fn ($q) => $q->where('nik', $row['nik']))
                    ->exists();

                if ($exists || $altExists) {
                    throw ValidationException::withMessages(['import' => "NIK {$row['nik']} sudah terdaftar pada Musim/Tahun tersebut. Preview ulang data."]);
                }
            }

            $rowsByPoktan = collect($validRows)->groupBy('poktan_id');
            $batches = [];

            foreach ($rowsByPoktan as $poktanId => $rows) {
                $failedCount = collect($preview['error_rows'] ?? [])->where('poktan_id', (int) $poktanId)->count();
                $batches[$poktanId] = BatchImportPengajuan::create([
                    'kode_batch' => 'IMP-' . now()->format('Ymd-His') . '-' . strtoupper(Str::random(6)),
                    'penyuluh_id' => $penyuluh->id,
                    'poktan_id' => $poktanId,
                    'musim_tanam_id' => $preview['musim_tanam_id'],
                    'periode_id' => $preview['periode_id'],
                    'nama_file' => $preview['nama_file'],
                    'total_data' => $rows->count() + $failedCount,
                    'total_valid' => $rows->count(),
                    'total_gagal' => $failedCount,
                    'status' => 'diproses',
                ]);
            }

            foreach ($validRows as $row) {
                $finalPath = "berkas-pengajuan/{$preview['periode_id']}/{$preview['musim_tanam_id']}/{$row['nik']}_" . now()->format('YmdHis') . '.pdf';

                if (!Storage::disk('local')->exists($row['temp_pdf_path'])) {
                    throw ValidationException::withMessages(['import' => "PDF NIK {$row['nik']} tidak tersedia lagi. Upload ulang ZIP."]);
                }

                Storage::disk('local')->move($row['temp_pdf_path'], $finalPath);

                $pengajuan = PengajuanPetani::create([
                    'penyuluh_id' => $penyuluh->id,
                    'poktan_id' => $row['poktan_id'],
                    'musim_tanam_id' => $preview['musim_tanam_id'],
                    'periode_id' => $preview['periode_id'],
                    'batch_import_id' => $batches[$row['poktan_id']]->id,
                    'asal_input' => 'import',
                    'nik' => $row['nik'],
                    'nama_petani' => $row['nama_petani'],
                    'luas_lahan_ha' => $row['luas_lahan_ha'],
                    'luas_lahan' => $row['luas_lahan'],
                    'jenis_komoditas' => $row['jenis_komoditas'],
                    'kelengkapan_berkas' => $row['kelengkapan_berkas'],
                    'berkas_persyaratan_path' => $finalPath,
                    'kebutuhan_pupuk' => $row['kebutuhan_pupuk'],
                    'subsektor_pertanian' => $row['subsektor_pertanian'],
                    'status' => 'menunggu_verifikasi',
                    'tanggal_pengajuan' => now(),
                ]);

                $this->pupukService->syncPengajuan($pengajuan, $row['pupuk_rows']);
            }

            foreach ($batches as $batch) {
                $batch->update(['status' => 'selesai']);
            }
        });

        if (!empty($preview['temp_directory'])) {
            Storage::disk('local')->deleteDirectory($preview['temp_directory']);
        }

        session()->forget($key);

        $gagal = count($preview['error_rows'] ?? []);
        $message = count($validRows) . ' pengajuan berhasil diimport.';
        if ($gagal > 0) {
            $message .= " {$gagal} data ditolak dan tidak disimpan.";
        }

        return redirect()->route('pengajuan.index')->with('success', $message);
    }
}
