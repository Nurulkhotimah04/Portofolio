@php
    $isEdit = isset($alternatifMusim);
    $existingPupuk = $isEdit && $alternatifMusim->relationLoaded('pupuk')
        ? $alternatifMusim->pupuk->pluck('jumlah_kg','jenis_pupuk_id')->all()
        : [];
@endphp

@if($errors->any())<div class="alert alert-danger"><ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>@endif

@if($isEdit)
<div class="alert alert-light border">
    <strong>Aturan edit:</strong> menambah PDF atau mengubah jumlah kg tanpa mengubah kategori C1–C5 tidak menghapus hasil VIKOR. Jika nilai C1–C5/scope berubah, hasil VIKOR Kecamatan terkait perlu dihitung ulang.
</div>
@endif

<div class="card shadow-sm border-0 mb-4"><div class="card-header bg-white font-weight-bold text-success">Identitas & Periode</div><div class="card-body"><div class="row">
    <div class="col-md-4 mb-3"><label>NIK</label><input name="nik" maxlength="16" class="form-control" value="{{ old('nik',$isEdit?$alternatifMusim->petani?->nik:'') }}" required></div>
    <div class="col-md-8 mb-3"><label>Nama Petani</label><input name="nama_petani" class="form-control" value="{{ old('nama_petani',$isEdit?$alternatifMusim->petani?->nama_petani:'') }}" required></div>
    <div class="col-md-4 mb-3"><label>Musim Tanam</label><select name="musim_tanam_id" class="form-control" required><option value="">-- Pilih --</option>@foreach($musimTanam as $m)<option value="{{ $m->id }}" {{ (string)old('musim_tanam_id',$isEdit?$alternatifMusim->musim_tanam_id:'')===(string)$m->id?'selected':'' }}>{{ $m->kode_musim }} - {{ $m->nama_musim }}</option>@endforeach</select></div>
    <div class="col-md-4 mb-3"><label>Tahun</label><select name="periode_id" class="form-control" required><option value="">-- Pilih --</option>@foreach($periode as $p)<option value="{{ $p->id }}" {{ (string)old('periode_id',$isEdit?$alternatifMusim->periode_id:'')===(string)$p->id?'selected':'' }}>{{ $p->tahun }}</option>@endforeach</select></div>
    <div class="col-md-4 mb-3"><label>Poktan</label><select name="poktan_id" id="altPoktan" class="form-control" required><option value="" data-kecamatan="">-- Pilih --</option>@foreach($poktan as $p)<option value="{{ $p->id }}" data-kecamatan="{{ $p->kecamatan?->nama_kecamatan }}" {{ (string)old('poktan_id',$isEdit?$alternatifMusim->poktan_id:'')===(string)$p->id?'selected':'' }}>{{ $p->nama_poktan }} — {{ $p->kecamatan?->nama_kecamatan }}</option>@endforeach</select><small class="text-muted">Kecamatan: <strong id="altKecamatan">-</strong></small></div>
</div></div></div>

