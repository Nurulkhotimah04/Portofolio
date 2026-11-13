@extends('layouts.app')
@section('title', 'Tambah Kriteria')

@section('content')
<a href="{{ route('kriteria.index') }}" class="app-back-link">
    <i class="fas fa-arrow-left mr-2"></i>Kembali ke Data Kriteria
</a>

<div class="app-page-header">
    <div>
        <h1 class="app-page-title">Tambah Kriteria</h1>
    </div>
</div>

@if($errors->any())
    <div class="alert alert-danger app-alert">
        <strong>Periksa kembali data:</strong>
        <ul class="mb-0 mt-2">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
    </div>
@endif

<div class="row justify-content-center">
    <div class="col-xl-8 col-lg-9">
        <div class="app-card">
            <div class="app-card-header">
                <div class="app-card-title">Informasi Kriteria</div>
            </div>
            <form action="{{ route('kriteria.store') }}" method="POST">
                @csrf
                <div class="p-4">
                    <div class="form-row">
                        <div class="form-group col-md-4">
                            <label class="font-weight-bold">Kode Kriteria <span class="text-danger">*</span></label>
                            <input type="text" name="kode_kriteria" class="form-control @error('kode_kriteria') is-invalid @enderror"
                                   value="{{ old('kode_kriteria') }}" placeholder="Contoh: C6" required>
                            @error('kode_kriteria')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="form-group col-md-8">
                            <label class="font-weight-bold">Nama Kriteria <span class="text-danger">*</span></label>
                            <input type="text" name="nama_kriteria" class="form-control @error('nama_kriteria') is-invalid @enderror"
                                   value="{{ old('nama_kriteria') }}" placeholder="Nama kriteria" required>
                            @error('nama_kriteria')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group col-md-6 mb-md-0">
                            <label class="font-weight-bold">Bobot <span class="text-danger">*</span></label>
                            <input type="number" name="bobot" step="any" class="form-control @error('bobot') is-invalid @enderror"
                                   value="{{ old('bobot') }}" placeholder="Contoh: 0.125" required>
                            @error('bobot')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            <small class="form-text text-muted">Gunakan nilai numerik sesuai konfigurasi sistem.</small>
                        </div>
                        <div class="form-group col-md-6 mb-0">
                            <label class="font-weight-bold">Atribut <span class="text-danger">*</span></label>
                            <select name="atribut" class="form-control @error('atribut') is-invalid @enderror" required>
                                <option value="">-- Pilih Atribut --</option>
                                <option value="benefit" {{ old('atribut') === 'benefit' ? 'selected' : '' }}>Benefit</option>
                                <option value="cost" {{ old('atribut') === 'cost' ? 'selected' : '' }}>Cost</option>
                            </select>
                            @error('atribut')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>
                </div>
                <div class="px-4 py-3 bg-light text-right">
                    <a href="{{ route('kriteria.index') }}" class="btn btn-light border mr-2">Batal</a>
                    <button class="btn btn-success px-4"><i class="fas fa-save mr-1"></i>Simpan Kriteria</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
