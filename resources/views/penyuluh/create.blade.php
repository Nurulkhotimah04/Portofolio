@extends('layouts.app')
@section('title', 'Tambah Penyuluh')
@section('content')
<a href="{{ route('penyuluh.index') }}" class="app-back-link"><i class="fas fa-arrow-left mr-2"></i>Kembali ke Data Penyuluh</a>

<div class="app-page-header">
    <div>
        <h1 class="app-page-title">Tambah Penyuluh</h1>
    </div>
</div>

@if($errors->any())
    <div class="alert alert-danger app-alert"><ul class="mb-0">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
@endif

<div class="row">
    <div class="col-xl-8 col-lg-10">
        <div class="app-card mb-4">
            <div class="app-card-header">
                <div class="app-card-title">Informasi Penyuluh</div>
            </div>
            <div class="p-4">
                <form method="POST" action="{{ route('penyuluh.store') }}">
                    @csrf
                    <div class="form-group">
                        <label class="font-weight-bold">Nama Penyuluh</label>
                        <input type="text" name="nama_penyuluh" class="form-control" value="{{ old('nama_penyuluh') }}" required>
                    </div>
                    <div class="form-group">
                        <label class="font-weight-bold">Username</label>
                        <input type="text" name="username" class="form-control" value="{{ old('username') }}" required>
                        <small class="text-muted">Email sistem dibuat otomatis dari username.</small>
                    </div>
                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label class="font-weight-bold">Password</label>
                            <input type="password" name="password" class="form-control" required>
                        </div>
                        <div class="form-group col-md-6">
                            <label class="font-weight-bold">Konfirmasi Password</label>
                            <input type="password" name="password_confirmation" class="form-control" required>
                        </div>
                    </div>

                    <hr class="my-4">
                    <h6 class="font-weight-bold mb-3"><i class="fas fa-map-marker-alt text-success mr-2"></i>Penugasan Wilayah</h6>
                    <div class="form-row">
                        <div class="form-group col-md-7">
                            <label class="font-weight-bold">Kecamatan Tugas</label>
                            <select name="kecamatan_id" class="form-control" required>
                                <option value="">-- Pilih Kecamatan --</option>
                                @foreach($kecamatans as $item)
                                    <option value="{{ $item->id }}" {{ (string) old('kecamatan_id') === (string) $item->id ? 'selected' : '' }}>{{ $item->nama_kecamatan }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group col-md-5">
                            <label class="font-weight-bold">Tanggal Mulai Tugas</label>
                            <input type="date" name="tanggal_mulai" class="form-control" value="{{ old('tanggal_mulai', now()->toDateString()) }}" required>
                        </div>
                    </div>
                    <div class="alert alert-light border small mb-0">
                        Kecamatan Poktan yang dibuat untuk Penyuluh ini nantinya mengikuti Kecamatan tugas secara otomatis.
                    </div>

                    <div class="d-flex justify-content-end mt-4">
                        <a href="{{ route('penyuluh.index') }}" class="btn btn-light border mr-2">Batal</a>
                        <button class="btn btn-success px-4"><i class="fas fa-save mr-1"></i>Simpan Penyuluh</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
