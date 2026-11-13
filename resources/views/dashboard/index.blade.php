@extends('layouts.app')

@section('content')

<style>

/* =========================================================
   DASHBOARD
   ========================================================= */

.dashboard-page {
    padding-bottom: 35px;
}


/* =========================================================
   WELCOME
   ========================================================= */

.dashboard-welcome {
    position: relative;
    overflow: hidden;

    padding: 25px 28px;
    margin-bottom: 24px;

    border-radius: 16px;

    color: #ffffff;

    background: linear-gradient(
        135deg,
        #0f766e 0%,
        #16a34a 55%,
        #22c55e 100%
    );

    box-shadow:
        0 8px 24px
        rgba(22, 163, 74, 0.18);
}


.dashboard-welcome::before {
    content: "";

    position: absolute;

    width: 170px;
    height: 170px;

    border-radius: 50%;

    background:
        rgba(255,255,255,.07);

    right: -50px;
    top: -65px;
}


.dashboard-welcome::after {
    content: "";

    position: absolute;

    width: 110px;
    height: 110px;

    border-radius: 50%;

    background:
        rgba(255,255,255,.05);

    right: 110px;
    bottom: -70px;
}


.dashboard-welcome-content {
    position: relative;
    z-index: 2;
}


.dashboard-welcome h3 {
    font-weight: 700;
    margin-bottom: 7px;
}


.dashboard-welcome p {
    opacity: .92;
    margin-bottom: 0;
}


.dashboard-date {
    display: inline-flex;
    align-items: center;

    padding: 7px 12px;

    border-radius: 20px;

    background:
        rgba(255,255,255,.15);

    border:
        1px solid
        rgba(255,255,255,.20);

    font-size: .76rem;
}


.dashboard-action {
    position: relative;
    z-index: 2;
}


/* =========================================================
   STAT CARD
   ========================================================= */

.stat-link {
    display: block;
    height: 100%;

    color: inherit !important;
    text-decoration: none !important;
}


.stat-card {
    position: relative;
    overflow: hidden;

    height: 100%;

    background: #ffffff;

    border: none;
    border-radius: 14px;

    box-shadow:
        0 4px 15px
        rgba(0,0,0,.06);

    transition:
        transform .2s ease,
        box-shadow .2s ease;
}


.stat-link:hover .stat-card {
    transform: translateY(-4px);

    box-shadow:
        0 10px 25px
        rgba(0,0,0,.11);
}


.stat-card::after {
    content: "";

    position: absolute;

    left: 0;
    bottom: 0;

    width: 0;
    height: 3px;

    background: #16a34a;

    transition:
        width .2s ease;
}


.stat-link:hover .stat-card::after {
    width: 100%;
}


.stat-body {
    padding: 19px;
}


.stat-content {
    display: flex;

    align-items: center;
    justify-content: space-between;
}


.stat-label {
    font-size: .69rem;

    color: #858796;

    font-weight: 700;

    text-transform: uppercase;

    letter-spacing: .5px;
}


.stat-value {
    margin-top: 7px;

    font-size: 1.65rem;

    line-height: 1.2;

    font-weight: 700;

    color: #343a40;
}


.stat-caption {
    margin-top: 4px;

    font-size: .71rem;

    color: #9ca3af;
}


.stat-icon {
    display: flex;

    width: 52px;
    height: 52px;

    align-items: center;
    justify-content: center;

    border-radius: 12px;

    font-size: 1.3rem;
}


.icon-green {
    background: #dcfce7;
    color: #16a34a;
}


.icon-blue {
    background: #dbeafe;
    color: #2563eb;
}


.icon-yellow {
    background: #fef3c7;
    color: #d97706;
}


.icon-cyan {
    background: #cffafe;
    color: #0891b2;
}


.icon-red {
    background: #fee2e2;
    color: #dc2626;
}


/* =========================================================
   MAIN CARD
   ========================================================= */

.dashboard-card {
    height: 100%;

    overflow: hidden;

    background: #ffffff;

    border: none;
    border-radius: 14px;

    box-shadow:
        0 4px 15px
        rgba(0,0,0,.06);
}


.dashboard-card-header {
    padding: 17px 20px;

    background: #ffffff;

    border-bottom:
        1px solid #edf0f5;
}


.dashboard-card-header h6 {
    margin: 0;

    font-size: .91rem;

    font-weight: 700;

    color: #343a40;
}


.dashboard-card-header small {
    font-size: .70rem;

    color: #858796;
}


.dashboard-card-body {
    padding: 20px;
}


/* =========================================================
   TABLE
   ========================================================= */

.dashboard-table {
    margin: 0;

    font-size: .76rem;
}


.dashboard-table thead th {
    border-top: none;

    background: #f8fafc;

    color: #6b7280;

    font-size: .67rem;

    font-weight: 700;

    text-transform: uppercase;

    white-space: nowrap;

    vertical-align: middle;
}


.dashboard-table tbody td {
    vertical-align: middle;

    padding-top: 11px;
    padding-bottom: 11px;

    border-color: #f1f3f5;
}


.dashboard-table tbody tr:hover {
    background: #f8fffb;
}


/* =========================================================
   RANKING
   ========================================================= */

