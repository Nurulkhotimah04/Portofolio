<?php

namespace App\Console\Commands;

use App\Models\AlternatifMusim;
use App\Models\AlternatifMusimPupuk;
use App\Models\JenisPupuk;
use App\Models\MusimTanam;
use App\Models\Penyuluh;
use App\Models\Periode;
use App\Models\Petani;
use App\Models\Poktan;
use App\Services\PenilaianKriteriaService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use RuntimeException;
use Throwable;

class Tambah30AlternatifSetiapPenyuluh extends Command
{
    protected $signature = 'data:tambah-30-alternatif-penyuluh';

    protected $description = 'Menambahkan 30 alternatif bernama natural untuk setiap Penyuluh aktif pada MT1 periode 2026 tanpa mengubah data lama.';

    public function handle(PenilaianKriteriaService $penilaian): int
    {
        $this->info('Menambahkan 30 alternatif untuk setiap Penyuluh aktif pada MT1 / 2026...');

        try {
            DB::transaction(function () use ($penilaian) {
                $musim = MusimTanam::whereRaw('UPPER(kode_musim) = ?', ['MT1'])->first();
                if (!$musim) {
                    throw new RuntimeException('Musim Tanam MT1 belum tersedia.');
                }

                $periode = Periode::where('tahun', 2026)->first();
                if (!$periode) {
                    throw new RuntimeException('Periode 2026 belum tersedia.');
                }

                $c2 = $penilaian->options('C2');
                $c3 = $penilaian->options('C3');
                $c5 = $penilaian->options('C5');

                if (count($c2) < 1 || count($c3) < 1 || count($c5) < 1) {
                    throw new RuntimeException('Master Subkriteria C2/C3/C5 belum lengkap.');
                }

                $jenisPupuk = [
                    'Urea' => JenisPupuk::where('nama_pupuk', 'Urea')->first(),
                    'ZA' => JenisPupuk::where('nama_pupuk', 'ZA')->first(),
                    'NPK' => JenisPupuk::where('nama_pupuk', 'NPK')->first(),
                ];

                foreach ($jenisPupuk as $nama => $row) {
                    if (!$row) {
                        throw new RuntimeException("Jenis pupuk {$nama} belum tersedia.");
                    }
                }

                $penyuluhs = Penyuluh::where(function ($query) {
                        $query->where('status', 'aktif')->orWhereNull('status');
                    })
                    ->orderBy('id')
                    ->get();

                if ($penyuluhs->isEmpty()) {
                    throw new RuntimeException('Belum ada Penyuluh aktif.');
                }

                $nextNumber = $this->nextAlternativeNumber($musim->id, $periode->id);
                $totalBaru = 0;

                foreach ($penyuluhs as $penyuluh) {
                    // Khusus Penyuluh Ikhsan: 30 alternatif tambahan diarahkan ke MT1 / 2025.
                    $periodeAktif = $periode;
                    if (strtolower(trim((string) $penyuluh->nama_penyuluh)) === 'ikhsan') {
                        $periodeKhusus = Periode::where('tahun', 2025)->first();
                        if (!$periodeKhusus) {
                            throw new RuntimeException('Periode 2025 belum tersedia untuk Penyuluh Ikhsan.');
                        }
                        $periodeAktif = $periodeKhusus;
                    }

                    $poktans = Poktan::where('penyuluh_id', $penyuluh->id)
                        ->whereNotNull('kecamatan_id')
                        ->where(function ($query) {
                            $query->where('status', 'aktif')->orWhereNull('status');
                        })
                        ->orderBy('id')
                        ->get();

                    if ($poktans->isEmpty()) {
                        $poktans = Poktan::where('penyuluh_id', $penyuluh->id)
                            ->whereNotNull('kecamatan_id')
                            ->orderBy('id')
                            ->get();
                    }

                    if ($poktans->isEmpty()) {
                        $kecamatanId = DB::table('penugasan_penyuluh')
                            ->where('penyuluh_id', $penyuluh->id)
                            ->whereNotNull('kecamatan_id')
                            ->orderByDesc('id')
                            ->value('kecamatan_id');

                        if ($kecamatanId) {
                            $poktans->push(Poktan::create([
                                'nama_poktan' => 'Poktan ' . $penyuluh->nama_penyuluh,
                                'penyuluh_id' => $penyuluh->id,
                                'kecamatan_id' => $kecamatanId,
                                'status' => 'aktif',
                            ]));
                            $this->line("  Poktan otomatis dibuat untuk {$penyuluh->nama_penyuluh}.");
                        } else {
                            throw new RuntimeException(
                                "Penyuluh {$penyuluh->nama_penyuluh} belum memiliki Poktan dan tidak memiliki kecamatan penugasan."
                            );
                        }
                    }

                    $dibuatUntukPenyuluh = 0;
                    $sudahAdaUntukPenyuluh = 0;

                    for ($i = 1; $i <= 30; $i++) {
                        $nik = $this->syntheticNik((int) $penyuluh->id, $i);
                        $nama = $this->naturalName((int) $penyuluh->id, $i);
                        $poktan = $poktans[($i - 1) % $poktans->count()];

                        $petani = Petani::where('nik', $nik)->first();

                        if ($petani) {
                            $existing = AlternatifMusim::where('petani_id', $petani->id)
                                ->where('musim_tanam_id', $musim->id)
                                ->where('periode_id', $periodeAktif->id)
                                ->first();

                            if ($existing) {
                                $sudahAdaUntukPenyuluh++;
                                continue;
                            }
                        }

                        $luasHa = $this->luasLahan($i, (int) $penyuluh->id);
                        $luasLabel = $penilaian->resolveLuasLahan($luasHa);
                        $c2Value = $c2[(($i - 1) + (int) $penyuluh->id) % count($c2)];
                        $c3Value = $c3[(($i - 1) * 2 + (int) $penyuluh->id) % count($c3)];
                        $c5Value = $c5[(($i - 1) * 3 + (int) $penyuluh->id) % count($c5)];
                        $pupukRows = $this->pupukRows($i, (int) $penyuluh->id);
                        $c4Value = $penilaian->resolveKebutuhanPupuk(count($pupukRows));

                        if (!$petani) {
                            $petani = Petani::create([
                                'nik' => $nik,
                                'nama_petani' => $nama,
                                'poktan_id' => $poktan->id,
                                'luas_lahan' => $luasLabel,
                                'jenis_komoditas' => $c2Value,
                                'kelengkapan_berkas' => $c3Value,
                                'kebutuhan_pupuk' => $c4Value,
                                'subsektor_pertanian' => $c5Value,
                            ]);
                        }

                        $kode = 'A' . $nextNumber++;

                        $alternatif = AlternatifMusim::create([
                            'petani_id' => $petani->id,
                            'poktan_id' => $poktan->id,
                            'musim_tanam_id' => $musim->id,
                            'periode_id' => $periodeAktif->id,
                            'pengajuan_petani_id' => null,
                            'kode_alternatif' => $kode,
                            'luas_lahan_ha' => $luasHa,
                            'luas_lahan' => $luasLabel,
                            'jenis_komoditas' => $c2Value,
                            'kelengkapan_berkas' => $c3Value,
                            'berkas_persyaratan_path' => null,
                            'kebutuhan_pupuk' => $c4Value,
                            'subsektor_pertanian' => $c5Value,
                        ]);

                        foreach ($pupukRows as $namaPupuk => $kg) {
                            AlternatifMusimPupuk::create([
                                'alternatif_musim_id' => $alternatif->id,
                                'jenis_pupuk_id' => $jenisPupuk[$namaPupuk]->id,
                                'jumlah_kg' => $kg,
                            ]);
                        }

                        $dibuatUntukPenyuluh++;
                        $totalBaru++;
                    }

                    $this->line(
                        "- {$penyuluh->nama_penyuluh}: {$dibuatUntukPenyuluh} alternatif baru (" . $periodeAktif->tahun . ")" .
                        ($sudahAdaUntukPenyuluh > 0 ? ", {$sudahAdaUntukPenyuluh} sudah tersedia" : '')
                    );
                }

                $this->newLine();
                $this->info("Selesai. Total alternatif baru: {$totalBaru}.");
                $this->line('Data lama tidak diubah. Jika VIKOR pernah dihitung untuk MT1/2026, lakukan hitung ulang secara manual.');
            });
        } catch (Throwable $e) {
            $this->error('Gagal: ' . $e->getMessage());
            return self::FAILURE;
        }

        return self::SUCCESS;
    }

