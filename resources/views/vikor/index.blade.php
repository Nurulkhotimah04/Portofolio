@extends('layouts.app')
@section('title', 'Perhitungan VIKOR')

@section('content')
<div class="app-page-header">
    <div>
        <h1 class="app-page-title">Perhitungan VIKOR</h1>
    </div>

    <div class="d-flex align-items-center" style="gap:8px;">
        <a href="{{ route('vikor.riwayat') }}" class="btn btn-light border">
            <i class="fas fa-history mr-1"></i>Riwayat
        </a>
        <!-- Tombol filter di header dihapus karena form sudah tampil di halaman utama -->
    </div>
</div>

@if($errors->any())
    <div class="alert alert-danger app-alert">
        <ul class="mb-0">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div class="app-card p-4">
    <form method="POST"
          action="{{ route('vikor.hitung') }}"
          onsubmit="return confirm('Hitung ulang VIKOR untuk data yang dipilih? Hasil ranking scope ini akan diperbarui.');">
        @csrf

        <div class="mb-4">
            <h5 class="font-weight-bold">Pilih Data Perhitungan VIKOR</h5>
            <small class="text-muted">Perhitungan dilakukan untuk satu Musim Tanam, Tahun, dan Kecamatan.</small>
        </div>

        <div class="form-row">
            <div class="form-group col-md-4">
                <label class="font-weight-bold">Musim Tanam <span class="text-danger">*</span></label>
                <select name="musim_tanam_id" class="form-control" required>
                    <option value="">-- Pilih Musim Tanam --</option>
                    @foreach($musimTanam as $item)
                        <option value="{{ $item->id }}" {{ (string) old('musim_tanam_id', request('musim_tanam_id')) === (string) $item->id ? 'selected' : '' }}>
                            {{ $item->kode_musim }} - {{ $item->nama_musim }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="form-group col-md-4">
                <label class="font-weight-bold">Tahun / Periode <span class="text-danger">*</span></label>
                <select name="periode_id" class="form-control" required>
                    <option value="">-- Pilih Tahun --</option>
                    @foreach($periode as $item)
                        <option value="{{ $item->id }}" {{ (string) old('periode_id', request('periode_id')) === (string) $item->id ? 'selected' : '' }}>
                            {{ $item->tahun }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="form-group col-md-4">
                <label class="font-weight-bold">Kecamatan <span class="text-danger">*</span></label>
                <select name="kecamatan_id" class="form-control" required>
                    <option value="">-- Pilih Kecamatan --</option>
                    @foreach($kecamatan as $item)
                        <option value="{{ $item->id }}" {{ (string) old('kecamatan_id', request('kecamatan_id')) === (string) $item->id ? 'selected' : '' }}>
                            {{ $item->nama_kecamatan }}
                        </option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="text-right mt-3">
            <button type="submit" class="btn btn-success px-4">
                <i class="fas fa-calculator mr-1"></i> Hitung VIKOR
            </button>
        </div>
    </form>
</div>
@endsection