.rank-circle {
    display: inline-flex;

    width: 31px;
    height: 31px;

    align-items: center;
    justify-content: center;

    border-radius: 50%;

    background: #ecfdf5;

    color: #15803d;

    font-weight: 700;
}


.rank-first {
    background: #fef3c7;

    color: #b45309;
}


/* =========================================================
   STATUS
   ========================================================= */

.status-badge {
    display: inline-block;

    padding: 5px 8px;

    border-radius: 20px;

    font-size: .64rem;

    font-weight: 700;

    white-space: nowrap;
}


.status-menunggu {
    background: #fef3c7;

    color: #92400e;
}


.status-perbaikan {
    background: #cffafe;

    color: #155e75;
}


.status-disetujui {
    background: #dcfce7;

    color: #166534;
}


.status-ditolak {
    background: #fee2e2;

    color: #991b1b;
}


.status-kuota {
    background: #dcfce7;

    color: #166534;
}


.status-luar {
    background: #e5e7eb;

    color: #4b5563;
}


/* =========================================================
   ACTION ALERT
   ========================================================= */

.action-alert {
    padding: 14px 16px;

    margin-bottom: 12px;

    border:
        1px solid #fde68a;

    border-radius: 10px;

    background: #fffbeb;
}


.action-alert:last-child {
    margin-bottom: 0;
}


.action-alert-title {
    color: #78350f;

    font-size: .82rem;

    font-weight: 700;
}


.action-alert-text {
    margin-top: 3px;

    color: #92400e;

    font-size: .72rem;
}


/* =========================================================
   SCOPE
   ========================================================= */

.scope-box {
    display: flex;

    flex-wrap: wrap;

    gap: 7px;

    margin-top: 8px;
}


.scope-item {
    padding: 4px 8px;

    border-radius: 15px;

    background: #ecfdf5;

    color: #166534;

    font-size: .67rem;

    font-weight: 600;
}


/* =========================================================
   BUTTON
   ========================================================= */

.btn-dashboard {
    padding: 6px 11px;

    border-radius: 7px;

    font-size: .72rem;

    font-weight: 600;
}


/* =========================================================
   CHART
   ========================================================= */

.chart-box {
    position: relative;

    width: 100%;
    height: 285px;
}


/* =========================================================
   EMPTY
   ========================================================= */

.empty-dashboard {
    padding: 28px 15px;

    text-align: center;

    color: #9ca3af;

    font-size: .8rem;
}


.empty-dashboard i {
    display: block;

    margin-bottom: 10px;

    color: #d1d5db;

    font-size: 2rem;
}


/* =========================================================
   RESPONSIVE
   ========================================================= */

@media(max-width:768px) {

    .dashboard-welcome {
        padding: 20px;
    }

    .dashboard-action {
        margin-top: 15px;
        text-align: left !important;
    }

    .chart-box {
        height: 240px;
    }
}

</style>



