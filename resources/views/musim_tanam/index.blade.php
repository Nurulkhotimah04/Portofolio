@extends('layouts.app')

@section('content')

<div class="container-fluid">

    <div class="mb-4">
        <h4 class="font-weight-bold text-gray-800">
            <i class="fas fa-seedling text-success mr-2"></i>
            Master Musim Tanam
        </h4>
    </div>

    @if(session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger">
            {{ session('error') }}
        </div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif


    <div class="card shadow-sm border-0 mb-4">

        <div class="card-header bg-white">
            <strong>Tambah Musim Tanam</strong>
        </div>

        <div class="card-body">

            <form method="POST"
                  action="{{ route('musim-tanam.store') }}">

                @csrf

                <div class="row">

                    <div class="col-md-4 mb-3">

                        <label>Kode Musim</label>

                        <input type="text"
                               name="kode_musim"
                               class="form-control"
                               placeholder="Contoh: MT1"
                               value="{{ old('kode_musim') }}"
                               required>

                    </div>

                    <div class="col-md-6 mb-3">

                        <label>Nama Musim</label>

                        <input type="text"
                               name="nama_musim"
                               class="form-control"
                               placeholder="Contoh: Musim Tanam I"
                               value="{{ old('nama_musim') }}"
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


    <div class="card shadow-sm border-0">

        <div class="card-header bg-white">
            <strong>Daftar Musim Tanam</strong>
        </div>

        <div class="card-body">

            <div class="table-responsive">

                <table class="table table-hover">

                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Kode</th>
                            <th>Nama Musim</th>
                            <th width="180">Aksi</th>
                        </tr>
                    </thead>

                    <tbody>

                    @forelse($musimTanam as $item)

                        <tr>
                            <td>{{ $loop->iteration }}</td>

                            <td>
                                <span class="badge badge-success">
                                    {{ $item->kode_musim }}
                                </span>
                            </td>

                            <td>{{ $item->nama_musim }}</td>

                            <td>

                                <button class="btn btn-warning btn-sm"
                                        data-toggle="modal"
                                        data-target="#edit{{ $item->id }}">
                                    <i class="fas fa-edit"></i>
                                </button>

                                <form method="POST"
                                      action="{{ route('musim-tanam.destroy', $item) }}"
                                      class="d-inline">

                                    @csrf
                                    @method('DELETE')

                                    <button type="submit"
                                            onclick="return confirm('Hapus Musim Tanam ini?')"
                                            class="btn btn-danger btn-sm">

                                        <i class="fas fa-trash"></i>

                                    </button>

                                </form>

                            </td>

                        </tr>


                        <div class="modal fade"
                             id="edit{{ $item->id }}"
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
                                                &times;
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

                    @empty

                        <tr>
                            <td colspan="4"
                                class="text-center text-muted">
                                Belum ada data Musim Tanam.
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