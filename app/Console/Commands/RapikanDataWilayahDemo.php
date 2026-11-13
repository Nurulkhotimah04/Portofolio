<?php

namespace App\Console\Commands;

use App\Models\AlokasiKecamatan;
use App\Models\AlternatifMusim;
use App\Models\AlternatifMusimPupuk;
use App\Models\JenisPupuk;
use App\Models\Kecamatan;
use App\Models\MusimTanam;
use App\Models\PenugasanPenyuluh;
use App\Models\PenugasanPoktan;
use App\Models\Penyuluh;
use App\Models\Periode;
use App\Models\Petani;
use App\Models\Poktan;
use App\Models\User;
use App\Models\VikorHasil;
use App\Services\PenilaianKriteriaService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use RuntimeException;
use Throwable;

class RapikanDataWilayahDemo extends Command
{
    protected $signature = 'data:rapikan-wilayah-demo
                            {--preview : Tampilkan rencana/perubahan tanpa menyimpannya}';

    protected $description = 'Merapikan Kecamatan-Penyuluh-Poktan lama dan menambahkan data demo Pangenan + alternatif MT1/2026 tanpa mengubah A1-A30.';

    private array $stats = [
        'kecamatan_baru' => 0,
        'penugasan_penyuluh' => 0,
        'poktan_diperbaiki' => 0,
        'poktan_dialihkan' => 0,
        'poktan_baru' => 0,
        'penyuluh_demo' => 0,
        'alternatif_demo' => 0,
    ];

    public function handle(PenilaianKriteriaService $penilaian): int
    {
        $preview = (bool) $this->option('preview');

        $this->newLine();
        $this->info('=== RAPikan Data Wilayah + Data Demo Pangenan ===');
        $this->line('Mode: ' . ($preview ? 'PREVIEW (tidak menyimpan)' : 'EKSEKUSI'));
        $this->line('A1-A30 tidak akan diubah.');
        $this->newLine();

        try {
            if ($preview) {
                DB::beginTransaction();
                $this->runAll($penilaian);
                DB::rollBack();
            } else {
                DB::transaction(fn () => $this->runAll($penilaian));
            }
        } catch (Throwable $e) {
            if (DB::transactionLevel() > 0) {
                DB::rollBack();
            }

            $this->error('Gagal: ' . $e->getMessage());
            return self::FAILURE;
        }

        $this->newLine();
        $this->table(
            ['Proses', 'Jumlah'],
            [
                ['Kecamatan baru', $this->stats['kecamatan_baru']],
                ['Penugasan Kecamatan Penyuluh dibuat', $this->stats['penugasan_penyuluh']],
                ['Poktan diperbaiki/diisi Kecamatan', $this->stats['poktan_diperbaiki']],
                ['Poktan tanpa Penyuluh dialihkan', $this->stats['poktan_dialihkan']],
                ['Poktan demo/binaan baru', $this->stats['poktan_baru']],
                ['Penyuluh demo Pangenan dibuat', $this->stats['penyuluh_demo']],
                ['Alternatif demo MT1/2026 dibuat', $this->stats['alternatif_demo']],
            ]
        );

        if ($preview) {
            $this->warn('PREVIEW selesai. Tidak ada perubahan yang disimpan.');
            $this->line('Jalankan tanpa --preview jika hasilnya sudah sesuai.');
        } else {
            $this->info('Selesai. Data sudah dirapikan dan data demo sudah ditambahkan.');
            $this->newLine();
            $this->line('Akun demo Pangenan (jika baru dibuat):');
            $this->line('  demo_pangenan_01 / Demo12345!');
            $this->line('  demo_pangenan_02 / Demo12345!');
            $this->line('  demo_pangenan_03 / Demo12345!');
        }

        return self::SUCCESS;
    }

