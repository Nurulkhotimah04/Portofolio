@extends('layouts.app')

@section('content')

<div class="container-fluid px-3">

    {{-- HEADER --}}
    <div class="d-flex justify-content-between align-items-center mb-4">

        <div>
            <h1 class="h3 mb-1 text-gray-800">
                Detail Pengajuan Petani
            </h1>

            <p class="text-muted mb-0">
                Informasi lengkap pengajuan calon Data Alternatif.
            </p>
        </div>

        <a
            href="{{ route('pengajuan-petani.index') }}"
            class="btn btn-secondary"
        >
            <i class="fas fa-arrow-left mr-1"></i>
            Kembali
        </a>

    </div>


    {{-- SUCCESS --}}
    @if(session('success'))

        <div class="alert alert-success">
            {{ session('success') }}
        </div>

    @endif


    {{-- STATUS --}}
    <div class="card shadow mb-4">

        <div class="card-body">

            <div class="row align-items-center">

                <div class="col-md-6">

                    <small class="text-muted">
                        Status Pengajuan
                    </small>

                    <div class="mt-1">

                        @if($pengajuan->status === 'menunggu_verifikasi')

                            <span class="badge badge-warning p-2">
                                Menunggu Verifikasi
                            </span>

                        @elseif($pengajuan->status === 'perlu_perbaikan')

                            <span class="badge badge-info p-2">
                                Perlu Perbaikan
                            </span>

                        @elseif($pengajuan->status === 'disetujui')

                            <span class="badge badge-success p-2">
                                Disetujui
                            </span>

                        @elseif($pengajuan->status === 'ditolak')

                            <span class="badge badge-danger p-2">
                                Ditolak
                            </span>

                        @endif

                    </div>

                </div>


                <div class="col-md-6 text-md-right mt-3 mt-md-0">

                    <small class="text-muted">
                        Tanggal Pengajuan
                    </small>

                    <div class="font-weight-bold">

                        @if($pengajuan->tanggal_pengajuan)

                            {{ \Carbon\Carbon::parse($pengajuan->tanggal_pengajuan)
                                ->format('d-m-Y H:i') }}

                        @else
                            -
                        @endif

                    </div>

                </div>

            </div>

        </div>

    </div>


    {{-- DATA PETANI --}}
    <div class="card shadow mb-4">

        <div class="card-header bg-success text-white">

            <strong>
                <i class="fas fa-user mr-1"></i>
                Data Petani
            </strong>

        </div>

        <div class="card-body">

            <div class="row">

                <div class="col-md-6">

                    <div class="mb-3">
                        <small class="text-muted">NIK</small>

                        <div class="font-weight-bold">
                            {{ $pengajuan->nik ?? '-' }}
                        </div>
                    </div>

                </div>


                <div class="col-md-6">

                    <div class="mb-3">
                        <small class="text-muted">Nama Petani</small>

                        <div class="font-weight-bold">
                            {{ $pengajuan->nama_petani ?? '-' }}
                        </div>
                    </div>

                </div>


                <div class="col-md-6">

                    <div class="mb-3">

                        <small class="text-muted">
                            Kelompok Tani
                        </small>

                        <div class="font-weight-bold">

                            {{ $pengajuan->poktan->nama_poktan ?? '-' }}

                        </div>

                    </div>

                </div>


                <div class="col-md-6">

                    <div class="mb-3">

                        <small class="text-muted">
                            Penyuluh
                        </small>

                        <div class="font-weight-bold">

                            {{ $pengajuan->penyuluh->nama_penyuluh ?? '-' }}

                        </div>

                    </div>

                </div>

            </div>

        </div>

    </div>


    {{-- DATA PENILAIAN --}}
    <div class="card shadow mb-4">

        <div class="card-header bg-success text-white">

            <strong>
                <i class="fas fa-clipboard-list mr-1"></i>
                Data Kriteria Petani
            </strong>

        </div>


        <div class="card-body">

            <div class="row">


                {{-- C1 --}}
                <div class="col-md-6">

                    <div class="mb-3">

                        <small class="text-muted">
                            Luas Lahan
                        </small>

                        <div class="font-weight-bold">
                            {{ $pengajuan->luas_lahan ?? '-' }}
                        </div>

                    </div>

                </div>


                {{-- KOMODITAS --}}
                <div class="col-md-6">

                    <div class="mb-3">

                        <small class="text-muted">
                            Jenis Komoditas
                        </small>

                        <div class="font-weight-bold">
                            {{ $pengajuan->jenis_komoditas ?? '-' }}
                        </div>

                    </div>

                </div>


                {{-- BERKAS --}}
                <div class="col-md-6">

                    <div class="mb-3">

                        <small class="text-muted">
                            Kelengkapan Berkas
                        </small>

                        <div class="font-weight-bold">
                            {{ $pengajuan->kelengkapan_berkas ?? '-' }}
                        </div>

                    </div>

                </div>


                {{-- PUPUK --}}
                <div class="col-md-6">

                    <div class="mb-3">

                        <small class="text-muted">
                            Kebutuhan Pupuk
                        </small>

                        <div class="font-weight-bold">
                            {{ $pengajuan->kebutuhan_pupuk ?? '-' }}
                        </div>

                    </div>

                </div>


                {{-- SUBSEKTOR --}}
                <div class="col-md-6">

                    <div class="mb-3">

                        <small class="text-muted">
                            Subsektor Pertanian
                        </small>

                        <div class="font-weight-bold">
                            {{ $pengajuan->subsektor_pertanian ?? '-' }}
                        </div>

                    </div>

                </div>

            </div>

        </div>

    </div>


    {{-- CATATAN --}}
    <div class="card shadow mb-4">

        <div class="card-header">

            <strong>
                Catatan Pengajuan
            </strong>

        </div>

        <div class="card-body">

            <div class="mb-4">

                <small class="text-muted">
                    Catatan Penyuluh
                </small>

                <div class="mt-1">

                    {{ $pengajuan->catatan_penyuluh ?: '-' }}

                </div>

            </div>


            @if($pengajuan->catatan_admin)

                <hr>

                <div>

                    <small class="text-muted">
                        Catatan Admin
                    </small>

                    <div class="mt-1 font-weight-bold">

                        {{ $pengajuan->catatan_admin }}

                    </div>

                </div>

            @endif

        </div>

    </div>


    {{-- ========================================================== --}}
    {{-- PENYULUH --}}
    {{-- ========================================================== --}}

    @if(auth()->user()->role === 'penyuluh')

        @if($pengajuan->status === 'perlu_perbaikan')

            <div class="text-right mb-4">

                <a
                    href="{{ route('pengajuan-petani.edit', $pengajuan->id) }}"
                    class="btn btn-warning"
                >
                    <i class="fas fa-edit mr-1"></i>
                    Perbaiki Pengajuan
                </a>

            </div>

        @endif

    @endif


    {{-- ========================================================== --}}
    {{-- ADMIN --}}
    {{-- ========================================================== --}}

    @if(auth()->user()->role === 'admin')

        @if($pengajuan->status === 'menunggu_verifikasi')

            <div class="card shadow mb-4">

                <div class="card-header bg-dark text-white">

                    <strong>
                        <i class="fas fa-check-circle mr-1"></i>
                        Verifikasi Pengajuan
                    </strong>

                </div>

                <div class="card-body">

                    <div class="row">


                        {{-- SETUJUI --}}
                        <div class="col-md-4 mb-2">

                            <form
                                action="{{ route(
                                    'pengajuan-petani.approve',
                                    $pengajuan->id
                                ) }}"
                                method="POST"
                                onsubmit="return confirm(
                                    'Setujui pengajuan ini dan masukkan petani ke Data Alternatif?'
                                )"
                            >

                                @csrf

                                <button
                                    type="submit"
                                    class="btn btn-success btn-block"
                                >
                                    <i class="fas fa-check mr-1"></i>
                                    Setujui
                                </button>

                            </form>

                        </div>


                        {{-- PERBAIKAN --}}
                        <div class="col-md-4 mb-2">

                            <button
                                type="button"
                                class="btn btn-warning btn-block"
                                data-toggle="modal"
                                data-target="#modalPerbaikan"
                            >
                                <i class="fas fa-edit mr-1"></i>
                                Minta Perbaikan
                            </button>

                        </div>


                        {{-- TOLAK --}}
                        <div class="col-md-4 mb-2">

                            <button
                                type="button"
                                class="btn btn-danger btn-block"
                                data-toggle="modal"
                                data-target="#modalTolak"
                            >
                                <i class="fas fa-times mr-1"></i>
                                Tolak
                            </button>

                        </div>

                    </div>

                </div>

            </div>

        @endif

    @endif


