@extends('layouts.app')
@section('title', 'Edit Admin')
@section('content')
<a href="{{ route('admin.index') }}" class="app-back-link"><i class="fas fa-arrow-left mr-2"></i>Kembali ke Data Admin</a>

<div class="app-page-header">
    <div>
        <h1 class="app-page-title">Edit Admin</h1>
    </div>
</div>

@if($errors->any())
    <div class="alert alert-danger app-alert">
        <strong>Periksa kembali data berikut:</strong>
        <ul class="mb-0 mt-2">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
    </div>
@endif

<div class="row">
    <div class="col-xl-8 col-lg-10">
        <div class="app-card">
            <div class="app-card-header">
                <div class="app-card-title">Informasi Akun</div>
            </div>
            <div class="p-4">
                <form action="{{ route('admin.update', $admin) }}" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="form-group">
                        <label class="font-weight-bold">Nama Admin</label>
                        <input type="text" name="name" class="form-control" value="{{ old('name', $admin->name) }}" required>
                    </div>
                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label class="font-weight-bold">Username</label>
                            <input type="text" name="username" class="form-control" value="{{ old('username', $admin->username) }}" required>
                        </div>
                        <div class="form-group col-md-6">
                            <label class="font-weight-bold">Email</label>
                            <input type="email" name="email" class="form-control" value="{{ old('email', $admin->email) }}" required>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label class="font-weight-bold">Password Baru <span class="text-muted font-weight-normal">(opsional)</span></label>
                            <input type="password" name="password" class="form-control">
                        </div>
                        <div class="form-group col-md-6">
                            <label class="font-weight-bold">Konfirmasi Password Baru</label>
                            <input type="password" name="password_confirmation" class="form-control">
                        </div>
                    </div>
                    <div class="d-flex justify-content-end mt-3">
                        <a href="{{ route('admin.index') }}" class="btn btn-light border mr-2">Batal</a>
                        <button class="btn btn-success px-4" type="submit"><i class="fas fa-save mr-1"></i>Simpan Perubahan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
