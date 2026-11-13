@extends('layouts.app')

@section('content')

<div class="container-fluid">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="font-weight-bold text-gray-800 mb-1">
                <i class="fas fa-map-marked-alt text-success mr-2"></i>
                Rekap Prioritas per Kecamatan
            </h4>
        </div>
        <div class="d-flex align-items-center" style="gap:8px;">
            <a href="{{ route('laporan.index') }}" class="btn btn-secondary">Kembali</a>
        </div>
    </div>

    <!-- Form Filter Langsung Tampil -->
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-body">
            <h6 class="font-weight-bold mb-3">Filter Rekap Kecamatan</h6>
            <form method="GET" action="{{ route('laporan.rekap') }}">
                <div class="form-row">
                    <div class="form-group col-md-6">
                        <label class="font-weight-bold">Musim Tanam</label>
                        <select name="musim_tanam_id" class="form-control" required>
                            <option value="">-- Pilih Musim --</option>
                            @foreach($musimTanam as $item)
                                <option value="{{ $item->id }}" {{ request('musim_tanam_id') == $item->id ? 'selected' : '' }}>{{ $item->kode_musim }} - {{ $item->nama_musim }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group col-md-6">
                        <label class="font-weight-bold">Tahun</label>
                        <select name="periode_id" class="form-control" required>
                            <option value="">-- Pilih Tahun --</option>
                            @foreach($periode as $item)
                                <option value="{{ $item->id }}" {{ request('periode_id') == $item->id ? 'selected' : '' }}>{{ $item->tahun }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="mt-3">
                    <button type="submit" class="btn btn-success px-4"><i class="fas fa-check mr-1"></i>Terapkan</button>
                    <a href="{{ route('laporan.rekap') }}" class="btn btn-light border ml-1">Reset</a>
                </div>
            </form>
        </div>
    </div>
    <!-- End Form Filter -->

    @if(request()->filled('musim_tanam_id') || request()->filled('periode_id'))
        <div class="app-filter-summary mb-4">
            @if(request()->filled('musim_tanam_id'))
                @php $fm = $musimTanam->firstWhere('id', (int) request('musim_tanam_id')); @endphp
                @if($fm)<span class="app-badge app-badge-success">{{ $fm->kode_musim }}</span>@endif
            @endif
            @if(request()->filled('periode_id'))
                @php $fp = $periode->firstWhere('id', (int) request('periode_id')); @endphp
                @if($fp)<span class="app-badge app-badge-success">{{ $fp->tahun }}</span>@endif
            @endif
        </div>
    @endif

    @if($data)
        <div class="d-flex justify-content-between mb-3">
            <div>
                <strong>{{ $data['musimDipilih']->nama_musim }}</strong>
                / {{ $data['periodeDipilih']->tahun }}
            </div>
            <div>
                <a href="{{ route('laporan.rekap.pdf', request()->only(['musim_tanam_id', 'periode_id'])) }}" class="btn btn-danger btn-sm">
                    <i class="fas fa-file-pdf mr-1"></i> PDF
                </a>
                <a href="{{ route('laporan.rekap.excel', request()->only(['musim_tanam_id', 'periode_id'])) }}" class="btn btn-success btn-sm">
                    <i class="fas fa-file-excel mr-1"></i> Excel
                </a>
            </div>
        </div>

        <div class="card shadow-sm border-0">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-hover">
                        <thead class="thead-light">
                            <tr>
                                <th>No</th>
                                <th>Kecamatan</th>
                                <th>Kandidat</th>
                                <th>Kuota</th>
                                <th>Sudah Dihitung</th>
                                <th>Masuk Kuota</th>
                                <th>Di Luar Kuota</th>
                                <th>Belum Dihitung</th>
                                <th>Kebutuhan Pupuk yang Diajukan</th>
                            </tr>
                        </thead>
                        <tbody>
                        @foreach($data['rekap'] as $item)
                            <tr>
                                <td>{{ $data['rekap']->firstItem() + $loop->index }}</td>
                                <td><strong>{{ $item['kecamatan']->nama_kecamatan }}</strong></td>
                                <td>{{ $item['kandidat'] }}</td>
                                <td>{{ $item['kuota'] ?? 'Belum Diatur' }}</td>
                                <td>{{ $item['sudah_dihitung'] }}</td>
                                <td class="text-success font-weight-bold">{{ $item['masuk_kuota'] }}</td>
                                <td>{{ $item['di_luar_kuota'] }}</td>
                                <td>{{ $item['belum_dihitung'] }}</td>
                                <td>
                                    <div>{{ $item['ringkasan_pupuk'] }}</div>
                                    @if($item['detail_pupuk_belum_tercatat'] > 0)
                                        <small class="text-muted">{{ $item['detail_pupuk_belum_tercatat'] }} alternatif belum memiliki rincian pupuk.</small>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                        <tfoot class="font-weight-bold">
                            <tr>
                                <td colspan="2">TOTAL</td>
                                <td>{{ $data['totalKandidat'] }}</td>
                                <td>{{ $data['totalKuota'] }}</td>
                                <td>{{ $data['totalKandidat'] - $data['totalBelumDihitung'] }}</td>
                                <td>{{ $data['totalMasuk'] }}</td>
                                <td>{{ $data['totalLuar'] }}</td>
                                <td>{{ $data['totalBelumDihitung'] }}</td>
                                <td>
                                    <div><strong>{{ $data['totalRingkasanPupuk'] }}</strong></div>
                                    <small class="text-muted">{{ $data['totalDetailPupukTercatat'] }} alternatif memiliki rincian; {{ $data['totalDetailPupukBelumTercatat'] }} belum memiliki rincian.</small>
                                </td>
                            </tr>
                        </tfoot>
                    </table>
                </div>

                @if($data['rekap']->hasPages())
                    <div class="app-pagination-footer">
                        <div class="app-pagination-info">
                            Menampilkan {{ $data['rekap']->firstItem() }}–{{ $data['rekap']->lastItem() }} dari {{ $data['rekap']->total() }} kecamatan
                        </div>
                        <div class="app-pagination-links">{{ $data['rekap']->links() }}</div>
                    </div>
                @endif
            </div>
        </div>
    @endif
</div>
@endsection