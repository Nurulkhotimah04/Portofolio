@extends('layouts.app')
@section('title', 'Riwayat Perhitungan VIKOR')

@section('content')
<div class="app-page-header">
    <div>
        <h1 class="app-page-title">Riwayat Perhitungan VIKOR</h1>
    </div>
    <a href="{{ route('vikor.index') }}" class="btn btn-light border"><i class="fas fa-arrow-left mr-1"></i>Kembali ke VIKOR</a>
</div>

<div class="app-card">
    <div class="app-card-header d-flex justify-content-between align-items-center">
        <div>
            <div class="app-card-title">Daftar Perhitungan</div>
        </div>
        <span class="app-badge app-badge-muted">{{ $runs->total() }} run</span>
    </div>
    <div class="table-responsive">
        <table class="table app-table">
            <thead><tr><th>Waktu</th><th>Scope</th><th>Kandidat</th><th>Kuota</th><th class="text-center">Aksi</th></tr></thead>
            <tbody>
            @forelse($runs as $run)
                <tr>
                    <td class="text-nowrap">{{ \Carbon\Carbon::parse($run->calculated_at)->format('d-m-Y H:i:s') }}</td>
                    <td>
                        <strong>{{ $run->kode_musim }} / {{ $run->tahun }}</strong><br>
                        <small class="text-muted">Kecamatan {{ $run->nama_kecamatan }}</small>
                    </td>
                    <td>{{ $run->total_alternatif }}</td>
                    <td>{{ $run->kuota_penerima }}</td>
                    <td class="text-center">
                        <a href="{{ route('vikor.riwayat.detail', $run->run_uuid) }}" class="btn btn-light border btn-sm">Lihat</a>
                        <form action="{{ route('vikor.riwayat.hapus', $run->run_uuid) }}" method="POST" class="d-inline">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger btn-sm"
                                onclick="return confirm('Hapus riwayat perhitungan VIKOR ini?')">
                                Hapus
                            </button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr><td colspan="5" class="text-center text-muted py-5">Belum ada riwayat perhitungan.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    @if($runs->hasPages())
        <div class="app-pagination-footer">
            <div class="app-pagination-info">Menampilkan {{ $runs->firstItem() }}–{{ $runs->lastItem() }} dari {{ $runs->total() }} run</div>
            <div class="app-pagination-links">{{ $runs->links() }}</div>
        </div>
    @endif
</div>
@endsection
