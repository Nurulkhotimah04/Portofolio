@extends('layouts.app')

@section('content')

<style>

    .process-title {
        font-size: 1.35rem;
        font-weight: 700;
        color: #343a40;
    }

    .process-subtitle {
        font-size: .82rem;
        color: #858796;
    }

    .process-card {
        border: none;
        border-radius: 12px;
        box-shadow: 0 3px 12px rgba(0,0,0,.07);
        overflow: hidden;
    }

    .process-header {
        background: linear-gradient(135deg, #198754, #157347);
        color: white;
        padding: 14px 18px;
    }

    .table-process {
        font-size: .73rem;
    }

    .table-process th,
    .table-process td {
        vertical-align: middle !important;
        text-align: center;
        white-space: nowrap;
    }

    .kode-badge {
        background: #e9f7ef;
        color: #198754;
        padding: 5px 8px;
        border-radius: 6px;
        font-weight: 700;
    }

</style>


<div class="container-fluid px-3">

    {{-- HEADER --}}

    <div class="d-flex justify-content-between align-items-center mb-4">

        <div>

            <div class="process-title">

                <i class="fas fa-calculator text-success mr-2"></i>

                Proses Perhitungan VIKOR

            </div>


            <div class="process-subtitle">

                Detail perhitungan VIKOR pada scope yang dipilih.

            </div>

        </div>


        <a href="{{ route('vikor.index') }}"
           class="btn btn-outline-secondary">

            <i class="fas fa-arrow-left mr-1"></i>

            Kembali

        </a>

    </div>


    {{-- SCOPE --}}

    <div class="card process-card mb-4">

        <div class="card-body">

            <div class="row">

                <div class="col-md-3">

                    <small class="text-muted">
                        Musim Tanam
                    </small>

                    <div class="font-weight-bold">

                        {{ $musimTanam->kode_musim }}
                        -
                        {{ $musimTanam->nama_musim }}

                    </div>

                </div>


                <div class="col-md-3">

                    <small class="text-muted">
                        Tahun
                    </small>

                    <div class="font-weight-bold">

                        {{ $periode->tahun }}

                    </div>

                </div>


                <div class="col-md-3">

                    <small class="text-muted">
                        Kecamatan
                    </small>

                    <div class="font-weight-bold">

                        {{ $kecamatan->nama_kecamatan }}

                    </div>

                </div>


                <div class="col-md-3">

                    <small class="text-muted">
                        Kuota Penerima
                    </small>

                    <div class="font-weight-bold text-success">

                        {{ $kuota }} Petani

                    </div>

                </div>

            </div>

        </div>

    </div>


    {{-- SUMMARY --}}

    <div class="row mb-4">

        <div class="col-md-3 mb-3">

            <div class="card process-card h-100">

                <div class="card-body">

                    <small class="text-muted">
                        Jumlah Alternatif
                    </small>

                    <h4 class="font-weight-bold text-success mb-0">

                        {{ count($alternatif) }}

                    </h4>

                </div>

            </div>

        </div>


        <div class="col-md-3 mb-3">

            <div class="card process-card h-100">

                <div class="card-body">

                    <small class="text-muted">
                        Nilai v
                    </small>

                    <h4 class="font-weight-bold text-success mb-0">

                        {{ number_format($v, 2) }}

                    </h4>

                </div>

            </div>

        </div>


        <div class="col-md-3 mb-3">

            <div class="card process-card h-100">

                <div class="card-body">

                    <small class="text-muted">
                        S Min / S Max
                    </small>

                    <div class="font-weight-bold text-success">

                        {{ number_format($sMinus, 6) }}
                        /
                        {{ number_format($sPlus, 6) }}

                    </div>

                </div>

            </div>

        </div>


        <div class="col-md-3 mb-3">

            <div class="card process-card h-100">

                <div class="card-body">

                    <small class="text-muted">
                        R Min / R Max
                    </small>

                    <div class="font-weight-bold text-success">

                        {{ number_format($rMinus, 6) }}
                        /
                        {{ number_format($rPlus, 6) }}

                    </div>

                </div>

            </div>

        </div>

    </div>


    {{-- ========================================================= --}}
    {{-- 1. BOBOT ANP --}}
    {{-- ========================================================= --}}

    <div class="card process-card mb-4">

        <div class="process-header">

            <strong>

                1. Bobot Kriteria Hasil ANP

            </strong>

        </div>


        <div class="card-body">

            <div class="table-responsive">

                <table class="table table-bordered table-process">

                    <thead>

                        <tr>

                            <th>Keterangan</th>

                            @foreach($kriteria as $krit)

                                <th>

                                    {{ $krit->kode_kriteria }}

                                </th>

                            @endforeach

                        </tr>

                    </thead>


                    <tbody>

                        <tr>

                            <th>Bobot</th>


                            @foreach($kriteria as $krit)

                                <td>

                                    {{
                                        number_format(
                                            $bobot[$krit->kode_kriteria],
                                            8
                                        )
                                    }}

                                </td>

                            @endforeach

                        </tr>


                        <tr>

                            <th>Atribut</th>


                            @foreach($kriteria as $krit)

                                <td>

                                    {{ strtoupper($krit->atribut) }}

                                </td>

                            @endforeach

                        </tr>

                    </tbody>

                </table>

            </div>

        </div>

    </div>


    {{-- ========================================================= --}}
    {{-- 2. MATRIKS SKOR --}}
    {{-- ========================================================= --}}

    <div class="card process-card mb-4">

        <div class="process-header">

            <strong>

                2. Matriks Keputusan / Skor Subkriteria

            </strong>

        </div>


        <div class="card-body">

            <div class="table-responsive">

                <table class="table table-bordered table-process">

                    <thead>

                        <tr>

                            <th>No</th>

                            <th>Kode</th>

                            <th>Nama Petani</th>

                            @foreach($kriteria as $krit)

                                <th>

                                    {{ $krit->kode_kriteria }}

                                </th>

                            @endforeach

                        </tr>

                    </thead>


                    <tbody>

                    @foreach($alternatif as $item)

                        <tr>

                            <td>

                                {{ $loop->iteration }}

                            </td>


                            <td>

                                <span class="kode-badge">

                                    {{ $item['kode'] }}

                                </span>

                            </td>


                            <td class="text-left">

                                {{ $item['petani']->nama_petani }}

                            </td>


                            @foreach($kriteria as $krit)

                                <td>

                                    {{
                                        number_format(
                                            $item['skor'][$krit->kode_kriteria],
                                            2
                                        )
                                    }}

                                </td>

                            @endforeach

                        </tr>

                    @endforeach

                    </tbody>

                </table>

            </div>

        </div>

    </div>


    {{-- ========================================================= --}}
    {{-- 3. MAX MIN --}}
    {{-- ========================================================= --}}

    <div class="card process-card mb-4">

        <div class="process-header">

            <strong>

                3. Nilai Maksimum, Minimum, dan Selisih

            </strong>

        </div>


        <div class="card-body">

            <div class="table-responsive">

                <table class="table table-bordered table-process">

                    <thead>

                        <tr>

                            <th>Keterangan</th>

                            @foreach($kriteria as $krit)

                                <th>

                                    {{ $krit->kode_kriteria }}

                                </th>

                            @endforeach

                        </tr>

                    </thead>


                    <tbody>

                        <tr>

                            <th>MAX</th>

                            @foreach($kriteria as $krit)

                                <td>

                                    {{
                                        number_format(
                                            $nilaiMax[$krit->kode_kriteria],
                                            4
                                        )
                                    }}

                                </td>

                            @endforeach

                        </tr>


                        <tr>

                            <th>MIN</th>

                            @foreach($kriteria as $krit)

                                <td>

                                    {{
                                        number_format(
                                            $nilaiMin[$krit->kode_kriteria],
                                            4
                                        )
                                    }}

                                </td>

                            @endforeach

                        </tr>


                        <tr>

                            <th>MAX - MIN</th>

                            @foreach($kriteria as $krit)

                                <td>

                                    {{
                                        number_format(
                                            $selisih[$krit->kode_kriteria],
                                            4
                                        )
                                    }}

                                </td>

                            @endforeach

                        </tr>

                    </tbody>

                </table>

            </div>

        </div>

    </div>


    {{-- ========================================================= --}}
    {{-- 4. NORMALISASI --}}
    {{-- ========================================================= --}}

    <div class="card process-card mb-4">

        <div class="process-header">

            <strong>

                4. Matriks Normalisasi

            </strong>

        </div>


        <div class="card-body">

            <div class="table-responsive">

                <table class="table table-bordered table-process">

                    <thead>

                        <tr>

                            <th>Kode</th>

                            <th>Nama</th>

                            @foreach($kriteria as $krit)

                                <th>

                                    {{ $krit->kode_kriteria }}

                                </th>

                            @endforeach

                        </tr>

                    </thead>


                    <tbody>

                    @foreach($alternatif as $item)

                        <tr>

                            <td>

                                {{ $item['kode'] }}

                            </td>


                            <td class="text-left">

                                {{ $item['petani']->nama_petani }}

                            </td>


                            @foreach($kriteria as $krit)

                                <td>

                                    {{
                                        number_format(
                                            $item['normalisasi'][$krit->kode_kriteria],
                                            8
                                        )
                                    }}

                                </td>

                            @endforeach

                        </tr>

                    @endforeach

                    </tbody>

                </table>

            </div>

        </div>

    </div>


    {{-- ========================================================= --}}
    {{-- 5. TERBOBOT + S R --}}
    {{-- ========================================================= --}}

    <div class="card process-card mb-4">

        <div class="process-header">

            <strong>

                5. Matriks Terbobot, Nilai S dan R

            </strong>

        </div>


        <div class="card-body">

            <div class="table-responsive">

                <table class="table table-bordered table-process">

                    <thead>

                        <tr>

                            <th>Kode</th>

                            <th>Nama</th>

                            @foreach($kriteria as $krit)

                                <th>

                                    {{ $krit->kode_kriteria }}

                                </th>

                            @endforeach

                            <th>S</th>

                            <th>R</th>

                        </tr>

                    </thead>


                    <tbody>

                    @foreach($alternatif as $item)

                        <tr>

                            <td>

                                {{ $item['kode'] }}

                            </td>


                            <td class="text-left">

                                {{ $item['petani']->nama_petani }}

                            </td>


                            @foreach($kriteria as $krit)

                                <td>

                                    {{
                                        number_format(
                                            $item['terbobot'][$krit->kode_kriteria],
                                            8
                                        )
                                    }}

                                </td>

                            @endforeach


                            <td>

                                {{
                                    number_format(
                                        $item['nilai_s'],
                                        8
                                    )
                                }}

                            </td>


                            <td>

                                {{
                                    number_format(
                                        $item['nilai_r'],
                                        8
                                    )
                                }}

                            </td>

                        </tr>

                    @endforeach

                    </tbody>

                </table>

            </div>

        </div>

    </div>


    {{-- ========================================================= --}}
    {{-- 6. Q + RANKING + KUOTA --}}
    {{-- ========================================================= --}}

    <div class="card process-card mb-4">

        <div class="process-header">

            <strong>

                6. Nilai Q, Ranking, dan Status Kuota

            </strong>

        </div>


        <div class="card-body">

            <div class="alert alert-light border">

                Ranking

                <strong>
                    1 sampai {{ $kuota }}
                </strong>

                berstatus

                <span class="badge badge-success">

                    Masuk Kuota

                </span>.

                Ranking setelahnya berstatus

                <span class="badge badge-secondary">

                    Di Luar Kuota

                </span>.

            </div>


            <div class="table-responsive">

                <table class="table table-bordered table-process">

                    <thead>

                        <tr>

                            <th>Ranking</th>

                            <th>Kode</th>

                            <th>NIK</th>

                            <th>Nama Petani</th>

                            <th>Poktan</th>

                            <th>QS</th>

                            <th>QR</th>

                            <th>Q</th>

                            <th>Status Kuota</th>

                        </tr>

                    </thead>


                    <tbody>

                    @foreach($ranking as $item)

                        <tr class="{{ $item['ranking'] <= $kuota ? 'table-success' : '' }}">

                            <td>

                                <strong>

                                    {{ $item['ranking'] }}

                                </strong>

                            </td>


                            <td>

                                {{ $item['kode'] }}

                            </td>


                            <td>

                                {{ $item['petani']->nik }}

                            </td>


                            <td class="text-left">

                                {{ $item['petani']->nama_petani }}

                            </td>


                            <td class="text-left">

                                {{
                                    optional(
                                        $item['alternatif']->poktan
                                    )->nama_poktan ?? '-'
                                }}

                            </td>


                            <td>

                                {{
                                    number_format(
                                        $item['nilai_qs'],
                                        8
                                    )
                                }}

                            </td>


                            <td>

                                {{
                                    number_format(
                                        $item['nilai_qr'],
                                        8
                                    )
                                }}

                            </td>


                            <td>

                                <strong>

                                    {{
                                        number_format(
                                            $item['nilai_q'],
                                            8
                                        )
                                    }}

                                </strong>

                            </td>


                            <td>

                                @if(
                                    $item['status_kuota']
                                    ===
                                    'masuk_kuota'
                                )

                                    <span class="badge badge-success">

                                        Masuk Kuota

                                    </span>

                                @else

                                    <span class="badge badge-secondary">

                                        Di Luar Kuota

                                    </span>

                                @endif

                            </td>

                        </tr>

                    @endforeach

                    </tbody>

                </table>

            </div>

        </div>

    </div>


    {{-- SAVE --}}

    <form method="POST"
          action="{{ route('vikor.hitung') }}">

        @csrf


        <input type="hidden"
               name="musim_tanam_id"
               value="{{ $musimTanam->id }}">


        <input type="hidden"
               name="periode_id"
               value="{{ $periode->id }}">


        <input type="hidden"
               name="kecamatan_id"
               value="{{ $kecamatan->id }}">


        <button type="submit"
                class="btn btn-success"
                onclick="return confirm('Simpan hasil VIKOR untuk scope ini?')">

            <i class="fas fa-save mr-1"></i>

            Simpan Hasil VIKOR

        </button>


        <a href="{{ route('vikor.index') }}"
           class="btn btn-secondary">

            Batal

        </a>

    </form>

</div>

@endsection