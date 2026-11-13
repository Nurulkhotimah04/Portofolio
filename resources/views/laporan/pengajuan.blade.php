@extends('layouts.app')

@section('content')

<div class="container-fluid">

    <div class="d-flex justify-content-between align-items-center mb-4">

        <div>

            <h4 class="font-weight-bold text-gray-800">

                <i class="fas fa-clipboard-list text-success mr-2"></i>

                {{ $isPenyuluh
                    ? 'Laporan Pengajuan Petani Binaan'
                    : 'Laporan Pengajuan & Verifikasi' }}

            </h4>

        </div>


        <div class="d-flex align-items-center" style="gap:8px;">
            <button type="button" class="app-filter-icon-btn" data-toggle="modal" data-target="#filterLaporanPengajuanModal" title="Filter laporan" aria-label="Buka filter laporan">
                <i class="fas fa-filter"></i>
                @if(
                    request()->filled('musim_tanam_id') ||
                    request()->filled('periode_id') ||
                    (!$isPenyuluh && request()->filled('kecamatan_id')) ||
                    request()->filled('status') ||
                    request()->filled('asal_input')
                )<span class="filter-active-dot"></span>@endif
            </button>
            <a href="{{ route('laporan.index') }}" class="btn btn-secondary">Kembali</a>
        </div>

    </div>


    @if(
        request()->filled('musim_tanam_id') ||
        request()->filled('periode_id') ||
        (!$isPenyuluh && request()->filled('kecamatan_id')) ||
        request()->filled('status') ||
        request()->filled('asal_input') ||
        ($isPenyuluh && $kecamatanPenyuluh)
    )
        <div class="app-filter-summary">

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
            @if(request()->filled('status'))<span class="app-badge app-badge-info">{{ str_replace('_', ' ', request('status')) }}</span>@endif
            @if(request()->filled('asal_input'))<span class="app-badge app-badge-muted">{{ request('asal_input') === 'import' ? 'Import Excel' : 'Manual' }}</span>@endif
            @if(
                request()->filled('musim_tanam_id') ||
                request()->filled('periode_id') ||
                (!$isPenyuluh && request()->filled('kecamatan_id')) ||
                request()->filled('status') ||
                request()->filled('asal_input')
            )
                <a href="{{ route('laporan.pengajuan') }}" class="small text-danger"><i class="fas fa-times mr-1"></i>Reset Filter</a>
            @endif
        </div>
    @endif

    <div class="d-flex justify-content-between mb-3">

        <strong>
            {{ $pengajuan->total() }} Data
        </strong>


        <div>

            <a href="{{ route(
                'laporan.pengajuan.pdf',
                request()->query()
            ) }}"
               class="btn btn-danger btn-sm">

                <i class="fas fa-file-pdf mr-1"></i>
                PDF

            </a>


            <a href="{{ route(
                'laporan.pengajuan.excel',
                request()->query()
            ) }}"
               class="btn btn-success btn-sm">

                <i class="fas fa-file-excel mr-1"></i>
                Excel

            </a>

        </div>

    </div>


    <div class="card shadow-sm border-0">

        <div class="card-body">

            <div class="table-responsive">

                <table class="table table-bordered table-hover table-sm">

                    <thead class="thead-light">

                        <tr>

                            <th>No</th>
                            <th>Tanggal</th>
                            <th>NIK / Nama</th>
                            <th>Poktan / Kecamatan</th>
                            <th>Musim / Tahun</th>

                            @if(!$isPenyuluh)
                                <th>Penyuluh</th>
                            @endif

                            <th>Input</th>
                            <th>C3 Diajukan</th>
                            <th>C3 Hasil Admin</th>
                            <th>Kebutuhan Pupuk yang Diajukan</th>
                            <th>Status</th>
                            <th>Catatan Admin</th>

                        </tr>

                    </thead>

                    <tbody>

                    @forelse($pengajuan as $item)

                        <tr>

                            <td>
                                {{ $pengajuan->firstItem() + $loop->index }}
                            </td>

                            <td>

                                {{ $item->tanggal_pengajuan
                                    ? $item->tanggal_pengajuan->format('d-m-Y H:i')
                                    : '-' }}

                            </td>

                            <td>

                                <strong>
                                    {{ $item->nama_petani }}
                                </strong>

                                <br>

                                <small>
                                    {{ $item->nik }}
                                </small>

                            </td>

                            <td>

                                {{ optional($item->poktan)->nama_poktan ?? '-' }}

                                <br>

                                <small class="text-muted">

                                    {{ optional(optional($item->poktan)->kecamatan)->nama_kecamatan ?? '-' }}

                                </small>

                            </td>

                            <td>

                                {{ optional($item->musimTanam)->kode_musim ?? '-' }}

                                /

                                {{ optional($item->periode)->tahun ?? '-' }}

                            </td>


                            @if(!$isPenyuluh)

                                <td>

                                    {{ optional($item->penyuluh)->nama_penyuluh ?? '-' }}

                                </td>

                            @endif


                            <td>

                                {{ $item->asal_input === 'import'
                                    ? 'Excel'
                                    : 'Manual' }}

                            </td>

                            <td>{{ $item->kelengkapan_berkas ?? '-' }}</td>

                            <td>
                                @if($item->c3_terverifikasi)
                                    <span class="badge badge-success">{{ $item->kelengkapan_berkas_verifikasi }}</span>
                                    <br><small class="text-muted">Diverifikasi {{ optional($item->c3_verified_at)->format('d-m-Y H:i') }}</small>
                                @elseif($item->status === 'disetujui')
                                    <span class="text-muted">Tidak tercatat (data lama)</span>
                                @else
                                    <span class="badge badge-warning">Belum diverifikasi</span>
                                @endif
                            </td>

                            <td>{{ $item->ringkasan_pupuk }}</td>

                            <td>

                                <span class="badge badge-{{ $item->status_class }}">

                                    {{ $item->status_label }}

                                </span>

                            </td>

                            <td>

                                {{ $item->catatan_admin ?? '-' }}

                            </td>

                        </tr>

                    @empty

                        <tr>

                            <td colspan="{{ $isPenyuluh ? 11 : 12 }}"
                                class="text-center text-muted py-4">

                                Tidak ada data.

                            </td>

                        </tr>

                    @endforelse

                    </tbody>

                </table>

            </div>

            @if($pengajuan->hasPages())
                <div class="app-pagination-footer">
                    <div class="app-pagination-info">
                        Menampilkan {{ $pengajuan->firstItem() }}–{{ $pengajuan->lastItem() }} dari {{ $pengajuan->total() }} data
                    </div>
                    <div class="app-pagination-links">{{ $pengajuan->links() }}</div>
                </div>
            @endif

        </div>

    </div>

