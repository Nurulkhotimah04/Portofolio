@extends('layouts.app')

@section('content')

<style>
    .hasil-page-title {
        font-size: 1.35rem;
        font-weight: 700;
        color: #343a40;
        margin-bottom: 4px;
    }

    .hasil-page-subtitle {
        font-size: 0.82rem;
        color: #858796;
    }

    .hasil-card {
        border: none;
        border-radius: 12px;
        box-shadow: 0 3px 12px rgba(0, 0, 0, 0.07);
        overflow: hidden;
    }

    .hasil-card-header {
        background: linear-gradient(135deg, #198754, #157347);
        color: white;
        padding: 15px 20px;
        border: none;
    }

    .hasil-card-header h6 {
        margin: 0;
        font-size: 0.95rem;
        font-weight: 700;
    }

    .hasil-card-header small {
        opacity: 0.88;
        font-size: 0.72rem;
    }

    .summary-card {
        border: 1px solid #e9ecef;
        border-radius: 10px;
        padding: 18px;
        height: 100%;
        background: #fff;
    }

    .summary-label {
        font-size: 0.72rem;
        color: #858796;
        text-transform: uppercase;
        letter-spacing: 0.4px;
        font-weight: 700;
        margin-bottom: 6px;
    }

    .summary-value {
        font-size: 1.35rem;
        font-weight: 700;
        color: #198754;
    }

    .summary-sub {
        font-size: 0.75rem;
        color: #6c757d;
        margin-top: 3px;
    }

    .hasil-table th,
    .hasil-table td {
        vertical-align: middle !important;
        font-size: 0.8rem;
    }

    .hasil-table thead th {
        background: #f8f9fa;
        color: #495057;
        font-weight: 700;
        white-space: nowrap;
    }

    .kode-badge {
        display: inline-block;
        min-width: 42px;
        padding: 5px 9px;
        border-radius: 6px;
        background: #e9f7ef;
        color: #198754;
        border: 1px solid #cfe9da;
        font-weight: 700;
        font-size: 0.72rem;
    }

    .atribut-benefit {
        display: inline-block;
        padding: 4px 9px;
        border-radius: 20px;
        background: #d1e7dd;
        color: #0f5132;
        font-size: 0.7rem;
        font-weight: 600;
    }

    .atribut-cost {
        display: inline-block;
        padding: 4px 9px;
        border-radius: 20px;
        background: #fff3cd;
        color: #664d03;
        font-size: 0.7rem;
        font-weight: 600;
    }

    .bobot-progress {
        height: 8px;
        border-radius: 20px;
        background: #e9ecef;
        overflow: hidden;
    }

    .bobot-progress-bar {
        height: 100%;
        background: #198754;
        border-radius: 20px;
    }

    .info-box {
        background: #f8f9fa;
        border-left: 4px solid #198754;
        padding: 12px 15px;
        border-radius: 6px;
        font-size: 0.78rem;
        color: #495057;
    }

    .btn-hasil {
        border-radius: 7px;
        font-size: 0.8rem;
        font-weight: 600;
        padding: 8px 15px;
    }

    .empty-result {
        text-align: center;
        padding: 45px 20px;
        color: #858796;
    }

    @media (max-width: 768px) {
        .hasil-table {
            min-width: 800px;
        }
    }
</style>


<div class="container-fluid px-3">

    {{-- ============================================================
         HEADER
    ============================================================ --}}
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4">

        <div>
            <div class="hasil-page-title">
                <i class="fas fa-chart-bar text-success mr-2"></i>
                Hasil Perhitungan ANP
            </div>

            <div class="hasil-page-subtitle">
                Bobot akhir kriteria hasil proses Analytical Network Process.
            </div>
        </div>

        <div class="mt-3 mt-md-0">

            <a href="{{ route('anp.index') }}"
               class="btn btn-outline-secondary btn-hasil mr-2">
                <i class="fas fa-arrow-left mr-1"></i>
                Kembali
            </a>

            <a href="{{ route('vikor.index') }}"
               class="btn btn-success btn-hasil">
                <i class="fas fa-arrow-right mr-1"></i>
                Lanjut ke VIKOR
            </a>

        </div>

    </div>


    {{-- ============================================================
         ALERT
    ============================================================ --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show shadow-sm">
            <i class="fas fa-check-circle mr-2"></i>
            {{ session('success') }}

            <button type="button"
                    class="close"
                    data-dismiss="alert">
                <span>&times;</span>
            </button>
        </div>
    @endif


    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show shadow-sm">
            <i class="fas fa-exclamation-triangle mr-2"></i>
            {{ session('error') }}

            <button type="button"
                    class="close"
                    data-dismiss="alert">
                <span>&times;</span>
            </button>
        </div>
    @endif


    @php
        $hasilTersedia = collect($bobotANP)->filter(function ($nilai) {
            return $nilai !== null;
        });

        $totalBobot = $hasilTersedia->sum();

        $kodeTerbesar = null;
        $bobotTerbesar = null;

        if ($hasilTersedia->isNotEmpty()) {
            $kodeTerbesar = $hasilTersedia
                ->sortDesc()
                ->keys()
                ->first();

            $bobotTerbesar = $hasilTersedia[$kodeTerbesar];
        }

        $kriteriaTerbesar = $kriteria->firstWhere(
            'kode_kriteria',
            $kodeTerbesar
        );
    @endphp


    @if($hasilTersedia->isEmpty())

        {{-- ========================================================
             BELUM ADA HASIL
        ======================================================== --}}
        <div class="card hasil-card">

            <div class="card-body empty-result">

                <i class="fas fa-calculator fa-3x text-gray-300 mb-3"></i>

                <h6 class="font-weight-bold text-gray-700">
                    Hasil ANP Belum Tersedia
                </h6>

                <p class="small mb-3">
                    Silakan lakukan perhitungan ANP terlebih dahulu.
                </p>

                <a href="{{ route('anp.index') }}"
                   class="btn btn-success btn-hasil">

                    <i class="fas fa-calculator mr-1"></i>
                    Mulai Perhitungan ANP

                </a>

            </div>

        </div>

    @else

        {{-- ========================================================
             SUMMARY
        ======================================================== --}}
        <div class="row mb-4">

            <div class="col-md-4 mb-3 mb-md-0">

                <div class="summary-card">

                    <div class="summary-label">
                        Total Bobot
                    </div>

                    <div class="summary-value">
                        {{ number_format($totalBobot, 6) }}
                    </div>

                    <div class="summary-sub">
                        Total ideal = 1.000000
                    </div>

                </div>

            </div>


            <div class="col-md-4 mb-3 mb-md-0">

                <div class="summary-card">

                    <div class="summary-label">
                        Bobot Tertinggi
                    </div>

                    <div class="summary-value">
                        {{ $kodeTerbesar ?? '-' }}
                    </div>

                    <div class="summary-sub">
                        {{ $kriteriaTerbesar->nama_kriteria ?? '-' }}
                    </div>

                </div>

            </div>


            <div class="col-md-4">

                <div class="summary-card">

                    <div class="summary-label">
                        Persentase Tertinggi
                    </div>

                    <div class="summary-value">
                        {{ $bobotTerbesar !== null ? number_format($bobotTerbesar * 100, 2) . '%' : '-' }}
                    </div>

                    <div class="summary-sub">
                        Prioritas terbesar hasil ANP
                    </div>

                </div>

            </div>

        </div>


        {{-- ========================================================
             TABEL HASIL
        ======================================================== --}}
        <div class="card hasil-card mb-4">

            <div class="hasil-card-header">

                <h6>
                    <i class="fas fa-table mr-2"></i>
                    Bobot Akhir Kriteria
                </h6>

                <small>
                    Bobot ini akan digunakan sebagai bobot kriteria pada proses VIKOR.
                </small>

            </div>


            <div class="card-body">

                <div class="table-responsive">

                    <table class="table table-bordered table-hover hasil-table mb-0">

                        <thead>

                            <tr>

                                <th class="text-center" style="width: 7%;">
                                    No
                                </th>

                                <th class="text-center" style="width: 12%;">
                                    Kode
                                </th>

                                <th>
                                    Kriteria
                                </th>

                                <th class="text-center" style="width: 15%;">
                                    Atribut
                                </th>

                                <th class="text-center" style="width: 15%;">
                                    Bobot
                                </th>

                                <th class="text-center" style="width: 15%;">
                                    Persentase
                                </th>

                                <th style="width: 20%;">
                                    Proporsi
                                </th>

                            </tr>

                        </thead>


                        <tbody>

                            @foreach($kriteria as $item)

                                @php
                                    $bobot = $bobotANP[$item->kode_kriteria] ?? null;
                                    $persentase = $bobot !== null
                                        ? $bobot * 100
                                        : 0;
                                @endphp

                                <tr>

                                    <td class="text-center">
                                        {{ $loop->iteration }}
                                    </td>


                                    <td class="text-center">
                                        <span class="kode-badge">
                                            {{ $item->kode_kriteria }}
                                        </span>
                                    </td>


                                    <td class="font-weight-bold text-gray-800">
                                        {{ $item->nama_kriteria }}
                                    </td>


                                    <td class="text-center">

                                        @if($item->atribut === 'benefit')

                                            <span class="atribut-benefit">
                                                Benefit
                                            </span>

                                        @else

                                            <span class="atribut-cost">
                                                Cost
                                            </span>

                                        @endif

                                    </td>


                                    <td class="text-center font-weight-bold">
                                        {{ $bobot !== null ? number_format($bobot, 8) : '-' }}
                                    </td>


                                    <td class="text-center">
                                        {{ $bobot !== null ? number_format($persentase, 2) . '%' : '-' }}
                                    </td>


                                    <td>

                                        <div class="bobot-progress">
                                            <div class="bobot-progress-bar"
                                                 style="width: {{ min($persentase, 100) }}%;">
                                            </div>
                                        </div>

                                    </td>

                                </tr>

                            @endforeach

                        </tbody>


                        <tfoot>

                            <tr class="font-weight-bold bg-light">

                                <td colspan="4"
                                    class="text-right">

                                    TOTAL

                                </td>

                                <td class="text-center">
                                    {{ number_format($totalBobot, 8) }}
                                </td>

                                <td class="text-center">
                                    {{ number_format($totalBobot * 100, 2) }}%
                                </td>

                                <td></td>

                            </tr>

                        </tfoot>

                    </table>

                </div>

            </div>

        </div>


        {{-- ========================================================
             INFORMASI HASIL
        ======================================================== --}}
        <div class="info-box mb-4">

            <strong>
                Penggunaan hasil:
            </strong>

            bobot akhir ANP di atas akan digunakan otomatis sebagai bobot
            C1 sampai C5 pada proses perhitungan VIKOR.

        </div>


        {{-- ========================================================
             ALUR
        ======================================================== --}}
        <div class="card hasil-card mb-4">

            <div class="card-body">

                <div class="font-weight-bold text-gray-800 mb-3">
                    Alur Hasil Perhitungan
                </div>

                <div class="row text-center">

                    <div class="col-md mb-3 mb-md-0">
                        <span class="kode-badge px-3 py-2">
                            Feedback
                        </span>
                    </div>

                    <div class="col-md mb-3 mb-md-0">
                        <i class="fas fa-arrow-right text-muted mr-2"></i>
                        <span class="kode-badge px-3 py-2">
                            Pairwise
                        </span>
                    </div>

                    <div class="col-md mb-3 mb-md-0">
                        <i class="fas fa-arrow-right text-muted mr-2"></i>
                        <span class="kode-badge px-3 py-2">
                            CI & CR
                        </span>
                    </div>

                    <div class="col-md mb-3 mb-md-0">
                        <i class="fas fa-arrow-right text-muted mr-2"></i>
                        <span class="kode-badge px-3 py-2">
                            Supermatrix
                        </span>
                    </div>

                    <div class="col-md">
                        <i class="fas fa-arrow-right text-muted mr-2"></i>
                        <span class="kode-badge px-3 py-2">
                            Bobot Akhir
                        </span>
                    </div>

                </div>

            </div>

        </div>

    @endif

</div>

@endsection