    private function runAll(PenilaianKriteriaService $penilaian): void
    {
        $this->assertSchemaReady();

        $kecamatans = $this->ensureKecamatans();

        // 1) Rapikan penugasan Kecamatan untuk seluruh Penyuluh aktif yang belum punya.
        $this->ensureAssignmentsForExistingPenyuluh($kecamatans);

        // 2) Sinkronkan Poktan lama yang sudah punya Penyuluh.
        $this->normalizeExistingPoktan();

        // 3) Buat tiga Penyuluh demo di Kecamatan Pangenan.
        $dummyPenyuluh = $this->ensurePangenanDummyPenyuluh($kecamatans['Pangenan']);

        // 4) Bereskan lebih dulu Poktan lama yang belum punya Penyuluh.
        //    Kecamatan Poktan yang sudah ada dipertahankan; Penyuluh dipilih dari Kecamatan yang sama.
        $this->assignRemainingUnownedPoktan();

        // 5) Pastikan setiap Penyuluh aktif memiliki minimal satu Poktan.
        //    Jika Poktan lama tidak cukup, sistem membuat satu Poktan binaan demo di Kecamatan tugasnya.
        $this->ensureEveryPenyuluhHasPoktan();

        // 6) Buat 9 alternatif demo Pangenan pada MT1 / 2026 (3 petani per Penyuluh demo).
        $this->ensureDummyAlternatives($dummyPenyuluh, $kecamatans['Pangenan'], $penilaian);
    }

    private function assertSchemaReady(): void
    {
        $requiredTables = [
            'users', 'penyuluh', 'poktan', 'kecamatan', 'penugasan_penyuluh', 'penugasan_poktan',
            'petani', 'alternatif_musim', 'musim_tanam', 'periode', 'jenis_pupuk',
            'alternatif_musim_pupuk', 'alokasi_kecamatan', 'vikor_hasil',
        ];

        foreach ($requiredTables as $table) {
            if (!Schema::hasTable($table)) {
                throw new RuntimeException("Tabel {$table} belum tersedia. Pastikan migration refactor terakhir sudah terpasang.");
            }
        }
    }

    /**
     * Kecamatan yang dipakai memang berada di Kabupaten Cirebon.
     */
    private function ensureKecamatans(): array
    {
        $names = [
            'Pangenan',
            'Astanajapura',
            'Mundu',
            'Lemahabang',
            'Losari',
            'Babakan',
            'Gebang',
        ];

        $result = [];

        foreach ($names as $name) {
            $existing = Kecamatan::whereRaw('LOWER(nama_kecamatan) = ?', [mb_strtolower($name)])->first();
            if (!$existing) {
                $existing = Kecamatan::create(['nama_kecamatan' => $name]);
                $this->stats['kecamatan_baru']++;
                $this->line("+ Kecamatan dibuat: {$name}");
            }
            $result[$name] = $existing;
        }

        return $result;
    }

    private function ensureAssignmentsForExistingPenyuluh(array $kecamatans): void
    {
        $fallback = collect([
            $kecamatans['Astanajapura'],
            $kecamatans['Mundu'],
            $kecamatans['Lemahabang'],
            $kecamatans['Losari'],
        ]);

        $counter = 0;

        Penyuluh::where(function ($q) {
            $q->where('status', 'aktif')->orWhereNull('status');
        })->orderBy('id')->get()->each(function (Penyuluh $penyuluh) use ($fallback, &$counter) {
            $aktif = PenugasanPenyuluh::where('penyuluh_id', $penyuluh->id)
                ->where('status', 'aktif')
                ->whereNull('tanggal_selesai')
                ->latest('tanggal_mulai')
                ->first();

            if ($aktif) {
                return;
            }

            // Jika ada Poktan lama yang sudah memiliki Kecamatan, pilih Kecamatan yang paling banyak dipakai.
            $majority = DB::table('poktan')
                ->where('penyuluh_id', $penyuluh->id)
                ->whereNotNull('kecamatan_id')
                ->select('kecamatan_id', DB::raw('COUNT(*) as total'))
                ->groupBy('kecamatan_id')
                ->orderByDesc('total')
                ->orderBy('kecamatan_id')
                ->first();

            $kecamatanId = $majority?->kecamatan_id;

            if (!$kecamatanId) {
                $kecamatanId = $fallback[$counter % $fallback->count()]->id;
                $counter++;
            }

            PenugasanPenyuluh::create([
                'penyuluh_id' => $penyuluh->id,
                'kecamatan_id' => $kecamatanId,
                'tanggal_mulai' => now()->toDateString(),
                'tanggal_selesai' => null,
                'status' => 'aktif',
                'catatan' => 'Penugasan awal hasil perapihan data lama.',
            ]);

            if ($penyuluh->status !== 'aktif') {
                $penyuluh->update(['status' => 'aktif']);
            }

            $this->stats['penugasan_penyuluh']++;
            $namaKecamatan = Kecamatan::find($kecamatanId)?->nama_kecamatan ?? '-';
            $this->line("+ Kecamatan Penyuluh: {$penyuluh->nama_penyuluh} -> {$namaKecamatan}");
        });
    }

