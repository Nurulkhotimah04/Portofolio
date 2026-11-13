@extends('layouts.app')

@section('content')
<style>
.form-card {
    border: none;
    border-radius: 16px;
    box-shadow: 0 8px 32px rgba(0,0,0,0.07);
}
.kriteria-card {
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    border-radius: 12px;
    padding: 1.25rem 1.5rem;
    margin-bottom: 1rem;
    transition: border-color 0.2s, box-shadow 0.2s;
}
.kriteria-card:focus-within {
    border-color: #16a34a;
    box-shadow: 0 0 0 3px rgba(22,163,74,0.1);
    background: #fff;
}
.kriteria-number {
    width: 32px; height: 32px;
    background: linear-gradient(135deg, #16a34a, #15803d);
    color: white; border-radius: 50%;
    display: inline-flex; align-items: center; justify-content: center;
    font-weight: 700; font-size: 0.85rem; flex-shrink: 0;
}
.form-select-modern {
    border: 1.5px solid #e2e8f0;
    border-radius: 8px;
    padding: 10px 14px;
    background: white;
    transition: border-color 0.2s;
    width: 100%;
    font-size: 0.9rem;
}
.form-select-modern:focus {
    outline: none;
    border-color: #16a34a;
    box-shadow: 0 0 0 3px rgba(22,163,74,0.15);
}
.petani-header {
    background: linear-gradient(135deg, #16a34a, #064e3b);
    border-radius: 12px;
    color: white;
    padding: 1.5rem 2rem;
    margin-bottom: 1.5rem;
}
.btn-submit {
    background: linear-gradient(135deg, #16a34a, #15803d);
    border: none; color: white;
    padding: 12px 32px; border-radius: 10px;
    font-weight: 700; font-size: 1rem;
    transition: transform 0.2s, box-shadow 0.2s;
}
.btn-submit:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(22,163,74,0.35);
    color: white;
}
</style>

<div class="container-fluid py-4">
    <div class="d-flex align-items-center mb-4">
        <a href="/penilaian" class="btn btn-light btn-sm rounded-pill mr-3 shadow-sm">
            <i class="fas fa-arrow-left mr-1"></i> Kembali
        </a>
        <div>
            <h1 class="h3 mb-0 text-gray-800 font-weight-bold">Form Penilaian Petani</h1>
            <p class="text-muted small mb-0">Isi nilai untuk setiap kriteria berdasarkan kondisi nyata di lapangan</p>
        </div>
    </div>

    <div class="row justify-content-center">
        <div class="col-xl-8 col-lg-10">

            <form action="/penilaian" method="POST">
                @csrf

                {{-- Pilih Petani (tampil jika tidak ada petani yang dipilih dari URL) --}}
                @if(!isset($selectedPetani))
                <div class="form-card card mb-4">
                    <div class="card-body p-4">
                        <label class="font-weight-bold text-gray-800 mb-2"><i class="fas fa-user text-success mr-2"></i>Pilih Petani</label>
                        <select name="petani_id" class="form-select-modern" required>
                            <option value="">-- Pilih Petani yang Akan Dinilai --</option>
                            @foreach($petani as $item)
                                @if($item->luas_lahan)
                                <option value="{{ $item->id }}">
                                    {{ $item->nama_petani }} — {{ $item->jenis_komoditas }}, {{ $item->luas_lahan }} Ha
                                </option>
                                @endif
                            @endforeach
                        </select>
                    </div>
                </div>
                @else
                {{-- Petani sudah dipilih dari tombol "Nilai" langsung --}}
                <input type="hidden" name="petani_id" value="{{ $selectedPetani->id }}">
                <div class="petani-header d-flex align-items-center gap-3 mb-4">
                    <div style="font-size:2.5rem;">🧑‍🌾</div>
                    <div>
                        <h5 class="font-weight-bold mb-0">{{ $selectedPetani->nama_petani }}</h5>
                        <p class="mb-0" style="opacity:0.85;">
                            <i class="fas fa-seedling mr-1"></i>{{ $selectedPetani->jenis_komoditas }} ·
                            <i class="fas fa-map mr-1 ml-2"></i>{{ $selectedPetani->luas_lahan }} Ha ·
                            <i class="fas fa-box mr-1 ml-2"></i>{{ $selectedPetani->kebutuhan_pupuk }}
                        </p>
                        <p class="mb-0 small" style="opacity:0.75;">
                            Berkas: <strong>{{ $selectedPetani->kelengkapan_berkas }}</strong> ·
                            Subsektor: <strong>{{ $selectedPetani->subsektor_pertanian }}</strong>
                        </p>
                    </div>
                </div>
                @endif

                {{-- Kriteria Penilaian --}}
                <div class="form-card card">
                    <div class="card-header bg-white border-0 py-4 px-4">
                        <h6 class="font-weight-bold text-gray-800 mb-0">
                            <i class="fas fa-clipboard-check text-success mr-2"></i>Penilaian 5 Kriteria
                        </h6>
                        <p class="text-muted small mb-0 mt-1">Pilih subkriteria yang paling sesuai dengan kondisi nyata petani di lapangan</p>
                    </div>
                    <div class="card-body px-4 pb-4">

                        @foreach($kriteria as $index => $k)
                        <div class="kriteria-card">
                            <div class="d-flex align-items-center gap-3 mb-3">
                                <span class="kriteria-number">{{ $index + 1 }}</span>
                                <div>
                                    <div class="font-weight-bold text-gray-800">{{ $k->nama_kriteria }}</div>
                                    <div class="text-muted small">{{ $k->kode_kriteria }} · Bobot: {{ $k->bobot }}</div>
                                </div>
                            </div>
                            <select name="subkriteria[{{ $k->id }}]" class="form-select-modern" required>
                                <option value="">-- Pilih Subkriteria --</option>
                                @foreach($k->subkriteria as $sub)
                                <option value="{{ $sub->id }}">
                                    {{ $sub->nama_subkriteria }} — Skor {{ $sub->bobot }}
                                </option>
                                @endforeach
                            </select>
                        </div>
                        @endforeach

                        <div class="d-flex justify-content-between align-items-center mt-4 pt-3 border-top">
                            <a href="/penilaian" class="btn btn-light rounded-pill px-4">
                                <i class="fas fa-times mr-1"></i> Batal
                            </a>
                            <button type="submit" class="btn-submit">
                                <i class="fas fa-save mr-2"></i>Simpan Penilaian
                            </button>
                        </div>
                    </div>
                </div>

            </form>
        </div>
    </div>
</div>
@endsection