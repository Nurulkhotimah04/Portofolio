@extends('layouts.app')

@section('content')

<div class="container-fluid">

    {{-- ===================================================== --}}
    {{-- HEADER --}}
    {{-- ===================================================== --}}
    <div class="mb-4">

        <div class="mb-2">
            <a href="{{ route('pengaturan.index') }}" class="text-muted small text-decoration-none">
                <i class="fas fa-arrow-left mr-1"></i>
                Kembali ke Pengaturan
            </a>
        </div>

        <h4 class="font-weight-bold text-gray-800 mb-1">

            <i class="fas fa-sliders-h text-success mr-2"></i>

            Pengaturan Periode & Wilayah

        </h4>

    </div>


    {{-- ===================================================== --}}
    {{-- ALERT --}}
    {{-- ===================================================== --}}
    @if(session('success'))

        <div class="alert alert-success alert-dismissible fade show shadow-sm"
             style="border-radius: 10px;">

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

        <div class="alert alert-danger alert-dismissible fade show shadow-sm"
             style="border-radius: 10px;">

            <i class="fas fa-exclamation-circle mr-2"></i>

            {{ session('error') }}

            <button type="button"
                    class="close"
                    data-dismiss="alert">

                <span>&times;</span>

            </button>

        </div>

    @endif


    @if($errors->any())

        <div class="alert alert-danger shadow-sm"
             style="border-radius: 10px;">

            <div class="font-weight-bold mb-2">

                <i class="fas fa-exclamation-triangle mr-1"></i>

                Terdapat data yang belum benar:

            </div>

            <ul class="mb-0">

                @foreach($errors->all() as $error)

                    <li>{{ $error }}</li>

                @endforeach

            </ul>

        </div>

    @endif


    {{-- ===================================================== --}}
    {{-- TAB NAVIGATION --}}
    {{-- ===================================================== --}}
    <div class="card shadow-sm border-0 mb-4"
         style="border-radius: 12px; overflow: hidden;">

        <div class="card-body p-2">

            <div class="nav nav-pills nav-fill">

                {{-- MUSIM --}}
                <a href="{{ route('pengaturan-periode-wilayah.index', ['tab' => 'musim']) }}"
                   class="nav-link py-3 {{ $activeTab === 'musim' ? 'active' : '' }}">

                    <i class="fas fa-seedling mr-2"></i>

                    Musim Tanam

                </a>


                {{-- PERIODE --}}
                <a href="{{ route('pengaturan-periode-wilayah.index', ['tab' => 'periode']) }}"
                   class="nav-link py-3 {{ $activeTab === 'periode' ? 'active' : '' }}">

                    <i class="fas fa-calendar-alt mr-2"></i>

                    Tahun / Periode

                </a>


                {{-- KECAMATAN --}}
                <a href="{{ route('pengaturan-periode-wilayah.index', ['tab' => 'kecamatan']) }}"
                   class="nav-link py-3 {{ $activeTab === 'kecamatan' ? 'active' : '' }}">

                    <i class="fas fa-map-marker-alt mr-2"></i>

                    Kecamatan

                </a>


                {{-- ALOKASI --}}
                <a href="{{ route('pengaturan-periode-wilayah.index', ['tab' => 'alokasi']) }}"
                   class="nav-link py-3 {{ $activeTab === 'alokasi' ? 'active' : '' }}">

                    <i class="fas fa-users mr-2"></i>

                    Alokasi Kecamatan

                </a>

            </div>

        </div>

    </div>


    {{-- ===================================================== --}}
    {{-- TAB MUSIM TANAM --}}
    {{-- ===================================================== --}}
    @if($activeTab === 'musim')

        <div class="card shadow-sm border-0 mb-4"
             style="border-radius: 12px;">

            <div class="card-header bg-white py-3">

                <h6 class="m-0 font-weight-bold text-success">

                    <i class="fas fa-plus-circle mr-2"></i>

                    Tambah Musim Tanam

                </h6>

            </div>


            <div class="card-body">

                <form method="POST"
                      action="{{ route('musim-tanam.store') }}">

                    @csrf

                    <div class="row">

                        <div class="col-md-3 mb-3">

                            <label class="font-weight-bold">
                                Kode Musim
                            </label>

                            <input type="text"
                                   name="kode_musim"
                                   class="form-control"
                                   value="{{ old('kode_musim') }}"
                                   placeholder="Contoh: MT1"
                                   required>

                        </div>


                        <div class="col-md-7 mb-3">

                            <label class="font-weight-bold">
                                Nama Musim
                            </label>

                            <input type="text"
                                   name="nama_musim"
                                   class="form-control"
                                   value="{{ old('nama_musim') }}"
                                   placeholder="Contoh: Musim Tanam I"
                                   required>

                        </div>


                        <div class="col-md-2 mb-3 d-flex align-items-end">

                            <button type="submit"
                                    class="btn btn-success btn-block">

                                <i class="fas fa-save mr-1"></i>

                                Simpan

                            </button>

                        </div>

                    </div>

                </form>

            </div>

        </div>


        <div class="card shadow-sm border-0"
             style="border-radius: 12px;">

            <div class="card-header bg-white py-3">

                <div class="d-flex justify-content-between">

                    <h6 class="m-0 font-weight-bold text-success">

                        Daftar Musim Tanam

                    </h6>

                    <span class="badge badge-light px-3 py-2">

                        {{ $musimTanam->count() }} Data

                    </span>

                </div>

            </div>


            <div class="card-body">

                <div class="table-responsive">

                    <table class="table table-hover">

                        <thead class="bg-light">

                            <tr>
                                <th width="8%">No</th>
                                <th width="20%">Kode</th>
                                <th>Nama Musim</th>
                                <th width="18%" class="text-center">
                                    Aksi
                                </th>
                            </tr>

                        </thead>

                        <tbody>

                        @forelse($musimTanam as $item)

                            <tr>

                                <td>
                                    {{ $loop->iteration }}
                                </td>

                                <td>

                                    <span class="badge badge-success px-3 py-2">

                                        {{ $item->kode_musim }}

                                    </span>

                                </td>

                                <td class="font-weight-bold">

                                    {{ $item->nama_musim }}

                                </td>

                                <td class="text-center">

                                    <button type="button"
                                            class="btn btn-warning btn-sm"
                                            data-toggle="modal"
                                            data-target="#editMusim{{ $item->id }}">

                                        <i class="fas fa-edit"></i>

                                    </button>


                                    <form method="POST"
                                          action="{{ route('musim-tanam.destroy', $item) }}"
                                          class="d-inline">

                                        @csrf
                                        @method('DELETE')

                                        <button type="submit"
                                                class="btn btn-danger btn-sm"
                                                onclick="return confirm('Yakin ingin menghapus Musim Tanam ini?')">

                                            <i class="fas fa-trash"></i>

                                        </button>

                                    </form>

                                </td>

                            </tr>

                        @empty

                            <tr>

                                <td colspan="4"
                                    class="text-center py-4 text-muted">

                                    Belum ada data Musim Tanam.

                                </td>

                            </tr>

                        @endforelse

                        </tbody>

                    </table>

                </div>

            </div>

        </div>


        {{-- MODAL EDIT MUSIM --}}
        @foreach($musimTanam as $item)

            <div class="modal fade"
                 id="editMusim{{ $item->id }}"
                 tabindex="-1">

                <div class="modal-dialog">

                    <div class="modal-content">

                        <form method="POST"
                              action="{{ route('musim-tanam.update', $item) }}">

                            @csrf
                            @method('PUT')


                            <div class="modal-header">

                                <h5 class="modal-title">
                                    Edit Musim Tanam
                                </h5>

                                <button type="button"
                                        class="close"
                                        data-dismiss="modal">

                                    <span>&times;</span>

                                </button>

                            </div>


                            <div class="modal-body">

                                <div class="form-group">

                                    <label>Kode Musim</label>

                                    <input type="text"
                                           name="kode_musim"
                                           class="form-control"
                                           value="{{ $item->kode_musim }}"
                                           required>

                                </div>


                                <div class="form-group">

                                    <label>Nama Musim</label>

                                    <input type="text"
                                           name="nama_musim"
                                           class="form-control"
                                           value="{{ $item->nama_musim }}"
                                           required>

                                </div>

                            </div>


                            <div class="modal-footer">

                                <button type="button"
                                        class="btn btn-secondary"
                                        data-dismiss="modal">

                                    Batal

                                </button>

                                <button type="submit"
                                        class="btn btn-success">

                                    Simpan

                                </button>

                            </div>

                        </form>

                    </div>

                </div>

            </div>

        @endforeach

    @endif



    {{-- ===================================================== --}}
    {{-- TAB PERIODE --}}
    {{-- ===================================================== --}}
    @if($activeTab === 'periode')

        <div class="card shadow-sm border-0 mb-4"
             style="border-radius: 12px;">

            <div class="card-header bg-white py-3">

                <h6 class="m-0 font-weight-bold text-success">

                    <i class="fas fa-plus-circle mr-2"></i>

                    Tambah Tahun / Periode

                </h6>

            </div>

            <div class="card-body">

                <form method="POST"
                      action="{{ route('periode.store') }}">

                    @csrf

                    <div class="row">

                        <div class="col-md-9 mb-3">

                            <label class="font-weight-bold">
                                Tahun
                            </label>

                            <input type="number"
                                   name="tahun"
                                   min="2020"
                                   max="2100"
                                   class="form-control"
                                   value="{{ old('tahun') }}"
                                   placeholder="Contoh: 2026"
                                   required>

                        </div>

                        <div class="col-md-3 mb-3 d-flex align-items-end">

                            <button type="submit"
                                    class="btn btn-success btn-block">

                                <i class="fas fa-save mr-1"></i>

                                Simpan

                            </button>

                        </div>

                    </div>

                </form>

            </div>

        </div>


        <div class="card shadow-sm border-0"
             style="border-radius: 12px;">

            <div class="card-header bg-white py-3">

                <div class="d-flex justify-content-between">

                    <h6 class="m-0 font-weight-bold text-success">
                        Daftar Tahun / Periode
                    </h6>

                    <span class="badge badge-light px-3 py-2">
                        {{ $periode->count() }} Data
                    </span>

                </div>

            </div>


            <div class="card-body">

                <table class="table table-hover">

                    <thead class="bg-light">

                        <tr>
                            <th>No</th>
                            <th>Tahun / Periode</th>
                            <th class="text-center">
                                Aksi
                            </th>
                        </tr>

                    </thead>

                    <tbody>

                    @forelse($periode as $item)

                        <tr>

                            <td>
                                {{ $loop->iteration }}
                            </td>

                            <td>

                                <span class="font-weight-bold">

                                    {{ $item->tahun }}

                                </span>

                            </td>

                            <td class="text-center">

                                <button type="button"
                                        class="btn btn-warning btn-sm"
                                        data-toggle="modal"
                                        data-target="#editPeriode{{ $item->id }}">

                                    <i class="fas fa-edit"></i>

                                </button>


                                <form method="POST"
                                      action="{{ route('periode.destroy', $item) }}"
                                      class="d-inline">

                                    @csrf
                                    @method('DELETE')

                                    <button type="submit"
                                            class="btn btn-danger btn-sm"
                                            onclick="return confirm('Yakin ingin menghapus Periode ini?')">

                                        <i class="fas fa-trash"></i>

                                    </button>

                                </form>

                            </td>

                        </tr>

                    @empty

                        <tr>

                            <td colspan="3"
                                class="text-center py-4 text-muted">

                                Belum ada data Tahun / Periode.

                            </td>

                        </tr>

                    @endforelse

                    </tbody>

                </table>

            </div>

        </div>


        {{-- MODAL PERIODE --}}
        @foreach($periode as $item)

            <div class="modal fade"
                 id="editPeriode{{ $item->id }}">

                <div class="modal-dialog">

                    <div class="modal-content">

                        <form method="POST"
                              action="{{ route('periode.update', $item) }}">

                            @csrf
                            @method('PUT')

                            <div class="modal-header">

                                <h5 class="modal-title">
                                    Edit Tahun / Periode
                                </h5>

                                <button type="button"
                                        class="close"
                                        data-dismiss="modal">

                                    <span>&times;</span>

                                </button>

                            </div>


                            <div class="modal-body">

                                <div class="form-group">

                                    <label>Tahun</label>

                                    <input type="number"
                                           name="tahun"
                                           min="2020"
                                           max="2100"
                                           class="form-control"
                                           value="{{ $item->tahun }}"
                                           required>

                                </div>

                            </div>


                            <div class="modal-footer">

                                <button type="button"
                                        class="btn btn-secondary"
                                        data-dismiss="modal">

                                    Batal

                                </button>

                                <button type="submit"
                                        class="btn btn-success">

                                    Simpan

                                </button>

                            </div>

                        </form>

                    </div>

                </div>

            </div>

        @endforeach

    @endif



    {{-- ===================================================== --}}
    {{-- TAB KECAMATAN --}}
    {{-- ===================================================== --}}
    @if($activeTab === 'kecamatan')

        <div class="card shadow-sm border-0 mb-4"
             style="border-radius: 12px;">

            <div class="card-header bg-white py-3">

                <h6 class="m-0 font-weight-bold text-success">

                    <i class="fas fa-plus-circle mr-2"></i>

                    Tambah Kecamatan

                </h6>

            </div>

            <div class="card-body">

                <form method="POST"
                      action="{{ route('kecamatan.store') }}">

                    @csrf

                    <div class="row">

                        <div class="col-md-9 mb-3">

                            <label class="font-weight-bold">
                                Nama Kecamatan
                            </label>

                            <input type="text"
                                   name="nama_kecamatan"
                                   class="form-control"
                                   value="{{ old('nama_kecamatan') }}"
                                   placeholder="Nama Kecamatan"
                                   required>

                        </div>

                        <div class="col-md-3 mb-3 d-flex align-items-end">

                            <button type="submit"
                                    class="btn btn-success btn-block">

                                <i class="fas fa-save mr-1"></i>

                                Simpan

                            </button>

                        </div>

                    </div>

                </form>

            </div>

        </div>


        <div class="card shadow-sm border-0"
             style="border-radius: 12px;">

            <div class="card-header bg-white py-3">

                <div class="d-flex justify-content-between">

                    <h6 class="m-0 font-weight-bold text-success">
                        Daftar Kecamatan
                    </h6>

                    <span class="badge badge-light px-3 py-2">

                        {{ $kecamatan->count() }} Kecamatan

                    </span>

                </div>

            </div>


            <div class="card-body">

                <div class="table-responsive">

                    <table class="table table-hover">

                        <thead class="bg-light">

                            <tr>

                                <th>No</th>

                                <th>
                                    Nama Kecamatan
                                </th>

                                <th class="text-center">
                                    Jumlah Poktan
                                </th>

                                <th class="text-center">
                                    Aksi
                                </th>

                            </tr>

                        </thead>


                        <tbody>

                        @forelse($kecamatan as $item)

                            <tr>

                                <td>
                                    {{ $loop->iteration }}
                                </td>

                                <td class="font-weight-bold">

                                    {{ $item->nama_kecamatan }}

                                </td>

                                <td class="text-center">

                                    <span class="badge badge-info px-3 py-2">

                                        {{ $item->poktan_count }} Poktan

                                    </span>

                                </td>


                                <td class="text-center">

                                    <button type="button"
                                            class="btn btn-warning btn-sm"
                                            data-toggle="modal"
                                            data-target="#editKecamatan{{ $item->id }}">

                                        <i class="fas fa-edit"></i>

                                    </button>


                                    <form method="POST"
                                          action="{{ route('kecamatan.destroy', $item) }}"
                                          class="d-inline">

                                        @csrf
                                        @method('DELETE')

                                        <button type="submit"
                                                class="btn btn-danger btn-sm"
                                                onclick="return confirm('Yakin ingin menghapus Kecamatan ini?')">

                                            <i class="fas fa-trash"></i>

                                        </button>

                                    </form>

                                </td>

                            </tr>

                        @empty

                            <tr>

                                <td colspan="4"
                                    class="text-center py-4 text-muted">

                                    Belum ada data Kecamatan.

                                </td>

                            </tr>

                        @endforelse

                        </tbody>

                    </table>

                </div>

            </div>

        </div>


        {{-- MODAL KECAMATAN --}}
        @foreach($kecamatan as $item)

            <div class="modal fade"
                 id="editKecamatan{{ $item->id }}">

                <div class="modal-dialog">

                    <div class="modal-content">

                        <form method="POST"
                              action="{{ route('kecamatan.update', $item) }}">

                            @csrf
                            @method('PUT')

                            <div class="modal-header">

                                <h5 class="modal-title">
                                    Edit Kecamatan
                                </h5>

                                <button type="button"
                                        class="close"
                                        data-dismiss="modal">

                                    <span>&times;</span>

                                </button>

                            </div>


                            <div class="modal-body">

                                <div class="form-group">

                                    <label>
                                        Nama Kecamatan
                                    </label>

                                    <input type="text"
                                           name="nama_kecamatan"
                                           class="form-control"
                                           value="{{ $item->nama_kecamatan }}"
                                           required>

                                </div>

                            </div>


                            <div class="modal-footer">

                                <button type="button"
                                        class="btn btn-secondary"
                                        data-dismiss="modal">

                                    Batal

                                </button>

                                <button type="submit"
                                        class="btn btn-success">

                                    Simpan

                                </button>

                            </div>

                        </form>

                    </div>

                </div>

            </div>

        @endforeach

    @endif



    {{-- ===================================================== --}}
    {{-- TAB ALOKASI --}}
    {{-- ===================================================== --}}
    @if($activeTab === 'alokasi')

        <div class="card shadow-sm border-0 mb-4"
             style="border-radius: 12px;">

            <div class="card-header bg-white py-3">

                <h6 class="m-0 font-weight-bold text-success">

                    <i class="fas fa-plus-circle mr-2"></i>

                    Tambah Alokasi Kecamatan

                </h6>

            </div>

            <div class="card-body">

                <form method="POST"
                      action="{{ route('alokasi-kecamatan.store') }}">

                    @csrf

                    <div class="row">

                        <div class="col-lg-3 col-md-6 mb-3">

                            <label class="font-weight-bold">
                                Kecamatan
                            </label>

                            <select name="kecamatan_id"
                                    class="form-control"
                                    required>

                                <option value="">
                                    -- Pilih Kecamatan --
                                </option>

                                @foreach($kecamatan as $item)

                                    <option value="{{ $item->id }}">

                                        {{ $item->nama_kecamatan }}

                                    </option>

                                @endforeach

                            </select>

                        </div>


                        <div class="col-lg-3 col-md-6 mb-3">

                            <label class="font-weight-bold">
                                Musim Tanam
                            </label>

                            <select name="musim_tanam_id"
                                    class="form-control"
                                    required>

                                <option value="">
                                    -- Pilih Musim --
                                </option>

                                @foreach($musimTanam as $item)

                                    <option value="{{ $item->id }}">

                                        {{ $item->nama_musim }}

                                    </option>

                                @endforeach

                            </select>

                        </div>


                        <div class="col-lg-2 col-md-6 mb-3">

                            <label class="font-weight-bold">
                                Tahun
                            </label>

                            <select name="periode_id"
                                    class="form-control"
                                    required>

                                <option value="">
                                    -- Pilih Tahun --
                                </option>

                                @foreach($periode as $item)

                                    <option value="{{ $item->id }}">

                                        {{ $item->tahun }}

                                    </option>

                                @endforeach

                            </select>

                        </div>


                        <div class="col-lg-2 col-md-6 mb-3">

                            <label class="font-weight-bold">
                                Kuota
                            </label>

                            <input type="number"
                                   name="kuota_penerima"
                                   min="0"
                                   class="form-control"
                                   placeholder="Contoh: 100"
                                   required>

                        </div>


                        <div class="col-lg-2 mb-3 d-flex align-items-end">

                            <button type="submit"
                                    class="btn btn-success btn-block">

                                <i class="fas fa-save mr-1"></i>

                                Simpan

                            </button>

                        </div>

                    </div>

                </form>

            </div>

        </div>


        <div class="card shadow-sm border-0"
             style="border-radius: 12px;">

            <div class="card-header bg-white py-3">

                <div class="d-flex justify-content-between">

                    <h6 class="m-0 font-weight-bold text-success">

                        Daftar Alokasi Kecamatan

                    </h6>

                    <span class="badge badge-light px-3 py-2">

                        {{ $alokasi->count() }} Alokasi

                    </span>

                </div>

            </div>


            <div class="card-body">

                <div class="table-responsive">

                    <table class="table table-hover">

                        <thead class="bg-light">

                            <tr>

                                <th>No</th>
                                <th>Kecamatan</th>
                                <th>Musim Tanam</th>
                                <th>Tahun</th>
                                <th class="text-center">
                                    Kuota
                                </th>
                                <th class="text-center">
                                    Aksi
                                </th>

                            </tr>

                        </thead>


                        <tbody>

                        @forelse($alokasi as $item)

                            <tr>

                                <td>
                                    {{ $loop->iteration }}
                                </td>

                                <td class="font-weight-bold">

                                    {{ optional($item->kecamatan)->nama_kecamatan ?? '-' }}

                                </td>

                                <td>

                                    {{ optional($item->musimTanam)->nama_musim ?? '-' }}

                                </td>

                                <td>

                                    {{ optional($item->periode)->tahun ?? '-' }}

                                </td>

                                <td class="text-center">

                                    <span class="badge badge-success px-3 py-2">

                                        {{ number_format($item->kuota_penerima, 0, ',', '.') }}
                                        Penerima

                                    </span>

                                </td>


                                <td class="text-center">

                                    <button type="button"
                                            class="btn btn-warning btn-sm"
                                            data-toggle="modal"
                                            data-target="#editAlokasi{{ $item->id }}">

                                        <i class="fas fa-edit"></i>

                                    </button>


                                    <form method="POST"
                                          action="{{ route('alokasi-kecamatan.destroy', $item) }}"
                                          class="d-inline">

                                        @csrf
                                        @method('DELETE')

                                        <button type="submit"
                                                class="btn btn-danger btn-sm"
                                                onclick="return confirm('Yakin ingin menghapus Alokasi ini?')">

                                            <i class="fas fa-trash"></i>

                                        </button>

                                    </form>

                                </td>

                            </tr>

                        @empty

                            <tr>

                                <td colspan="6"
                                    class="text-center py-4 text-muted">

                                    Belum ada data Alokasi Kecamatan.

                                </td>

                            </tr>

                        @endforelse

                        </tbody>

                    </table>

                </div>

            </div>

        </div>


        {{-- MODAL ALOKASI --}}
        @foreach($alokasi as $item)

            <div class="modal fade"
                 id="editAlokasi{{ $item->id }}">

                <div class="modal-dialog">

                    <div class="modal-content">

                        <form method="POST"
                              action="{{ route('alokasi-kecamatan.update', $item) }}">

                            @csrf
                            @method('PUT')


                            <div class="modal-header">

                                <h5 class="modal-title">
                                    Edit Alokasi Kecamatan
                                </h5>

                                <button type="button"
                                        class="close"
                                        data-dismiss="modal">

                                    <span>&times;</span>

                                </button>

                            </div>


                            <div class="modal-body">

                                <div class="form-group">

                                    <label>Kecamatan</label>

                                    <select name="kecamatan_id"
                                            class="form-control"
                                            required>

                                        @foreach($kecamatan as $kec)

                                            <option value="{{ $kec->id }}"
                                                {{ $item->kecamatan_id == $kec->id ? 'selected' : '' }}>

                                                {{ $kec->nama_kecamatan }}

                                            </option>

                                        @endforeach

                                    </select>

                                </div>


                                <div class="form-group">

                                    <label>
                                        Musim Tanam
                                    </label>

                                    <select name="musim_tanam_id"
                                            class="form-control"
                                            required>

                                        @foreach($musimTanam as $musim)

                                            <option value="{{ $musim->id }}"
                                                {{ $item->musim_tanam_id == $musim->id ? 'selected' : '' }}>

                                                {{ $musim->nama_musim }}

                                            </option>

                                        @endforeach

                                    </select>

                                </div>


                                <div class="form-group">

                                    <label>
                                        Tahun / Periode
                                    </label>

                                    <select name="periode_id"
                                            class="form-control"
                                            required>

                                        @foreach($periode as $tahun)

                                            <option value="{{ $tahun->id }}"
                                                {{ $item->periode_id == $tahun->id ? 'selected' : '' }}>

                                                {{ $tahun->tahun }}

                                            </option>

                                        @endforeach

                                    </select>

                                </div>


                                <div class="form-group">

                                    <label>
                                        Kuota Penerima
                                    </label>

                                    <input type="number"
                                           name="kuota_penerima"
                                           min="0"
                                           class="form-control"
                                           value="{{ $item->kuota_penerima }}"
                                           required>

                                </div>

                            </div>


                            <div class="modal-footer">

                                <button type="button"
                                        class="btn btn-secondary"
                                        data-dismiss="modal">

                                    Batal

                                </button>

                                <button type="submit"
                                        class="btn btn-success">

                                    Simpan

                                </button>

                            </div>

                        </form>

                    </div>

                </div>

            </div>

        @endforeach

    @endif

</div>


<style>

    .nav-pills .nav-link {
        color: #5a5c69;
        font-weight: 600;
        border-radius: 8px;
        margin: 2px;
        transition: all .2s ease;
    }

    .nav-pills .nav-link:hover {
        background-color: #f0fff6;
        color: #1cc88a;
    }

    .nav-pills .nav-link.active {
        background-color: #1cc88a;
        color: white;
        box-shadow: 0 4px 10px rgba(28, 200, 138, .2);
    }

    .table th,
    .table td {
        vertical-align: middle !important;
    }

    .btn {
        transition: all .2s ease;
    }

    .btn:hover {
        transform: translateY(-1px);
    }

</style>

@endsection