@php
    $item = $pengajuan ?? null;
    $source = $prefill ?? null;
    $sourcePetani = $source?->petani;

    $nikValue = old('nik', $item->nik ?? $sourcePetani->nik ?? '');
    $namaValue = old('nama_petani', $item->nama_petani ?? $sourcePetani->nama_petani ?? '');
    $luasHaValue = old('luas_lahan_ha', $item->luas_lahan_ha ?? $source->luas_lahan_ha ?? '');
    $komoditasValue = old('jenis_komoditas', $item->jenis_komoditas ?? $source->jenis_komoditas ?? '');
    $berkasValue = old('kelengkapan_berkas', $item->kelengkapan_berkas ?? $source->kelengkapan_berkas ?? '');
    $subsektorValue = old('subsektor_pertanian', $item->subsektor_pertanian ?? $source->subsektor_pertanian ?? '');

    $existingPupuk = [];
    if ($item && $item->relationLoaded('pupuk')) {
        $existingPupuk = $item->pupuk->pluck('jumlah_kg', 'jenis_pupuk_id')->all();
    } elseif ($source && $source->relationLoaded('pupuk')) {
        $existingPupuk = $source->pupuk->pluck('jumlah_kg', 'jenis_pupuk_id')->all();
    }
@endphp

@if($source)
    <div class="alert alert-info border-0 shadow-sm">
        <i class="fas fa-redo mr-2"></i>
        <strong>Ajukan kembali untuk musim/periode baru.</strong>
        Identitas dan data periode sebelumnya hanya dipakai sebagai draft. Periksa kembali seluruh kondisi petani sebelum dikirim.
    </div>
@endif

