@extends('layouts.app')

@section('content')

<style>
    /* ================================
       CARD
    ================================= */
    .card-custom {
        border: none;
        border-radius: 12px;
        box-shadow: 0 3px 12px rgba(0, 0, 0, 0.06);
        overflow: hidden;
    }

    /* ================================
       HEADER
    ================================= */
    .page-title {
        font-size: 1.25rem;
        font-weight: 700;
        color: #343a40;
        margin-bottom: 3px;
    }

    .page-subtitle {
        font-size: 0.82rem;
        color: #858796;
        margin-bottom: 0;
    }

    /* ================================
       BUTTON GENERATE
    ================================= */
    .btn-generate {
        border-radius: 8px;
        padding: 8px 15px;
        font-size: 0.78rem;
        font-weight: 600;
        box-shadow: 0 2px 5px rgba(0, 123, 255, 0.15);
    }

    /* ================================
       STAT CARD
    ================================= */
    .stat-card {
        border: none;
        border-radius: 10px;
        box-shadow: 0 3px 10px rgba(0, 0, 0, 0.05);
        padding: 15px;
        transition: all 0.2s ease;
    }

    .stat-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.08);
    }

    .stat-title {
        font-size: 0.7rem;
        font-weight: 700;
        color: #858796;
        letter-spacing: 0.4px;
        text-transform: uppercase;
    }

    .stat-number {
        font-size: 1.4rem;
        font-weight: 700;
        margin-top: 3px;
    }

    .stat-icon {
        width: 40px;
        height: 40px;
        border-radius: 9px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1rem;
    }

    /* ================================
       TABLE
    ================================= */
    .table-container {
        max-height: 420px;
        overflow-y: auto;
    }

    .table {
        margin-bottom: 0;
    }

    .table thead th {
        padding: 12px 14px !important;
        font-size: 0.72rem !important;
        font-weight: 700;
        color: #495057;
        text-transform: uppercase;
        letter-spacing: 0.3px;
        border-top: none;
        border-bottom: 1px solid #e9ecef;
        background: #f8f9fa;
        position: sticky;
        top: 0;
        z-index: 2;
    }

    .table tbody td {
        padding: 11px 14px !important;
        font-size: 0.8rem !important;
        vertical-align: middle;
        border-color: #f0f0f0;
    }

    .table tbody tr {
        transition: background 0.15s ease;
    }

    .table tbody tr:hover {
        background-color: #f8fffa;
    }

    /* ================================
       PETANI
    ================================= */
    .petani-avatar {
        width: 36px;
        height: 36px;
        border-radius: 50%;
        background: #e8f5e9;
        color: #198754;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-right: 10px;
        font-size: 0.85rem;
    }

    .petani-name {
        font-size: 0.82rem;
        font-weight: 600;
        color: #343a40;
    }

    .petani-label {
        font-size: 0.68rem;
        color: #858796;
    }

    /* ================================
       STATUS
    ================================= */
    .status-badge {
        display: inline-flex;
        align-items: center;
        border-radius: 20px;
        padding: 5px 10px;
        font-size: 0.67rem;
        font-weight: 600;
    }

    .status-badge i {
        font-size: 0.55rem;
        margin-right: 5px;
    }

    /* ================================
       BUTTON AKSI
    ================================= */
    .btn-action {
        border-radius: 6px;
        padding: 5px 12px;
        font-size: 0.7rem;
        font-weight: 600;
        min-width: 60px;
    }

    /* ================================
       PAGINATION
    ================================= */
    .pagination {
        margin: 0;
    }

    .pagination .page-link {
        padding: 5px 10px;
        font-size: 0.72rem;
        color: #198754;
        border: 1px solid #dee2e6;
    }

    .pagination .page-item.active .page-link {
        background-color: #198754;
        border-color: #198754;
        color: white;
    }

    .pagination .page-link:hover {
        background-color: #eaf7ef;
        color: #146c43;
    }

    /* ================================
       EMPTY DATA
    ================================= */
    .empty-data {
        padding: 45px 20px;
        text-align: center;
        color: #858796;
    }

    .empty-data i {
        font-size: 2.5rem;
        margin-bottom: 12px;
        color: #adb5bd;
    }

    /* ================================
       RESPONSIVE
    ================================= */
    @media (max-width: 768px) {

        .page-title {
            font-size: 1.1rem;
        }

        .btn-generate {
            width: 100%;
            margin-top: 12px;
        }

        .table-container {
            max-height: 400px;
        }

    }
</style>


