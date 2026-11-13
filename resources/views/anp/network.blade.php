@extends('layouts.app')

@section('content')
<style>
    .custom-container {
        max-width: 1050px;
        margin: 0 auto;
    }
    .anp-card {
        border: none;
        border-radius: 12px;
        box-shadow: 0 4px 15px rgba(0,0,0,0.05);
        overflow: hidden;
    }
    .anp-header {
        background: linear-gradient(135deg, #0284c7, #1e3a8a); /* Warna biru untuk membedakan tahapan */
        color: white;
        padding: 1.2rem 1.5rem;
    }
    .matrix-table th {
        background: #f8fafc;
        color: #334155;
        font-weight: 600;
        text-align: center;
        border-bottom: 2px solid #e2e8f0;
        font-size: 0.9rem;
        vertical-align: middle;
    }
    .matrix-table td {
        vertical-align: middle;
        text-align: center;
        padding: 0.8rem !important;
        transition: background-color 0.2s;
    }
    .matrix-table td:hover {
        background-color: #f1f5f9;
    }
    .header-row {
        background: #f8fafc;
        color: #334155;
        font-weight: 600;
        text-align: left !important;
        font-size: 0.85rem;
        width: 25%;
    }
    .diagonal-cell {
        background: #e2e8f0 !important;
        color: #94a3b8;
        font-weight: 600;
    }
    /* Kustomisasi Checkbox agar lebih besar dan jelas */
    .custom-checkbox .custom-control-label::before {
        width: 1.5rem;
        height: 1.5rem;
        border-radius: 4px;
        border: 2px solid #cbd5e1;
        cursor: pointer;
    }
    .custom-checkbox .custom-control-label::after {
        width: 1.5rem;
        height: 1.5rem;
        cursor: pointer;
    }
    .custom-control-input:checked ~ .custom-control-label::before {
        background-color: #0284c7;
        border-color: #0284c7;
    }
    .btn-anp {
        background: linear-gradient(135deg, #0284c7, #0369a1);
        color: white; 
        border: none;
        padding: 12px 24px;
        border-radius: 8px;
        font-weight: 600; 
        font-size: 1rem;
        transition: all 0.3s ease;
        width: 100%;
    }
    .btn-anp:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(2,132,199,0.3);
        color: white;
    }
</style>

<div class="container custom-container py-4">

    <div class="mb-4">
        <h1 class="h4 font-weight-bold text-gray-800 mb-1">Keterkaitan Antar Kriteria (Inner Dependence)</h1>
        <p class="text-muted small mb-0">Centang kotak jika kriteria pada <strong>Kolom</strong> mempengaruhi kriteria pada <strong>Baris</strong>.</p>
    </div>

    <div class="anp-card card mb-4">
        <div class="anp-header d-flex align-items-center">
            <div style="font-size:1.5rem;" class="mr-3"><i class="fas fa-network-wired"></i></div>
            <div>
                <h6 class="mb-0 font-weight-bold">Matriks Jaringan Kriteria (0 dan 1)</h6>
                <p class="mb-0 text-white-50" style="font-size: 0.8rem;">Tahap penentuan struktur penyusun Supermatriks</p>
            </div>
        </div>
        
        <div class="card-body p-4">
            <form action="{{ route('anp.simpan_network') }}" method="POST">
                @csrf
                
                <div class="table-responsive mb-4">
                    <table class="table table-bordered matrix-table mb-0">
                        <thead>
                            <tr>
                                <th>Kriteria yang Dipengaruhi &darr; \ Mempengaruhi &rarr;</th>
                                @foreach($kriteria as $kolom)
                                    <th title="{{ $kolom->nama_kriteria }}">{{ $kolom->kode_kriteria }}</th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($kriteria as $i => $baris)
                                <tr>
                                    <td class="header-row">
                                        {{ $baris->kode_kriteria }} - {{ $baris->nama_kriteria }}
                                    </td>
                                    
                                    @foreach($kriteria as $j => $kolom)
                                        @if($baris->id == $kolom->id)
                                            {{-- Sel Diagonal (Kriteria terhadap dirinya sendiri) dinonaktifkan / 0 --}}
                                            <td class="diagonal-cell text-center" title="Tidak ada self-feedback">
                                                -
                                            </td>
                                        @else
                                            {{-- Input Checkbox untuk Keterkaitan --}}
                                            <td>
                                                <div class="custom-control custom-checkbox d-flex justify-content-center">
                                                    {{-- Kita set value="1" jika dicentang. Jika tidak, data tidak akan terkirim --}}
                                                    <input type="checkbox" 
                                                           class="custom-control-input" 
                                                           id="net_{{$baris->id}}_{{$kolom->id}}" 
                                                           name="network[{{$baris->id}}][{{$kolom->id}}]" 
                                                           value="1">
                                                    <label class="custom-control-label" for="net_{{$baris->id}}_{{$kolom->id}}"></label>
                                                </div>
                                            </td>
                                        @endif
                                    @endforeach
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="row justify-content-center">
                    <div class="col-md-6">
                        <button type="submit" class="btn-anp">
                            <i class="fas fa-save mr-2"></i> Simpan Jaringan & Lanjut Isi Bobot
                        </button>
                    </div>
                </div>

            </form>
        </div>
    </div>
</div>
@endsection