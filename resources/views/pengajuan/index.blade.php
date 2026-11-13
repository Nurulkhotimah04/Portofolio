@extends('layouts.app')
@section('title', auth()->user()->role === 'admin' ? 'Verifikasi Pengajuan' : 'Pengajuan Petani')

@section('content')
<div class="app-page-header">
    <h1 class="app-page-title">{{ auth()->user()->role === 'admin' ? 'Verifikasi Pengajuan Petani' : 'Pengajuan Petani' }}</h1>
    @if(auth()->user()->role === 'penyuluh')
        <div class="d-flex flex-wrap" style="gap:8px;">
            <a href="{{ route('pengajuan.import.form') }}" class="btn btn-light border"><i class="fas fa-file-excel text-success mr-1"></i>Import Excel</a>
            <a href="{{ route('pengajuan.create') }}" class="btn btn-success"><i class="fas fa-plus mr-1"></i>Pengajuan Baru</a>
        </div>
    @endif
</div>

@if($errors->any())
    <div class="alert alert-danger app-alert">
        <strong>Proses belum dapat dilakukan:</strong>
        <ul class="mb-0 mt-2">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
    </div>
@endif

<div class="app-toolbar">
    <form method="GET" action="{{ route('pengajuan.index') }}">
        @foreach(['status','musim_tanam_id','periode_id','asal_input','poktan_id'] as $field)
            <input type="hidden" name="{{ $field }}" value="{{ request($field) }}">
        @endforeach
        @if(auth()->user()->role === 'admin')
            <input type="hidden" name="kecamatan_id" value="{{ request('kecamatan_id') }}">
            <input type="hidden" name="penyuluh_id" value="{{ request('penyuluh_id') }}">
        @endif

        <div class="row align-items-center">
            <div class="col-lg-9 col-md-8 mb-2 mb-md-0">
                <div class="app-search">
                    <i class="fas fa-search"></i>
                    <input type="text" name="search" value="{{ request('search') }}" class="form-control" placeholder="Cari NIK, nama petani, atau Poktan...">
                </div>
            </div>
            <div class="col-lg-1 col-md-2 mb-2 mb-md-0">
                <button class="btn btn-outline-success btn-block" type="submit" title="Cari"><i class="fas fa-search"></i></button>
            </div>
            <div class="col-lg-2 col-md-2 text-md-right">
                <button type="button" class="app-filter-icon-btn" data-toggle="modal" data-target="#filterPengajuanModal" title="Filter" aria-label="Buka filter">
                    <i class="fas fa-filter"></i>
                    @if(request()->filled('musim_tanam_id') || request()->filled('periode_id') || request()->filled('poktan_id') || request()->filled('asal_input') || request()->filled('status') || (auth()->user()->role === 'admin' && (request()->filled('kecamatan_id') || request()->filled('penyuluh_id'))))
                        <span class="filter-active-dot"></span>
                    @endif
                </button>
            </div>
        </div>
    </form>

    <div class="d-flex flex-wrap mt-3" style="gap:7px;">
        @php
            $statusItems = [
                '' => ['Semua', $statusCounts['semua']],
                'menunggu_verifikasi' => ['Menunggu', $statusCounts['menunggu_verifikasi']],
                'perlu_perbaikan' => ['Perlu Perbaikan', $statusCounts['perlu_perbaikan']],
                'disetujui' => ['Disetujui', $statusCounts['disetujui']],
                'ditolak' => ['Ditolak', $statusCounts['ditolak']],
            ];
        @endphp

        @foreach($statusItems as $statusValue => $statusInfo)
            @php
                $params = request()->except(['status', 'page']);
                if ($statusValue !== '') $params['status'] = $statusValue;
                $active = (string) request('status') === (string) $statusValue;
            @endphp
            <a href="{{ route('pengajuan.index', $params) }}" class="app-badge {{ $active ? 'app-badge-success' : 'app-badge-muted' }} text-decoration-none">
                {{ $statusInfo[0] }} <span class="ml-1">{{ $statusInfo[1] }}</span>
            </a>
        @endforeach

        @if(request()->filled('search') || request()->filled('status') || request()->filled('musim_tanam_id') || request()->filled('periode_id') || request()->filled('poktan_id') || request()->filled('asal_input') || (auth()->user()->role === 'admin' && (request()->filled('kecamatan_id') || request()->filled('penyuluh_id'))))
            <a href="{{ route('pengajuan.index') }}" class="small text-danger d-inline-flex align-items-center ml-1"><i class="fas fa-times mr-1"></i>Reset</a>
        @endif
    </div>
