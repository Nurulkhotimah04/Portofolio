@extends('layouts.app')

@section('content')

<style>
    .anp-page-title {
        font-size: 1.35rem;
        font-weight: 700;
        color: #343a40;
        margin-bottom: 4px;
    }

    .anp-page-subtitle {
        font-size: 0.82rem;
        color: #858796;
    }

    .anp-card {
        border: none;
        border-radius: 12px;
        box-shadow: 0 3px 12px rgba(0, 0, 0, 0.07);
        overflow: hidden;
    }

    .anp-card-header {
        background: linear-gradient(135deg, #198754, #157347);
        color: white;
        padding: 15px 20px;
        border: none;
    }

    .anp-card-header h6 {
        margin: 0;
        font-size: 0.95rem;
        font-weight: 700;
    }

    .anp-card-header small {
        opacity: 0.88;
        font-size: 0.72rem;
    }

    .step-badge {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 30px;
        height: 30px;
        border-radius: 50%;
        background: rgba(255,255,255,0.18);
        font-weight: 700;
        margin-right: 10px;
    }

    .network-table th,
    .network-table td,
    .pairwise-table th,
    .pairwise-table td {
        vertical-align: middle !important;
        text-align: center;
        font-size: 0.78rem;
    }

    .network-table thead th,
    .pairwise-table thead th {
        background: #f8f9fa;
        color: #495057;
        font-weight: 700;
        white-space: nowrap;
    }

    .network-checkbox {
        width: 18px;
        height: 18px;
        cursor: pointer;
    }

    .self-cell {
        background: #f1f3f5;
        color: #adb5bd;
        font-weight: 700;
    }

    .pairwise-group {
        border: 1px solid #e9ecef;
        border-radius: 10px;
        margin-bottom: 20px;
        overflow: hidden;
    }

    .pairwise-group-header {
        background: #f8f9fa;
        padding: 12px 15px;
        border-bottom: 1px solid #e9ecef;
    }

    .pairwise-group-header strong {
        color: #198754;
    }

    .pairwise-select {
        min-width: 95px;
        font-size: 0.78rem;
    }

    .status-box {
        border-radius: 8px;
        padding: 10px 12px;
        font-size: 0.78rem;
        margin-top: 10px;
    }

    .status-success {
        background: #d1e7dd;
        color: #0f5132;
        border: 1px solid #badbcc;
    }

    .status-danger {
        background: #f8d7da;
        color: #842029;
        border: 1px solid #f5c2c7;
    }

    .status-muted {
        background: #f8f9fa;
        color: #6c757d;
        border: 1px solid #e9ecef;
    }

    .info-box {
        background: #f8f9fa;
        border-left: 4px solid #198754;
        padding: 12px 15px;
        border-radius: 6px;
        font-size: 0.78rem;
        color: #495057;
    }

    .btn-anp {
        border-radius: 7px;
        font-size: 0.8rem;
        font-weight: 600;
        padding: 8px 15px;
    }

    .criteria-chip {
        display: inline-block;
        padding: 4px 8px;
        margin: 2px;
        border-radius: 20px;
        background: #e9f7ef;
        color: #198754;
        font-size: 0.7rem;
        font-weight: 600;
    }

    @media (max-width: 768px) {
        .network-table,
        .pairwise-table {
            min-width: 720px;
        }
    }
</style>


<div class="container-fluid px-3">

    {{-- ============================================================
         HEADER
    ============================================================ --}}
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4">

        <div>
            <div class="anp-page-title">
                <i class="fas fa-project-diagram text-success mr-2"></i>
                Perhitungan ANP
            </div>

            <div class="anp-page-subtitle">
                Tentukan hubungan antar kriteria, isi perbandingan berpasangan, lalu lakukan perhitungan ANP.
            </div>
        </div>

        <div class="mt-3 mt-md-0">
            <a href="{{ route('anp.hasil') }}"
               class="btn btn-outline-success btn-anp">
                <i class="fas fa-chart-bar mr-1"></i>
                Lihat Hasil Bobot
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


    @if($errors->any())
        <div class="alert alert-danger shadow-sm">
            <strong>Terjadi kesalahan:</strong>

            <ul class="mb-0 mt-2">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif


    {{-- ============================================================
         INFORMASI KRITERIA
    ============================================================ --}}
    <div class="info-box mb-4">

        <div class="font-weight-bold mb-2">
            Kriteria yang digunakan
        </div>

        @foreach($kriteria as $item)
            <span class="criteria-chip">
                {{ $item->kode_kriteria }} - {{ $item->nama_kriteria }}
            </span>
        @endforeach

    </div>


    {{-- ============================================================
         STEP 1 - FEEDBACK
    ============================================================ --}}
    <div class="card anp-card mb-4">

        <div class="anp-card-header">

            <div class="d-flex align-items-center">

                <span class="step-badge">1</span>

                <div>
                    <h6>Matriks Feedback Antar Kriteria</h6>
                    <small>
                        Centang apabila kriteria pada baris memengaruhi kriteria pada kolom.
                    </small>
                </div>

            </div>

        </div>


        <div class="card-body">

            <form action="{{ route('anp.simpan_network') }}"
                  method="POST">

                @csrf

                <div class="table-responsive">

                    <table class="table table-bordered network-table mb-3">

                        <thead>
                            <tr>
                                <th>Kriteria</th>

                                @foreach($kriteria as $kolom)
                                    <th>
                                        {{ $kolom->kode_kriteria }}
                                    </th>
                                @endforeach
                            </tr>
                        </thead>


                        <tbody>

                            @foreach($kriteria as $baris)

                                <tr>

                                    <th class="text-left">
                                        {{ $baris->kode_kriteria }}
                                        <br>
                                        <small class="text-muted">
                                            {{ $baris->nama_kriteria }}
                                        </small>
                                    </th>


                                    @foreach($kriteria as $kolom)

                                        @if($baris->id == $kolom->id)

                                            <td class="self-cell">
                                                —
                                            </td>

                                        @else

                                            <td>

                                                <input type="hidden"
                                                       name="network[{{ $baris->id }}][{{ $kolom->id }}]"
                                                       value="0">

                                                <input type="checkbox"
                                                       class="network-checkbox"
                                                       name="network[{{ $baris->id }}][{{ $kolom->id }}]"
                                                       value="1"
                                                       {{ isset($network[$baris->id][$kolom->id]) ? 'checked' : '' }}>

                                            </td>

                                        @endif

                                    @endforeach

                                </tr>

                            @endforeach

                        </tbody>

                    </table>

                </div>


                <div class="d-flex justify-content-end">

                    <button type="submit"
                            class="btn btn-success btn-anp">

                        <i class="fas fa-save mr-1"></i>
                        Simpan Feedback

                    </button>

                </div>

            </form>

        </div>

    </div>


    {{-- ============================================================
         STEP 2 - PAIRWISE
    ============================================================ --}}
    <div class="card anp-card mb-4">

        <div class="anp-card-header">

            <div class="d-flex align-items-center">

                <span class="step-badge">2</span>

                <div>
                    <h6>Perbandingan Berpasangan</h6>
                    <small>
                        Bandingkan kriteria yang berada dalam satu kelompok feedback menggunakan skala Saaty.
                    </small>
                </div>

            </div>

        </div>


        <div class="card-body">

            @php
                $adaKelompokPairwise = false;
            @endphp


            <form action="{{ route('anp.hitung') }}"
                  method="POST">

                @csrf


                @foreach($kriteria as $sumber)

                    @php
                        $anggota = $kelompokFeedback[$sumber->id] ?? collect();
                    @endphp


                    @if($anggota->count() > 0)

                        @php
                            $adaKelompokPairwise = true;
                        @endphp


                        <div class="pairwise-group">

                            <div class="pairwise-group-header">

                                <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center">

                                    <div>
                                        <strong>
                                            Kriteria yang dipengaruhi {{ $sumber->kode_kriteria }}
                                        </strong>

                                        <div class="small text-muted mt-1">
                                            {{ $sumber->kode_kriteria }} - {{ $sumber->nama_kriteria }}
                                        </div>
                                    </div>


                                    <div class="mt-2 mt-md-0">

                                        @foreach($anggota as $anggotaItem)
                                            <span class="criteria-chip">
                                                {{ $anggotaItem->kode_kriteria }}
                                            </span>
                                        @endforeach

                                    </div>

                                </div>

                            </div>


                            <div class="p-3">

                                @if($anggota->count() == 1)

                                    <div class="status-box status-muted mb-0">
                                        <i class="fas fa-info-circle mr-1"></i>
                                        Hanya terdapat satu kriteria yaitu
                                        <strong>{{ $anggota->first()->kode_kriteria }}</strong>,
                                        sehingga bobot lokal otomatis bernilai <strong>1</strong>
                                        dan tidak memerlukan perbandingan.
                                    </div>

                                @else

                                    <div class="table-responsive">

                                        <table class="table table-bordered pairwise-table mb-2">

                                            <thead>
                                                <tr>
                                                    <th>Perbandingan</th>
                                                    <th>Nilai Saaty</th>
                                                    <th>Keterangan</th>
                                                </tr>
                                            </thead>


                                            <tbody>

                                                @for($i = 0; $i < $anggota->count(); $i++)

                                                    @for($j = $i + 1; $j < $anggota->count(); $j++)

                                                        @php
                                                            $baris = $anggota[$i];
                                                            $kolom = $anggota[$j];

                                                            $nilaiTersimpan = $perbandingan
                                                                ->first(function ($item) use ($sumber, $baris, $kolom) {
                                                                    return
                                                                        (int) $item->kriteria_dipengaruhi_id === (int) $sumber->id &&
                                                                        (int) $item->kriteria_baris_id === (int) $baris->id &&
                                                                        (int) $item->kriteria_kolom_id === (int) $kolom->id;
                                                                });

                                                            $nilaiSaatIni = $nilaiTersimpan
                                                                ? (float) $nilaiTersimpan->nilai
                                                                : null;
                                                        @endphp


                                                        <tr>

                                                            <td class="font-weight-bold">

                                                                {{ $baris->kode_kriteria }}
                                                                <span class="text-muted mx-1">vs</span>
                                                                {{ $kolom->kode_kriteria }}

                                                            </td>


                                                            <td>

                                                                <select
                                                                    name="pairwise[{{ $sumber->id }}][{{ $baris->id }}][{{ $kolom->id }}]"
                                                                    class="form-control pairwise-select"
                                                                    required>

                                                                    <option value="">
                                                                        -- Pilih --
                                                                    </option>

                                                                    @php
                                                                        $skalaSaaty = [
                                                                            '0.11111111' => '1/9',
                                                                            '0.125'      => '1/8',
                                                                            '0.14285714' => '1/7',
                                                                            '0.16666667' => '1/6',
                                                                            '0.2'        => '1/5',
                                                                            '0.25'       => '1/4',
                                                                            '0.33333333' => '1/3',
                                                                            '0.5'        => '1/2',
                                                                            '1'          => '1',
                                                                            '2'          => '2',
                                                                            '3'          => '3',
                                                                            '4'          => '4',
                                                                            '5'          => '5',
                                                                            '6'          => '6',
                                                                            '7'          => '7',
                                                                            '8'          => '8',
                                                                            '9'          => '9',
                                                                        ];
                                                                    @endphp


                                                                    @foreach($skalaSaaty as $nilai => $label)

                                                                        <option
                                                                            value="{{ $nilai }}"
                                                                            {{ $nilaiSaatIni !== null && abs($nilaiSaatIni - (float) $nilai) < 0.00001 ? 'selected' : '' }}>

                                                                            {{ $label }}

                                                                        </option>

                                                                    @endforeach

                                                                </select>

                                                            </td>


                                                            <td class="text-left text-muted">

                                                                @if($nilaiSaatIni !== null)
                                                                    Nilai tersimpan:
                                                                    <strong>
                                                                        {{ number_format($nilaiSaatIni, 4) }}
                                                                    </strong>
                                                                @else
                                                                    Belum diisi
                                                                @endif

                                                            </td>

                                                        </tr>

                                                    @endfor

                                                @endfor

                                            </tbody>

                                        </table>

                                    </div>

                                @endif


                                @if(isset($konsistensi[$sumber->id]))

                                    @php
                                        $hasilKonsistensi = $konsistensi[$sumber->id];
                                    @endphp


                                    @if($hasilKonsistensi->status_konsisten)

                                        <div class="status-box status-success">

                                            <i class="fas fa-check-circle mr-1"></i>

                                            Konsisten —
                                            λmax:
                                            <strong>{{ number_format($hasilKonsistensi->lambda_max, 6) }}</strong>,
                                            CI:
                                            <strong>{{ number_format($hasilKonsistensi->ci, 6) }}</strong>,
                                            CR:
                                            <strong>{{ number_format($hasilKonsistensi->cr, 6) }}</strong>

                                        </div>

                                    @else

                                        <div class="status-box status-danger">

                                            <i class="fas fa-exclamation-triangle mr-1"></i>

                                            Tidak Konsisten —
                                            λmax:
                                            <strong>{{ number_format($hasilKonsistensi->lambda_max, 6) }}</strong>,
                                            CI:
                                            <strong>{{ number_format($hasilKonsistensi->ci, 6) }}</strong>,
                                            CR:
                                            <strong>{{ number_format($hasilKonsistensi->cr, 6) }}</strong>

                                        </div>

                                    @endif

                                @endif

                            </div>

                        </div>

                    @endif

                @endforeach


                @if(!$adaKelompokPairwise)

                    <div class="text-center py-5 text-muted">

                        <i class="fas fa-project-diagram fa-3x mb-3 text-gray-300"></i>

                        <h6 class="font-weight-bold">
                            Matriks Feedback Belum Diisi
                        </h6>

                        <p class="small mb-0">
                            Isi dan simpan matriks feedback pada langkah 1 terlebih dahulu.
                        </p>

                    </div>

                @else

                    <div class="info-box mb-3">
                        <strong>Catatan:</strong>
                        nilai kebalikan tidak perlu diinput.
                        Sistem akan otomatis membentuk nilai reciprocal,
                        misalnya jika C1 terhadap C2 = 3 maka C2 terhadap C1 = 1/3.
                    </div>


                    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center">

                        <small class="text-muted mb-2 mb-md-0">
                            Perhitungan hanya dapat diteruskan apabila seluruh nilai pairwise memiliki CR ≤ 0,10.
                        </small>


                        <button type="submit"
                                class="btn btn-success btn-anp">

                            <i class="fas fa-calculator mr-1"></i>
                            Hitung ANP

                        </button>

                    </div>

                @endif

            </form>

        </div>

    </div>


    {{-- ============================================================
         ALUR PERHITUNGAN
    ============================================================ --}}
    <div class="card anp-card mb-4">

        <div class="card-body">

            <div class="font-weight-bold text-gray-800 mb-3">
                Alur Perhitungan
            </div>

            <div class="row text-center">

                <div class="col-md mb-3 mb-md-0">
                    <div class="criteria-chip px-3 py-2">
                        Feedback
                    </div>
                </div>

                <div class="col-md mb-3 mb-md-0">
                    <i class="fas fa-arrow-right text-muted"></i>
                    <div class="criteria-chip px-3 py-2">
                        Pairwise
                    </div>
                </div>

                <div class="col-md mb-3 mb-md-0">
                    <i class="fas fa-arrow-right text-muted"></i>
                    <div class="criteria-chip px-3 py-2">
                        CI & CR
                    </div>
                </div>

                <div class="col-md mb-3 mb-md-0">
                    <i class="fas fa-arrow-right text-muted"></i>
                    <div class="criteria-chip px-3 py-2">
                        Supermatrix
                    </div>
                </div>

                <div class="col-md">
                    <i class="fas fa-arrow-right text-muted"></i>
                    <div class="criteria-chip px-3 py-2">
                        Bobot Akhir
                    </div>
                </div>

            </div>

        </div>

    </div>

</div>

@endsection