<div class="card shadow-sm border-0 mb-4"><div class="card-header bg-white font-weight-bold text-success">Penilaian & Data Pendukung</div><div class="card-body"><div class="row">
    <div class="col-md-6 mb-3"><label>C1 - Luas Lahan aktual (Ha) @if(!$isEdit)<span class="text-danger">*</span>@endif</label><input type="number" step="0.01" min="0.01" max="2" name="luas_lahan_ha" id="altLuas" class="form-control" value="{{ old('luas_lahan_ha',$isEdit?$alternatifMusim->luas_lahan_ha:'') }}" {{ !$isEdit?'required':'' }}>@if($isEdit && !$alternatifMusim->luas_lahan_ha)<small class="text-muted">Data lama belum punya angka Ha. Kosongkan jika belum diketahui; kategori lama <strong>{{ $alternatifMusim->luas_lahan }}</strong> tetap dipakai.</small>@else<small class="text-muted">Subkriteria otomatis: <strong id="altC1">-</strong></small>@endif</div>
    <div class="col-md-6 mb-3"><label>C2 - Komoditas</label><select name="jenis_komoditas" class="form-control" required><option value="">-- Pilih --</option>@foreach($c2 as $o)<option value="{{ $o }}" {{ old('jenis_komoditas',$isEdit?$alternatifMusim->jenis_komoditas:'')===$o?'selected':'' }}>{{ $o }}</option>@endforeach</select></div>
    <div class="col-md-6 mb-3"><label>C3 - Kelengkapan Berkas</label><select name="kelengkapan_berkas" class="form-control" required>@foreach($c3 as $o)<option value="{{ $o }}" {{ old('kelengkapan_berkas',$isEdit?$alternatifMusim->kelengkapan_berkas:'')===$o?'selected':'' }}>{{ $o }}</option>@endforeach</select></div>
    <div class="col-md-6 mb-3"><label>PDF Persyaratan</label><input type="file" name="berkas_pdf" accept="application/pdf" class="form-control-file">@if($isEdit && $alternatifMusim->berkas_persyaratan_path)<a href="{{ route('alternatif-musim.berkas',$alternatifMusim) }}" target="_blank" class="small">Buka PDF tersimpan</a>@elseif($isEdit)<small class="text-muted">Belum tersedia pada data lama.</small>@endif</div>

    <div class="col-12"><hr><label class="font-weight-bold">C4 - Kebutuhan Pupuk yang Diajukan</label><div class="row">@foreach($jenisPupuk as $jp)<div class="col-md-4 mb-3"><label class="small">{{ $jp->nama_pupuk }} (kg)</label><input type="number" min="0" step="0.01" name="pupuk[{{ $jp->id }}]" class="form-control alt-pupuk" value="{{ old('pupuk.'.$jp->id,$existingPupuk[$jp->id]??0) }}"></div>@endforeach</div><div class="alert alert-light border py-2">Subkriteria otomatis dari jumlah jenis &gt;0 kg: <strong id="altC4">{{ $isEdit?$alternatifMusim->kebutuhan_pupuk:'-' }}</strong>@if($isEdit && $alternatifMusim->pupuk->isEmpty())<br><small class="text-muted">Rincian jenis/kg belum tersedia pada data lama. Jika tidak diisi, C4 lama tetap dipakai.</small>@endif</div></div>
    <div class="col-md-6 mb-3"><label>C5 - Subsektor</label><select name="subsektor_pertanian" class="form-control" required>@foreach($c5 as $o)<option value="{{ $o }}" {{ old('subsektor_pertanian',$isEdit?$alternatifMusim->subsektor_pertanian:'')===$o?'selected':'' }}>{{ $o }}</option>@endforeach</select></div>
</div></div></div>

@push('scripts')
<script>
(function(){
 const p=document.getElementById('altPoktan'),k=document.getElementById('altKecamatan'); function upK(){if(k)k.textContent=p?.options[p.selectedIndex]?.dataset?.kecamatan||'-';} p?.addEventListener('change',upK);upK();
 const l=document.getElementById('altLuas'),c1=document.getElementById('altC1'); function up1(){if(!c1)return;const v=parseFloat(l?.value||0);c1.textContent=v>0&&v<=.5?'≤ 0,5 Ha':v<=1&&v>.5?'0,6 – 1 Ha':v<=2&&v>1?'1,1 – 2 Ha':'-';}l?.addEventListener('input',up1);up1();
 const ins=[...document.querySelectorAll('.alt-pupuk')],c4=document.getElementById('altC4');function up4(){const n=ins.filter(x=>parseFloat(x.value||0)>0).length;if(!n)return; c4.textContent=n===1?'Membutuhkan 1 Pupuk':n===2?'Membutuhkan 2 Pupuk':n===3?'Membutuhkan 3 Pupuk':'Membutuhkan >3 Pupuk';}ins.forEach(x=>x.addEventListener('input',up4));up4();
})();
</script>
@endpush