</div>


{{-- ========================================================== --}}
{{-- MODAL MINTA PERBAIKAN --}}
{{-- ========================================================== --}}

@if(auth()->user()->role === 'admin')

<div
    class="modal fade"
    id="modalPerbaikan"
    tabindex="-1"
>

    <div class="modal-dialog">

        <div class="modal-content">

            <form
                action="{{ route(
                    'pengajuan-petani.revision',
                    $pengajuan->id
                ) }}"
                method="POST"
            >

                @csrf

                <div class="modal-header">

                    <h5 class="modal-title">
                        Minta Perbaikan
                    </h5>

                    <button
                        type="button"
                        class="close"
                        data-dismiss="modal"
                    >
                        <span>&times;</span>
                    </button>

                </div>


                <div class="modal-body">

                    <div class="form-group">

                        <label>
                            Catatan Perbaikan
                        </label>

                        <textarea
                            name="catatan_admin"
                            rows="4"
                            class="form-control"
                            placeholder="Tuliskan bagian yang harus diperbaiki..."
                            required
                        ></textarea>

                    </div>

                </div>


                <div class="modal-footer">

                    <button
                        type="button"
                        class="btn btn-secondary"
                        data-dismiss="modal"
                    >
                        Batal
                    </button>

                    <button
                        type="submit"
                        class="btn btn-warning"
                    >
                        Kirim Perbaikan
                    </button>

                </div>

            </form>

        </div>

    </div>

