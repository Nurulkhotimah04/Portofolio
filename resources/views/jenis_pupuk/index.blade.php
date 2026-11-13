@extends('layouts.app')
@section('title', 'Jenis Pupuk')
@section('content')
<a href="{{ route('pengaturan.index') }}" class="app-back-link"><i class="fas fa-arrow-left mr-2"></i>Kembali ke Pengaturan</a>

<div class="app-page-header">
    <div>
        <h1 class="app-page-title">Jenis Pupuk</h1>
    </div>
    <button type="button" class="btn btn-success" data-toggle="modal" data-target="#tambahPupukModal"><i class="fas fa-plus mr-1"></i>Tambah Jenis</button>
</div>

@if(session('success'))<div class="alert alert-success app-alert">{{ session('success') }}</div>@endif
@if($errors->any())<div class="alert alert-danger app-alert"><ul class="mb-0">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif

<div class="app-toolbar">
    <form method="GET" action="{{ route('jenis-pupuk.index') }}">
        <div class="app-search"><i class="fas fa-search"></i><input type="text" class="form-control" name="search" value="{{ request('search') }}" placeholder="Cari jenis pupuk..."></div>
    </form>
</div>

<div class="app-card">
    <div class="app-card-header d-flex justify-content-between align-items-center">
        <div><div class="app-card-title">Daftar Jenis Pupuk</div></div>
        <span class="app-badge app-badge-muted">{{ $jenisPupuk->total() }} data</span>
    </div>
    <div class="table-responsive">
        <table class="table app-table">
            <thead><tr><th style="width:70px">No</th><th>Nama Pupuk</th><th>Status</th><th style="width:190px" class="text-center">Aksi</th></tr></thead>
            <tbody>
            @forelse($jenisPupuk as $item)
                <tr>
                    <td>{{ $jenisPupuk->firstItem() + $loop->index }}</td>
                    <td class="font-weight-bold">{{ $item->nama_pupuk }}</td>
                    <td><span class="badge badge-{{ $item->is_active ? 'success' : 'secondary' }}">{{ $item->is_active ? 'Aktif' : 'Nonaktif' }}</span></td>
                    <td class="text-center text-nowrap">
                        <button type="button" class="btn btn-light border app-action-btn" data-toggle="modal" data-target="#editPupuk{{ $item->id }}" title="Edit"><i class="fas fa-pen text-warning"></i></button>
                        <form action="{{ route('jenis-pupuk.destroy', $item) }}" method="POST" class="d-inline">@csrf @method('DELETE')
                            <button class="btn btn-light border app-action-btn" title="Hapus / nonaktifkan" onclick="return confirm('Jika jenis ini sudah pernah digunakan, sistem hanya akan menonaktifkannya. Lanjutkan?')"><i class="fas fa-trash text-danger"></i></button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr><td colspan="4" class="text-center text-muted py-5">Belum ada jenis pupuk.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    @if($jenisPupuk->hasPages())<div class="app-pagination-footer"><div class="app-pagination-info">Menampilkan {{ $jenisPupuk->firstItem() }}–{{ $jenisPupuk->lastItem() }} dari {{ $jenisPupuk->total() }} data</div><div class="app-pagination-links">{{ $jenisPupuk->links() }}</div></div>@endif
</div>

<div class="modal fade" id="tambahPupukModal" tabindex="-1" role="dialog"><div class="modal-dialog modal-dialog-centered"><div class="modal-content"><form method="POST" action="{{ route('jenis-pupuk.store') }}">@csrf
    <div class="modal-header"><h5 class="modal-title font-weight-bold">Tambah Jenis Pupuk</h5><button type="button" class="close" data-dismiss="modal"><span>&times;</span></button></div>
    <div class="modal-body"><label class="font-weight-bold">Nama Pupuk</label><input type="text" name="nama_pupuk" class="form-control" placeholder="Contoh: Urea" required></div>
    <div class="modal-footer"><button type="button" class="btn btn-light border" data-dismiss="modal">Batal</button><button class="btn btn-success">Simpan</button></div>
</form></div></div></div>

@foreach($jenisPupuk as $item)
<div class="modal fade" id="editPupuk{{ $item->id }}" tabindex="-1" role="dialog"><div class="modal-dialog modal-dialog-centered"><div class="modal-content"><form method="POST" action="{{ route('jenis-pupuk.update', $item) }}">@csrf @method('PUT')
    <div class="modal-header"><h5 class="modal-title font-weight-bold">Edit Jenis Pupuk</h5><button type="button" class="close" data-dismiss="modal"><span>&times;</span></button></div>
    <div class="modal-body">
        <div class="form-group"><label class="font-weight-bold">Nama Pupuk</label><input type="text" name="nama_pupuk" class="form-control" value="{{ $item->nama_pupuk }}" required></div>
        <div class="form-group mb-0"><label class="font-weight-bold">Status</label><select name="is_active" class="form-control"><option value="1" {{ $item->is_active ? 'selected' : '' }}>Aktif</option><option value="0" {{ !$item->is_active ? 'selected' : '' }}>Nonaktif</option></select></div>
    </div>
    <div class="modal-footer"><button type="button" class="btn btn-light border" data-dismiss="modal">Batal</button><button class="btn btn-success">Simpan Perubahan</button></div>
</form></div></div></div>
@endforeach
@endsection
