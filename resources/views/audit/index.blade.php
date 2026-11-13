@extends('layouts.app')
@section('title', 'Riwayat Aktivitas')

@section('content')
<div class="app-page-header">
    <h1 class="app-page-title">Riwayat Aktivitas</h1>
    <a href="{{ route('pengaturan.index') }}" class="btn btn-light border"><i class="fas fa-arrow-left mr-1"></i>Kembali ke Pengaturan</a>
</div>

@if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
@if($errors->any())<div class="alert alert-danger">{{ $errors->first() }}</div>@endif

<div class="app-toolbar">
    <form method="GET" action="{{ route('audit.index') }}">
        <div class="row align-items-center">
            <div class="col-lg-8 mb-2 mb-lg-0">
                <div class="app-search">
                    <i class="fas fa-search"></i>
                    <input type="text" name="search" class="form-control" value="{{ request('search') }}" placeholder="Cari aktivitas, pengguna, atau modul...">
                </div>
            </div>
            <div class="col-lg-3 mb-2 mb-lg-0">
                <select name="module" class="form-control">
                    <option value="">Semua Modul</option>
                    @foreach($modules as $module)
                        <option value="{{ $module }}" {{ request('module') === $module ? 'selected' : '' }}>{{ ucfirst($module) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-lg-1"><button class="btn btn-success btn-block" title="Terapkan"><i class="fas fa-search"></i></button></div>
        </div>
    </form>
</div>

<div class="d-flex justify-content-end mb-3" style="gap:8px;">
    <button type="button" id="deleteSelectedBtn" class="btn btn-outline-danger" disabled><i class="fas fa-trash mr-1"></i>Hapus Terpilih</button>
    <form method="POST" action="{{ route('audit.destroy-all') }}" onsubmit="return confirm('Hapus seluruh riwayat aktivitas? Tindakan ini tidak dapat dibatalkan.');">
        @csrf
        @method('DELETE')
        <button class="btn btn-danger" {{ $logs->total() === 0 ? 'disabled' : '' }}><i class="fas fa-trash-alt mr-1"></i>Hapus Semua</button>
    </form>
</div>

<form id="selectedForm" method="POST" action="{{ route('audit.destroy-selected') }}">
    @csrf
    @method('DELETE')
    <div class="app-card">
        <div class="app-card-header d-flex justify-content-between align-items-center">
            <div class="app-card-title">Aktivitas Sistem</div>
            <span class="app-badge app-badge-muted">{{ $logs->total() }} log</span>
        </div>
        <div class="table-responsive">
            <table class="table app-table">
                <thead><tr><th style="width:45px;" class="text-center"><input type="checkbox" id="selectAll"></th><th>Waktu</th><th>Pengguna</th><th>Modul</th><th>Aksi</th><th>Keterangan</th></tr></thead>
                <tbody>
                @forelse($logs as $log)
                    <tr>
                        <td class="text-center"><input type="checkbox" class="logCheck" name="ids[]" value="{{ $log->id }}"></td>
                        <td class="text-nowrap">{{ $log->created_at?->format('d-m-Y H:i:s') }}</td>
                        <td><strong>{{ $log->user?->name ?? 'Sistem' }}</strong><br><small class="text-muted">{{ $log->user?->username ?? '-' }}</small></td>
                        <td><span class="app-badge app-badge-muted">{{ ucfirst($log->module) }}</span></td>
                        <td><span class="app-badge app-badge-info">{{ str_replace('_', ' ', ucfirst($log->action)) }}</span></td>
                        <td>{{ $log->description ?: '-' }}</td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="text-center text-muted py-5">Belum ada aktivitas yang tercatat.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
        @if($logs->hasPages())
            <div class="app-pagination-footer">
                <div class="app-pagination-info">Menampilkan {{ $logs->firstItem() }}–{{ $logs->lastItem() }} dari {{ $logs->total() }} log</div>
                <div class="app-pagination-links">{{ $logs->links() }}</div>
            </div>
        @endif
    </div>
</form>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const all = document.getElementById('selectAll');
    const checks = Array.from(document.querySelectorAll('.logCheck'));
    const button = document.getElementById('deleteSelectedBtn');
    const form = document.getElementById('selectedForm');

    function refresh() {
        const selected = checks.filter(c => c.checked).length;
        button.disabled = selected === 0;
        if (all) all.checked = checks.length > 0 && selected === checks.length;
    }

    all?.addEventListener('change', () => {
        checks.forEach(c => c.checked = all.checked);
        refresh();
    });
    checks.forEach(c => c.addEventListener('change', refresh));
    button?.addEventListener('click', () => {
        const total = checks.filter(c => c.checked).length;
        if (total && confirm('Hapus ' + total + ' riwayat terpilih?')) form.submit();
    });
});
</script>
@endpush
