@extends('layouts.app')
@section('title', 'Tambah Subkriteria')

@section('content')
<a href="{{ route('subkriteria.index') }}" class="app-back-link"><i class="fas fa-arrow-left mr-2"></i>Kembali ke Data Subkriteria</a>
<div class="app-page-header">
    <div><h1 class="app-page-title">Tambah Subkriteria</h1></div>
</div>

@if($errors->any())
    <div class="alert alert-danger app-alert"><strong>Periksa kembali data:</strong><ul class="mb-0 mt-2">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
@endif

<div class="row justify-content-center">
    <div class="col-xl-8 col-lg-9">
        <div class="app-card">
            <div class="app-card-header"><div class="app-card-title">Informasi Subkriteria</div></div>
            <form action="{{ route('subkriteria.store') }}" method="POST">
                @csrf
                <div class="p-4">
                    <div class="form-group">
                        <label class="font-weight-bold">Kriteria <span class="text-danger">*</span></label>
                        <select name="kriteria_id" class="form-control @error('kriteria_id') is-invalid @enderror" required>
                            <option value="">-- Pilih Kriteria --</option>
                            @foreach($kriteria as $item)
                                <option value="{{ $item->id }}" {{ (string)old('kriteria_id') === (string)$item->id ? 'selected' : '' }}>
                                    {{ $item->kode_kriteria }} - {{ $item->nama_kriteria }}
                                </option>
                            @endforeach
                        </select>
                        @error('kriteria_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="form-row">
                        <div class="form-group col-md-4">
                            <label class="font-weight-bold">Kode Subkriteria <span class="text-danger">*</span></label>
                            <input type="text" name="kode_subkriteria" class="form-control @error('kode_subkriteria') is-invalid @enderror"
                                   value="{{ old('kode_subkriteria') }}" placeholder="Contoh: S1.4" required>
                            @error('kode_subkriteria')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="form-group col-md-8">
                            <label class="font-weight-bold">Nama Subkriteria <span class="text-danger">*</span></label>
                            <input type="text" name="nama_subkriteria" class="form-control @error('nama_subkriteria') is-invalid @enderror"
                                   value="{{ old('nama_subkriteria') }}" placeholder="Nama pilihan / kategori" required>
                            @error('nama_subkriteria')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>

                    <div class="form-group mb-0">
                        <label class="font-weight-bold">Bobot <span class="text-danger">*</span></label>
                        <input type="number" name="bobot" step="any" class="form-control @error('bobot') is-invalid @enderror"
                               value="{{ old('bobot') }}" placeholder="Masukkan bobot subkriteria" required>
                        @error('bobot')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>
                <div class="px-4 py-3 bg-light text-right">
                    <a href="{{ route('subkriteria.index') }}" class="btn btn-light border mr-2">Batal</a>
                    <button class="btn btn-success px-4"><i class="fas fa-save mr-1"></i>Simpan Subkriteria</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
