<?php

namespace App\Services;

use App\Models\JenisPupuk;
use Illuminate\Validation\ValidationException;

class KebutuhanPupukService
{
    public function fromRequestArray(array $input, bool $required = true): array
    {
        $aktif = JenisPupuk::query()->where('is_active', true)->pluck('id')->map(fn ($id) => (int) $id)->all();
        $rows = [];

        foreach ($input as $jenisId => $jumlah) {
            $jenisId = (int) $jenisId;

            if (!in_array($jenisId, $aktif, true)) {
                continue;
            }

            if ($jumlah === null || $jumlah === '') {
                continue;
            }

            if (!is_numeric($jumlah) || (float) $jumlah < 0) {
                throw ValidationException::withMessages([
                    "pupuk.{$jenisId}" => 'Jumlah kebutuhan pupuk harus berupa angka 0 atau lebih.',
                ]);
            }

            if ((float) $jumlah > 0) {
                $rows[$jenisId] = round((float) $jumlah, 2);
            }
        }

        if ($required && count($rows) === 0) {
            throw ValidationException::withMessages([
                'pupuk' => 'Isi minimal satu jenis pupuk dengan jumlah lebih dari 0 kg.',
            ]);
        }

        return $rows;
    }

    public function syncPengajuan($pengajuan, array $rows): void
    {
        $pengajuan->pupuk()->delete();

        foreach ($rows as $jenisId => $jumlah) {
            $pengajuan->pupuk()->create([
                'jenis_pupuk_id' => $jenisId,
                'jumlah_kg' => $jumlah,
            ]);
        }
    }

    public function syncAlternatif($alternatif, array $rows): void
    {
        $alternatif->pupuk()->delete();

        foreach ($rows as $jenisId => $jumlah) {
            $alternatif->pupuk()->create([
                'jenis_pupuk_id' => $jenisId,
                'jumlah_kg' => $jumlah,
            ]);
        }
    }
}