    private function normalizeExistingPoktan(): void
    {
        $poktans = Poktan::whereNotNull('penyuluh_id')->orderBy('id')->get();

        foreach ($poktans as $poktan) {
            $penugasan = PenugasanPenyuluh::where('penyuluh_id', $poktan->penyuluh_id)
                ->where('status', 'aktif')
                ->whereNull('tanggal_selesai')
                ->latest('tanggal_mulai')
                ->first();

            if (!$penugasan) {
                continue;
            }

            // Untuk data aktif lama, Kecamatan harus sama dengan Kecamatan tugas Penyuluh saat ini.
            // Ini memperbaiki kasus Ikhsan = Gebang tetapi beberapa Poktan Ikhsan masih tampil '-'.
            if (($poktan->status ?? 'aktif') === 'aktif' && (int) $poktan->kecamatan_id !== (int) $penugasan->kecamatan_id) {
                $poktan->update(['kecamatan_id' => $penugasan->kecamatan_id]);
                $this->stats['poktan_diperbaiki']++;
                $this->line("~ Poktan diperbaiki: {$poktan->nama_poktan}");
            }

            $this->syncPoktanAssignment($poktan, (int) $poktan->penyuluh_id);
        }
    }

    /** @return array<int, Penyuluh> */
    private function ensurePangenanDummyPenyuluh(Kecamatan $pangenan): array
    {
        $rows = [
            ['name' => 'Penyuluh Demo Pangenan 1', 'username' => 'demo_pangenan_01'],
            ['name' => 'Penyuluh Demo Pangenan 2', 'username' => 'demo_pangenan_02'],
            ['name' => 'Penyuluh Demo Pangenan 3', 'username' => 'demo_pangenan_03'],
        ];

        $result = [];

        foreach ($rows as $row) {
            $user = User::where('username', $row['username'])->first();
            if (!$user) {
                $user = User::create([
                    'name' => $row['name'],
                    'username' => $row['username'],
                    'email' => $row['username'] . '@demo.local',
                    'password' => Hash::make('Demo12345!'),
                    'role' => 'penyuluh',
                ]);
            }

            $penyuluh = Penyuluh::where('user_id', $user->id)->first();
            if (!$penyuluh) {
                // Kolom poktan_id pada tabel penyuluh adalah kolom legacy dari struktur lama.
                // Isi string kosong bila kolom tersebut masih ada dan masih NOT NULL pada database pengguna.
                $insert = [
                    'user_id' => $user->id,
                    'nama_penyuluh' => $row['name'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
                if (Schema::hasColumn('penyuluh', 'status')) {
                    $insert['status'] = 'aktif';
                }
                if (Schema::hasColumn('penyuluh', 'poktan_id')) {
                    $insert['poktan_id'] = '';
                }

                $id = DB::table('penyuluh')->insertGetId($insert);
                $penyuluh = Penyuluh::findOrFail($id);
                $this->stats['penyuluh_demo']++;
                $this->line("+ Penyuluh demo dibuat: {$row['name']}");
            } else {
                $penyuluh->update(['nama_penyuluh' => $row['name'], 'status' => 'aktif']);
            }

            $aktif = PenugasanPenyuluh::where('penyuluh_id', $penyuluh->id)
                ->where('status', 'aktif')
                ->whereNull('tanggal_selesai')
                ->first();

            if (!$aktif) {
                PenugasanPenyuluh::create([
                    'penyuluh_id' => $penyuluh->id,
                    'kecamatan_id' => $pangenan->id,
                    'tanggal_mulai' => now()->toDateString(),
                    'status' => 'aktif',
                    'catatan' => 'Data demo untuk pengujian multi-Penyuluh dalam satu Kecamatan.',
                ]);
                $this->stats['penugasan_penyuluh']++;
            } elseif ((int) $aktif->kecamatan_id !== (int) $pangenan->id) {
                // Akun demo harus konsisten berada di Pangenan.
                $aktif->update(['kecamatan_id' => $pangenan->id]);
            }

            $result[] = $penyuluh->fresh();
        }

        return $result;
    }

    private function ensureEveryPenyuluhHasPoktan(): void
    {
        $penyuluhs = Penyuluh::where('status', 'aktif')->orderBy('id')->get();

        foreach ($penyuluhs as $penyuluh) {
            if (Poktan::where('penyuluh_id', $penyuluh->id)->exists()) {
                continue;
            }

            $penugasan = PenugasanPenyuluh::where('penyuluh_id', $penyuluh->id)
                ->where('status', 'aktif')
                ->whereNull('tanggal_selesai')
                ->first();

            if (!$penugasan) {
                continue;
            }

            // Gunakan Poktan lama tanpa Penyuluh terlebih dahulu.
            $candidate = Poktan::whereNull('penyuluh_id')
                ->where(function ($q) use ($penugasan) {
                    $q->whereNull('kecamatan_id')
                        ->orWhere('kecamatan_id', $penugasan->kecamatan_id);
                })
                ->orderBy('id')
                ->first();

            if ($candidate) {
                $candidate->update([
                    'penyuluh_id' => $penyuluh->id,
                    'kecamatan_id' => $penugasan->kecamatan_id,
                    'status' => 'aktif',
                ]);
                $this->syncPoktanAssignment($candidate, $penyuluh->id);
                $this->stats['poktan_dialihkan']++;
                $this->line("~ Poktan tanpa Penyuluh dialihkan: {$candidate->nama_poktan} -> {$penyuluh->nama_penyuluh}");
                continue;
            }

            // Jika Poktan lama tidak cukup, buat satu Poktan binaan demo agar setiap Penyuluh punya binaan.
            $nama = 'POKTAN BINAAN ' . strtoupper($this->safeName($penyuluh->nama_penyuluh)) . ' #' . $penyuluh->id;
            $poktan = Poktan::firstOrCreate(
                ['nama_poktan' => $nama],
                [
                    'penyuluh_id' => $penyuluh->id,
                    'kecamatan_id' => $penugasan->kecamatan_id,
                    'status' => 'aktif',
                ]
            );

            if (!$poktan->wasRecentlyCreated) {
                $poktan->update([
                    'penyuluh_id' => $penyuluh->id,
                    'kecamatan_id' => $penugasan->kecamatan_id,
                    'status' => 'aktif',
                ]);
            } else {
                $this->stats['poktan_baru']++;
            }

            $this->syncPoktanAssignment($poktan, $penyuluh->id);
            $this->line("+ Poktan binaan dibuat: {$poktan->nama_poktan}");
        }
    }

    private function assignRemainingUnownedPoktan(): void
    {
        $unowned = Poktan::whereNull('penyuluh_id')->orderBy('id')->get();

        foreach ($unowned as $poktan) {
            $target = null;

            if ($poktan->kecamatan_id) {
                // Prioritas pertama: Penyuluh aktif yang memang bertugas di Kecamatan Poktan.
                $target = Penyuluh::where('status', 'aktif')
                    ->whereHas('penugasanAktif', fn ($q) => $q->where('kecamatan_id', $poktan->kecamatan_id))
                    ->withCount('poktan')
                    ->orderBy('poktan_count')
                    ->orderBy('id')
                    ->first();

                if (!$target) {
                    // Kalau belum ada Penyuluh di Kecamatan itu, pakai Penyuluh aktif yang belum punya Poktan,
                    // lalu sesuaikan penugasannya ke Kecamatan Poktan. Aman karena belum punya binaan.
                    $target = Penyuluh::where('status', 'aktif')
                        ->whereHas('penugasanAktif')
                        ->whereDoesntHave('poktan')
                        ->orderBy('id')
                        ->first();

                    if ($target) {
                        $aktif = PenugasanPenyuluh::where('penyuluh_id', $target->id)
                            ->where('status', 'aktif')
                            ->whereNull('tanggal_selesai')
                            ->first();

                        if ($aktif) {
                            $aktif->update([
                                'kecamatan_id' => $poktan->kecamatan_id,
                                'catatan' => 'Kecamatan disesuaikan saat perapihan berdasarkan Poktan lama yang belum memiliki pembina.',
                            ]);
                        }
                    }
                }

                if (!$target) {
                    // Jangan pindahkan Kecamatan Poktan hanya demi mencari pembina.
                    // Jika benar-benar belum ada Penyuluh yang bisa dipakai, buat akun demo pembina Kecamatan tersebut.
                    $kecamatan = Kecamatan::findOrFail($poktan->kecamatan_id);
                    $target = $this->ensureAutoDemoPenyuluh($kecamatan);
                }
            } else {
                // Poktan yang Kecamatan-nya juga kosong boleh mengikuti wilayah Penyuluh dengan binaan paling sedikit.
                $target = Penyuluh::where('status', 'aktif')
                    ->whereHas('penugasanAktif')
                    ->withCount('poktan')
                    ->orderBy('poktan_count')
                    ->orderBy('id')
                    ->first();
            }

            if (!$target) {
                throw new RuntimeException('Tidak ada Penyuluh aktif yang dapat menerima Poktan tanpa pembina.');
            }

            $assignment = PenugasanPenyuluh::where('penyuluh_id', $target->id)
                ->where('status', 'aktif')
                ->whereNull('tanggal_selesai')
                ->firstOrFail();

            $poktan->update([
                'penyuluh_id' => $target->id,
                // Kecamatan lama yang sudah terisi tidak diganti.
                'kecamatan_id' => $poktan->kecamatan_id ?: $assignment->kecamatan_id,
                'status' => 'aktif',
            ]);

            $this->syncPoktanAssignment($poktan, $target->id);
            $this->stats['poktan_dialihkan']++;
            $this->line("~ Poktan tanpa Penyuluh dialihkan: {$poktan->nama_poktan} -> {$target->nama_penyuluh}");
        }
    }

    private function ensureAutoDemoPenyuluh(Kecamatan $kecamatan): Penyuluh
    {
        $username = 'demo_auto_kec_' . $kecamatan->id;
        $name = 'Penyuluh Demo ' . $kecamatan->nama_kecamatan;

        $user = User::where('username', $username)->first();
        if (!$user) {
            $user = User::create([
                'name' => $name,
                'username' => $username,
                'email' => $username . '@demo.local',
                'password' => Hash::make('Demo12345!'),
                'role' => 'penyuluh',
            ]);
        }

        $penyuluh = Penyuluh::where('user_id', $user->id)->first();
        if (!$penyuluh) {
            $insert = [
                'user_id' => $user->id,
                'nama_penyuluh' => $name,
                'created_at' => now(),
                'updated_at' => now(),
            ];
            if (Schema::hasColumn('penyuluh', 'status')) {
                $insert['status'] = 'aktif';
            }
            if (Schema::hasColumn('penyuluh', 'poktan_id')) {
                $insert['poktan_id'] = '';
            }

            $id = DB::table('penyuluh')->insertGetId($insert);
            $penyuluh = Penyuluh::findOrFail($id);
            $this->stats['penyuluh_demo']++;
        }

        $assignment = PenugasanPenyuluh::where('penyuluh_id', $penyuluh->id)
            ->where('status', 'aktif')
            ->whereNull('tanggal_selesai')
            ->first();

        if (!$assignment) {
            PenugasanPenyuluh::create([
                'penyuluh_id' => $penyuluh->id,
                'kecamatan_id' => $kecamatan->id,
                'tanggal_mulai' => now()->toDateString(),
                'status' => 'aktif',
                'catatan' => 'Penyuluh demo otomatis untuk melengkapi Poktan lama tanpa pembina.',
            ]);
            $this->stats['penugasan_penyuluh']++;
        }

        return $penyuluh->fresh();
    }

    private function syncPoktanAssignment(Poktan $poktan, int $penyuluhId): void
    {
        $current = PenugasanPoktan::where('poktan_id', $poktan->id)
            ->where('status', 'aktif')
            ->whereNull('tanggal_selesai')
            ->latest('tanggal_mulai')
            ->first();

        if ($current && (int) $current->penyuluh_id === $penyuluhId) {
            return;
        }

        if ($current) {
            $current->update([
                'tanggal_selesai' => now()->toDateString(),
                'status' => 'selesai',
                'catatan' => 'Ditutup saat perapihan data Poktan.',
            ]);
        }

        PenugasanPoktan::create([
            'poktan_id' => $poktan->id,
            'penyuluh_id' => $penyuluhId,
            'tanggal_mulai' => now()->toDateString(),
            'tanggal_selesai' => null,
            'status' => 'aktif',
            'catatan' => 'Pembinaan aktif hasil perapihan data.',
        ]);
    }

    /**
     * @param array<int, Penyuluh> $dummyPenyuluh
     */
    private function ensureDummyAlternatives(array $dummyPenyuluh, Kecamatan $pangenan, PenilaianKriteriaService $penilaian): void
    {
        $musim = MusimTanam::whereRaw('UPPER(kode_musim) = ?', ['MT1'])->first();
        if (!$musim) {
            $musim = MusimTanam::create([
                'kode_musim' => 'MT1',
                'nama_musim' => 'Musim Tanam I',
            ]);
        }

        $periode = Periode::firstOrCreate(['tahun' => 2026]);

        // Agar Pangenan langsung bisa dipakai untuk menguji VIKOR.
        AlokasiKecamatan::firstOrCreate(
            [
                'kecamatan_id' => $pangenan->id,
                'musim_tanam_id' => $musim->id,
                'periode_id' => $periode->id,
            ],
            ['kuota_penerima' => 6]
        );

        $c2 = $penilaian->options('C2');
        $c3 = $penilaian->options('C3');
        $c5 = $penilaian->options('C5');

        if (count($c2) < 1 || count($c3) < 1 || count($c5) < 1) {
            throw new RuntimeException('Master Subkriteria C2/C3/C5 belum lengkap. Data alternatif demo tidak dapat dibuat.');
        }

        $pupuk = [
            'Urea' => JenisPupuk::firstOrCreate(['nama_pupuk' => 'Urea'], ['is_active' => true]),
            'ZA' => JenisPupuk::firstOrCreate(['nama_pupuk' => 'ZA'], ['is_active' => true]),
            'NPK' => JenisPupuk::firstOrCreate(['nama_pupuk' => 'NPK'], ['is_active' => true]),
        ];

        $samples = [
            ['luas' => 0.40, 'c2' => 0, 'c3' => 0, 'c5' => 0, 'pupuk' => ['Urea' => 100]],
            ['luas' => 0.75, 'c2' => 1, 'c3' => 0, 'c5' => 0, 'pupuk' => ['Urea' => 100, 'ZA' => 50]],
            ['luas' => 1.30, 'c2' => 2, 'c3' => min(1, count($c3) - 1), 'c5' => min(2, count($c5) - 1), 'pupuk' => ['Urea' => 125, 'ZA' => 50, 'NPK' => 100]],
            ['luas' => 0.50, 'c2' => min(3, count($c2) - 1), 'c3' => 0, 'c5' => min(1, count($c5) - 1), 'pupuk' => ['NPK' => 150]],
            ['luas' => 0.90, 'c2' => 0, 'c3' => min(1, count($c3) - 1), 'c5' => 0, 'pupuk' => ['Urea' => 150, 'NPK' => 100]],
            ['luas' => 1.70, 'c2' => 1, 'c3' => 0, 'c5' => min(2, count($c5) - 1), 'pupuk' => ['Urea' => 175, 'ZA' => 75, 'NPK' => 125]],
            ['luas' => 0.35, 'c2' => 0, 'c3' => 0, 'c5' => 0, 'pupuk' => ['ZA' => 75]],
            ['luas' => 0.65, 'c2' => 1, 'c3' => 0, 'c5' => 0, 'pupuk' => ['Urea' => 100, 'ZA' => 50]],
            ['luas' => 1.10, 'c2' => min(2, count($c2) - 1), 'c3' => min(1, count($c3) - 1), 'c5' => min(2, count($c5) - 1), 'pupuk' => ['Urea' => 150, 'ZA' => 50, 'NPK' => 100]],
        ];

        // Pastikan masing-masing Penyuluh demo punya satu Poktan di Pangenan.
        $demoPoktans = [];
        foreach ($dummyPenyuluh as $index => $penyuluh) {
            $poktan = Poktan::where('penyuluh_id', $penyuluh->id)
                ->where('kecamatan_id', $pangenan->id)
                ->orderBy('id')
                ->first();

            if (!$poktan) {
                $poktan = Poktan::create([
                    'nama_poktan' => 'POKTAN DEMO PANGENAN ' . ($index + 1),
                    'penyuluh_id' => $penyuluh->id,
                    'kecamatan_id' => $pangenan->id,
                    'status' => 'aktif',
                ]);
                $this->stats['poktan_baru']++;
                $this->syncPoktanAssignment($poktan, $penyuluh->id);
            }

            $demoPoktans[$index] = $poktan;
        }

        $nextNumber = $this->nextAlternativeNumber($musim->id, $periode->id);
        $newAlternatives = 0;

        for ($i = 0; $i < 9; $i++) {
            $nik = '320999000000' . str_pad((string) ($i + 1), 4, '0', STR_PAD_LEFT);
            $nama = 'Petani Demo Pangenan ' . str_pad((string) ($i + 1), 2, '0', STR_PAD_LEFT);
            $poktan = $demoPoktans[$i % count($demoPoktans)];
            $sample = $samples[$i];

            $petani = Petani::where('nik', $nik)->first();
            if (!$petani) {
                $petani = Petani::create([
                    'nik' => $nik,
                    'nama_petani' => $nama,
                    'poktan_id' => $poktan->id,
                ]);
            } else {
                $petani->update([
                    'nama_petani' => $nama,
                    'poktan_id' => $poktan->id,
                ]);
            }

            // Idempotent: jangan buat ulang kalau data demo ini sudah ada pada MT1/2026.
            $existing = AlternatifMusim::where('petani_id', $petani->id)
                ->where('musim_tanam_id', $musim->id)
                ->where('periode_id', $periode->id)
                ->first();

            if ($existing) {
                continue;
            }

            $luasLabel = $penilaian->resolveLuasLahan((float) $sample['luas']);
            $c2Value = $c2[$sample['c2'] % count($c2)];
            $c3Value = $c3[$sample['c3'] % count($c3)];
            $c5Value = $c5[$sample['c5'] % count($c5)];
            $jumlahJenis = count($sample['pupuk']);
            $c4Value = $penilaian->resolveKebutuhanPupuk($jumlahJenis);

            $kode = 'A' . $nextNumber++;

            // A1-A30 tidak disentuh. Kode baru selalu dimulai setelah kode tertinggi yang sudah ada.
            if ((int) substr($kode, 1) <= 30) {
                $kode = 'A' . max(31, $nextNumber++);
            }

            $alternatif = AlternatifMusim::create([
                'petani_id' => $petani->id,
                'poktan_id' => $poktan->id,
                'musim_tanam_id' => $musim->id,
                'periode_id' => $periode->id,
                'pengajuan_petani_id' => null,
                'kode_alternatif' => $kode,
                'luas_lahan_ha' => $sample['luas'],
                'luas_lahan' => $luasLabel,
                'jenis_komoditas' => $c2Value,
                'kelengkapan_berkas' => $c3Value,
                'berkas_persyaratan_path' => null,
                'kebutuhan_pupuk' => $c4Value,
                'subsektor_pertanian' => $c5Value,
            ]);

            // Untuk kompatibilitas tampilan data lama di tabel petani, simpan snapshot kriterianya juga.
            $petani->update([
                'luas_lahan' => $luasLabel,
                'jenis_komoditas' => $c2Value,
                'kelengkapan_berkas' => $c3Value,
                'kebutuhan_pupuk' => $c4Value,
                'subsektor_pertanian' => $c5Value,
            ]);

            foreach ($sample['pupuk'] as $namaPupuk => $kg) {
                AlternatifMusimPupuk::create([
                    'alternatif_musim_id' => $alternatif->id,
                    'jenis_pupuk_id' => $pupuk[$namaPupuk]->id,
                    'jumlah_kg' => $kg,
                ]);
            }

            $newAlternatives++;
            $this->stats['alternatif_demo']++;
            $this->line("+ Alternatif demo {$kode}: {$nama} / {$poktan->nama_poktan}");
        }

        if ($newAlternatives > 0) {
            // Jika Pangenan sebelumnya pernah dihitung, ranking lama tidak lagi valid karena kandidat bertambah.
            $ids = AlternatifMusim::where('musim_tanam_id', $musim->id)
                ->where('periode_id', $periode->id)
                ->whereHas('poktan', fn ($q) => $q->where('kecamatan_id', $pangenan->id))
                ->pluck('id');

            if ($ids->isNotEmpty()) {
                VikorHasil::whereIn('alternatif_musim_id', $ids)->delete();
            }
        }
    }

    private function nextAlternativeNumber(int $musimId, int $periodeId): int
    {
        $max = 0;
        $codes = AlternatifMusim::where('musim_tanam_id', $musimId)
            ->where('periode_id', $periodeId)
            ->pluck('kode_alternatif');

        foreach ($codes as $code) {
            if (preg_match('/^A(\d+)$/i', trim((string) $code), $m)) {
                $max = max($max, (int) $m[1]);
            }
        }

        return max(31, $max + 1);
    }

    private function safeName(string $name): string
    {
        $name = preg_replace('/[^A-Za-z0-9 ]+/', '', $name) ?: 'PENYULUH';
        $name = preg_replace('/\s+/', ' ', trim($name));
        return mb_substr($name, 0, 40);
    }
}