<div class="card shadow-sm border-0 mb-4">
    <div class="card-header bg-white font-weight-bold text-success">Periode & Poktan</div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-4 mb-3">
                <label>Musim Tanam <span class="text-danger">*</span></label>
                <select name="musim_tanam_id" class="form-control @error('musim_tanam_id') is-invalid @enderror" required>
                    <option value="">-- Pilih Musim --</option>
                    @foreach($musimTanam as $musim)
                        <option value="{{ $musim->id }}" {{ (string)old('musim_tanam_id', $item->musim_tanam_id ?? '') === (string)$musim->id ? 'selected' : '' }}>
                            {{ $musim->kode_musim }} - {{ $musim->nama_musim }}
                        </option>
                    @endforeach
                </select>
                @error('musim_tanam_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-4 mb-3">
                <label>Tahun / Periode <span class="text-danger">*</span></label>
                <select name="periode_id" class="form-control @error('periode_id') is-invalid @enderror" required>
                    <option value="">-- Pilih Tahun --</option>
                    @foreach($periode as $tahun)
                        <option value="{{ $tahun->id }}" {{ (string)old('periode_id', $item->periode_id ?? '') === (string)$tahun->id ? 'selected' : '' }}>{{ $tahun->tahun }}</option>
                    @endforeach
                </select>
                @error('periode_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-4 mb-3">
                <label>Poktan Binaan <span class="text-danger">*</span></label>
                <select id="poktan_id" name="poktan_id" class="form-control @error('poktan_id') is-invalid @enderror" required>
                    <option value="" data-kecamatan="">-- Pilih Poktan --</option>
                    @foreach($poktan as $kelompok)
                        <option value="{{ $kelompok->id }}"
                                data-kecamatan="{{ optional($kelompok->kecamatan)->nama_kecamatan }}"
                                {{ (string)old('poktan_id', $item->poktan_id ?? $source->poktan_id ?? '') === (string)$kelompok->id ? 'selected' : '' }}>
                            {{ $kelompok->nama_poktan }}
                        </option>
                    @endforeach
                </select>
                @error('poktan_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
        </div>
        <div class="alert alert-light border mb-0 py-2">
            <i class="fas fa-map-marker-alt text-success mr-2"></i>
            Kecamatan: <strong id="namaKecamatan">-</strong>
            <small class="text-muted ml-2">otomatis mengikuti Poktan/penugasan Penyuluh.</small>
        </div>
    </div>
</div>

<div class="card shadow-sm border-0 mb-4">
    <div class="card-header bg-white font-weight-bold text-success">Identitas Petani</div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-6 mb-3">
                <label>NIK <span class="text-danger">*</span></label>
                <input type="text" name="nik" maxlength="16" inputmode="numeric" value="{{ $nikValue }}" class="form-control @error('nik') is-invalid @enderror" required>
                @error('nik')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-6 mb-3">
                <label>Nama Petani <span class="text-danger">*</span></label>
                <input type="text" name="nama_petani" value="{{ $namaValue }}" class="form-control @error('nama_petani') is-invalid @enderror" required>
                @error('nama_petani')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
        </div>
    </div>
</div>

<div class="card shadow-sm border-0 mb-4">
    <div class="card-header bg-white font-weight-bold text-success">Data Faktual Penilaian</div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-6 mb-3">
                <label>C1 - Luas Lahan (Ha) <span class="text-danger">*</span></label>
                <input id="luas_lahan_ha" type="number" step="0.01" min="0.01" max="2" name="luas_lahan_ha" value="{{ $luasHaValue }}" class="form-control @error('luas_lahan_ha') is-invalid @enderror" required>
                @error('luas_lahan_ha')<div class="invalid-feedback">{{ $message }}</div>@enderror
                <small class="text-muted">Subkriteria C1 ditentukan otomatis. <span id="previewC1" class="font-weight-bold"></span></small>
            </div>

            <div class="col-md-6 mb-3">
                <label>C2 - Jenis Komoditas <span class="text-danger">*</span></label>
                <select name="jenis_komoditas" class="form-control @error('jenis_komoditas') is-invalid @enderror" required>
                    <option value="">-- Pilih Komoditas --</option>
                    @foreach($criteriaOptions['C2'] ?? [] as $opsi)
                        <option value="{{ $opsi }}" {{ $komoditasValue === $opsi ? 'selected' : '' }}>{{ $opsi }}</option>
                    @endforeach
                </select>
                @error('jenis_komoditas')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="col-md-6 mb-3">
                <label>C3 - Kelengkapan Berkas <span class="text-danger">*</span></label>
                <select name="kelengkapan_berkas" class="form-control @error('kelengkapan_berkas') is-invalid @enderror" required>
                    <option value="">-- Pilih Status Berkas --</option>
                    @foreach($criteriaOptions['C3'] ?? [] as $opsi)
                        <option value="{{ $opsi }}" {{ $berkasValue === $opsi ? 'selected' : '' }}>{{ $opsi }}</option>
                    @endforeach
                </select>
                @error('kelengkapan_berkas')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="col-md-6 mb-3">
                <label>Berkas Persyaratan PDF @if(!$item || !$item->berkas_persyaratan_path)<span class="text-danger">*</span>@endif</label>
                <div>
                    <button type="button" class="btn btn-outline-success btn-sm" data-toggle="modal" data-target="#modalUploadPdf">
                        <i class="fas fa-file-pdf mr-1"></i> Pilih PDF
                    </button>
                    <span id="pdfFileName" class="small text-muted ml-2">Belum memilih file</span>
                </div>
                @if($item?->berkas_persyaratan_path)
                    <div class="mt-2"><a href="{{ route('pengajuan.berkas', $item) }}" target="_blank">Buka PDF yang tersimpan</a></div>
                @endif
                @error('berkas_pdf')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
            </div>

            <div class="col-12 mb-2">
                <label class="font-weight-bold">C4 - Kebutuhan Pupuk yang Diajukan</label>
                <div class="row">
                    @foreach($jenisPupuk as $jp)
                        @php $jumlah = old('pupuk.'.$jp->id, $existingPupuk[$jp->id] ?? 0); @endphp
                        <div class="col-md-4 mb-3">
                            <label class="small">{{ $jp->nama_pupuk }} (kg)</label>
                            <input type="number" step="0.01" min="0" name="pupuk[{{ $jp->id }}]" value="{{ $jumlah }}" class="form-control pupuk-kg @error('pupuk.'.$jp->id) is-invalid @enderror">
                        </div>
                    @endforeach
                </div>
                <div class="alert alert-light border py-2">
                    Sistem menghitung jumlah jenis pupuk dengan kebutuhan &gt; 0 kg. Subkriteria otomatis: <strong id="previewC4">-</strong>
                </div>
                @error('pupuk')<div class="text-danger small">{{ $message }}</div>@enderror
            </div>

            <div class="col-md-6 mb-3">
                <label>C5 - Subsektor Pertanian <span class="text-danger">*</span></label>
                <select name="subsektor_pertanian" class="form-control @error('subsektor_pertanian') is-invalid @enderror" required>
                    <option value="">-- Pilih Subsektor --</option>
                    @foreach($criteriaOptions['C5'] ?? [] as $opsi)
                        <option value="{{ $opsi }}" {{ $subsektorValue === $opsi ? 'selected' : '' }}>{{ $opsi }}</option>
                    @endforeach
                </select>
                @error('subsektor_pertanian')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="col-md-6 mb-3">
                <label>Catatan Penyuluh</label>
                <textarea name="catatan_penyuluh" rows="3" class="form-control">{{ old('catatan_penyuluh', $item->catatan_penyuluh ?? '') }}</textarea>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modalUploadPdf" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header"><h6 class="modal-title">Upload Berkas</h6><button type="button" class="close" data-dismiss="modal"><span>&times;</span></button></div>
            <div class="modal-body">
                <p class="mb-2">Gabungkan seluruh persyaratan menjadi <strong>1 file PDF</strong>.</p>
                <small class="text-muted">Format PDF, maksimal 10 MB. Nama file disesuaikan otomatis berdasarkan NIK.</small>
                <input id="berkas_pdf" type="file" name="berkas_pdf" accept="application/pdf" class="form-control-file mt-3">
            </div>
            <div class="modal-footer"><button type="button" class="btn btn-success" data-dismiss="modal">Selesai</button></div>
        </div>
    </div>
</div>

@push('scripts')
<script>
(function(){
    const poktan = document.getElementById('poktan_id');
    const kec = document.getElementById('namaKecamatan');
    function updateKec(){ const o = poktan?.options[poktan.selectedIndex]; if(kec) kec.textContent = o?.dataset?.kecamatan || '-'; }
    poktan?.addEventListener('change', updateKec); updateKec();

    const luas = document.getElementById('luas_lahan_ha');
    const c1 = document.getElementById('previewC1');
    function updateC1(){
        const v=parseFloat(luas?.value||0); let t='-';
        if(v>0 && v<=0.5) t='≤ 0,5 Ha'; else if(v<=1 && v>0.5) t='0,6 – 1 Ha'; else if(v<=2 && v>1) t='1,1 – 2 Ha';
        if(c1) c1.textContent=t;
    }
    luas?.addEventListener('input',updateC1); updateC1();

    const pupuk = Array.from(document.querySelectorAll('.pupuk-kg'));
    const c4 = document.getElementById('previewC4');
    function updateC4(){
        const count=pupuk.filter(i=>parseFloat(i.value||0)>0).length;
        let t='-'; if(count===1)t='Membutuhkan 1 Pupuk'; else if(count===2)t='Membutuhkan 2 Pupuk'; else if(count===3)t='Membutuhkan 3 Pupuk'; else if(count>3)t='Membutuhkan >3 Pupuk';
        if(c4)c4.textContent=t;
    }
    pupuk.forEach(i=>i.addEventListener('input',updateC4)); updateC4();

    const pdf=document.getElementById('berkas_pdf'); const name=document.getElementById('pdfFileName');
    pdf?.addEventListener('change',()=>{ if(name) name.textContent=pdf.files?.[0]?.name || 'Belum memilih file'; });
})();
</script>
@endpush
