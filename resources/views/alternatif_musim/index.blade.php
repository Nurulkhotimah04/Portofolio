@extends('layouts.app')
@section('title', 'Data Alternatif')

@section('content')
<div class="app-page-header">
    <div>
        <h1 class="app-page-title">Data Alternatif</h1>
    </div>
    @if(auth()->user()->role === 'admin')
        <a href="{{ route('alternatif-musim.create') }}" class="btn btn-success px-3">
            <i class="fas fa-plus mr-1"></i>Tambah Alternatif
        </a>
    @endif
</div>

@if(auth()->user()->role === 'penyuluh' && $wilayahPenyuluh)
<div class="alert alert-light border py-2"><i class="fas fa-map-marker-alt text-success mr-2"></i>Wilayah binaan: <strong>{{ $wilayahPenyuluh->nama_kecamatan }}</strong></div>
@endif

<div class="app-toolbar">
    <form method="GET" action="{{ route('alternatif-musim.index') }}">
        <input type="hidden" name="musim_tanam_id" value="{{ request('musim_tanam_id') }}">
        <input type="hidden" name="periode_id" value="{{ request('periode_id') }}">
        @if(auth()->user()->role === 'admin')
            <input type="hidden" name="kecamatan_id" value="{{ request('kecamatan_id') }}">
        @endif
        <input type="hidden" name="poktan_id" value="{{ request('poktan_id') }}">

        <div class="row align-items-center">
            <div class="col-lg-9 col-md-8 mb-2 mb-md-0">
                <div class="app-search">
                    <i class="fas fa-search"></i>
                    <input type="text" name="search" value="{{ request('search') }}" class="form-control"
                           placeholder="Cari NIK, nama petani, atau kode alternatif...">
                </div>
            </div>
            <div class="col-lg-1 col-md-2 mb-2 mb-md-0">
                <button class="btn btn-outline-success btn-block" type="submit" title="Cari"><i class="fas fa-search"></i></button>
            </div>
            <div class="col-lg-2 col-md-2 text-md-right">
                <button type="button" class="app-filter-icon-btn" data-toggle="modal" data-target="#filterAlternatifModal" title="Filter" aria-label="Buka filter">
                    <i class="fas fa-filter"></i>
                    @if(request()->filled('musim_tanam_id') || request()->filled('periode_id') || (auth()->user()->role === 'admin' && request()->filled('kecamatan_id')) || request()->filled('poktan_id'))
                        <span class="filter-active-dot"></span>
                    @endif
                </button>
            </div>
        </div>
    </form>

    @php
        $filterMusim = $musimTanam->firstWhere('id', (int) request('musim_tanam_id'));
        $filterPeriode = $periode->firstWhere('id', (int) request('periode_id'));
        $filterKecamatan = auth()->user()->role === 'admin' ? $kecamatan->firstWhere('id', (int) request('kecamatan_id')) : null;
        $filterPoktan = $poktan->firstWhere('id', (int) request('poktan_id'));
    @endphp

    @if(request()->filled('search') || $filterMusim || $filterPeriode || $filterKecamatan || $filterPoktan)
        <div class="d-flex flex-wrap align-items-center mt-2" style="gap:6px;">
            @if(request()->filled('search'))<span class="app-badge app-badge-muted"><i class="fas fa-search mr-1"></i>{{ request('search') }}</span>@endif
            @if($filterMusim)<span class="app-badge app-badge-success">{{ $filterMusim->kode_musim }}</span>@endif
            @if($filterPeriode)<span class="app-badge app-badge-success">{{ $filterPeriode->tahun }}</span>@endif
            @if($filterKecamatan)<span class="app-badge app-badge-success">{{ $filterKecamatan->nama_kecamatan }}</span>@endif
            @if($filterPoktan)<span class="app-badge app-badge-success">{{ $filterPoktan->nama_poktan }}</span>@endif
            <a href="{{ route('alternatif-musim.index') }}" class="small text-danger ml-1"><i class="fas fa-times mr-1"></i>Reset</a>
        </div>
    @endif
</div>

