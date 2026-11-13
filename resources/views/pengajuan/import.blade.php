@extends('layouts.app')
@section('title','Import Pengajuan')
@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800">Import Pengajuan Petani</h1>
        <a href="{{ route('pengajuan.index') }}" class="btn btn-light border">Kembali</a>
    </div>

    @if($errors->any())
        <div class="alert alert-danger"><ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>
    @endif

    <div class="alert alert-light border">
        <i class="fas fa-map-marker-alt text-success mr-2"></i>
        Wilayah binaan: <strong>{{ $wilayahPenyuluh?->nama_kecamatan ?? 'Belum ditetapkan' }}</strong>
    </div>

    <form method="POST" action="{{ route('pengajuan.import.preview') }}" enctype="multipart/form-data">
        @csrf

        <div class="card shadow-sm border-0 mb-4">
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label>Musim Tanam</label>
                        <select name="musim_tanam_id" class="form-control" required>
                            <option value="">-- Pilih --</option>
                            @foreach($musimTanam as $m)
                                <option value="{{ $m->id }}" {{ old('musim_tanam_id')==$m->id?'selected':'' }}>{{ $m->kode_musim }} - {{ $m->nama_musim }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label>Tahun</label>
                        <select name="periode_id" class="form-control" required>
                            <option value="">-- Pilih --</option>
                            @foreach($periode as $p)
                                <option value="{{ $p->id }}" {{ old('periode_id')==$p->id?'selected':'' }}>{{ $p->tahun }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-6 mb-4">
                <div class="card shadow-sm border-0 h-100">
                    <div class="card-body">
                        <h6 class="font-weight-bold text-success">Data Excel</h6>
                        <a href="{{ route('pengajuan.import.template') }}" class="btn btn-outline-success btn-sm mb-3">
                            <i class="fas fa-download mr-1"></i>Download Template
                        </a>
                        <input type="file" name="file_excel" accept=".xlsx,.xls" class="form-control-file" required>
                        <small class="text-muted d-block mt-2">XLS/XLSX, maksimal 5 MB. Susunan kolom boleh berbeda dan kolom tambahan boleh ada.</small>
                        <small class="text-muted d-block">Kolom Poktan wajib. Jika kolom Penyuluh tersedia, nama harus sesuai akun yang sedang login.</small>
                    </div>
                </div>
            </div>

            <div class="col-lg-6 mb-4">
                <div class="card shadow-sm border-0 h-100">
                    <div class="card-body">
                        <h6 class="font-weight-bold text-success">Berkas PDF Massal</h6>
                        <button type="button" class="btn btn-outline-success btn-sm mb-3" data-toggle="modal" data-target="#zipInfo">
                            <i class="fas fa-file-archive mr-1"></i>Pilih ZIP
                        </button>
                        <span id="zipName" class="small text-muted ml-2">Belum memilih file</span>
                        <input id="file_zip" type="file" name="file_zip" accept=".zip,application/zip" class="d-none" required>
                        <small class="text-muted d-block mt-2">ZIP maksimal 50 MB, PDF maksimal 10 MB/file, maksimal 500 petani per import.</small>
                    </div>
                </div>
            </div>
        </div>

        <div class="text-right">
            <button class="btn btn-success"><i class="fas fa-search mr-1"></i>Preview Data</button>
        </div>
    </form>
</div>

<div class="modal fade" id="zipInfo" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header"><h6 class="modal-title">Upload Berkas PDF</h6><button class="close" data-dismiss="modal"><span>&times;</span></button></div>
            <div class="modal-body">
                <p class="mb-1">Nama setiap PDF wajib menggunakan NIK petani.</p>
                <small class="text-muted">Contoh: 3209123456780001.pdf • Maks. 10 MB/file</small>
            </div>
            <div class="modal-footer"><button type="button" id="chooseZip" class="btn btn-success">Pilih ZIP</button></div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
(function(){
    const modal=$('#zipInfo');
    const input=document.getElementById('file_zip');
    const name=document.getElementById('zipName');
    document.getElementById('chooseZip')?.addEventListener('click',()=>input.click());
    input?.addEventListener('change',()=>{
        name.textContent=input.files?.[0]?.name||'Belum memilih file';
        modal.modal('hide');
    });
})();
</script>
@endpush