<div class="container-fluid dashboard-page">


    {{-- =========================================================
         WELCOME
    ========================================================= --}}

    <div class="dashboard-welcome">

        <div class="row align-items-center dashboard-welcome-content">


            <div class="col-lg-7">

                <h3>

                    Selamat Datang,
                    {{ auth()->user()->name }}

                </h3>


                @if(auth()->user()->role === 'admin')

                    <p>

                        Pantau pengajuan petani,
                        alternatif per Musim Tanam,
                        proses VIKOR,
                        kuota Kecamatan,
                        dan laporan prioritas penerima subsidi pupuk.

                    </p>

                @else

                    <p>

                        Pantau Poktan binaan,
                        pengajuan petani,
                        dan hasil prioritas penerima subsidi pupuk.

                    </p>

                @endif

            </div>


            <div class="col-lg-5 text-lg-right dashboard-action">

                <div class="mb-2">

                    <span class="dashboard-date">

                        <i class="fas fa-calendar-alt mr-2"></i>

                        {{ now()->format('d M Y') }}

                    </span>

                </div>


                @if(auth()->user()->role === 'admin')

                    <a href="{{ route('vikor.index') }}"
                       class="btn btn-light btn-dashboard">

                        <i class="fas fa-calculator mr-1"></i>

                        VIKOR

                    </a>


                    <a href="{{ route('laporan.index') }}"
                       class="btn btn-outline-light btn-dashboard">

                        <i class="fas fa-file-alt mr-1"></i>

                        Laporan

                    </a>

                @else

                    <a href="{{ route('pengajuan.create') }}"
                       class="btn btn-light btn-dashboard">

                        <i class="fas fa-plus mr-1"></i>

                        Ajukan Petani

                    </a>


                    <a href="{{ route('laporan.index') }}"
                       class="btn btn-outline-light btn-dashboard">

                        <i class="fas fa-file-alt mr-1"></i>

                        Laporan

                    </a>

                @endif

            </div>

        </div>

    </div>



    {{-- =========================================================
         ADMIN
    ========================================================= --}}

    @if(auth()->user()->role === 'admin')


        {{-- =====================================================
             STAT ADMIN
        ====================================================== --}}

        <div class="row mb-4">


            {{-- ALTERNATIF MUSIM --}}

            <div class="col-xl-3 col-md-6 mb-3">

                <a href="{{ route('alternatif-musim.index') }}"
                   class="stat-link">

                    <div class="stat-card">

                        <div class="stat-body">

                            <div class="stat-content">

                                <div>

                                    <div class="stat-label">

                                        Alternatif Musim

                                    </div>


                                    <div class="stat-value">

                                        {{ $totalAlternatif ?? 0 }}

                                    </div>


                                    <div class="stat-caption">

                                        Seluruh Musim & Tahun

                                    </div>

                                </div>


                                <div class="stat-icon icon-green">

                                    <i class="fas fa-users"></i>

                                </div>

                            </div>

                        </div>

                    </div>

                </a>

            </div>


            {{-- PENYULUH --}}

            <div class="col-xl-3 col-md-6 mb-3">

                <a href="{{ route('penyuluh.index') }}"
                   class="stat-link">

                    <div class="stat-card">

                        <div class="stat-body">

                            <div class="stat-content">

                                <div>

                                    <div class="stat-label">

                                        Data Penyuluh

                                    </div>


                                    <div class="stat-value">

                                        {{ $totalPenyuluh ?? 0 }}

                                    </div>


                                    <div class="stat-caption">

                                        Penyuluh terdaftar

                                    </div>

                                </div>


                                <div class="stat-icon icon-blue">

                                    <i class="fas fa-user-tie"></i>

                                </div>

                            </div>

                        </div>

                    </div>

                </a>

            </div>


            {{-- MENUNGGU --}}

            <div class="col-xl-3 col-md-6 mb-3">

                <a href="{{ route(
                        'pengajuan.index',
                        [
                            'status' =>
                                'menunggu_verifikasi'
                        ]
                    ) }}"
                   class="stat-link">

                    <div class="stat-card">

                        <div class="stat-body">

                            <div class="stat-content">

                                <div>

                                    <div class="stat-label">

                                        Menunggu Verifikasi

                                    </div>


                                    <div class="stat-value">

                                        {{ $pengajuanMenunggu ?? 0 }}

                                    </div>


                                    <div class="stat-caption">

                                        Perlu tindakan Admin

                                    </div>

                                </div>


                                <div class="stat-icon icon-yellow">

                                    <i class="fas fa-clock"></i>

                                </div>

                            </div>

                        </div>

                    </div>

                </a>

            </div>


            {{-- DISETUJUI --}}

            <div class="col-xl-3 col-md-6 mb-3">

                <a href="{{ route(
                        'pengajuan.index',
                        [
                            'status' =>
                                'disetujui'
                        ]
                    ) }}"
                   class="stat-link">

                    <div class="stat-card">

                        <div class="stat-body">

                            <div class="stat-content">

                                <div>

                                    <div class="stat-label">

                                        Pengajuan Disetujui

                                    </div>


                                    <div class="stat-value">

                                        {{ $pengajuanDisetujui ?? 0 }}

                                    </div>


                                    <div class="stat-caption">

                                        Telah diverifikasi

                                    </div>

                                </div>


                                <div class="stat-icon icon-cyan">

                                    <i class="fas fa-check-circle"></i>

                                </div>

                            </div>

                        </div>

                    </div>

                </a>

            </div>

        </div>



        {{-- =====================================================
             CHART ADMIN
        ====================================================== --}}

        <div class="row mb-4">


            {{-- STATUS PENGAJUAN --}}

            <div class="col-lg-5 mb-4">

                <div class="dashboard-card">

                    <div class="dashboard-card-header">

                        <h6>

                            <i class="fas fa-chart-pie text-success mr-2"></i>

                            Status Pengajuan Petani

                        </h6>


                        <small>

                            Distribusi seluruh proses pengajuan.

                        </small>

                    </div>


                    <div class="dashboard-card-body">

                        <div class="chart-box">

                            <canvas id="chartStatusAdmin"></canvas>

                        </div>

                    </div>

                </div>

            </div>


            {{-- KOMODITAS --}}

            <div class="col-lg-7 mb-4">

                <div class="dashboard-card">

                    <div class="dashboard-card-header">

                        <h6>

                            <i class="fas fa-chart-bar text-success mr-2"></i>

                            Alternatif Musim Berdasarkan Komoditas

                        </h6>


                        <small>

                            Jumlah alternatif pada setiap jenis komoditas.

                        </small>

                    </div>


                    <div class="dashboard-card-body">

                        <div class="chart-box">

                            <canvas id="chartKomoditas"></canvas>

                        </div>

                    </div>

                </div>

            </div>

        </div>



        {{-- =====================================================
             RANKING + PENGAJUAN
        ====================================================== --}}

        <div class="row">


            {{-- RANKING TERBARU --}}

            <div class="col-xl-6 mb-4">

                <div class="dashboard-card">

                    <div class="dashboard-card-header">

                        <div class="d-flex justify-content-between align-items-center">

                            <div>

                                <h6>

                                    <i class="fas fa-trophy text-warning mr-2"></i>

                                    Prioritas VIKOR Terbaru

                                </h6>


                                @if($rankingScope)

                                    <div class="scope-box">

                                        <span class="scope-item">

                                            {{ $rankingScope['musim'] }}

                                        </span>

                                        <span class="scope-item">

                                            {{ $rankingScope['tahun'] }}

                                        </span>

                                        <span class="scope-item">

                                            {{ $rankingScope['kecamatan'] }}

                                        </span>

                                        @if($rankingScope['kuota'] !== null)

                                            <span class="scope-item">

                                                Kuota:
                                                {{ $rankingScope['kuota'] }}

                                            </span>

                                        @endif

                                    </div>

                                @else

                                    <small>

                                        Belum ada scope VIKOR tersimpan.

                                    </small>

                                @endif

                            </div>


                            @if($rankingScope)

                                <a href="{{ route(
                                        'ranking.index',
                                        [
                                            'musim_tanam_id' =>
                                                $rankingScope['musim_tanam_id'],

                                            'periode_id' =>
                                                $rankingScope['periode_id'],

                                            'kecamatan_id' =>
                                                $rankingScope['kecamatan_id'],
                                        ]
                                    ) }}"
                                   class="btn btn-outline-success btn-dashboard">

                                    Lihat Semua

                                </a>

                            @else

                                <a href="{{ route('vikor.index') }}"
                                   class="btn btn-outline-success btn-dashboard">

                                    Hitung VIKOR

                                </a>

                            @endif

                        </div>

                    </div>


                    <div class="table-responsive">

                        <table class="table dashboard-table">

                            <thead>

                                <tr>

                                    <th class="text-center">
                                        Rank
                                    </th>

                                    <th>
                                        Kode
                                    </th>

                                    <th>
                                        Nama
                                    </th>

                                    <th>
                                        Poktan
                                    </th>

                                    <th class="text-center">
                                        Q
                                    </th>

                                    <th class="text-center">
                                        Status
                                    </th>

                                </tr>

                            </thead>


                            <tbody>

                            @forelse($topRanking ?? [] as $hasil)

                                @php

                                    $masukKuota =
                                        $rankingScope
                                        &&
                                        $rankingScope['kuota']
                                        !==
                                        null
                                        &&
                                        $hasil->ranking
                                        <=
                                        $rankingScope['kuota'];

                                @endphp


                                <tr>

                                    <td class="text-center">

                                        <span class="
                                            rank-circle
                                            {{ $hasil->ranking == 1 ? 'rank-first' : '' }}
                                        ">

                                            {{ $hasil->ranking }}

                                        </span>

                                    </td>


                                    <td>

                                        <strong class="text-success">

                                            {{
                                                optional(
                                                    $hasil->alternatifMusim
                                                )->kode_alternatif ?? '-'
                                            }}

                                        </strong>

                                    </td>


                                    <td>

                                        {{
                                            optional(
                                                $hasil->petani
                                            )->nama_petani ?? '-'
                                        }}

                                    </td>


                                    <td>

                                        {{
                                            optional(
                                                optional(
                                                    $hasil->alternatifMusim
                                                )->poktan
                                            )->nama_poktan ?? '-'
                                        }}

                                    </td>


                                    <td class="text-center">

                                        {{
                                            number_format(
                                                $hasil->nilai_q ?? 0,
                                                6
                                            )
                                        }}

                                    </td>


                                    <td class="text-center">

                                        @if(
                                            $rankingScope
                                            &&
                                            $rankingScope['kuota']
                                            !==
                                            null
                                        )

                                            @if($masukKuota)

                                                <span class="status-badge status-kuota">

                                                    Masuk Kuota

                                                </span>

                                            @else

                                                <span class="status-badge status-luar">

                                                    Di Luar

                                                </span>

                                            @endif

                                        @else

                                            <span class="status-badge status-menunggu">

                                                Kuota -

                                            </span>

                                        @endif

                                    </td>

                                </tr>

                            @empty

                                <tr>

                                    <td colspan="6">

                                        <div class="empty-dashboard">

                                            <i class="fas fa-chart-line"></i>

                                            Belum ada hasil
                                            VIKOR baru.

                                        </div>

                                    </td>

                                </tr>

                            @endforelse

                            </tbody>

                        </table>

                    </div>

                </div>

            </div>



            {{-- PENGAJUAN TERBARU --}}

            <div class="col-xl-6 mb-4">

                <div class="dashboard-card">

                    <div class="dashboard-card-header">

                        <div class="d-flex justify-content-between align-items-center">

                            <div>

                                <h6>

                                    <i class="fas fa-file-signature text-success mr-2"></i>

                                    Pengajuan Terbaru

                                </h6>


                                <small>

                                    Pengajuan terbaru dari Penyuluh.

                                </small>

                            </div>


                            <a href="{{ route('pengajuan.index') }}"
                               class="btn btn-outline-success btn-dashboard">

                                Lihat Semua

                            </a>

                        </div>

                    </div>


                    <div class="table-responsive">

                        <table class="table dashboard-table">

                            <thead>

                                <tr>

                                    <th>
                                        Petani
                                    </th>

                                    <th>
                                        Penyuluh
                                    </th>

                                    <th>
                                        Musim
                                    </th>

                                    <th class="text-center">
                                        Status
                                    </th>

                                    <th class="text-center">
                                        Aksi
                                    </th>

                                </tr>

                            </thead>


                            <tbody>

                            @forelse($pengajuanTerbaru ?? [] as $item)

                                <tr>

                                    <td>

                                        <strong>
                                            {{ $item->nama_petani }}
                                        </strong>

                                        <br>

                                        <small class="text-muted">

                                            {{ $item->nik }}

                                        </small>

                                    </td>


                                    <td>

                                        {{
                                            optional(
                                                $item->penyuluh
                                            )->nama_penyuluh ?? '-'
                                        }}

                                    </td>


                                    <td>

                                        {{
                                            optional(
                                                $item->musimTanam
                                            )->kode_musim ?? '-'
                                        }}

                                        /

                                        {{
                                            optional(
                                                $item->periode
                                            )->tahun ?? '-'
                                        }}

                                    </td>


                                    <td class="text-center">

                                        @if($item->status === 'menunggu_verifikasi')

                                            <span class="status-badge status-menunggu">

                                                Menunggu

                                            </span>

                                        @elseif($item->status === 'perlu_perbaikan')

                                            <span class="status-badge status-perbaikan">

                                                Perbaikan

                                            </span>

                                        @elseif($item->status === 'disetujui')

                                            <span class="status-badge status-disetujui">

                                                Disetujui

                                            </span>

                                        @else

                                            <span class="status-badge status-ditolak">

                                                Ditolak

                                            </span>

                                        @endif

                                    </td>


                                    <td class="text-center">

                                        <a href="{{ route(
                                                'pengajuan.show',
                                                $item->id
                                            ) }}"
                                           class="btn btn-sm btn-outline-success btn-dashboard">

                                            <i class="fas fa-eye"></i>

                                        </a>

                                    </td>

                                </tr>

                            @empty

                                <tr>

                                    <td colspan="5">

                                        <div class="empty-dashboard">

                                            <i class="fas fa-folder-open"></i>

                                            Belum ada pengajuan.

                                        </div>

                                    </td>

                                </tr>

                            @endforelse

                            </tbody>

                        </table>

                    </div>

                </div>

            </div>

        </div>

    @endif



    {{-- =========================================================
         PENYULUH
    ========================================================= --}}

    @if(auth()->user()->role === 'penyuluh')


        @if(!$penyuluh)

            <div class="alert alert-warning">

                <i class="fas fa-exclamation-triangle mr-1"></i>

                Akun Anda belum terhubung dengan
                data Penyuluh.

            </div>

        @endif


        {{-- =====================================================
             STAT PENYULUH
        ====================================================== --}}

        <div class="row mb-4">


            {{-- POKTAN --}}

            <div class="col-xl-3 col-md-6 mb-3">

                <a href="{{ route('poktan.index') }}"
                   class="stat-link">

                    <div class="stat-card">

                        <div class="stat-body">

                            <div class="stat-content">

                                <div>

                                    <div class="stat-label">

                                        Poktan Binaan

                                    </div>


                                    <div class="stat-value">

                                        {{ $totalPoktan ?? 0 }}

                                    </div>


                                    <div class="stat-caption">

                                        Kelompok tani binaan

                                    </div>

                                </div>


                                <div class="stat-icon icon-green">

                                    <i class="fas fa-users-cog"></i>

                                </div>

                            </div>

                        </div>

                    </div>

                </a>

            </div>


            {{-- PETANI BINAAN --}}

            <div class="col-xl-3 col-md-6 mb-3">

                <a href="{{ route('alternatif-musim.index') }}"
                   class="stat-link">

                    <div class="stat-card">

                        <div class="stat-body">

                            <div class="stat-content">

                                <div>

                                    <div class="stat-label">

                                        Petani Binaan

                                    </div>


                                    <div class="stat-value">

                                        {{ $totalPetaniBinaan ?? 0 }}

                                    </div>


                                    <div class="stat-caption">

                                        {{
                                            $totalAlternatifBinaan ?? 0
                                        }}

                                        alternatif musim

                                    </div>

                                </div>


                                <div class="stat-icon icon-blue">

                                    <i class="fas fa-users"></i>

                                </div>

                            </div>

                        </div>

                    </div>

                </a>

            </div>


            {{-- MENUNGGU --}}

            <div class="col-xl-3 col-md-6 mb-3">

                <a href="{{ route(
                        'pengajuan.index',
                        [
                            'status' =>
                                'menunggu_verifikasi'
                        ]
                    ) }}"
                   class="stat-link">

                    <div class="stat-card">

                        <div class="stat-body">

                            <div class="stat-content">

                                <div>

                                    <div class="stat-label">

                                        Menunggu Verifikasi

                                    </div>


                                    <div class="stat-value">

                                        {{ $pengajuanMenunggu ?? 0 }}

                                    </div>


                                    <div class="stat-caption">

                                        Sedang diperiksa Admin

                                    </div>

                                </div>


                                <div class="stat-icon icon-yellow">

                                    <i class="fas fa-clock"></i>

                                </div>

                            </div>

                        </div>

                    </div>

                </a>

            </div>


            {{-- PERBAIKAN --}}

            <div class="col-xl-3 col-md-6 mb-3">

                <a href="{{ route(
                        'pengajuan.index',
                        [
                            'status' =>
                                'perlu_perbaikan'
                        ]
                    ) }}"
                   class="stat-link">

                    <div class="stat-card">

                        <div class="stat-body">

                            <div class="stat-content">

                                <div>

                                    <div class="stat-label">

                                        Perlu Perbaikan

                                    </div>


                                    <div class="stat-value">

                                        {{ $pengajuanPerbaikan ?? 0 }}

                                    </div>


                                    <div class="stat-caption">

                                        Perlu ditindaklanjuti

                                    </div>

                                </div>


                                <div class="stat-icon icon-red">

                                    <i class="fas fa-exclamation-triangle"></i>

                                </div>

                            </div>

                        </div>

                    </div>

                </a>

            </div>

        </div>



        {{-- =====================================================
             STATUS + PERLU TINDAKAN
        ====================================================== --}}

        <div class="row mb-4">


            {{-- CHART STATUS --}}

            <div class="col-lg-5 mb-4">

                <div class="dashboard-card">

                    <div class="dashboard-card-header">

                        <h6>

                            <i class="fas fa-chart-pie text-success mr-2"></i>

                            Status Pengajuan Saya

                        </h6>


                        <small>

                            Ringkasan proses pengajuan petani.

                        </small>

                    </div>


                    <div class="dashboard-card-body">

                        <div class="chart-box">

                            <canvas id="chartStatusPenyuluh"></canvas>

                        </div>

                    </div>

                </div>

            </div>


            {{-- PERLU TINDAKAN --}}

            <div class="col-lg-7 mb-4">

                <div class="dashboard-card">

                    <div class="dashboard-card-header">

                        <div class="d-flex justify-content-between align-items-center">

                            <div>

                                <h6>

                                    <i class="fas fa-exclamation-circle text-warning mr-2"></i>

                                    Pengajuan Perlu Tindakan

                                </h6>


                                <small>

                                    Pengajuan yang dikembalikan
                                    Admin untuk diperbaiki.

                                </small>

                            </div>


                            <a href="{{ route('pengajuan.create') }}"
                               class="btn btn-success btn-dashboard">

                                <i class="fas fa-plus mr-1"></i>

                                Ajukan Petani

                            </a>

                        </div>

                    </div>


                    <div class="dashboard-card-body">

                    @forelse($pengajuanPerluTindakan ?? [] as $item)

                        <div class="action-alert">

                            <div class="d-flex justify-content-between align-items-center">

                                <div>

                                    <div class="action-alert-title">

                                        {{ $item->nama_petani }}

                                    </div>


                                    <div class="action-alert-text">

                                        Poktan:

                                        {{
                                            optional(
                                                $item->poktan
                                            )->nama_poktan ?? '-'
                                        }}

                                        <br>

                                        Periode:

                                        {{
                                            optional(
                                                $item->musimTanam
                                            )->kode_musim ?? '-'
                                        }}

                                        /

                                        {{
                                            optional(
                                                $item->periode
                                            )->tahun ?? '-'
                                        }}


                                        @if($item->catatan_admin)

                                            <br>

                                            Catatan Admin:

                                            {{ $item->catatan_admin }}

                                        @endif

                                    </div>

                                </div>


                                <a href="{{ route(
                                        'pengajuan.edit',
                                        $item->id
                                    ) }}"
                                   class="btn btn-warning btn-dashboard ml-3">

                                    <i class="fas fa-edit mr-1"></i>

                                    Perbaiki

                                </a>

                            </div>

                        </div>

                    @empty

                        <div class="empty-dashboard">

                            <i class="fas fa-check-circle text-success"></i>

                            Tidak ada pengajuan yang perlu diperbaiki.

                        </div>

                    @endforelse

                    </div>

                </div>

            </div>

        </div>



        {{-- =====================================================
             PRIORITAS + PENGAJUAN TERBARU
        ====================================================== --}}

        <div class="row">


            {{-- PRIORITAS TERBARU --}}

            <div class="col-xl-6 mb-4">

                <div class="dashboard-card">

                    <div class="dashboard-card-header">

                        <div class="d-flex justify-content-between align-items-center">

                            <div>

                                <h6>

                                    <i class="fas fa-trophy text-warning mr-2"></i>

                                    Hasil Prioritas Binaan

                                </h6>


                                @if($rankingScope)

                                    <div class="scope-box">

                                        <span class="scope-item">

                                            {{ $rankingScope['musim'] }}

                                        </span>

                                        <span class="scope-item">

                                            {{ $rankingScope['tahun'] }}

                                        </span>

                                        <span class="scope-item">

                                            {{ $rankingScope['kecamatan'] }}

                                        </span>

                                        @if($rankingScope['kuota'] !== null)

                                            <span class="scope-item">

                                                Kuota:
                                                {{ $rankingScope['kuota'] }}

                                            </span>

                                        @endif

                                    </div>

                                @else

                                    <small>

                                        Belum ada hasil prioritas.

                                    </small>

                                @endif

                            </div>


                            @if($rankingScope)

                                <a href="{{ route(
                                        'ranking.index',
                                        [
                                            'musim_tanam_id' =>
                                                $rankingScope['musim_tanam_id'],

                                            'periode_id' =>
                                                $rankingScope['periode_id'],

                                            'kecamatan_id' =>
                                                $rankingScope['kecamatan_id'],
                                        ]
                                    ) }}"
                                   class="btn btn-outline-success btn-dashboard">

                                    Lihat Semua

                                </a>

                            @else

                                <a href="{{ route('ranking.index') }}"
                                   class="btn btn-outline-success btn-dashboard">

                                    Hasil Prioritas

                                </a>

                            @endif

                        </div>

                    </div>


                    <div class="table-responsive">

                        <table class="table dashboard-table">

                            <thead>

                                <tr>

                                    <th class="text-center">
                                        Rank Kecamatan
                                    </th>

                                    <th>
                                        Nama
                                    </th>

                                    <th>
                                        Poktan
                                    </th>

                                    <th class="text-center">
                                        Q
                                    </th>

                                    <th class="text-center">
                                        Status
                                    </th>

                                </tr>

                            </thead>


                            <tbody>

                            @forelse($topRanking ?? [] as $hasil)

                                @php

                                    $masukKuota =
                                        $rankingScope
                                        &&
                                        $rankingScope['kuota']
                                        !==
                                        null
                                        &&
                                        $hasil->ranking
                                        <=
                                        $rankingScope['kuota'];

                                @endphp


                                <tr>

                                    <td class="text-center">

                                        <span class="
                                            rank-circle
                                            {{ $hasil->ranking == 1 ? 'rank-first' : '' }}
                                        ">

                                            {{ $hasil->ranking }}

                                        </span>

                                    </td>


                                    <td>

                                        {{
                                            optional(
                                                $hasil->petani
                                            )->nama_petani ?? '-'
                                        }}

                                    </td>


                                    <td>

                                        {{
                                            optional(
                                                optional(
                                                    $hasil->alternatifMusim
                                                )->poktan
                                            )->nama_poktan ?? '-'
                                        }}

                                    </td>


                                    <td class="text-center">

                                        {{
                                            number_format(
                                                $hasil->nilai_q ?? 0,
                                                6
                                            )
                                        }}

                                    </td>


                                    <td class="text-center">

                                        @if(
                                            $rankingScope
                                            &&
                                            $rankingScope['kuota']
                                            !==
                                            null
                                        )

                                            @if($masukKuota)

                                                <span class="status-badge status-kuota">

                                                    Masuk Kuota

                                                </span>

                                            @else

                                                <span class="status-badge status-luar">

                                                    Di Luar

                                                </span>

                                            @endif

                                        @else

                                            -

                                        @endif

                                    </td>

                                </tr>

                            @empty

                                <tr>

                                    <td colspan="5">

                                        <div class="empty-dashboard">

                                            <i class="fas fa-trophy"></i>

                                            Belum ada hasil prioritas
                                            untuk petani binaan Anda.

                                        </div>

                                    </td>

                                </tr>

                            @endforelse

                            </tbody>

                        </table>

                    </div>

                </div>

            </div>



            {{-- PENGAJUAN TERBARU --}}

            <div class="col-xl-6 mb-4">

                <div class="dashboard-card">

                    <div class="dashboard-card-header">

                        <div class="d-flex justify-content-between align-items-center">

                            <div>

                                <h6>

                                    <i class="fas fa-history text-success mr-2"></i>

                                    Pengajuan Terbaru Saya

                                </h6>


                                <small>

                                    Riwayat pengajuan petani terbaru.

                                </small>

                            </div>


                            <a href="{{ route('pengajuan.index') }}"
                               class="btn btn-outline-success btn-dashboard">

                                Lihat Semua

                            </a>

                        </div>

                    </div>


                    <div class="table-responsive">

                        <table class="table dashboard-table">

                            <thead>

                                <tr>

                                    <th>
                                        Nama Petani
                                    </th>

                                    <th>
                                        Poktan
                                    </th>

                                    <th>
                                        Musim
                                    </th>

                                    <th class="text-center">
                                        Status
                                    </th>

                                    <th class="text-center">
                                        Aksi
                                    </th>

                                </tr>

                            </thead>


                            <tbody>

                            @forelse($pengajuanTerbaru ?? [] as $item)

                                <tr>

                                    <td>

                                        <strong>

                                            {{ $item->nama_petani }}

                                        </strong>

                                        <br>

                                        <small class="text-muted">

                                            {{ $item->nik }}

                                        </small>

                                    </td>


                                    <td>

                                        {{
                                            optional(
                                                $item->poktan
                                            )->nama_poktan ?? '-'
                                        }}

                                    </td>


                                    <td>

                                        {{
                                            optional(
                                                $item->musimTanam
                                            )->kode_musim ?? '-'
                                        }}

                                        /

                                        {{
                                            optional(
                                                $item->periode
                                            )->tahun ?? '-'
                                        }}

                                    </td>


                                    <td class="text-center">

                                        @if($item->status === 'menunggu_verifikasi')

                                            <span class="status-badge status-menunggu">

                                                Menunggu

                                            </span>

                                        @elseif($item->status === 'perlu_perbaikan')

                                            <span class="status-badge status-perbaikan">

                                                Perbaikan

                                            </span>

                                        @elseif($item->status === 'disetujui')

                                            <span class="status-badge status-disetujui">

                                                Disetujui

                                            </span>

                                        @else

                                            <span class="status-badge status-ditolak">

                                                Ditolak

                                            </span>

                                        @endif

                                    </td>


                                    <td class="text-center">

                                        <a href="{{ route(
                                                'pengajuan.show',
                                                $item->id
                                            ) }}"
                                           class="btn btn-outline-success btn-dashboard">

                                            <i class="fas fa-eye"></i>

                                        </a>

                                    </td>

                                </tr>

                            @empty

                                <tr>

                                    <td colspan="5">

                                        <div class="empty-dashboard">

                                            <i class="fas fa-folder-open"></i>

                                            Anda belum memiliki
                                            pengajuan petani.

                                        </div>

                                    </td>

                                </tr>

                            @endforelse

                            </tbody>

                        </table>

                    </div>

                </div>

            </div>

        </div>

    @endif

