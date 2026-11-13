<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PengajuanPetani extends Model
{
    use HasFactory;

    protected $table = 'pengajuan_petani';

    protected $fillable = [
        'penyuluh_id',
        'poktan_id',

        'musim_tanam_id',
        'periode_id',
        'batch_import_id',
        'asal_input',

        'nik',
        'nama_petani',
        'luas_lahan_ha',

        'luas_lahan',
        'jenis_komoditas',
        'kelengkapan_berkas',
        'kelengkapan_berkas_verifikasi',
        'berkas_persyaratan_path',
        'catatan_verifikasi_berkas',
        'c3_verified_by',
        'c3_verified_at',
        'kebutuhan_pupuk',
        'subsektor_pertanian',

        'status',

        'catatan_penyuluh',
        'catatan_admin',

        'verified_by',

        'tanggal_pengajuan',
        'tanggal_verifikasi',
    ];

    protected $casts = [
        'luas_lahan_ha' => 'decimal:2',
        'tanggal_pengajuan' => 'datetime',
        'tanggal_verifikasi' => 'datetime',
        'c3_verified_at' => 'datetime',
    ];

    public function penyuluh()
    {
        return $this->belongsTo(Penyuluh::class, 'penyuluh_id');
    }

    public function poktan()
    {
        return $this->belongsTo(Poktan::class, 'poktan_id');
    }

    public function musimTanam()
    {
        return $this->belongsTo(MusimTanam::class, 'musim_tanam_id');
    }

    public function periode()
    {
        return $this->belongsTo(Periode::class, 'periode_id');
    }

    public function batchImport()
    {
        return $this->belongsTo(
            BatchImportPengajuan::class,
            'batch_import_id'
        );
    }

    public function verifier()
    {
        return $this->belongsTo(User::class, 'verified_by');
    }


    public function c3Verifier()
    {
        return $this->belongsTo(User::class, 'c3_verified_by');
    }

    public function getKelengkapanBerkasFinalAttribute(): string
    {
        return $this->kelengkapan_berkas_verifikasi ?: $this->kelengkapan_berkas;
    }

    public function getC3TerverifikasiAttribute(): bool
    {
        return !is_null($this->c3_verified_at) && !blank($this->kelengkapan_berkas_verifikasi);
    }

    public function alternatifMusim()
    {
        return $this->hasOne(
            AlternatifMusim::class,
            'pengajuan_petani_id'
        );
    }


    public function pupuk()
    {
        return $this->hasMany(
            PengajuanPetaniPupuk::class,
            'pengajuan_petani_id'
        );
    }

    public function getRingkasanPupukAttribute(): string
    {
        if (!$this->relationLoaded('pupuk')) {
            $this->load('pupuk.jenisPupuk');
        }

        if ($this->pupuk->isEmpty()) {
            return $this->kebutuhan_pupuk
                ? 'Rincian belum tersedia (' . $this->kebutuhan_pupuk . ')'
                : '-';
        }

        return $this->pupuk
            ->filter(fn ($item) => (float) $item->jumlah_kg > 0)
            ->map(function ($item) {
                $nama = $item->jenisPupuk?->nama_pupuk ?? 'Pupuk';
                $kg = rtrim(rtrim(number_format((float) $item->jumlah_kg, 2, ',', '.'), '0'), ',');
                return $nama . ' ' . $kg . ' kg';
            })
            ->implode(', ');
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'menunggu_verifikasi' => 'Menunggu Verifikasi',
            'perlu_perbaikan' => 'Perlu Perbaikan',
            'disetujui' => 'Disetujui',
            'ditolak' => 'Ditolak',
            default => ucfirst(
                str_replace('_', ' ', $this->status)
            ),
        };
    }

    public function getStatusClassAttribute(): string
    {
        return match ($this->status) {
            'menunggu_verifikasi' => 'warning',
            'perlu_perbaikan' => 'info',
            'disetujui' => 'success',
            'ditolak' => 'danger',
            default => 'secondary',
        };
    }

    public function getAsalInputLabelAttribute(): string
    {
        return match ($this->asal_input) {
            'import' => 'Import Excel',
            default => 'Manual',
        };
    }
}