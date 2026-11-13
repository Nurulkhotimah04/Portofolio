@extends('layouts.app')
@section('title', 'Data Penyuluh')
@section('content')
<div class="app-page-header">
    <div>
        <h1 class="app-page-title">Data Penyuluh</h1>
    </div>
    <a href="{{ route('penyuluh.create') }}" class="btn btn-success px-3"><i class="fas fa-plus mr-1"></i> Tambah Penyuluh</a>
</div>

@if(session('success'))<div class="alert alert-success app-alert">{{ session('success') }}</div>@endif
@if(session('error'))<div class="alert alert-danger app-alert">{{ session('error') }}</div>@endif

<div class="app-toolbar">
    <div class="row align-items-center">
        <div class="col-md-10 mb-2 mb-md-0">
            <form method="GET" action="{{ route('penyuluh.index') }}" class="d-flex">
                @if(request('status'))<input type="hidden" name="status" value="{{ request('status') }}">@endif
                <div class="app-search flex-grow-1"><i class="fas fa-search"></i><input type="text" name="search" value="{{ request('search') }}" class="form-control" placeholder="Cari nama, username, atau Kecamatan..."></div>
            </form>
        </div>
        <div class="col-md-2 text-md-right">
            <button type="button" class="btn btn-light border position-relative" data-toggle="modal" data-target="#filterPenyuluhModal" title="Filter">
                <i class="fas fa-filter"></i>
                @if(request()->filled('status'))<span class="position-absolute bg-success rounded-circle" style="width:7px;height:7px;right:5px;top:5px;"></span>@endif
            </button>
        </div>
    </div>
    @if(request()->filled('search') || request()->filled('status'))
        <div class="mt-2"><a href="{{ route('penyuluh.index') }}" class="small text-muted"><i class="fas fa-times mr-1"></i>Reset pencarian & filter</a></div>
    @endif
</div>

<div class="app-card">
    <div class="app-card-header d-flex justify-content-between align-items-center">
        <div><div class="app-card-title">Daftar Penyuluh</div></div>
        <span class="app-badge app-badge-muted">{{ $penyuluh->total() }} data</span>
    </div>
    <div class="table-responsive">
        <table class="table app-table">
            <thead><tr><th style="width:60px">No</th><th>Penyuluh</th><th>Kecamatan Aktif</th><th>Poktan</th><th>Status</th><th style="width:120px" class="text-center">Aksi</th></tr></thead>
            <tbody>
            @forelse($penyuluh as $item)
                <tr>
                    <td>{{ $penyuluh->firstItem() + $loop->index }}</td>
                    <td><div class="font-weight-bold">{{ $item->nama_penyuluh }}</div><small class="text-muted">{{ optional($item->user)->username ?? '-' }}</small></td>
                    <td>
                        @if($item->penugasanAktif?->kecamatan)
                            <div class="font-weight-bold">{{ $item->penugasanAktif->kecamatan->nama_kecamatan }}</div>
                            <small class="text-muted">Sejak {{ optional($item->penugasanAktif->tanggal_mulai)->format('d-m-Y') }}</small>
                        @else
                            <span class="text-muted">Tidak ada penugasan aktif</span>
                        @endif
                    </td>
                    <td><strong>{{ $item->poktan_count }}</strong> Poktan</td>
                    <td>
                        @php $statusClass = $item->status === 'aktif' ? 'success' : ($item->status === 'mutasi_keluar' ? 'warning' : 'secondary'); @endphp
                        <span class="badge badge-{{ $statusClass }}">{{ $item->status === 'mutasi_keluar' ? 'Mutasi Keluar' : ucfirst($item->status) }}</span>
                    </td>
                    <td class="text-center text-nowrap">
                        <a href="{{ route('penyuluh.edit', $item->id) }}" class="btn btn-light border app-action-btn" title="Edit"><i class="fas fa-pen text-warning"></i></a>
                        <form action="{{ route('penyuluh.destroy', $item->id) }}" method="POST" class="d-inline">@csrf @method('DELETE')
                            <button type="submit" class="btn btn-light border app-action-btn" title="Hapus hanya jika belum ada histori" onclick="return confirm('Hapus hanya diperbolehkan jika Penyuluh belum memiliki riwayat operasional. Lanjutkan?')"><i class="fas fa-trash text-danger"></i></button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr><td colspan="6" class="text-center text-muted py-5">Belum ada data Penyuluh.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    @if($penyuluh->hasPages())
        <div class="app-pagination-footer"><div class="app-pagination-info">Menampilkan {{ $penyuluh->firstItem() }}–{{ $penyuluh->lastItem() }} dari {{ $penyuluh->total() }} data</div><div class="app-pagination-links">{{ $penyuluh->links() }}</div></div>
    @endif
</div>

<div class="modal fade" id="filterPenyuluhModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-sm modal-dialog-centered"><div class="modal-content">
        <form method="GET" action="{{ route('penyuluh.index') }}">
            @if(request('search'))<input type="hidden" name="search" value="{{ request('search') }}">@endif
            <div class="modal-header"><h5 class="modal-title font-weight-bold">Filter Penyuluh</h5><button type="button" class="close" data-dismiss="modal"><span>&times;</span></button></div>
            <div class="modal-body"><label class="font-weight-bold">Status</label><select name="status" class="form-control"><option value="">Semua Status</option><option value="aktif" {{ request('status') === 'aktif' ? 'selected' : '' }}>Aktif</option><option value="nonaktif" {{ request('status') === 'nonaktif' ? 'selected' : '' }}>Nonaktif</option><option value="mutasi_keluar" {{ request('status') === 'mutasi_keluar' ? 'selected' : '' }}>Mutasi Keluar</option></select></div>
            <div class="modal-footer"><a href="{{ route('penyuluh.index', request('search') ? ['search'=>request('search')] : []) }}" class="btn btn-light border">Reset</a><button class="btn btn-success">Terapkan</button></div>
        </form>
    </div></div>
</div>
@endsection
