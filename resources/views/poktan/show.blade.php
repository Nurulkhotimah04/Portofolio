@extends('layouts.app')
@section('title', 'Detail Poktan')

@section('content')
<a href="{{ route('poktan.index') }}" class="app-back-link">
    <i class="fas fa-arrow-left mr-2"></i>Kembali ke Data Poktan
</a>

<div class="app-page-header">
    <div>
        <h1 class="app-page-title">{{ $poktan->nama_poktan }}</h1>
    </div>

    @if(auth()->user()->role === 'admin')
        <a href="{{ route('poktan.edit', $poktan) }}" class="btn btn-success px-3">
            <i class="fas fa-pen mr-1"></i>Edit Poktan
        </a>
    @endif
</div>

<div class="row">
    <div class="col-xl-4 mb-4">
        <div class="app-card h-100">
            <div class="app-card-header">
                <div class="app-card-title">Informasi Poktan</div>
            </div>
            <div class="p-4">
                <div class="small text-muted mb-1">Nama Poktan</div>
                <div class="font-weight-bold text-gray-800 mb-4">{{ $poktan->nama_poktan }}</div>

                <div class="small text-muted mb-1">Kecamatan</div>
                <div class="font-weight-bold text-gray-800 mb-4">
                    {{ optional($poktan->kecamatan)->nama_kecamatan ?? 'Belum ditentukan' }}
                </div>

                <div class="small text-muted mb-1">Penyuluh</div>
                <div class="font-weight-bold text-gray-800 mb-4">
                    {{ optional($poktan->penyuluh)->nama_penyuluh ?? 'Belum ditentukan' }}
                </div>

                <div class="small text-muted mb-1">Jumlah Petani Master</div>
                <span class="app-badge app-badge-success">{{ $poktan->petani_count }} petani</span>
            </div>
        </div>
    </div>

    <div class="col-xl-8 mb-4">
        <div class="app-card">
            <div class="app-card-header d-flex justify-content-between align-items-center">
                <div>
                    <div class="app-card-title">Petani pada Poktan</div>
                </div>
                <span class="app-badge app-badge-muted">{{ $poktan->petani_count }} data</span>
            </div>

            <div class="table-responsive">
                <table class="table app-table">
                    <thead>
                    <tr>
                        <th style="width:70px;">No</th>
                        <th>NIK</th>
                        <th>Nama Petani</th>
                    </tr>
                    </thead>
                    <tbody>
                    @forelse($petani as $item)
                        <tr>
                            <td>{{ $petani->firstItem() + $loop->index }}</td>
                            <td>{{ $item->nik }}</td>
                            <td class="font-weight-bold text-gray-800">{{ $item->nama_petani }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="text-center text-muted py-5">Belum ada petani pada Poktan ini.</td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>

            @if($petani->hasPages())
                <div class="app-pagination-footer">
                    <div class="app-pagination-info">
                        Menampilkan {{ $petani->firstItem() }}–{{ $petani->lastItem() }} dari {{ $petani->total() }} data
                    </div>
                    <div class="app-pagination-links">{{ $petani->links() }}</div>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
