@extends('layouts.app')
@section('title','Preview Import')
@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800">Preview Import</h1>
        <a href="{{ route('pengajuan.import.form') }}" class="btn btn-light border">Kembali</a>
    </div>

    <div class="alert alert-light border">
        <strong>{{ $penyuluh->nama_penyuluh }}</strong> • {{ $musimTanam->kode_musim }} / {{ $periode->tahun }} • {{ $namaFile }}
    </div>

    @if(!empty($zipWarnings))
        <div class="alert alert-warning">
            @foreach($zipWarnings as $warning)<div>{{ $warning }}</div>@endforeach
        </div>
    @endif

    <div class="row mb-3">
        <div class="col-md-6">
            <div class="card border-left-success shadow-sm"><div class="card-body"><div class="text-xs font-weight-bold text-success text-uppercase">Valid</div><div class="h4 mb-0">{{ count($validRows) }}</div></div></div>
        </div>
        <div class="col-md-6">
            <div class="card border-left-danger shadow-sm"><div class="card-body"><div class="text-xs font-weight-bold text-danger text-uppercase">Ditolak</div><div class="h4 mb-0">{{ count($errorRows) }}</div></div></div>
        </div>
    </div>

    @if(count($validRows))
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-header bg-white font-weight-bold text-success">Data Valid</div>
            <div class="table-responsive">
                <table class="table table-sm table-hover mb-0">
                    <thead><tr><th>Baris</th><th>NIK / Nama</th><th>Poktan</th><th>C1</th><th>C2</th><th>C3/PDF</th><th>C4</th><th>C5</th></tr></thead>
                    <tbody>
                    @foreach($validRows as $r)
                        <tr>
                            <td>{{ $r['baris'] }}</td>
                            <td><strong>{{ $r['nama_petani'] }}</strong><br><small>{{ $r['nik'] }}</small></td>
                            <td>{{ $r['nama_poktan'] }}<br><small class="text-muted">{{ $r['nama_kecamatan'] ?? '-' }}</small></td>
                            <td>{{ $r['luas_lahan_ha'] }} Ha<br><small>{{ $r['luas_lahan'] }}</small></td>
                            <td>{{ $r['jenis_komoditas'] }}</td>
                            <td>{{ $r['kelengkapan_berkas'] }}<br><span class="badge badge-success">PDF ditemukan</span></td>
                            <td>{{ $r['kebutuhan_pupuk'] }}<br><small>@foreach($r['pupuk_rows'] as $id=>$kg){{ optional($jenisPupuk->firstWhere('id',$id))->nama_pupuk }} {{ $kg }} kg{{ !$loop->last?', ':'' }}@endforeach</small></td>
                            <td>{{ $r['subsektor_pertanian'] }}</td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <form method="POST" action="{{ route('pengajuan.import.commit') }}" class="text-right mb-4">
            @csrf
            <input type="hidden" name="token" value="{{ $token }}">
            <button class="btn btn-success" onclick="return confirm('Import semua data valid?')">Import {{ count($validRows) }} Data Valid</button>
        </form>
    @endif

    @if(count($errorRows))
        <div class="card shadow-sm border-0">
            <div class="card-header bg-white font-weight-bold text-danger">Data Ditolak</div>
            <div class="table-responsive">
                <table class="table table-sm mb-0">
                    <thead><tr><th>Baris</th><th>NIK / Nama</th><th>Penyuluh Excel</th><th>Poktan</th><th>Alasan</th></tr></thead>
                    <tbody>
                    @foreach($errorRows as $r)
                        <tr>
                            <td>{{ $r['baris'] }}</td>
                            <td>{{ $r['nik'] }}<br><strong>{{ $r['nama_petani'] }}</strong></td>
                            <td>{{ $r['penyuluh_excel'] ?: '-' }}</td>
                            <td>{{ $r['poktan'] ?: '-' }}</td>
                            <td><ul class="mb-0 pl-3">@foreach($r['errors'] as $e)<li>{{ $e }}</li>@endforeach</ul></td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif
</div>
@endsection
