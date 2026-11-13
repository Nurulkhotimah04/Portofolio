@extends('layouts.app')
@section('title', 'Data Kriteria')
@section('content')
<a href="{{ route('pengaturan.index') }}" class="app-back-link"><i class="fas fa-arrow-left mr-2"></i>Kembali ke Pengaturan</a>

<div class="app-page-header">
    <div><h1 class="app-page-title">Data Kriteria</h1></div>
    <a href="{{ route('kriteria.create') }}" class="btn btn-success"><i class="fas fa-plus mr-1"></i>Tambah Kriteria</a>
</div>

<div class="app-card">
    <div class="app-card-header d-flex justify-content-between align-items-center">
        <div><div class="app-card-title">Daftar Kriteria</div></div>
        <span class="app-badge app-badge-muted">{{ $kriteria->count() }} data</span>
    </div>
    <div class="table-responsive">
        <table class="table app-table">
            <thead><tr><th style="width:70px;">No</th><th>Kode</th><th>Nama Kriteria</th><th>Bobot</th><th>Atribut</th><th style="width:120px;" class="text-center">Aksi</th></tr></thead>
            <tbody>
            @forelse($kriteria as $item)
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td><span class="app-badge app-badge-success">{{ $item->kode_kriteria }}</span></td>
                    <td class="font-weight-bold text-gray-800">{{ $item->nama_kriteria }}</td>
                    <td>{{ $item->bobot }}</td>
                    <td><span class="app-badge {{ strtolower($item->atribut) === 'benefit' ? 'app-badge-success' : 'app-badge-warning' }}">{{ ucfirst($item->atribut) }}</span></td>
                    <td class="text-center text-nowrap">
                        <a href="{{ route('kriteria.edit', $item->id) }}" class="btn btn-light border app-action-btn" title="Edit"><i class="fas fa-pen text-warning"></i></a>
                        <form action="{{ route('kriteria.destroy', $item->id) }}" method="POST" class="d-inline">@csrf @method('DELETE')
                            <button class="btn btn-light border app-action-btn" title="Hapus" onclick="return confirm('Yakin ingin menghapus kriteria {{ $item->kode_kriteria }}?')"><i class="fas fa-trash text-danger"></i></button>
                        </form>
                    </td>
                </tr>
            @empty<tr><td colspan="6" class="text-center text-muted py-5">Belum ada data Kriteria.</td></tr>@endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
