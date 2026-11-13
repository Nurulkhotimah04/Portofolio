@extends('layouts.app')
@section('title','Detail Pengajuan')

@section('content')
<div class="app-page-header">
    <div>
        <h1 class="app-page-title">Detail Pengajuan</h1>
    </div>
    <a href="{{ route('pengajuan.index') }}" class="btn btn-light border"><i class="fas fa-arrow-left mr-1"></i>Kembali</a>
</div>

@if(session('success'))<div class="alert alert-success app-alert">{{ session('success') }}</div>@endif
@if(session('error'))<div class="alert alert-danger app-alert">{{ session('error') }}</div>@endif
@if($errors->any())
    <div class="alert alert-danger app-alert"><ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>
@endif
@if($pengajuan->catatan_admin)<div class="alert alert-info app-alert"><strong>Catatan Admin:</strong> {{ $pengajuan->catatan_admin }}</div>@endif

<div class="d-flex flex-wrap mb-3" style="gap:8px;">
    <span class="app-badge app-badge-{{ $pengajuan->status_class === 'warning' ? 'warning' : ($pengajuan->status_class === 'success' ? 'success' : ($pengajuan->status_class === 'danger' ? 'danger' : 'info')) }}">{{ $pengajuan->status_label }}</span>
    <span class="app-badge app-badge-muted">{{ $pengajuan->asal_input_label }}</span>
    <span class="app-badge app-badge-muted">{{ $pengajuan->musimTanam?->kode_musim ?? '-' }} / {{ $pengajuan->periode?->tahun ?? '-' }}</span>
</div>

<div class="app-card mb-4">
    <div class="app-card-header"><div class="app-card-title">Data Petani & Wilayah</div></div>
    <div class="p-3 p-md-4">
        <div class="row">
            <div class="col-md-6">
                <table class="table table-sm table-borderless mb-0">
                    <tr><th width="38%">NIK</th><td>{{ $pengajuan->nik }}</td></tr>
                    <tr><th>Nama Petani</th><td>{{ $pengajuan->nama_petani }}</td></tr>
                    <tr><th>Poktan</th><td>{{ $pengajuan->poktan?->nama_poktan ?? '-' }}</td></tr>
                </table>
            </div>
            <div class="col-md-6">
                <table class="table table-sm table-borderless mb-0">
                    <tr><th width="38%">Kecamatan</th><td>{{ $pengajuan->poktan?->kecamatan?->nama_kecamatan ?? '-' }}</td></tr>
                    <tr><th>Penyuluh</th><td>{{ $pengajuan->penyuluh?->nama_penyuluh ?? '-' }}</td></tr>
                    <tr><th>Diajukan</th><td>{{ $pengajuan->tanggal_pengajuan?->format('d-m-Y H:i') ?? '-' }}</td></tr>
                </table>
            </div>
        </div>
    </div>
</div>

<div class="app-card mb-4">
    <div class="app-card-header"><div class="app-card-title">Data Penilaian</div></div>
    <div class="table-responsive">
        <table class="table app-table mb-0">
            <thead><tr><th>Kriteria</th><th>Data</th><th>Keterangan</th></tr></thead>
            <tbody>
                <tr><td><strong>C1</strong> · Luas Lahan</td><td>{{ $pengajuan->luas_lahan_ha ? $pengajuan->luas_lahan_ha.' Ha' : '-' }}</td><td>{{ $pengajuan->luas_lahan }}</td></tr>
                <tr><td><strong>C2</strong> · Komoditas</td><td>{{ $pengajuan->jenis_komoditas }}</td><td>Subkriteria otomatis</td></tr>
                <tr>
                    <td><strong>C3</strong> · Kelengkapan Berkas</td>
                    <td>
                        Diajukan: <strong>{{ $pengajuan->kelengkapan_berkas }}</strong>
                        @if($pengajuan->c3_terverifikasi)
                            <br>Hasil Admin: <strong>{{ $pengajuan->kelengkapan_berkas_verifikasi }}</strong>
                        @endif
                    </td>
                    <td>
                        @if($pengajuan->berkas_persyaratan_path)
                            <a target="_blank" class="btn btn-outline-danger btn-sm" href="{{ route('pengajuan.berkas',$pengajuan) }}"><i class="fas fa-file-pdf mr-1"></i>Buka PDF</a>
                        @else
                            <span class="app-badge app-badge-muted">PDF belum tersedia</span>
                        @endif
                        @if($pengajuan->c3_terverifikasi)
                            <div class="small text-success mt-1"><i class="fas fa-check-circle mr-1"></i>Diverifikasi {{ $pengajuan->c3_verified_at?->format('d-m-Y H:i') }}</div>
                        @endif
                    </td>
                </tr>
                <tr><td><strong>C4</strong> · Kebutuhan Pupuk</td><td>{{ $pengajuan->kebutuhan_pupuk }}</td><td>{{ $pengajuan->ringkasan_pupuk }}</td></tr>
                <tr><td><strong>C5</strong> · Subsektor</td><td>{{ $pengajuan->subsektor_pertanian }}</td><td>Subkriteria otomatis</td></tr>
            </tbody>
        </table>
    </div>
