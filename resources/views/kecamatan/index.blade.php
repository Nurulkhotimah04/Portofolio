@extends('layouts.app')

@section('content')

<div class="container-fluid">

    <div class="mb-4">

        <h4 class="font-weight-bold text-gray-800">
            <i class="fas fa-map-marker-alt text-success mr-2"></i>
            Master Kecamatan
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
            <strong>Tambah Kecamatan</strong>
        </div>

        <div class="card-body">

            <form method="POST"
                  action="{{ route('kecamatan.store') }}">

                @csrf

                <div class="row">

                    <div class="col-md-8">

                        <input type="text"
                               name="nama_kecamatan"
                               class="form-control"
                               placeholder="Nama Kecamatan"
                               value="{{ old('nama_kecamatan') }}"
                               required>

                    </div>

                    <div class="col-md-4">

                        <button class="btn btn-success btn-block">
                            <i class="fas fa-save mr-1"></i>
                            Simpan
                        </button>

                    </div>

                </div>

            </form>

        </div>

    </div>


    <div class="card shadow-sm border-0">

        <div class="card-header bg-white">
            <strong>Daftar Kecamatan</strong>
        </div>

        <div class="card-body">

            <table class="table table-hover">

                <thead>
                    <tr>
                        <th>No</th>
                        <th>Nama Kecamatan</th>
                        <th>Jumlah Poktan</th>
                        <th width="180">Aksi</th>
                    </tr>
                </thead>

                <tbody>

                @forelse($kecamatan as $item)

                    <tr>

                        <td>{{ $loop->iteration }}</td>

                        <td>
                            <strong>
                                {{ $item->nama_kecamatan }}
                            </strong>
                        </td>

                        <td>
                            {{ $item->poktan_count }} Poktan
                        </td>

                        <td>

                            <button class="btn btn-warning btn-sm"
                                    data-toggle="modal"
                                    data-target="#edit{{ $item->id }}">
                                <i class="fas fa-edit"></i>
                            </button>

                            <form method="POST"
                                  action="{{ route('kecamatan.destroy', $item) }}"
                                  class="d-inline">

                                @csrf
                                @method('DELETE')

                                <button class="btn btn-danger btn-sm"
                                        onclick="return confirm('Hapus Kecamatan ini?')">

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
                                            &times;
                                        </button>

                                    </div>

                                    <div class="modal-body">

                                        <input type="text"
                                               name="nama_kecamatan"
                                               class="form-control"
                                               value="{{ $item->nama_kecamatan }}"
                                               required>

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
                        <td colspan="4"
                            class="text-center text-muted">
                            Belum ada Kecamatan.
                        </td>
                    </tr>

                @endforelse

                </tbody>

            </table>

        </div>

    </div>

</div>

@endsection