<div class="container-fluid px-3 py-3">

    {{-- ==========================================
         HEADER
    =========================================== --}}
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4">

        <div>
            <h1 class="page-title">
                <i class="fas fa-clipboard-check text-success mr-2"></i>
                Verifikasi & Penilaian Petani
            </h1>

            <p class="page-subtitle">
                Kelola proses verifikasi dan penilaian data petani.
            </p>
        </div>

        {{-- Tombol Generate --}}
        <form action="{{ route('penilaian.generate') }}"
              method="POST"
              class="d-inline">

            @csrf

            <button type="submit"
                    class="btn btn-primary btn-generate"
                    onclick="return confirm('Tarik semua data dari tabel Petani dan jadikan Penilaian otomatis?')">

                <i class="fas fa-sync-alt mr-1"></i>
                Generate Penilaian Otomatis

            </button>

        </form>

    </div>


    {{-- ==========================================
         STATISTIK
    =========================================== --}}
    <div class="row mb-4">

        {{-- Total Petani --}}
        <div class="col-12 col-md-4 mb-3 mb-md-0">

            <div class="card stat-card">

                <div class="d-flex justify-content-between align-items-center">

                    <div>

                        <div class="stat-title">
                            Total Petani
                        </div>

                        <div class="stat-number text-dark">
                            {{ $totalPetani ?? 0 }}
                        </div>

                    </div>

                    <div class="stat-icon bg-light">
                        <i class="fas fa-users text-primary"></i>
                    </div>

                </div>

            </div>

        </div>


        {{-- Sudah Dinilai --}}
        <div class="col-12 col-md-4 mb-3 mb-md-0">

            <div class="card stat-card">

                <div class="d-flex justify-content-between align-items-center">

                    <div>

                        <div class="stat-title">
                            Sudah Dinilai
                        </div>

                        <div class="stat-number text-success">
                            {{ $sudahDinilai ?? 0 }}
                        </div>

                    </div>

                    <div class="stat-icon bg-light">
                        <i class="fas fa-check-circle text-success"></i>
                    </div>

                </div>

            </div>

        </div>


        {{-- Belum Dinilai --}}
        <div class="col-12 col-md-4">

            <div class="card stat-card">

                <div class="d-flex justify-content-between align-items-center">

                    <div>

                        <div class="stat-title">
                            Belum Dinilai
                        </div>

                        <div class="stat-number text-warning">

                            {{ ($totalPetani ?? 0) - ($sudahDinilai ?? 0) }}

                        </div>

                    </div>

                    <div class="stat-icon bg-light">
                        <i class="fas fa-clock text-warning"></i>
                    </div>

                </div>

            </div>

        </div>

    </div>


    {{-- ==========================================
         TABEL PENILAIAN
    =========================================== --}}
    <div class="card card-custom">

        {{-- Card Header --}}
        <div class="card-header bg-white border-0 py-3">

            <div class="d-flex justify-content-between align-items-center">

                <div>

                    <h6 class="mb-1 font-weight-bold text-success">

                        <i class="fas fa-table mr-2"></i>
                        Daftar Penilaian Petani

                    </h6>

                    <small class="text-muted">
                        Status verifikasi dan penilaian setiap petani.
                    </small>

                </div>

                <span class="badge badge-light border px-3 py-2">

                    <i class="fas fa-list text-success mr-1"></i>

                    {{ $petani->total() }} Data

                </span>

            </div>

        </div>


        {{-- Tabel --}}
        <div class="card-body p-0">

            <div class="table-container">

                <table class="table table-hover">

                    <thead>

                        <tr>

                            <th class="text-center" width="6%">
                                #
                            </th>

                            <th width="35%">
                                Petani
                            </th>

                            <th width="25%">
                                Komoditas
                            </th>

                            <th class="text-center" width="17%">
                                Status
                            </th>

                            <th class="text-center" width="17%">
                                Aksi
                            </th>

                        </tr>

                    </thead>


                    <tbody>

                        @forelse($petani as $item)

                        <tr>

                            {{-- Nomor --}}
                            <td class="text-center font-weight-bold text-muted">

                                {{ $loop->iteration + ($petani->currentPage() - 1) * $petani->perPage() }}

                            </td>


                            {{-- Petani --}}
                            <td>

                                <div class="d-flex align-items-center">

                                    <div class="petani-avatar">

                                        <i class="fas fa-user"></i>

                                    </div>

                                    <div>

                                        <div class="petani-name">

                                            {{ $item->nama_petani }}

                                        </div>

                                        <div class="petani-label">

                                            {{ $item->kode_alternatif ?? 'Alternatif' }}

                                        </div>

                                    </div>

                                </div>

                            </td>


                            {{-- Komoditas --}}
                            <td>

                                @if($item->jenis_komoditas)

                                    <span class="text-dark">

                                        <i class="fas fa-seedling text-success mr-1"></i>

                                        {{ $item->jenis_komoditas }}

                                    </span>

                                @else

                                    <span class="text-muted">
                                        -
                                    </span>

                                @endif

                            </td>


                            {{-- Status --}}
                            <td class="text-center">

                                @if($item->penilaian()->count() > 0)

                                    <span class="status-badge bg-success text-white">

                                        <i class="fas fa-check"></i>
                                        Selesai

                                    </span>

                                @else

                                    <span class="status-badge bg-warning text-dark">

                                        <i class="fas fa-clock"></i>
                                        Pending

                                    </span>

                                @endif

                            </td>


                            {{-- Aksi --}}
                            <td class="text-center">

                                @if($item->penilaian()->count() > 0)

                                    <a href="/penilaian/{{ $item->id }}/edit"
                                       class="btn btn-warning btn-action"
                                       title="Edit Penilaian">

                                        <i class="fas fa-edit mr-1"></i>
                                        Edit

                                    </a>

                                @else

                                    <a href="/penilaian/{{ $item->id }}/create-for"
                                       class="btn btn-success btn-action"
                                       title="Berikan Penilaian">

                                        <i class="fas fa-star mr-1"></i>
                                        Nilai

                                    </a>

                                @endif

                            </td>

                        </tr>

                        @empty

                        <tr>

                            <td colspan="5">

                                <div class="empty-data">

                                    <i class="fas fa-clipboard-list d-block"></i>

                                    <h6 class="font-weight-bold text-gray-600">
                                        Data Penilaian Belum Tersedia
                                    </h6>

                                    <p class="mb-0">
                                        Belum terdapat data petani yang dapat dinilai.
                                    </p>

                                </div>

                            </td>

                        </tr>

                        @endforelse

                    </tbody>

                </table>

            </div>

        </div>


        {{-- ==========================================
             PAGINATION
        =========================================== --}}
        @if($petani->hasPages())

        <div class="card-footer bg-white border-0 py-3">

            <div class="d-flex justify-content-center">

                {{ $petani->links('pagination::bootstrap-4') }}

            </div>

        </div>

        @endif

    </div>

</div>

@endsection