</div>



{{-- =========================================================
     CHART JS
========================================================= --}}

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>


<script>

document.addEventListener(
    'DOMContentLoaded',
    function () {

        /*
        |--------------------------------------------------------------------------
        | ADMIN
        |--------------------------------------------------------------------------
        */

        @if(auth()->user()->role === 'admin')

            const statusAdminCanvas =
                document.getElementById(
                    'chartStatusAdmin'
                );


            if (statusAdminCanvas) {

                new Chart(
                    statusAdminCanvas,
                    {
                        type: 'doughnut',

                        data: {

                            labels: [
                                'Menunggu',
                                'Perlu Perbaikan',
                                'Disetujui',
                                'Ditolak'
                            ],

                            datasets: [
                                {
                                    data: [
                                        {{ $statusPengajuan['menunggu'] ?? 0 }},
                                        {{ $statusPengajuan['perbaikan'] ?? 0 }},
                                        {{ $statusPengajuan['disetujui'] ?? 0 }},
                                        {{ $statusPengajuan['ditolak'] ?? 0 }}
                                    ],

                                    backgroundColor: [
                                        '#f59e0b',
                                        '#06b6d4',
                                        '#22c55e',
                                        '#ef4444'
                                    ],

                                    borderWidth: 0,

                                    hoverOffset: 6
                                }
                            ]
                        },

                        options: {

                            responsive: true,

                            maintainAspectRatio: false,

                            cutout: '67%',

                            plugins: {

                                legend: {

                                    position: 'bottom',

                                    labels: {

                                        usePointStyle: true,

                                        padding: 17,

                                        font: {
                                            size: 11
                                        }
                                    }
                                }
                            }
                        }
                    }
                );
            }


            const komoditasCanvas =
                document.getElementById(
                    'chartKomoditas'
                );


            if (komoditasCanvas) {

                new Chart(
                    komoditasCanvas,
                    {
                        type: 'bar',

                        data: {

                            labels:
                                @json(
                                    ($dataKomoditas ?? collect())
                                        ->pluck(
                                            'jenis_komoditas'
                                        )
                                ),

                            datasets: [
                                {
                                    label:
                                        'Jumlah Alternatif',

                                    data:
                                        @json(
                                            ($dataKomoditas ?? collect())
                                                ->pluck(
                                                    'total'
                                                )
                                        ),

                                    backgroundColor:
                                        '#22c55e',

                                    borderRadius:
                                        7,

                                    maxBarThickness:
                                        50
                                }
                            ]
                        },

                        options: {

                            responsive: true,

                            maintainAspectRatio: false,

                            scales: {

                                y: {

                                    beginAtZero:
                                        true,

                                    ticks: {

                                        precision:
                                            0
                                    },

                                    grid: {

                                        color:
                                            'rgba(0,0,0,.05)'
                                    }
                                },

                                x: {

                                    grid: {

                                        display:
                                            false
                                    }
                                }
                            },

                            plugins: {

                                legend: {

                                    display:
                                        false
                                }
                            }
                        }
                    }
                );
            }

        @endif



        /*
        |--------------------------------------------------------------------------
        | PENYULUH
        |--------------------------------------------------------------------------
        */

        @if(auth()->user()->role === 'penyuluh')

            const statusPenyuluhCanvas =
                document.getElementById(
                    'chartStatusPenyuluh'
                );


            if (statusPenyuluhCanvas) {

                new Chart(
                    statusPenyuluhCanvas,
                    {
                        type: 'doughnut',

                        data: {

                            labels: [
                                'Menunggu',
                                'Perlu Perbaikan',
                                'Disetujui',
                                'Ditolak'
                            ],

                            datasets: [
                                {
                                    data: [
                                        {{ $statusPengajuan['menunggu'] ?? 0 }},
                                        {{ $statusPengajuan['perbaikan'] ?? 0 }},
                                        {{ $statusPengajuan['disetujui'] ?? 0 }},
                                        {{ $statusPengajuan['ditolak'] ?? 0 }}
                                    ],

                                    backgroundColor: [
                                        '#f59e0b',
                                        '#06b6d4',
                                        '#22c55e',
                                        '#ef4444'
                                    ],

                                    borderWidth:
                                        0,

                                    hoverOffset:
                                        6
                                }
                            ]
                        },

                        options: {

                            responsive:
                                true,

                            maintainAspectRatio:
                                false,

                            cutout:
                                '67%',

                            plugins: {

                                legend: {

                                    position:
                                        'bottom',

                                    labels: {

                                        usePointStyle:
                                            true,

                                        padding:
                                            17,

                                        font: {

                                            size:
                                                11
                                        }
                                    }
                                }
                            }
                        }
                    }
                );
            }

        @endif

    }
);

</script>

@endsection