</div>

@if($pengajuan->catatan_penyuluh)
<div class="app-card mb-4"><div class="p-3"><strong>Catatan Penyuluh</strong><div class="text-muted mt-1">{{ $pengajuan->catatan_penyuluh }}</div></div></div>
@endif

@if(auth()->user()->role==='penyuluh' && in_array($pengajuan->status,['menunggu_verifikasi','perlu_perbaikan']))
    <a href="{{ route('pengajuan.edit',$pengajuan) }}" class="btn btn-warning"><i class="fas fa-edit mr-1"></i>Edit / Perbaiki Pengajuan</a>
@endif

@if(auth()->user()->role==='admin' && in_array($pengajuan->status,['menunggu_verifikasi','perlu_perbaikan']))
    <div class="row mt-4">
        <div class="col-lg-6 mb-4">
            <div class="app-card h-100" style="border:1px solid #bbf7d0;">
                <div class="app-card-header">
                    <div class="app-card-title">1. Verifikasi Berkas C3</div>
                </div>
                <div class="p-3">
                    @if($pengajuan->berkas_persyaratan_path)
                        <a target="_blank" href="{{ route('pengajuan.berkas',$pengajuan) }}" class="btn btn-outline-danger btn-sm mb-3"><i class="fas fa-file-pdf mr-1"></i>Buka PDF Persyaratan</a>
                    @endif
                    <form method="POST" action="{{ route('pengajuan.verify-c3',$pengajuan) }}">
                        @csrf
                        <div class="form-group">
                            <label class="font-weight-bold">Hasil Verifikasi C3</label>
                            <select name="kelengkapan_berkas_verifikasi" class="form-control" required>
                                <option value="">-- Pilih hasil --</option>
                                @foreach($criteriaOptions['C3'] as $opsi)
                                    <option value="{{ $opsi }}" {{ old('kelengkapan_berkas_verifikasi', $pengajuan->kelengkapan_berkas_verifikasi) === $opsi ? 'selected' : '' }}>{{ $opsi }}</option>
                                @endforeach
                            </select>
                            <small class="text-muted">Status dari Penyuluh: <strong>{{ $pengajuan->kelengkapan_berkas }}</strong></small>
                        </div>
                        <div class="form-group">
                            <label>Catatan Verifikasi Berkas <span class="text-muted">(opsional)</span></label>
                            <textarea name="catatan_verifikasi_berkas" rows="2" class="form-control">{{ old('catatan_verifikasi_berkas', $pengajuan->catatan_verifikasi_berkas) }}</textarea>
                        </div>
                        <button class="btn btn-success"><i class="fas fa-save mr-1"></i>{{ $pengajuan->c3_terverifikasi ? 'Perbarui Verifikasi C3' : 'Simpan Verifikasi C3' }}</button>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-6 mb-4">
            <div class="app-card h-100">
                <div class="app-card-header">
                    <div class="app-card-title">2. Keputusan Pengajuan</div>
                </div>
                <div class="p-3">
                    @if($pengajuan->c3_terverifikasi)
                        <div class="alert alert-success py-2"><i class="fas fa-check-circle mr-1"></i>C3 sudah diverifikasi: <strong>{{ $pengajuan->kelengkapan_berkas_verifikasi }}</strong>.</div>
                        <form method="POST" action="{{ route('pengajuan.approve',$pengajuan) }}" class="mb-4">
                            @csrf
                            <div class="form-group"><label>Catatan Persetujuan <span class="text-muted">(opsional)</span></label><textarea name="catatan_admin" class="form-control" rows="2"></textarea></div>
                            <button class="btn btn-success btn-block" onclick="return confirm('Setujui pengajuan ini dan masukkan ke Data Alternatif?')"><i class="fas fa-check mr-1"></i>Setujui Pengajuan</button>
                        </form>
                    @else
                        <div class="alert alert-warning"><i class="fas fa-exclamation-triangle mr-1"></i>Persetujuan belum tersedia sampai verifikasi C3 disimpan.</div>
                    @endif

                    <div class="row">
                        <div class="col-md-6 mb-3 mb-md-0">
                            <form method="POST" action="{{ route('pengajuan.revision',$pengajuan) }}">@csrf
                                <textarea name="catatan_admin" class="form-control mb-2" rows="2" required placeholder="Catatan perbaikan"></textarea>
                                <button class="btn btn-info btn-block">Minta Perbaikan</button>
                            </form>
                        </div>
                        <div class="col-md-6">
                            <form method="POST" action="{{ route('pengajuan.reject',$pengajuan) }}">@csrf
                                <textarea name="catatan_admin" class="form-control mb-2" rows="2" required placeholder="Alasan penolakan"></textarea>
                                <button class="btn btn-danger btn-block" onclick="return confirm('Tolak pengajuan ini?')">Tolak</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endif
</div>
@endsection