</div>


{{-- ========================================================== --}}
{{-- MODAL TOLAK --}}
{{-- ========================================================== --}}

<div
    class="modal fade"
    id="modalTolak"
    tabindex="-1"
>

    <div class="modal-dialog">

        <div class="modal-content">

            <form
                action="{{ route(
                    'pengajuan-petani.reject',
                    $pengajuan->id
                ) }}"
                method="POST"
            >

                @csrf

                <div class="modal-header">

                    <h5 class="modal-title">
                        Tolak Pengajuan
                    </h5>

                    <button
                        type="button"
                        class="close"
                        data-dismiss="modal"
                    >
                        <span>&times;</span>
                    </button>

                </div>


                <div class="modal-body">

                    <div class="form-group">

                        <label>
                            Alasan Penolakan
                        </label>

                        <textarea
                            name="catatan_admin"
                            rows="4"
                            class="form-control"
                            placeholder="Tuliskan alasan pengajuan ditolak..."
                            required
                        ></textarea>

                    </div>

                </div>


                <div class="modal-footer">

                    <button
                        type="button"
                        class="btn btn-secondary"
                        data-dismiss="modal"
                    >
                        Batal
                    </button>

                    <button
                        type="submit"
                        class="btn btn-danger"
                    >
                        Tolak Pengajuan
                    </button>

                </div>

            </form>

        </div>

    </div>

</div>

@endif

@endsection