</div>

<div class="app-card">
    <div class="app-card-header d-flex justify-content-between align-items-center">
        <div class="app-card-title">Daftar Pengajuan</div>
        <span class="app-badge app-badge-muted">{{ $pengajuan->total() }} data</span>
    </div>

    <div class="table-responsive">
        <table class="table app-table">
            <thead>
            <tr>
                <th style="width:65px;">No</th>
                <th>Petani</th>
                <th>Periode</th>
                <th>Poktan / Kecamatan</th>
                @if(auth()->user()->role === 'admin')<th>Penyuluh</th>@endif
                <th>Input</th>
                <th>Status</th>
                @if(auth()->user()->role === 'admin')<th>Verifikasi C3</th>@endif
                <th style="width:100px;" class="text-center">Aksi</th>
            </tr>
            </thead>
            <tbody>
            @forelse($pengajuan as $item)
                <tr>
                    <td>{{ $pengajuan->firstItem() + $loop->index }}</td>
                    <td>
                        <div class="font-weight-bold text-gray-800">{{ $item->nama_petani }}</div>
                        <small class="text-muted">{{ $item->nik }}</small>
                    </td>
                    <td>
                        <div class="font-weight-bold">{{ optional($item->musimTanam)->kode_musim ?? '-' }}</div>
                        <small class="text-muted">{{ optional($item->periode)->tahun ?? '-' }}</small>
                    </td>
                    <td>
                        <div>{{ optional($item->poktan)->nama_poktan ?? '-' }}</div>
                        <small class="text-muted"><i class="fas fa-map-marker-alt mr-1"></i>{{ optional(optional($item->poktan)->kecamatan)->nama_kecamatan ?? '-' }}</small>
                    </td>
                    @if(auth()->user()->role === 'admin')<td>{{ optional($item->penyuluh)->nama_penyuluh ?? '-' }}</td>@endif
                    <td><span class="app-badge {{ $item->asal_input === 'import' ? 'app-badge-info' : 'app-badge-muted' }}">{{ $item->asal_input === 'import' ? 'Excel' : 'Manual' }}</span></td>
                    <td>
                        @php
                            $statusClass = match($item->status) {
                                'menunggu_verifikasi' => 'app-badge-warning',
                                'perlu_perbaikan' => 'app-badge-info',
                                'disetujui' => 'app-badge-success',
                                'ditolak' => 'app-badge-danger',
                                default => 'app-badge-muted'
                            };
                        @endphp
                        <span class="app-badge {{ $statusClass }}">{{ $item->status_label }}</span>
                    </td>
                    @if(auth()->user()->role === 'admin')
                        <td>
                            @if($item->c3_terverifikasi)
                                <span class="app-badge app-badge-success"><i class="fas fa-check mr-1"></i>{{ $item->kelengkapan_berkas_verifikasi }}</span>
                            @else
                                <span class="app-badge app-badge-warning">Belum</span>
                            @endif
                        </td>
                    @endif
                    <td class="text-center text-nowrap">
                        <a href="{{ route('pengajuan.show', $item) }}" class="btn btn-light border px-2" title="Periksa / Detail">{{ auth()->user()->role === 'admin' ? 'Periksa' : 'Detail' }}</a>
                        @if(auth()->user()->role === 'penyuluh' && in_array($item->status, ['menunggu_verifikasi','perlu_perbaikan'], true))
                            <a href="{{ route('pengajuan.edit', $item) }}" class="btn btn-light border app-action-btn" title="Edit"><i class="fas fa-pen text-warning"></i></a>
                        @endif
                    </td>
                </tr>
            @empty
                <tr><td colspan="{{ auth()->user()->role === 'admin' ? 9 : 7 }}" class="text-center text-muted py-5">Belum ada pengajuan sesuai pencarian/filter.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>

    @if($pengajuan->hasPages())
        <div class="app-pagination-footer">
            <div class="app-pagination-info">Menampilkan {{ $pengajuan->firstItem() }}–{{ $pengajuan->lastItem() }} dari {{ $pengajuan->total() }} data</div>
            <div class="app-pagination-links">{{ $pengajuan->links() }}</div>
        </div>
    @endif
