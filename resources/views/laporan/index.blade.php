@extends('layouts.app')
@section('title', 'Pusat Laporan')

@section('content')
<div class="app-page-header">
    <div>
        <h1 class="app-page-title">Pusat Laporan</h1>
    </div>
</div>

@if($errors->any())
    <div class="alert alert-danger app-alert">
        <ul class="mb-0">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

@if(auth()->user()->role === 'admin')
    <div class="app-card mb-4">
        <div class="app-card-header d-flex justify-content-between align-items-center flex-wrap" style="gap:10px;">
            <div>
                <div class="app-card-title">
                    <i class="fas fa-signature text-success mr-2"></i>Penandatangan Laporan
                </div>
            </div>

            <button type="button" class="btn btn-light border" data-toggle="modal" data-target="#penandatanganModal">
                <i class="fas {{ $penandatangan ? 'fa-pen' : 'fa-plus' }} mr-1"></i>
                {{ $penandatangan ? 'Edit Penandatangan' : 'Tambah Penandatangan' }}
            </button>
        </div>

        <div class="p-4">
            @if($penandatangan && $penandatangan->nama_kepala_dinas && $penandatangan->nip_kepala_dinas)
                <div class="row align-items-center">
                    <div class="col-lg-7 mb-3 mb-lg-0">
                        <div class="text-muted small mb-1">Kepala Dinas Pertanian Kabupaten Cirebon</div>
                        <div class="font-weight-bold text-gray-800" style="font-size:1rem;">{{ $penandatangan->nama_kepala_dinas }}</div>
                        <div class="text-muted mt-1">NIP. {{ $penandatangan->nip_kepala_dinas }}</div>
                    </div>
                    <div class="col-lg-5 text-lg-right">
                        <span class="app-badge app-badge-success"><i class="fas fa-check-circle mr-1"></i>Aktif</span>
                        @if($penandatangan->updated_at)
                            <div class="small text-muted mt-2">Terakhir diperbarui {{ $penandatangan->updated_at->format('d-m-Y H:i') }}</div>
                        @endif
                    </div>
                </div>
            @else
                <div class="d-flex align-items-center flex-wrap" style="gap:12px;">
                    <div class="d-flex align-items-center justify-content-center" style="width:44px;height:44px;border-radius:12px;background:#fef3c7;color:#b45309;">
                        <i class="fas fa-exclamation-triangle"></i>
                    </div>
                    <div>
                        <div class="font-weight-bold text-gray-800">Penandatangan belum diatur</div>
                        <div class="small text-muted">Tambahkan nama dan NIP Kepala Dinas sebelum mencetak laporan resmi.</div>
                    </div>
                </div>
            @endif
        </div>
    </div>

    <div class="row">
        <div class="col-xl-4 col-md-6 mb-4">
            <a href="{{ route('laporan.prioritas') }}" class="app-card d-block h-100 text-decoration-none p-4">
                <div class="d-flex align-items-center mb-3">
                    <div class="d-flex align-items-center justify-content-center mr-3" style="width:48px;height:48px;border-radius:12px;background:#dcfce7;color:#15803d;"><i class="fas fa-trophy"></i></div>
                    <div class="font-weight-bold text-gray-800">Prioritas Penerima</div>
                </div>
                <p class="text-muted small mb-4">Ranking VIKOR dan status masuk/di luar kuota berdasarkan Musim Tanam, Tahun, dan Kecamatan.</p>
                <span class="text-success font-weight-bold small">Buat Laporan <i class="fas fa-arrow-right ml-1"></i></span>
            </a>
        </div>

        <div class="col-xl-4 col-md-6 mb-4">
            <a href="{{ route('laporan.rekap') }}" class="app-card d-block h-100 text-decoration-none p-4">
                <div class="d-flex align-items-center mb-3">
                    <div class="d-flex align-items-center justify-content-center mr-3" style="width:48px;height:48px;border-radius:12px;background:#dbeafe;color:#2563eb;"><i class="fas fa-map-marked-alt"></i></div>
                    <div class="font-weight-bold text-gray-800">Rekap Kecamatan</div>
                </div>
                <p class="text-muted small mb-4">Ringkasan jumlah kandidat, kuota penerima, masuk kuota, dan di luar kuota per Kecamatan.</p>
                <span class="text-success font-weight-bold small">Buat Laporan <i class="fas fa-arrow-right ml-1"></i></span>
            </a>
        </div>

        <div class="col-xl-4 col-md-6 mb-4">
            <a href="{{ route('laporan.pengajuan') }}" class="app-card d-block h-100 text-decoration-none p-4">
                <div class="d-flex align-items-center mb-3">
                    <div class="d-flex align-items-center justify-content-center mr-3" style="width:48px;height:48px;border-radius:12px;background:#fef3c7;color:#b45309;"><i class="fas fa-clipboard-check"></i></div>
                    <div class="font-weight-bold text-gray-800">Pengajuan & Verifikasi</div>
                </div>
                <p class="text-muted small mb-4">Riwayat pengajuan petani beserta status dan hasil verifikasi yang dilakukan oleh Admin.</p>
                <span class="text-success font-weight-bold small">Buat Laporan <i class="fas fa-arrow-right ml-1"></i></span>
            </a>
        </div>
    </div>

    <div class="modal fade" id="penandatanganModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <form method="POST" action="{{ route('laporan.penandatangan.update') }}">
                    @csrf
                    <div class="modal-header">
                        <div>
                            <h5 class="modal-title font-weight-bold">{{ $penandatangan ? 'Edit' : 'Tambah' }} Penandatangan Laporan</h5>
                            <small class="text-muted">Jika Kepala Dinas berganti, edit data ini lalu simpan. Data lama akan diperbarui.</small>
                        </div>
                        <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                    </div>
                    <div class="modal-body">
                        <div class="form-group">
                            <label class="font-weight-bold">Nama Kepala Dinas</label>
                            <input type="text"
                                   name="nama_kepala_dinas"
                                   class="form-control"
                                   value="{{ old('nama_kepala_dinas', optional($penandatangan)->nama_kepala_dinas) }}"
                                   placeholder="Contoh: Dr. Ir. Nama Kepala Dinas, M.Si."
                                   required>
                        </div>

                        <div class="form-group mb-0">
                            <label class="font-weight-bold">NIP</label>
                            <input type="text"
                                   name="nip_kepala_dinas"
                                   class="form-control"
                                   value="{{ old('nip_kepala_dinas', optional($penandatangan)->nip_kepala_dinas) }}"
                                   placeholder="Contoh: 19700101 199501 1 001"
                                   required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light border" data-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-success px-4">
                            <i class="fas fa-save mr-1"></i>{{ $penandatangan ? 'Simpan Perubahan' : 'Simpan Penandatangan' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@else
    <div class="row">
        <div class="col-lg-6 mb-4">
            <a href="{{ route('laporan.pengajuan') }}" class="app-card d-block h-100 text-decoration-none p-4">
                <div class="font-weight-bold text-gray-800 mb-2"><i class="fas fa-file-signature text-success mr-2"></i>Pengajuan Petani Binaan</div>
                <p class="text-muted small mb-3">Riwayat pengajuan petani dari Poktan binaan Anda.</p>
                <span class="text-success font-weight-bold small">Buat Laporan <i class="fas fa-arrow-right ml-1"></i></span>
            </a>
        </div>
        <div class="col-lg-6 mb-4">
            <a href="{{ route('laporan.prioritas') }}" class="app-card d-block h-100 text-decoration-none p-4">
                <div class="font-weight-bold text-gray-800 mb-2"><i class="fas fa-trophy text-success mr-2"></i>Hasil Prioritas Binaan</div>
                <p class="text-muted small mb-3">Ranking Kecamatan, nilai Q, dan status kuota petani binaan Anda.</p>
                <span class="text-success font-weight-bold small">Buat Laporan <i class="fas fa-arrow-right ml-1"></i></span>
            </a>
        </div>
    </div>
@endif
@endsection

@if(auth()->user()->role === 'admin' && $errors->any())
@push('scripts')
<script>
    $(function () {
        $('#penandatanganModal').modal('show');
    });
</script>
@endpush
@endif
