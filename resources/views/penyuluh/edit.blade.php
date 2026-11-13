@extends('layouts.app')
@section('title', 'Edit Penyuluh')
@section('content')
<a href="{{ route('penyuluh.index') }}" class="app-back-link"><i class="fas fa-arrow-left mr-2"></i>Kembali ke Data Penyuluh</a>

<div class="app-page-header">
    <div>
        <h1 class="app-page-title">Edit Penyuluh</h1>
    </div>
</div>

@if($errors->any())
    <div class="alert alert-danger app-alert"><ul class="mb-0">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
@endif

@php
    $aktifPoktan = $penyuluh->poktan->where('status', 'aktif')->count();
    $current = $penyuluh->penugasanAktif;
@endphp

<div class="row">
    <div class="col-xl-8 col-lg-10">
        <div class="app-card mb-4">
            <div class="app-card-header">
                <div class="app-card-title">Informasi Penyuluh</div>
            </div>
            <div class="p-4">
                <form method="POST" action="{{ route('penyuluh.update', $penyuluh->id) }}">
                    @csrf @method('PUT')
                    <div class="form-group">
                        <label class="font-weight-bold">Nama Penyuluh</label>
                        <input type="text" name="nama_penyuluh" class="form-control" value="{{ old('nama_penyuluh', $penyuluh->nama_penyuluh) }}" required>
                    </div>
                    <div class="form-group">
                        <label class="font-weight-bold">Username</label>
                        <input type="text" name="username" class="form-control" value="{{ old('username', optional($penyuluh->user)->username) }}" required>
                    </div>
                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label class="font-weight-bold">Password Baru <span class="font-weight-normal text-muted">(opsional)</span></label>
                            <input type="password" name="password" class="form-control">
                        </div>
                        <div class="form-group col-md-6">
                            <label class="font-weight-bold">Konfirmasi Password Baru</label>
                            <input type="password" name="password_confirmation" class="form-control">
                        </div>
                    </div>

                    <hr class="my-4">
                    <h6 class="font-weight-bold mb-3"><i class="fas fa-map-marked-alt text-success mr-2"></i>Status & Penugasan</h6>

                    <div class="alert alert-light border">
                        <div><strong>Penugasan aktif:</strong> {{ $current?->kecamatan?->nama_kecamatan ?? 'Belum ada' }}</div>
                        <div><strong>Poktan aktif:</strong> {{ $aktifPoktan }}</div>
                        @if($aktifPoktan > 0)
                            <small class="text-danger">Untuk pindah Kecamatan, Nonaktif, atau Mutasi Keluar, alihkan/nonaktifkan seluruh Poktan aktif terlebih dahulu.</small>
                        @endif
                    </div>

                    <div class="form-row">
                        <div class="form-group col-md-5">
                            <label class="font-weight-bold">Status Penyuluh</label>
                            <select name="status" id="statusPenyuluh" class="form-control" required>
                                <option value="aktif" {{ old('status', $penyuluh->status) === 'aktif' ? 'selected' : '' }}>Aktif</option>
                                <option value="nonaktif" {{ old('status', $penyuluh->status) === 'nonaktif' ? 'selected' : '' }}>Nonaktif</option>
                                <option value="mutasi_keluar" {{ old('status', $penyuluh->status) === 'mutasi_keluar' ? 'selected' : '' }}>Mutasi Keluar</option>
                            </select>
                        </div>
                        <div class="form-group col-md-7" id="kecamatanGroup">
                            <label class="font-weight-bold">Kecamatan Tugas</label>
                            <select name="kecamatan_id" class="form-control">
                                <option value="">-- Pilih Kecamatan --</option>
                                @foreach($kecamatans as $item)
                                    <option value="{{ $item->id }}" {{ (string) old('kecamatan_id', $current?->kecamatan_id) === (string) $item->id ? 'selected' : '' }}>{{ $item->nama_kecamatan }}</option>
                                @endforeach
                            </select>
                            <small class="text-muted">Jika diganti, penugasan lama ditutup dan riwayat tetap tersimpan.</small>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group col-md-5" id="tanggalGroup">
                            <label class="font-weight-bold">Tanggal Mulai Penugasan Baru</label>
                            <input type="date" name="tanggal_mulai" class="form-control" value="{{ old('tanggal_mulai', now()->toDateString()) }}">
                        </div>
                        <div class="form-group col-md-7">
                            <label class="font-weight-bold">Catatan Penugasan <span class="font-weight-normal text-muted">(opsional)</span></label>
                            <input type="text" name="catatan_penugasan" class="form-control" value="{{ old('catatan_penugasan') }}" placeholder="Contoh: mutasi tugas / perubahan wilayah">
                        </div>
                    </div>

                    @if($penyuluh->penugasan->isNotEmpty())
                        <div class="mt-3 mb-2 font-weight-bold">Riwayat Penugasan</div>
                        <div class="table-responsive border rounded">
                            <table class="table table-sm mb-0">
                                <thead class="thead-light"><tr><th>Kecamatan</th><th>Mulai</th><th>Selesai</th><th>Status</th></tr></thead>
                                <tbody>
                                @foreach($penyuluh->penugasan->sortByDesc('tanggal_mulai') as $riwayat)
                                    <tr>
                                        <td>{{ $riwayat->kecamatan?->nama_kecamatan ?? '-' }}</td>
                                        <td>{{ optional($riwayat->tanggal_mulai)->format('d-m-Y') ?? '-' }}</td>
                                        <td>{{ optional($riwayat->tanggal_selesai)->format('d-m-Y') ?? '-' }}</td>
                                        <td>{{ ucfirst($riwayat->status) }}</td>
                                    </tr>
                                @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif

                    <div class="d-flex justify-content-end mt-4">
                        <a href="{{ route('penyuluh.index') }}" class="btn btn-light border mr-2">Batal</a>
                        <button class="btn btn-success px-4"><i class="fas fa-save mr-1"></i>Simpan Perubahan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
(function () {
    const status = document.getElementById('statusPenyuluh');
    const kecamatan = document.getElementById('kecamatanGroup');
    const tanggal = document.getElementById('tanggalGroup');
    function toggleAssignment() {
        const aktif = status.value === 'aktif';
        kecamatan.style.opacity = aktif ? '1' : '.55';
        tanggal.style.opacity = aktif ? '1' : '.55';
    }
    status.addEventListener('change', toggleAssignment);
    toggleAssignment();
})();
</script>
@endpush
@endsection
