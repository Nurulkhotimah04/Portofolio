@extends('layouts.app')
@section('title', 'Detail Riwayat VIKOR')

@section('content')
<div class="app-page-header">
    <div>
        <h1 class="app-page-title">Detail Riwayat VIKOR</h1>
    </div>
    <a href="{{ route('vikor.riwayat') }}" class="btn btn-light border"><i class="fas fa-arrow-left mr-1"></i>Kembali</a>
</div>

@php
    $alt = $first->alternatifMusim;
@endphp
<div class="d-flex flex-wrap mb-3" style="gap:8px;">
    <span class="app-badge app-badge-success">{{ $alt?->musimTanam?->kode_musim ?? '-' }}</span>
    <span class="app-badge app-badge-success">{{ $alt?->periode?->tahun ?? '-' }}</span>
    <span class="app-badge app-badge-success">{{ $alt?->poktan?->kecamatan?->nama_kecamatan ?? '-' }}</span>
    <span class="app-badge app-badge-muted">Kuota {{ $first->kuota_penerima }}</span>
    <span class="app-badge app-badge-muted">{{ $first->calculated_at?->format('d-m-Y H:i:s') }}</span>
</div>

<div class="app-card">
    <div class="table-responsive">
        <table class="table app-table">
            <thead><tr><th>Rank</th><th>Kode</th><th>Petani</th><th>Poktan</th><th>Q</th><th>Status</th></tr></thead>
            <tbody>
            @forelse($hasil as $item)
                <tr>
                    <td><strong>#{{ $item->ranking }}</strong></td>
                    <td>{{ $item->kode_alternatif ?? $item->alternatifMusim?->kode_alternatif ?? '-' }}</td>
                    <td>{{ $item->nama_petani ?? $item->petani?->nama_petani ?? '-' }}<br><small class="text-muted">{{ $item->nik ?? $item->petani?->nik ?? '-' }}</small></td>
                    <td>{{ $item->nama_poktan ?? $item->alternatifMusim?->poktan?->nama_poktan ?? '-' }}</td>
                    <td>{{ number_format((float)$item->nilai_q, 8) }}</td>
                    <td>
                        @if($item->status_kuota === 'masuk_kuota')
                            <span class="app-badge app-badge-success">Masuk Kuota</span>
                        @else
                            <span class="app-badge app-badge-muted">Di Luar Kuota</span>
                        @endif
                    </td>
                </tr>
            @empty
                <tr><td colspan="6" class="text-center text-muted py-5">Snapshot tidak memiliki data.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    @if($hasil->hasPages())
        <div class="app-pagination-footer">
            <div class="app-pagination-info">Menampilkan {{ $hasil->firstItem() }}–{{ $hasil->lastItem() }} dari {{ $hasil->total() }} data</div>
            <div class="app-pagination-links">{{ $hasil->links() }}</div>
        </div>
    @endif
</div>
@endsection
