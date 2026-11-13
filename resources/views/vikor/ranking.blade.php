@extends('layouts.app')
@section('title', 'Hasil Ranking')

@section('content')
<div class="app-page-header">
    <div>
        <h1 class="app-page-title">Hasil Ranking</h1>
    </div>
    <div class="d-flex align-items-center" style="gap:8px;">
        @if(auth()->user()->role === 'admin')
            <a href="{{ route('vikor.index') }}" class="btn btn-success"><i class="fas fa-calculator mr-1"></i>Perhitungan VIKOR</a>
        @endif
    </div>
</div>

<!-- Form Filter Langsung Tampil -->
<div class="card shadow-sm border-0 mb-4">
    <div class="card-body">
        <h6 class="font-weight-bold mb-3">Filter Hasil Ranking</h6>
        <form method="GET" action="{{ route('ranking.index') }}">
            <div class="form-row">
                <div class="form-group col-md-4">
                    <label class="font-weight-bold">Musim Tanam</label>
                    <select name="musim_tanam_id" class="form-control" required>
                        <option value="">-- Pilih Musim --</option>
                        @foreach($musimTanam as $item)
                            <option value="{{ $item->id }}" {{ (string)request('musim_tanam_id')===(string)$item->id?'selected':'' }}>{{ $item->kode_musim }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group col-md-4">
                    <label class="font-weight-bold">Tahun / Periode</label>
                    <select name="periode_id" class="form-control" required>
                        <option value="">-- Pilih Tahun --</option>
                        @foreach($periode as $item)
                            <option value="{{ $item->id }}" {{ (string)request('periode_id')===(string)$item->id?'selected':'' }}>{{ $item->tahun }}</option>
                        @endforeach
                    </select>
                </div>
                @if(auth()->user()->role === 'admin')
                    <div class="form-group col-md-4">
                        <label class="font-weight-bold">Kecamatan</label>
                        <select name="kecamatan_id" class="form-control" required>
                            <option value="">-- Pilih Kecamatan --</option>
                            @foreach($kecamatan as $item)
                                <option value="{{ $item->id }}" {{ (string)request('kecamatan_id')===(string)$item->id?'selected':'' }}>{{ $item->nama_kecamatan }}</option>
                            @endforeach
                        </select>
                    </div>
                @else
                    <div class="form-group col-md-4">
                        <label class="font-weight-bold">Wilayah Binaan</label>
                        <div class="form-control bg-light">{{ $wilayahPenyuluh?->nama_kecamatan ?? 'Belum ditentukan' }}</div>
                        <small class="text-muted">Otomatis dari penugasan aktif.</small>
                    </div>
                @endif
            </div>
            <div class="mt-3">
                <button type="submit" class="btn btn-success px-4"><i class="fas fa-check mr-1"></i>Terapkan</button>
                <a href="{{ route('ranking.index') }}" class="btn btn-light border ml-1">Reset</a>
            </div>
        </form>
    </div>
</div>
<!-- End Form Filter -->

@if($scopeLengkap)
    <div class="d-flex flex-wrap align-items-center mb-3" style="gap:8px;">
        <span class="app-badge app-badge-success">{{ optional($musimDipilih)->kode_musim ?? '-' }}</span>
        <span class="app-badge app-badge-success">{{ optional($periodeDipilih)->tahun ?? '-' }}</span>
        <span class="app-badge app-badge-success">{{ optional($kecamatanDipilih)->nama_kecamatan ?? '-' }}</span>
        <span class="app-badge app-badge-muted">Kuota: {{ $alokasi ? $alokasi->kuota_penerima : 'Belum Diatur' }}</span>
        @if(auth()->user()->role === 'penyuluh')
            <span class="app-badge app-badge-info">Nomor ranking tetap Ranking Kecamatan</span>
        @endif
    </div>

    <div class="app-card">
        <div class="app-card-header d-flex justify-content-between align-items-center">
            <div>
                <div class="app-card-title">Prioritas Penerima</div>
            </div>
            <span class="app-badge app-badge-muted">{{ method_exists($hasil, 'total') ? $hasil->total() : $hasil->count() }} data</span>
        </div>

        <div class="table-responsive">
            <table class="table app-table">
                <thead><tr><th style="width:80px;" class="text-center">Rank</th><th>Kode</th><th>Petani</th><th>Poktan</th><th>Nilai Q</th><th>Status Kuota</th></tr></thead>
                <tbody>
                @forelse($hasil as $item)
                    @php $masukKuota = $alokasi && (int)$item->ranking <= (int)$alokasi->kuota_penerima; @endphp
                    <tr>
                        <td class="text-center"><span class="app-badge {{ (int)$item->ranking === 1 ? 'app-badge-warning' : 'app-badge-success' }}">#{{ $item->ranking }}</span></td>
                        <td class="font-weight-bold">{{ optional($item->alternatifMusim)->kode_alternatif ?? '-' }}</td>
                        <td><div class="font-weight-bold text-gray-800">{{ optional($item->petani)->nama_petani ?? '-' }}</div><small class="text-muted">{{ optional($item->petani)->nik ?? '-' }}</small></td>
                        <td>{{ optional(optional($item->alternatifMusim)->poktan)->nama_poktan ?? '-' }}</td>
                        <td><strong>{{ number_format($item->nilai_q, 8) }}</strong></td>
                        <td>
                            @if(!$alokasi)<span class="app-badge app-badge-warning">Kuota Belum Ada</span>
                            @elseif($masukKuota)<span class="app-badge app-badge-success"><i class="fas fa-check mr-1"></i>Masuk Kuota</span>
                            @else<span class="app-badge app-badge-muted">Di Luar Kuota</span>@endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="text-center text-muted py-5">Belum ada hasil VIKOR pada scope ini.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>

        @if(method_exists($hasil, 'hasPages') && $hasil->hasPages())
            <div class="app-pagination-footer">
                <div class="app-pagination-info">Menampilkan {{ $hasil->firstItem() }}–{{ $hasil->lastItem() }} dari {{ $hasil->total() }} data</div>
                <div class="app-pagination-links">{{ $hasil->links() }}</div>
            </div>
        @endif
    </div>
@endif
@endsection