</div>

<div class="modal fade" id="filterPengajuanModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
        <div class="modal-content">
            <form method="GET" action="{{ route('pengajuan.index') }}">
                <input type="hidden" name="search" value="{{ request('search') }}">
                <div class="modal-header">
                    <h5 class="modal-title font-weight-bold">Filter Pengajuan</h5>
                    <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                </div>
                <div class="modal-body">
                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label class="font-weight-bold">Musim Tanam</label>
                            <select name="musim_tanam_id" class="form-control"><option value="">Semua Musim</option>@foreach($musimTanam as $item)<option value="{{ $item->id }}" {{ (string)request('musim_tanam_id')===(string)$item->id?'selected':'' }}>{{ $item->kode_musim }}</option>@endforeach</select>
                        </div>
                        <div class="form-group col-md-6">
                            <label class="font-weight-bold">Tahun / Periode</label>
                            <select name="periode_id" class="form-control"><option value="">Semua Tahun</option>@foreach($periode as $item)<option value="{{ $item->id }}" {{ (string)request('periode_id')===(string)$item->id?'selected':'' }}>{{ $item->tahun }}</option>@endforeach</select>
                        </div>
                        @if(auth()->user()->role === 'admin')
                            <div class="form-group col-md-6">
                                <label class="font-weight-bold">Kecamatan</label>
                                <select name="kecamatan_id" class="form-control"><option value="">Semua Kecamatan</option>@foreach($kecamatan as $item)<option value="{{ $item->id }}" {{ (string)request('kecamatan_id')===(string)$item->id?'selected':'' }}>{{ $item->nama_kecamatan }}</option>@endforeach</select>
                            </div>
                            <div class="form-group col-md-6">
                                <label class="font-weight-bold">Penyuluh</label>
                                <select name="penyuluh_id" class="form-control"><option value="">Semua Penyuluh</option>@foreach($penyuluhOptions as $item)<option value="{{ $item->id }}" {{ (string)request('penyuluh_id')===(string)$item->id?'selected':'' }}>{{ $item->nama_penyuluh }}</option>@endforeach</select>
                            </div>
                        @endif
                        <div class="form-group col-md-6">
                            <label class="font-weight-bold">Poktan</label>
                            <select name="poktan_id" class="form-control"><option value="">Semua Poktan</option>@foreach($poktanOptions as $item)<option value="{{ $item->id }}" {{ (string)request('poktan_id')===(string)$item->id?'selected':'' }}>{{ $item->nama_poktan }}</option>@endforeach</select>
                        </div>
                        <div class="form-group col-md-6">
                            <label class="font-weight-bold">Asal Input</label>
                            <select name="asal_input" class="form-control"><option value="">Semua Input</option><option value="manual" {{ request('asal_input')==='manual'?'selected':'' }}>Manual</option><option value="import" {{ request('asal_input')==='import'?'selected':'' }}>Import Excel</option></select>
                        </div>
                        <div class="form-group col-md-6 mb-0">
                            <label class="font-weight-bold">Status</label>
                            <select name="status" class="form-control"><option value="">Semua Status</option><option value="menunggu_verifikasi" {{ request('status')==='menunggu_verifikasi'?'selected':'' }}>Menunggu Verifikasi</option><option value="perlu_perbaikan" {{ request('status')==='perlu_perbaikan'?'selected':'' }}>Perlu Perbaikan</option><option value="disetujui" {{ request('status')==='disetujui'?'selected':'' }}>Disetujui</option><option value="ditolak" {{ request('status')==='ditolak'?'selected':'' }}>Ditolak</option></select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <a href="{{ route('pengajuan.index') }}" class="btn btn-light border">Reset</a>
                    <button type="button" class="btn btn-light border" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-success px-4"><i class="fas fa-check mr-1"></i>Terapkan</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
