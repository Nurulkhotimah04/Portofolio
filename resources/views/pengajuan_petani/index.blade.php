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

    .card-pengajuan {
        border: none;
        border-radius: 12px;
        box-shadow: 0 3px 12px rgba(0,0,0,0.07);
        overflow: hidden;
    }

    .card-header-pengajuan {
        background: linear-gradient(
            135deg,
            #198754,
            #157347
        );

        color: white;

        padding: 15px 20px;
    }

    .btn-tambah {
        background: white;
        color: #198754;

        border: none;
        border-radius: 7px;

        font-size: 0.78rem;
        font-weight: 600;

        padding: 7px 13px;
    }

    .btn-tambah:hover {
        background: #f1fdf6;
        color: #146c43;
    }

    .table-pengajuan {
        font-size: 0.78rem;
        margin-bottom: 0;
    }

    .table-pengajuan thead th {
        background: #f8f9fa;

        color: #495057;

        font-size: 0.72rem;
        font-weight: 700;

        text-transform: uppercase;

        padding: 11px 8px;

        vertical-align: middle;

        white-space: nowrap;
    }

    .table-pengajuan tbody td {
        padding: 10px 8px;
        vertical-align: middle;
    }

    .status-badge {
        display: inline-block;

        padding: 5px 9px;

        border-radius: 6px;

        font-size: 0.7rem;

        font-weight: 700;
    }

    .status-menunggu {
        background: #fff3cd;
        color: #856404;
    }

    .status-disetujui {
        background: #d1e7dd;
        color: #0f5132;
    }

    .status-ditolak {
        background: #f8d7da;
        color: #842029;
    }

    .status-perbaikan {
        background: #cff4fc;
        color: #055160;
    }

    .btn-detail {
        background: #e7f1ff;
        color: #0d6efd;

        border: 1px solid #b6d4fe;

        font-size: 0.72rem;

        padding: 5px 8px;

        border-radius: 6px;
    }

    .btn-detail:hover {
        background: #0d6efd;
        color: white;
    }

    .btn-edit-pengajuan {
        background: #fff3cd;
        color: #856404;

        border: 1px solid #ffe69c;

        font-size: 0.72rem;

        padding: 5px 8px;

        border-radius: 6px;
    }

</style>


