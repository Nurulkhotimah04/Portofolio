@extends('layouts.app')

@section('content')

<div class="container-fluid">

    <div class="mb-4">

        <h4 class="font-weight-bold text-gray-800">

            <i class="fas fa-users text-success mr-2"></i>

            Alokasi Kecamatan

        </h4>

    </div>


    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger">
            @foreach($errors->all() as $error)
                <div>{{ $error }}</div>
            @endforeach
        </div>
    @endif


    <div class="card shadow-sm border-0 mb-4">

        <div class="card-header bg-white">
            <strong>Tambah Alokasi</strong>
        </div>

        <div class="card-body">

            <form method="POST"
                  action="{{ route('alokasi-kecamatan.store') }}">

                @csrf

                <div class="row">

                    <div class="col-md-3 mb-3">

                        <label>Kecamatan</label>

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


                    <div class="col-md-3 mb-3">

                        <label>Musim Tanam</label>

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


                    <div class="col-md-2 mb-3">

                        <label>Tahun</label>

                        <select name="periode_id"
                                class="form-control"
                                required>

                            <option value="">
                                -- Tahun --
                            </option>

                            @foreach($periode as $item)

                                <option value="{{ $item->id }}">
                                    {{ $item->tahun }}
                                </option>

                            @endforeach

                        </select>

                    </div>


                    <div class="col-md-2 mb-3">

                        <label>Kuota</label>

                        <input type="number"
                               name="kuota_penerima"
                               min="0"
                               class="form-control"
                               required>

                    </div>


                    <div class="col-md-2 mb-3 d-flex align-items-end">

                        <button class="btn btn-success btn-block">
                            Simpan
                        </button>

                    </div>

                </div>

            </form>

        </div>

    </div>


    <div class="card shadow-sm border-0">

        <div class="card-header bg-white">
            <strong>Daftar Alokasi Kecamatan</strong>
        </div>

        <div class="card-body">

            <div class="table-responsive">

                <table class="table table-hover">

                    <thead>

                        <tr>
                            <th>No</th>
                            <th>Kecamatan</th>
                            <th>Musim Tanam</th>
                            <th>Tahun</th>
                            <th>Kuota</th>
                            <th>Aksi</th>
                        </tr>

                    </thead>

                    <tbody>

                    @forelse($alokasi as $item)

                        <tr>

                            <td>{{ $loop->iteration }}</td>

                            <td>
                                {{ $item->kecamatan->nama_kecamatan }}
                            </td>

                            <td>
                                {{ $item->musimTanam->nama_musim }}
                            </td>

                            <td>
                                {{ $item->periode->tahun }}
                            </td>

                            <td>
                                <span class="badge badge-success px-3 py-2">
                                    {{ $item->kuota_penerima }} Penerima
                                </span>
                            </td>

                            <td>

                                <button class="btn btn-warning btn-sm"
                                        data-toggle="modal"
                                        data-target="#edit{{ $item->id }}">
                                    <i class="fas fa-edit"></i>
                                </button>

                                <form method="POST"
                                      action="{{ route('alokasi-kecamatan.destroy', $item) }}"
                                      class="d-inline">

                                    @csrf
                                    @method('DELETE')

                                    <button class="btn btn-danger btn-sm"
                                            onclick="return confirm('Hapus alokasi ini?')">
                                        <i class="fas fa-trash"></i>
                                    </button>

                                </form>

                            </td>

                        </tr>


                        <div class="modal fade"
                             id="edit{{ $item->id }}">

                            <div class="modal-dialog">

                                <div class="modal-content">

                                    <form method="POST"
                                          action="{{ route('alokasi-kecamatan.update', $item) }}">

                                        @csrf
                                        @method('PUT')

                                        <div class="modal-header">

                                            <h5 class="modal-title">
                                                Edit Alokasi
                                            </h5>

                                            <button type="button"
                                                    class="close"
                                                    data-dismiss="modal">
                                                &times;
                                            </button>

                                        </div>

                                        <div class="modal-body">

                                            <div class="form-group">

                                                <label>Kecamatan</label>

                                                <select name="kecamatan_id"
                                                        class="form-control"
                                                        required>

                                                    @foreach($kecamatan as $kec)

                                                        <option
                                                            value="{{ $kec->id }}"
                                                            {{ $item->kecamatan_id == $kec->id ? 'selected' : '' }}>

                                                            {{ $kec->nama_kecamatan }}

                                                        </option>

                                                    @endforeach

                                                </select>

                                            </div>


                                            <div class="form-group">

                                                <label>Musim Tanam</label>

                                                <select name="musim_tanam_id"
                                                        class="form-control"
                                                        required>

                                                    @foreach($musimTanam as $musim)

                                                        <option
                                                            value="{{ $musim->id }}"
                                                            {{ $item->musim_tanam_id == $musim->id ? 'selected' : '' }}>

                                                            {{ $musim->nama_musim }}

                                                        </option>

                                                    @endforeach

                                                </select>

                                            </div>


                                            <div class="form-group">

                                                <label>Tahun</label>

                                                <select name="periode_id"
                                                        class="form-control"
                                                        required>

                                                    @foreach($periode as $tahun)

                                                        <option
                                                            value="{{ $tahun->id }}"
                                                            {{ $item->periode_id == $tahun->id ? 'selected' : '' }}>

                                                            {{ $tahun->tahun }}

                                                        </option>

                                                    @endforeach

                                                </select>

                                            </div>


                                            <div class="form-group">

                                                <label>Kuota Penerima</label>

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

                                            <button class="btn btn-success">
                                                Simpan
                                            </button>

                                        </div>

                                    </form>

                                </div>

                            </div>

                        </div>

                    @empty

                        <tr>
                            <td colspan="6"
                                class="text-center text-muted">

                                Belum ada data alokasi.

                            </td>
                        </tr>

                    @endforelse

                    </tbody>

                </table>

            </div>

        </div>

    </div>

</div>

@endsection