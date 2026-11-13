@extends('layouts.app')
@section('title', 'Data Subkriteria')
@section('content')
<a href="{{ route('pengaturan.index') }}" class="app-back-link"><i class="fas fa-arrow-left mr-2"></i>Kembali ke Pengaturan</a>

<div class="app-page-header">
    <div><h1 class="app-page-title">Data Subkriteria</h1></div>
    <a href="{{ route('subkriteria.create') }}" class="btn btn-success"><i class="fas fa-plus mr-1"></i>Tambah Subkriteria</a>
</div>

@foreach($kriteria as $krit)
    <div class="app-card mb-4">
        <div class="app-card-header d-flex justify-content-between align-items-center">
            <div>
                <div class="app-card-title"><span class="app-badge app-badge-success mr-2">{{ $krit->kode_kriteria }}</span>{{ $krit->nama_kriteria }}</div>
            </div>
        </div>
        <div class="table-responsive">
            <table class="table app-table">
                <thead><tr><th style="width:70px;">No</th><th>Kode</th><th>Nama Subkriteria</th><th>Bobot</th><th style="width:120px;" class="text-center">Aksi</th></tr></thead>
                <tbody>
                @forelse($krit->subkriteria as $sub)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td><span class="app-badge app-badge-muted">{{ $sub->kode_subkriteria }}</span></td>
                        <td class="font-weight-bold text-gray-800">{{ $sub->nama_subkriteria }}</td>
                        <td>{{ $sub->bobot }}</td>
                        <td class="text-center text-nowrap">
                            <a href="{{ route('subkriteria.edit', $sub->id) }}" class="btn btn-light border app-action-btn" title="Edit"><i class="fas fa-pen text-warning"></i></a>
                            <form action="{{ route('subkriteria.destroy', $sub->id) }}" method="POST" class="d-inline">@csrf @method('DELETE')
                                <button class="btn btn-light border app-action-btn" title="Hapus" onclick="return confirm('Yakin ingin menghapus subkriteria ini?')"><i class="fas fa-trash text-danger"></i></button>
                            </form>
                        </td>
                    </tr>
                @empty<tr><td colspan="5" class="text-center text-muted py-4">Belum ada subkriteria.</td></tr>@endforelse
                </tbody>
            </table>
        </div>
    </div>
@endforeach
@endsection