<div class="container-fluid px-3">


    {{-- HEADER --}}
    <div class="d-flex justify-content-between align-items-center mb-4">

        <div>

            <div class="page-title">

                <i class="fas fa-file-signature text-success mr-2"></i>

                Pengajuan Petani

            </div>


            <div class="page-subtitle">

                @if(auth()->user()->role === 'admin')

                    Verifikasi calon petani yang diajukan oleh Penyuluh.

                @else

                    Ajukan calon petani untuk menjadi Data Alternatif penerima subsidi pupuk.

                @endif

            </div>

        </div>


        {{-- HANYA PENYULUH --}}
        @if(auth()->user()->role === 'penyuluh')

            <a href="{{ route('pengajuan-petani.create') }}"
               class="btn btn-tambah shadow-sm">

                <i class="fas fa-plus mr-1"></i>

                Tambah Pengajuan

            </a>

        @endif

    </div>



    {{-- SUCCESS --}}
    @if(session('success'))

        <div class="alert alert-success alert-dismissible fade show">

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
    @if(session('error'))

        <div class="alert alert-danger alert-dismissible fade show">

            <i class="fas fa-exclamation-triangle mr-2"></i>

            {{ session('error') }}

            <button type="button"
                    class="close"
                    data-dismiss="alert">

                <span>&times;</span>

            </button>

        </div>

    @endif



    <div class="card card-pengajuan mb-4">


        <div class="card-header-pengajuan">

            <strong>

                <i class="fas fa-list mr-2"></i>

                Daftar Pengajuan Petani

            </strong>

        </div>


        <div class="card-body">


            <div class="table-responsive">

                <table
                    class="table table-hover table-pengajuan"
                    id="tabelPengajuan">

                    <thead>

                        <tr>

                            <th class="text-center">
                                No
                            </th>

                            <th>
                                NIK
                            </th>

                            <th>
                                Nama Petani
                            </th>

                            <th>
                                Poktan
                            </th>


                            @if(auth()->user()->role === 'admin')

                                <th>
                                    Penyuluh
                                </th>

                            @endif


                            <th>
                                Komoditas
                            </th>

                            <th class="text-center">
                                Status
                            </th>

                            <th class="text-center">
                                Tanggal
                            </th>

                            <th class="text-center">
                                Aksi
                            </th>

                        </tr>

                    </thead>


                    <tbody>

                        @forelse($pengajuan as $item)

                            <tr>


                                <td class="text-center">

                                    {{ $loop->iteration }}

                                </td>


                                <td>

                                    {{ $item->nik ?? '-' }}

                                </td>


                                <td>

                                    <strong>

                                        {{ $item->nama_petani ?? '-' }}

                                    </strong>

                                </td>


                                <td>

                                    {{ $item->poktan->nama_poktan ?? '-' }}

                                </td>


                                @if(auth()->user()->role === 'admin')

                                    <td>

                                        {{ $item->penyuluh->nama_penyuluh ?? '-' }}

                                    </td>

                                @endif


                                <td>

                                    {{ $item->jenis_komoditas ?? '-' }}

                                </td>


                                {{-- STATUS --}}
                                <td class="text-center">


                                    @if($item->status === 'menunggu_verifikasi')

                                        <span class="status-badge status-menunggu">

                                            Menunggu Verifikasi

                                        </span>


                                    @elseif($item->status === 'perlu_perbaikan')

                                        <span class="status-badge status-perbaikan">

                                            Perlu Perbaikan

                                        </span>


                                    @elseif($item->status === 'disetujui')

                                        <span class="status-badge status-disetujui">

                                            Disetujui

                                        </span>


                                    @elseif($item->status === 'ditolak')

                                        <span class="status-badge status-ditolak">

                                            Ditolak

                                        </span>

                                    @endif


                                </td>


                                {{-- TANGGAL --}}
                                <td class="text-center">

                                    @if($item->tanggal_pengajuan)

                                        {{ \Carbon\Carbon::parse(
                                            $item->tanggal_pengajuan
                                        )->format('d-m-Y') }}

                                    @else

                                        -

                                    @endif

                                </td>


                                {{-- AKSI --}}
                                <td class="text-center">


                                    {{-- DETAIL --}}
                                    <a
                                        href="{{ route(
                                            'pengajuan-petani.show',
                                            $item->id
                                        ) }}"
                                        class="btn btn-detail"
                                    >

                                        <i class="fas fa-eye"></i>

                                        @if(auth()->user()->role === 'admin')

                                            Verifikasi

                                        @else

                                            Detail

                                        @endif

                                    </a>



                                    {{-- EDIT JIKA DIMINTA PERBAIKAN --}}
                                    @if(
                                        auth()->user()->role === 'penyuluh'
                                        &&
                                        $item->status === 'perlu_perbaikan'
                                    )

                                        <a
                                            href="{{ route(
                                                'pengajuan-petani.edit',
                                                $item->id
                                            ) }}"
                                            class="btn btn-edit-pengajuan"
                                        >

                                            <i class="fas fa-edit"></i>

                                            Perbaiki

                                        </a>

                                    @endif


                                </td>

                            </tr>


                        @empty

                            <tr>

                                <td
                                    colspan="{{ auth()->user()->role === 'admin' ? 9 : 8 }}"
                                    class="text-center py-4 text-muted"
                                >

                                    <i class="fas fa-folder-open fa-2x mb-2 d-block"></i>

                                    Belum ada pengajuan petani.

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

    const table = document.querySelector("#tabelPengajuan");

    if (table) {

        new simpleDatatables.DataTable(table, {

            searchable: true,

            sortable: false,

            fixedHeight: false,

            perPage: 10,

            perPageSelect: false,

            labels: {

                placeholder: "Cari pengajuan petani...",

                noRows: "Belum ada pengajuan",

                noResults: "Pengajuan tidak ditemukan",

                info: "Menampilkan {start} sampai {end} dari {rows} pengajuan"

            }

        });

    }

});

</script>

@endsection