</div>


<div class="modal fade" id="filterLaporanPengajuanModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
        <div class="modal-content">
            <form method="GET" action="{{ route('laporan.pengajuan') }}">
                <div class="modal-header">
                    <div><h5 class="modal-title font-weight-bold">Filter Laporan Pengajuan</h5><small class="text-muted">Pilih filter yang diperlukan.</small></div>
                    <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                </div>
                <div class="modal-body">
                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label class="font-weight-bold">Musim Tanam</label>
                            <select name="musim_tanam_id" class="form-control"><option value="">Semua Musim</option>@foreach($musimTanam as $item)<option value="{{ $item->id }}" {{ request('musim_tanam_id') == $item->id ? 'selected' : '' }}>{{ $item->kode_musim }}</option>@endforeach</select>
                        </div>
                        <div class="form-group col-md-6">
                            <label class="font-weight-bold">Tahun</label>
                            <select name="periode_id" class="form-control"><option value="">Semua Tahun</option>@foreach($periode as $item)<option value="{{ $item->id }}" {{ request('periode_id') == $item->id ? 'selected' : '' }}>{{ $item->tahun }}</option>@endforeach</select>
                        </div>
                        @if(!$isPenyuluh)
                            <div class="form-group col-md-6">
                                <label class="font-weight-bold">Kecamatan</label>
                                <select name="kecamatan_id" class="form-control"><option value="">Semua Kecamatan</option>@foreach($kecamatan as $item)<option value="{{ $item->id }}" {{ request('kecamatan_id') == $item->id ? 'selected' : '' }}>{{ $item->nama_kecamatan }}</option>@endforeach</select>
                            </div>
                        @else
                            <div class="form-group col-md-6">
                                <label class="font-weight-bold">Wilayah Binaan</label>
                                <div class="form-control bg-light d-flex align-items-center" style="height:auto; min-height:38px;">
                                    <i class="fas fa-map-marker-alt text-success mr-2"></i>
                                    <strong>
                                        {{ $kecamatanPenyuluh
                                            ? 'Kecamatan ' . $kecamatanPenyuluh->nama_kecamatan
                                            : 'Kecamatan belum ditentukan' }}
                                    </strong>
                                </div>
                                <small class="text-muted">Otomatis dari penugasan aktif Penyuluh. Tidak dapat diubah dari filter laporan.</small>
                            </div>
                        @endif
                        <div class="form-group col-md-6">
                            <label class="font-weight-bold">Status</label>
                            <select name="status" class="form-control">
                                <option value="">Semua Status</option>
                                <option value="menunggu_verifikasi" {{ request('status') === 'menunggu_verifikasi' ? 'selected' : '' }}>Menunggu</option>
                                <option value="perlu_perbaikan" {{ request('status') === 'perlu_perbaikan' ? 'selected' : '' }}>Perlu Perbaikan</option>
                                <option value="disetujui" {{ request('status') === 'disetujui' ? 'selected' : '' }}>Disetujui</option>
                                <option value="ditolak" {{ request('status') === 'ditolak' ? 'selected' : '' }}>Ditolak</option>
                            </select>
                        </div>
                        <div class="form-group col-md-6 mb-0">
                            <label class="font-weight-bold">Asal Input</label>
                            <select name="asal_input" class="form-control">
                                <option value="">Semua Input</option>
                                <option value="manual" {{ request('asal_input') === 'manual' ? 'selected' : '' }}>Manual</option>
                                <option value="import" {{ request('asal_input') === 'import' ? 'selected' : '' }}>Import Excel</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <a href="{{ route('laporan.pengajuan') }}" class="btn btn-light border">Reset</a>
                    <button type="button" class="btn btn-light border" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-success px-4"><i class="fas fa-check mr-1"></i>Terapkan</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection