<?php

namespace App\Services;

use App\Models\Subkriteria;
use Illuminate\Validation\ValidationException;

class PenilaianKriteriaService
{
    public function options(string $kode): array
    {
        return Subkriteria::with('kriteria')
            ->whereHas('kriteria', fn ($q) => $q->where('kode_kriteria', $kode))
            ->orderBy('id')
            ->pluck('nama_subkriteria')
            ->all();
    }

    public function canonical(string $kode, ?string $value): string
    {
        $value = $this->normalize((string) $value);

        foreach ($this->options($kode) as $option) {
            if ($this->normalize($option) === $value) {
                return $option;
            }
        }

        throw ValidationException::withMessages([
            strtolower($kode) => "Nilai {$kode} tidak sesuai subkriteria yang tersedia.",
        ]);
    }

    public function resolveLuasLahan(float $ha): string
    {
        if ($ha <= 0 || $ha > 2) {
            throw ValidationException::withMessages([
                'luas_lahan_ha' => 'Luas lahan harus lebih dari 0 dan maksimal 2 Ha sesuai subkriteria C1 yang berlaku.',
            ]);
        }

        $options = $this->options('C1');

        $target = $ha <= 0.5 ? 1 : ($ha <= 1.0 ? 2 : 3);

        foreach ($options as $option) {
            $n = $this->normalize($option);

            if ($target === 1 && (str_contains($n, '≤0.5') || str_contains($n, '<=0.5'))) {
                return $option;
            }

            if ($target === 2 && str_contains($n, '0.6') && str_contains($n, '1')) {
                return $option;
            }

            if ($target === 3 && str_contains($n, '1.1') && str_contains($n, '2')) {
                return $option;
            }
        }

        throw ValidationException::withMessages([
            'luas_lahan_ha' => 'Subkriteria C1 untuk luas lahan tersebut tidak ditemukan.',
        ]);
    }

    public function resolveKebutuhanPupuk(int $jumlahJenis): string
    {
        if ($jumlahJenis < 1) {
            throw ValidationException::withMessages([
                'pupuk' => 'Minimal satu jenis pupuk harus memiliki jumlah kebutuhan lebih dari 0 kg.',
            ]);
        }

        $options = $this->options('C4');
        $needle = $jumlahJenis > 3 ? '>3' : (string) $jumlahJenis;

        foreach ($options as $option) {
            $n = $this->normalize($option);

            if ($jumlahJenis > 3) {
                if (str_contains($n, '>3')) {
                    return $option;
                }
            } elseif (str_contains($n, $needle . 'pupuk')) {
                return $option;
            }
        }

        throw ValidationException::withMessages([
            'pupuk' => 'Subkriteria C4 untuk jumlah jenis pupuk tersebut tidak ditemukan.',
        ]);
    }

    private function normalize(string $value): string
    {
        $value = trim(mb_strtolower($value));
        $value = str_replace(['–', '—', '−'], '-', $value);
        $value = str_replace(',', '.', $value);
        $value = preg_replace('/\s+/u', '', $value);
        return $value;
    }
}
