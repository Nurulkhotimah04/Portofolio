@extends('layouts.app')

@section('title', 'Data Admin')

@section('content')
<div class="app-page-header">
    <div>
        <h1 class="app-page-title">Data Admin</h1>
    </div>
    <a href="{{ route('admin.create') }}" class="btn btn-success px-3">
        <i class="fas fa-plus mr-1"></i> Tambah Admin
    </a>
</div>

<div class="app-toolbar">
    <form method="GET" action="{{ route('admin.index') }}">
        <div class="row align-items-center">
            <div class="col-lg-10 col-md-9 mb-2 mb-md-0">
                <div class="app-search">
                    <i class="fas fa-search"></i>
                    <input type="text" name="search" value="{{ request('search') }}"
                           class="form-control" placeholder="Cari nama, username, atau email Admin...">
                </div>
            </div>
            <div class="col-lg-2 col-md-3">
                <button class="btn btn-outline-success btn-block" type="submit">Cari</button>
            </div>
        </div>
        @if(request()->filled('search'))
            <div class="mt-2">
                <a href="{{ route('admin.index') }}" class="small text-muted">
                    <i class="fas fa-times mr-1"></i>Hapus pencarian
                </a>
            </div>
        @endif
    </form>
</div>

<div class="app-card">
    <div class="app-card-header d-flex justify-content-between align-items-center">
        <div>
            <div class="app-card-title">Daftar Admin</div>
        </div>
        <span class="app-badge app-badge-muted">{{ $admins->total() }} data</span>
    </div>

    <div class="table-responsive">
        <table class="table app-table">
            <thead>
            <tr>
                <th style="width:70px;">No</th>
                <th>Admin</th>
                <th>Username</th>
                <th>Email</th>
                <th style="width:120px;" class="text-center">Aksi</th>
            </tr>
            </thead>
            <tbody>
            @forelse($admins as $admin)
                <tr>
                    <td>{{ $admins->firstItem() + $loop->index }}</td>
                    <td>
                        <div class="font-weight-bold text-gray-800">{{ $admin->name }}</div>
                        @if((int) auth()->id() === (int) $admin->id)
                            <span class="app-badge app-badge-success mt-1">Akun Anda</span>
                        @endif
                    </td>
                    <td>{{ $admin->username }}</td>
                    <td>{{ $admin->email }}</td>
                    <td class="text-center text-nowrap">
                        <a href="{{ route('admin.edit', $admin) }}"
                           class="btn btn-light border app-action-btn" title="Edit">
                            <i class="fas fa-pen text-warning"></i>
                        </a>

                        @if((int) auth()->id() !== (int) $admin->id)
                            <form action="{{ route('admin.destroy', $admin) }}" method="POST" class="d-inline">
                                @csrf
                                @method('DELETE')
                                <button class="btn btn-light border app-action-btn" type="submit" title="Hapus"
                                        onclick="return confirm('Yakin ingin menghapus akun Admin {{ addslashes($admin->name) }}?')">
                                    <i class="fas fa-trash text-danger"></i>
                                </button>
                            </form>
                        @endif
                    </td>
                </tr>
            @empty
                <tr><td colspan="5" class="text-center text-muted py-5">Belum ada data Admin.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>

    @if($admins->hasPages())
        <div class="app-pagination-footer">
            <div class="app-pagination-info">
                Menampilkan {{ $admins->firstItem() }}–{{ $admins->lastItem() }} dari {{ $admins->total() }} data
            </div>
            <div class="app-pagination-links">{{ $admins->links() }}</div>
        </div>
    @endif
</div>
@endsection