<div class="app-card">
    <div class="app-card-header d-flex justify-content-between align-items-center">
        <div>
            <div class="app-card-title">Daftar Alternatif</div>
        </div>
        <span class="app-badge app-badge-muted">{{ $alternatif->total() }} data</span>
    </div>

    <div class="table-responsive">
        <table class="table app-table">
            <thead>
            <tr>
                <th style="width:80px;">Kode</th>
                <th>Petani</th>
                <th>Poktan / Kecamatan</th>
                <th>Periode</th>
                <th>Penilaian</th>
                <th>Status VIKOR</th>
                <th style="width:140px;" class="text-center">Aksi</th>
            </tr>
            </thead>
            <tbody>
            @forelse($alternatif as $item)
                <tr>
                    <td><span class="app-badge app-badge-success" style="font-size:.75rem;">{{ $item->kode_alternatif }}</span></td>
                    <td>
                        <div class="font-weight-bold text-gray-800">{{ optional($item->petani)->nama_petani ?? '-' }}</div>
                        <small class="text-muted">NIK {{ optional($item->petani)->nik ?? '-' }}</small>
                    </td>
                    <td>
                        <div>{{ optional($item->poktan)->nama_poktan ?? '-' }}</div>
                        <small class="text-muted"><i class="fas fa-map-marker-alt mr-1"></i>{{ optional(optional($item->poktan)->kecamatan)->nama_kecamatan ?? '-' }}</small>
                    </td>
                    <td>
                        <div class="font-weight-bold">{{ optional($item->musimTanam)->kode_musim ?? '-' }}</div>
                        <small class="text-muted">{{ optional($item->periode)->tahun ?? '-' }}</small>
                    </td>
                    <td style="min-width:210px;">
                        <div><strong>C1</strong> {{ $item->luas_lahan }} · <strong>C2</strong> {{ $item->jenis_komoditas }}</div>
                        <small class="text-muted"><strong>C3</strong> {{ $item->kelengkapan_berkas }} · <strong>C4</strong> {{ $item->kebutuhan_pupuk }} · <strong>C5</strong> {{ $item->subsektor_pertanian }}</small>
                        <div class="small mt-1 text-muted"><i class="fas fa-seedling mr-1"></i>{{ $item->ringkasan_pupuk }} @if($item->berkas_persyaratan_path) · <a href="{{ route('alternatif-musim.berkas',$item) }}" target="_blank">PDF</a>@else · PDF belum tersedia @endif</div>
                    </td>
                    <td>
                        @if($item->vikorHasil)
                            <span class="app-badge app-badge-success"><i class="fas fa-check mr-1"></i>Sudah Dihitung</span>
                        @else
                            <span class="app-badge app-badge-muted">Belum Dihitung</span>
                        @endif
                    </td>
                    <td class="text-center text-nowrap">
                        @if(auth()->user()->role === 'admin')
                            <a href="{{ route('alternatif-musim.edit', $item) }}" class="btn btn-light border app-action-btn" title="Edit"><i class="fas fa-pen text-warning"></i></a>
                            <form action="{{ route('alternatif-musim.destroy', $item) }}" method="POST" class="d-inline">@csrf @method('DELETE')<button type="submit" class="btn btn-light border app-action-btn" title="Hapus" onclick="return confirm('Hapus {{ $item->kode_alternatif }}?')"><i class="fas fa-trash text-danger"></i></button></form>
                        @else
                            <a href="{{ route('pengajuan.create',['alternatif_id'=>$item->id]) }}" class="btn btn-outline-success btn-sm" title="Ajukan kembali untuk musim lain"><i class="fas fa-redo mr-1"></i>Ajukan Kembali</a>
                        @endif
                    </td>
                </tr>
            @empty
                <tr><td colspan="7" class="text-center text-muted py-5">Belum ada data alternatif sesuai pencarian/filter.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>

    @if($alternatif->hasPages())
        <div class="app-pagination-footer">
            <div class="app-pagination-info">Menampilkan {{ $alternatif->firstItem() }}–{{ $alternatif->lastItem() }} dari {{ $alternatif->total() }} data</div>
            <div class="app-pagination-links">{{ $alternatif->links() }}</div>
        </div>
    @endif
</div>

<div class="modal fade" id="filterAlternatifModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
        <div class="modal-content">
            <form method="GET" action="{{ route('alternatif-musim.index') }}">
                <input type="hidden" name="search" value="{{ request('search') }}">
                <div class="modal-header">
                    <div>
                        <h5 class="modal-title font-weight-bold">Filter Data Alternatif</h5>
                        <small class="text-muted">Pilih hanya filter yang dibutuhkan.</small>
                    </div>
                    <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                </div>
                <div class="modal-body">
                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label class="font-weight-bold">Musim Tanam</label>
                            <select name="musim_tanam_id" class="form-control">
                                <option value="">Semua Musim Tanam</option>
                                @foreach($musimTanam as $item)<option value="{{ $item->id }}" {{ (string) request('musim_tanam_id') === (string) $item->id ? 'selected' : '' }}>{{ $item->kode_musim }} - {{ $item->nama_musim }}</option>@endforeach
                            </select>
                        </div>
                        <div class="form-group col-md-6">
                            <label class="font-weight-bold">Tahun / Periode</label>
                            <select name="periode_id" class="form-control">
                                <option value="">Semua Tahun</option>
                                @foreach($periode as $item)<option value="{{ $item->id }}" {{ (string) request('periode_id') === (string) $item->id ? 'selected' : '' }}>{{ $item->tahun }}</option>@endforeach
                            </select>
                        </div>
                        @if(auth()->user()->role === 'admin')
                            <div class="form-group col-md-6">
                                <label class="font-weight-bold">Kecamatan</label>
                                <select name="kecamatan_id" class="form-control">
                                    <option value="">Semua Kecamatan</option>
                                    @foreach($kecamatan as $item)<option value="{{ $item->id }}" {{ (string) request('kecamatan_id') === (string) $item->id ? 'selected' : '' }}>{{ $item->nama_kecamatan }}</option>@endforeach
                                </select>
                            </div>
                        @else
                            <div class="form-group col-md-6">
                                <label class="font-weight-bold">Wilayah Binaan</label>
                                <div class="form-control bg-light">{{ $wilayahPenyuluh?->nama_kecamatan ?? 'Belum ditentukan' }}</div>
                                <small class="text-muted">Kecamatan otomatis dari penugasan aktif.</small>
                            </div>
                        @endif
                        <div class="form-group col-md-6">
                            <label class="font-weight-bold">Poktan</label>
                            <select name="poktan_id" class="form-control">
                                <option value="">Semua Poktan</option>
                                @foreach($poktan as $item)<option value="{{ $item->id }}" {{ (string) request('poktan_id') === (string) $item->id ? 'selected' : '' }}>{{ $item->nama_poktan }}{{ $item->kecamatan ? ' - ' . $item->kecamatan->nama_kecamatan : '' }}</option>@endforeach
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <a href="{{ route('alternatif-musim.index') }}" class="btn btn-light border">Reset</a>
                    <button type="button" class="btn btn-light border" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-success px-4"><i class="fas fa-check mr-1"></i>Terapkan Filter</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
