@extends('layouts.app')

@section('content')

<div class="container-fluid">

    <div class="d-flex justify-content-between align-items-center mb-4">

        <div>

            <h4 class="font-weight-bold text-gray-800 mb-1">

                <i class="fas fa-trophy text-success mr-2"></i>

                {{ auth()->user()->role === 'admin'
                    ? 'Laporan Prioritas Penerima'
                    : 'Hasil Prioritas Petani Binaan' }}

            </h4>

        </div>

        <div class="d-flex align-items-center" style="gap:8px;">
            <a href="{{ route('laporan.index') }}" class="btn btn-secondary">Kembali</a>
        </div>

    </div>

    @if($errors->any())

        <div class="alert alert-danger">

            <ul class="mb-0">

                @foreach($errors->all() as $error)

                    <li>{{ $error }}</li>

                @endforeach

            </ul>

        </div>

    @endif

    <!-- Form Filter Langsung Tampil -->
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-body">
            <h6 class="font-weight-bold mb-3">Filter Laporan Prioritas</h6>
            <form method="GET" action="{{ route('laporan.prioritas') }}">
                <div class="form-row">
                    <div class="form-group {{ $isPenyuluh ? 'col-md-6' : 'col-md-4' }}">
                        <label class="font-weight-bold">Musim Tanam</label>
                        <select name="musim_tanam_id" class="form-control" required>
                            <option value="">-- Pilih Musim --</option>
                            @foreach($musimTanam as $item)
                                <option value="{{ $item->id }}" {{ request('musim_tanam_id') == $item->id ? 'selected' : '' }}>{{ $item->kode_musim }} - {{ $item->nama_musim }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group {{ $isPenyuluh ? 'col-md-6' : 'col-md-4' }}">
                        <label class="font-weight-bold">Tahun</label>
                        <select name="periode_id" class="form-control" required>
                            <option value="">-- Pilih Tahun --</option>
                            @foreach($periode as $item)
                                <option value="{{ $item->id }}" {{ request('periode_id') == $item->id ? 'selected' : '' }}>{{ $item->tahun }}</option>
                            @endforeach
                        </select>
                    </div>

                    @if(!$isPenyuluh)
                        <div class="form-group col-md-4">
                            <label class="font-weight-bold">Kecamatan</label>
                            <select name="kecamatan_id" class="form-control" required>
                                <option value="">-- Pilih Kecamatan --</option>
                                @foreach($kecamatan as $item)
                                    <option value="{{ $item->id }}" {{ request('kecamatan_id') == $item->id ? 'selected' : '' }}>{{ $item->nama_kecamatan }}</option>
                                @endforeach
                            </select>
                        </div>
                    @endif

                    @if($isPenyuluh)
                        <div class="col-12 mt-1">
                            <div class="alert alert-light border mb-0">
                                <div class="d-flex align-items-center">
                                    <i class="fas fa-map-marker-alt text-success mr-3"></i>
                                    <div>
                                        <div class="small text-muted">Wilayah Binaan</div>
                                        <strong>
                                            {{ $kecamatanPenyuluh
                                                ? 'Kecamatan ' . $kecamatanPenyuluh->nama_kecamatan
                                                : 'Kecamatan belum ditentukan' }}
                                        </strong>
                                        <div class="small text-muted mt-1">Kecamatan ditentukan otomatis dari Poktan binaan dan tidak dapat dipilih manual.</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
                <div class="mt-3">
                    <button type="submit" class="btn btn-success px-4"><i class="fas fa-check mr-1"></i>Terapkan</button>
                    <a href="{{ route('laporan.prioritas') }}" class="btn btn-light border ml-1">Reset</a>
                </div>
            </form>
        </div>
    </div>
    <!-- End Form Filter -->

    @if(
        request()->filled('musim_tanam_id') ||
        request()->filled('periode_id') ||
        (!$isPenyuluh && request()->filled('kecamatan_id'))
    )
        <div class="app-filter-summary mb-4">
            <!-- Badge untuk wilayah penyuluh sudah dihapus dari sini -->

            @if(request()->filled('musim_tanam_id'))
                @php $fm = $musimTanam->firstWhere('id', (int) request('musim_tanam_id')); @endphp
                @if($fm)<span class="app-badge app-badge-success">{{ $fm->kode_musim }}</span>@endif
            @endif

            @if(request()->filled('periode_id'))
                @php $fp = $periode->firstWhere('id', (int) request('periode_id')); @endphp
                @if($fp)<span class="app-badge app-badge-success">{{ $fp->tahun }}</span>@endif
            @endif

            @if(!$isPenyuluh && request()->filled('kecamatan_id'))
                @php $fk = $kecamatan->firstWhere('id', (int) request('kecamatan_id')); @endphp
                @if($fk)<span class="app-badge app-badge-success">{{ $fk->nama_kecamatan }}</span>@endif
            @endif
        </div>
    @endif

    @if($data)

        <div class="row mb-4">
            <div class="col-md-3 mb-2">
                <div class="card shadow-sm border-left-success">
                    <div class="card-body">
                        <small class="text-muted">Kandidat Kecamatan</small>
                        <h4 class="font-weight-bold mb-0">{{ $data['totalKandidat'] }}</h4>
                    </div>
                </div>
            </div>

            <div class="col-md-3 mb-2">
                <div class="card shadow-sm border-left-info">
                    <div class="card-body">
                        <small class="text-muted">Kuota</small>
                        <h4 class="font-weight-bold mb-0">{{ $data['kuota'] ?? '-' }}</h4>
                    </div>
                </div>
            </div>

            <div class="col-md-3 mb-2">
                <div class="card shadow-sm border-left-success">
                    <div class="card-body">
                        <small class="text-muted">Masuk Kuota Ditampilkan</small>
                        <h4 class="font-weight-bold mb-0">{{ $data['jumlahMasuk'] }}</h4>
                    </div>
                </div>
            </div>

            <div class="col-md-3 mb-2">
                <div class="card shadow-sm border-left-secondary">
                    <div class="card-body">
                        <small class="text-muted">Di Luar Kuota Ditampilkan</small>
                        <h4 class="font-weight-bold mb-0">{{ $data['jumlahLuar'] }}</h4>
                    </div>
                </div>
            </div>
        </div>

        <div class="d-flex justify-content-between mb-3">
            <div>
                <strong>{{ $data['musimDipilih']->nama_musim }}</strong>
                / {{ $data['periodeDipilih']->tahun }}
                / {{ $data['kecamatanDipilih']->nama_kecamatan }}
            </div>

            <div>
                <a href="{{ route('laporan.prioritas.pdf', request()->only(['musim_tanam_id', 'periode_id', 'kecamatan_id'])) }}" class="btn btn-danger btn-sm">
                    <i class="fas fa-file-pdf mr-1"></i> PDF
                </a>
                <a href="{{ route('laporan.prioritas.excel', request()->only(['musim_tanam_id', 'periode_id', 'kecamatan_id'])) }}" class="btn btn-success btn-sm">
                    <i class="fas fa-file-excel mr-1"></i> Excel
                </a>
            </div>
        </div>

        <div class="card shadow-sm border-0">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-hover table-sm">
                        <thead class="thead-light">
                            <tr>
                                <th>Rank Kecamatan</th>
                                <th>Status</th>
                                <th>Kode</th>
                                <th>NIK</th>
                                <th>Nama Petani</th>
                                <th>Poktan</th>
                                <th>Komoditas</th>
                                <th>Kebutuhan Pupuk yang Diajukan</th>
                                <th>S</th>
                                <th>R</th>
                                <th>Q</th>
                            </tr>
                        </thead>

                        <tbody>
                        @forelse($data['hasil'] as $item)
                            @php
                                $masuk = $data['kuota'] !== null && $item->ranking <= $data['kuota'];
                            @endphp

                            <tr class="{{ $masuk ? 'table-success' : '' }}">
                                <td class="text-center">
                                    <strong>{{ $item->ranking }}</strong>
                                </td>
                                <td>
                                    @if($data['kuota'] === null)
                                        <span class="badge badge-warning">Kuota Belum Diatur</span>
                                    @elseif($masuk)
                                        <span class="badge badge-success">Masuk Kuota</span>
                                    @else
                                        <span class="badge badge-secondary">Di Luar Kuota</span>
                                    @endif
                                </td>
                                <td>{{ optional($item->alternatifMusim)->kode_alternatif ?? '-' }}</td>
                                <td>{{ optional($item->petani)->nik ?? '-' }}</td>
                                <td>{{ optional($item->petani)->nama_petani ?? '-' }}</td>
                                <td>{{ optional(optional($item->alternatifMusim)->poktan)->nama_poktan ?? '-' }}</td>
                                <td>{{ optional($item->alternatifMusim)->jenis_komoditas ?? '-' }}</td>
                                <td>{{ optional($item->alternatifMusim)->ringkasan_pupuk ?? '-' }}</td>
                                <td>{{ number_format($item->nilai_s, 8) }}</td>
                                <td>{{ number_format($item->nilai_r, 8) }}</td>
                                <td>
                                    <strong>{{ number_format($item->nilai_q, 8) }}</strong>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="11" class="text-center text-muted py-4">
                                    Belum ada hasil VIKOR untuk scope tersebut.
                                </td>
                            </tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>

                @if($data['hasil']->hasPages())
                    <div class="app-pagination-footer">
                        <div class="app-pagination-info">
                            Menampilkan {{ $data['hasil']->firstItem() }}–{{ $data['hasil']->lastItem() }} dari {{ $data['hasil']->total() }} data
                        </div>
                        <div class="app-pagination-links">{{ $data['hasil']->links() }}</div>
                    </div>
                @endif
            </div>
        </div>
    @endif
</div>
@endsection