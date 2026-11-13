@extends('layouts.app')

@section('content')

<style>
    .page-title {
        font-size: 1.35rem;
        font-weight: 700;
        color: #343a40;
    }

    .page-subtitle {
        font-size: 0.82rem;
        color: #858796;
        margin-top: 3px;
    }

    .card-petani {
        border: none;
        border-radius: 12px;
        box-shadow: 0 3px 12px rgba(0, 0, 0, 0.07);
        overflow: hidden;
    }

    .card-header-petani {
        background: linear-gradient(135deg, #198754, #157347);
        color: white;
        padding: 15px 20px;
        border: none;
    }

    .card-header-petani h6 {
        margin: 0;
        font-size: 0.95rem;
        font-weight: 700;
    }

    .card-header-petani small {
        opacity: 0.85;
        font-size: 0.72rem;
    }

    .btn-tambah {
        background: white;
        color: #198754;
        border: none;
        border-radius: 7px;
        font-size: 0.78rem;
        font-weight: 600;
        padding: 7px 13px;
        transition: all 0.2s ease;
    }

    .btn-tambah:hover {
        background: #f1fdf6;
        color: #146c43;
        transform: translateY(-1px);
        box-shadow: 0 3px 8px rgba(0,0,0,0.12);
    }

    .table-petani {
        margin-bottom: 0;
        font-size: 0.78rem;
    }

    .table-petani thead th {
        background: #f8f9fa;
        color: #495057;
        font-size: 0.72rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.3px;
        padding: 11px 8px;
        border-top: none;
        border-bottom: 2px solid #e9ecef;
        white-space: nowrap;
        vertical-align: middle;
    }

    .table-petani tbody td {
        padding: 9px 8px;
        vertical-align: middle;
        border-color: #edf0f2;
        color: #495057;
    }

    .table-petani tbody tr:hover {
        background-color: #f8fffb;
    }

    .kode-badge {
        display: inline-block;
        min-width: 42px;
        padding: 4px 7px;
        border-radius: 5px;
        background: #e9f7ef;
        color: #198754;
        border: 1px solid #cfe9da;
        font-weight: 700;
        font-size: 0.7rem;
    }

    .nama-petani {
        font-weight: 600;
        color: #343a40;
    }

    .nik-text {
        font-size: 0.75rem;
        color: #6c757d;
    }

    .poktan-badge {
        display: inline-block;
        padding: 4px 8px;
        border-radius: 5px;
        background: #f1f3f5;
        color: #495057;
        font-size: 0.7rem;
        font-weight: 600;
    }

    .nilai-badge {
        display: inline-block;
        padding: 3px 6px;
        border-radius: 5px;
        background: #f8f9fa;
        border: 1px solid #e9ecef;
        color: #495057;
        font-weight: 600;
        font-size: 0.7rem;
    }

    .btn-aksi {
        width: 30px;
        height: 30px;
        padding: 0;
        border-radius: 6px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        margin: 0 2px;
        font-size: 0.72rem;
    }

    .btn-edit {
        color: #856404;
        background: #fff3cd;
        border: 1px solid #ffe69c;
    }

    .btn-edit:hover {
        background: #ffc107;
        color: white;
    }

    .btn-hapus {
        color: #842029;
        background: #f8d7da;
        border: 1px solid #f1aeb5;
    }

    .btn-hapus:hover {
        background: #dc3545;
        color: white;
    }

    .alert-custom {
        border: none;
        border-radius: 8px;
        font-size: 0.8rem;
    }

    .dataTable-input {
        width: 240px !important;
        height: 34px;
        padding: 6px 10px !important;
        border: 1px solid #d1d3e2 !important;
        border-radius: 7px !important;
        font-size: 0.75rem !important;
    }

    .dataTable-input:focus {
        border-color: #198754 !important;
    }

    @media (max-width: 768px) {

        .dataTable-input {
            width: 100% !important;
        }

        .table-petani {
            min-width: 850px;
        }
    }
</style>


<div class="container-fluid px-3">


    {{-- HEADER --}}
    <div class="d-flex justify-content-between align-items-center mb-4">

        <div>

            <div class="page-title">

                <i class="fas fa-users text-success mr-2"></i>

                @if(auth()->user()->role === 'admin')

                    Data Alternatif Petani

                @else

                    Data Petani

                @endif

            </div>


            <div class="page-subtitle">

                @if(auth()->user()->role === 'admin')

                    Kelola petani yang menjadi alternatif dalam proses ANP dan VIKOR.

                @else

                    Data petani dari kelompok tani yang menjadi binaan Anda.

                @endif

            </div>

        </div>

    </div>



    {{-- SUCCESS --}}
    @if(session('success'))

        <div class="alert alert-success alert-dismissible fade show alert-custom">

            <i class="fas fa-check-circle mr-2"></i>

            {{ session('success') }}

            <button type="button"
                    class="close"
                    data-dismiss="alert">

                <span>&times;</span>

            </button>

        </div>

    @endif



    {{-- ERROR --}}
    @if($errors->any())

        <div class="alert alert-danger alert-dismissible fade show alert-custom">

            <i class="fas fa-exclamation-triangle mr-2"></i>

            @foreach($errors->all() as $error)

                <div>{{ $error }}</div>

            @endforeach

            <button type="button"
                    class="close"
                    data-dismiss="alert">

                <span>&times;</span>

            </button>

        </div>

    @endif



    <div class="card card-petani mb-4">


        <div class="card-header-petani">

            <div class="d-flex justify-content-between align-items-center">

                <div>

                    <h6>
                        <i class="fas fa-table mr-2"></i>

                        @if(auth()->user()->role === 'admin')

                            Daftar Alternatif Petani

                        @else

                            Daftar Petani Binaan

                        @endif

                    </h6>


                    <small>

                        @if(auth()->user()->role === 'admin')

                            Petani yang dapat diproses dalam metode VIKOR

                        @else

                            Petani yang telah disetujui Admin

                        @endif

                    </small>

                </div>


                {{-- HANYA ADMIN --}}
                @if(auth()->user()->role === 'admin')

                    <a href="{{ route('petani.create') }}"
                       class="btn btn-tambah">

                        <i class="fas fa-plus mr-1"></i>

                        Tambah Data

                    </a>

                @endif

            </div>

        </div>



        <div class="card-body">

            <div class="table-responsive">

                <table class="table table-hover table-petani"
                       id="tabelPetani">

                    <thead>

                        <tr>

                            <th class="text-center">
                                ID
                            </th>

                            <th>
                                NIK
                            </th>

                            <th>
                                Nama Petani
                            </th>

                            <th class="text-center">
                                Poktan
                            </th>

                            <th class="text-center">
                                C1
                            </th>

                            <th class="text-center">
                                C2
                            </th>

                            <th class="text-center">
                                C3
                            </th>

                            <th class="text-center">
                                C4
                            </th>

                            <th class="text-center">
                                C5
                            </th>

                            <th class="text-center">
                                Aksi
                            </th>

                        </tr>

                    </thead>


                    <tbody>

                        @forelse($petani->sortBy('id') as $item)

                            <tr>


                                {{-- KODE ALTERNATIF --}}
                                <td class="text-center">

                                    <span class="kode-badge">

                                        {{ $item->kode_alternatif ?? '-' }}

                                    </span>

                                </td>


                                {{-- NIK --}}
                                <td>

                                    <span class="nik-text">

                                        {{ $item->nik ?? '-' }}

                                    </span>

                                </td>


                                {{-- NAMA --}}
                                <td>

                                    <span class="nama-petani">

                                        {{ $item->nama_petani ?? '-' }}

                                    </span>

                                </td>


                                {{-- POKTAN --}}
                                <td class="text-center">

                                    <span class="poktan-badge">

                                        {{ $item->poktan->nama_poktan ?? '-' }}

                                    </span>

                                </td>


                                {{-- C1 --}}
                                <td class="text-center">

                                    <span class="nilai-badge">

                                        {{ $item->luas_lahan ?? '-' }}

                                    </span>

                                </td>


                                {{-- C2 --}}
                                <td class="text-center">

                                    <span class="nilai-badge">

                                        {{ $item->jenis_komoditas ?? '-' }}

                                    </span>

                                </td>


                                {{-- C3 --}}
                                <td class="text-center">

                                    <span class="nilai-badge">

                                        {{ $item->kelengkapan_berkas ?? '-' }}

                                    </span>

                                </td>


                                {{-- C4 --}}
                                <td class="text-center">

                                    <span class="nilai-badge">

                                        {{ $item->kebutuhan_pupuk ?? '-' }}

                                    </span>

                                </td>


                                {{-- C5 --}}
                                <td class="text-center">

                                    <span class="nilai-badge">

                                        {{ $item->subsektor_pertanian ?? '-' }}

                                    </span>

                                </td>


                                {{-- AKSI --}}
                                <td class="text-center">


                                    {{-- ADMIN DAN PENYULUH BOLEH EDIT --}}
                                    <a href="{{ route('petani.edit', $item->id) }}"
                                       class="btn btn-aksi btn-edit"
                                       title="Edit Data">

                                        <i class="fas fa-edit"></i>

                                    </a>


                                    {{-- HANYA ADMIN BOLEH HAPUS --}}
                                    @if(auth()->user()->role === 'admin')

                                        <form
                                            action="{{ route('petani.destroy', $item->id) }}"
                                            method="POST"
                                            class="d-inline"
                                            onsubmit="return confirm('Yakin ingin menghapus data petani ini?')">

                                            @csrf
                                            @method('DELETE')

                                            <button
                                                type="submit"
                                                class="btn btn-aksi btn-hapus"
                                                title="Hapus Data">

                                                <i class="fas fa-trash"></i>

                                            </button>

                                        </form>

                                    @endif


                                </td>

                            </tr>


                        @empty

                            <tr>

                                <td colspan="10"
                                    class="text-center py-4 text-muted">

                                    <i class="fas fa-users-slash fa-2x mb-2 d-block"></i>

                                    Belum terdapat data petani.

                                </td>

                            </tr>

                        @endforelse

                    </tbody>

                </table>

            </div>

        </div>

    </div>

</div>



<link
    href="https://cdn.jsdelivr.net/npm/simple-datatables@latest/dist/style.css"
    rel="stylesheet">


<script src="https://cdn.jsdelivr.net/npm/simple-datatables@latest"></script>


<script>

document.addEventListener("DOMContentLoaded", function () {

    const table = document.querySelector("#tabelPetani");

    if (table) {

        new simpleDatatables.DataTable(table, {

            searchable: true,

            sortable: false,

            fixedHeight: false,

            perPage: 10,

            perPageSelect: false,

            labels: {

                placeholder: "Cari nama, NIK, atau data petani...",

                noRows: "Tidak ada data petani",

                noResults: "Data tidak ditemukan",

                info: "Menampilkan {start} sampai {end} dari {rows} data"

            }

        });

    }

});

</script>

@endsection