    private function nextAlternativeNumber(int $musimId, int $periodeId): int
    {
        $max = 0;
        $codes = AlternatifMusim::where('musim_tanam_id', $musimId)
            ->where('periode_id', $periodeId)
            ->lockForUpdate()
            ->pluck('kode_alternatif');

        foreach ($codes as $code) {
            if (preg_match('/^A(\d+)$/i', trim((string) $code), $match)) {
                $max = max($max, (int) $match[1]);
            }
        }

        return $max + 1;
    }

    private function syntheticNik(int $penyuluhId, int $index): string
    {
        return '999001'
            . str_pad((string) ($penyuluhId % 100000), 5, '0', STR_PAD_LEFT)
            . str_pad((string) $index, 5, '0', STR_PAD_LEFT);
    }

    private function naturalName(int $penyuluhId, int $index): string
    {
        $femaleFirst = [
            'Putri', 'Siti', 'Aisyah', 'Dewi', 'Rina', 'Fitri', 'Wulan', 'Nia', 'Intan', 'Ayu',
            'Nurul', 'Ratna', 'Sri', 'Maya', 'Nanda', 'Rani', 'Diah', 'Lina', 'Yuni', 'Anisa',
            'Desi', 'Rika', 'Indah', 'Tika', 'Novi',
        ];

        $femaleSecond = [
            'Lestari', 'Handayani', 'Wahyuni', 'Marlina', 'Puspitasari', 'Rahmawati', 'Kurniasih', 'Permatasari', 'Anggraini', 'Safitri',
            'Amalia', 'Oktaviani', 'Setyaningsih', 'Maulida', 'Kartika', 'Aprilia', 'Ningrum', 'Susanti', 'Rahayu', 'Melati',
            'Febriani', 'Damayanti', 'Utami', 'Rosdiana', 'Nuraini',
        ];

        $maleFirst = [
            'Ahmad', 'Budi', 'Dedi', 'Asep', 'Hendra', 'Rudi', 'Agus', 'Andi', 'Eko', 'Fajar',
            'Dimas', 'Rizki', 'Aditya', 'Irfan', 'Rahmat', 'Taufik', 'Yudi', 'Cecep', 'Jajang', 'Ujang',
            'Dani', 'Rian', 'Arif', 'Imam', 'Farhan',
        ];

        $maleSecond = [
            'Hidayat', 'Santoso', 'Maulana', 'Setiawan', 'Gunawan', 'Kurniawan', 'Pratama', 'Saputra', 'Ramadhan', 'Firmansyah',
            'Suhendar', 'Hermawan', 'Suryana', 'Nugraha', 'Permana', 'Fauzan', 'Hakim', 'Rohman', 'Supriatna', 'Hartono',
            'Irawan', 'Wijaya', 'Akbar', 'Nurdin', 'Sulaiman',
        ];

        $global = (($penyuluhId - 1) * 30) + ($index - 1);
        $female = $global % 2 === 0;

        if ($female) {
            $pair = intdiv($global, 2);
            $first = $femaleFirst[$pair % count($femaleFirst)];
            $second = $femaleSecond[intdiv($pair, count($femaleFirst)) % count($femaleSecond)];
            return $first . ' ' . $second;
        }

        $pair = intdiv($global, 2);
        $first = $maleFirst[$pair % count($maleFirst)];
        $second = $maleSecond[intdiv($pair, count($maleFirst)) % count($maleSecond)];
        return $first . ' ' . $second;
    }

    private function luasLahan(int $index, int $penyuluhId): float
    {
        $values = [0.35, 0.45, 0.50, 0.65, 0.75, 0.90, 1.00, 1.15, 1.30, 1.45, 1.60, 1.75, 1.90, 2.00];
        return $values[(($index - 1) + $penyuluhId) % count($values)];
    }

    private function pupukRows(int $index, int $penyuluhId): array
    {
        $selector = (($index - 1) + $penyuluhId) % 6;

        return match ($selector) {
            0 => ['Urea' => 100],
            1 => ['NPK' => 125],
            2 => ['Urea' => 100, 'ZA' => 50],
            3 => ['Urea' => 125, 'NPK' => 100],
            4 => ['ZA' => 75, 'NPK' => 125],
            default => ['Urea' => 150, 'ZA' => 50, 'NPK' => 100],
        };
    }
}
