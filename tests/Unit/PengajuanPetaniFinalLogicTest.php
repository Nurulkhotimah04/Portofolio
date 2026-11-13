<?php

namespace Tests\Unit;

use App\Models\PengajuanPetani;
use Carbon\Carbon;
use Tests\TestCase;

class PengajuanPetaniFinalLogicTest extends TestCase
{
    public function test_c3_final_uses_admin_verification_when_available(): void
    {
        $pengajuan = new PengajuanPetani();
        $pengajuan->kelengkapan_berkas = 'Lengkap';
        $pengajuan->kelengkapan_berkas_verifikasi = 'Kurang Lengkap';

        $this->assertSame('Kurang Lengkap', $pengajuan->kelengkapan_berkas_final);
    }

    public function test_c3_final_falls_back_to_submitted_value_for_legacy_data(): void
    {
        $pengajuan = new PengajuanPetani();
        $pengajuan->kelengkapan_berkas = 'Lengkap';
        $pengajuan->kelengkapan_berkas_verifikasi = null;

        $this->assertSame('Lengkap', $pengajuan->kelengkapan_berkas_final);
    }

    public function test_c3_is_verified_only_when_result_and_timestamp_exist(): void
    {
        $pengajuan = new PengajuanPetani();
        $pengajuan->kelengkapan_berkas_verifikasi = 'Lengkap';
        $pengajuan->c3_verified_at = Carbon::now();

        $this->assertTrue($pengajuan->c3_terverifikasi);

        $pengajuan->c3_verified_at = null;
        $this->assertFalse($pengajuan->c3_terverifikasi);
    }
}
