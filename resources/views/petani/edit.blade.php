@extends('layouts.app')

@section('content')

<h1 class="h3 mb-4 text-gray-800">
    Edit Data Alternatif
</h1>

<div class="card shadow">
    <div class="card-body">

        <!-- Form edit data alternatif -->
        <form action="/petani/{{ $petani->id }}" method="POST">

            @csrf
            @method('PUT')

            <div class="mb-3">
                <label>ID Petani / Kode Alternatif</label>
                <input type="text"
                       name="kode_alternatif"
                       class="form-control"
                       value="{{ $petani->kode_alternatif }}"
                       required>
            </div>

            <div class="mb-3">
                <label>NIK / No. KTP</label>
                <input type="text" name="nik" class="form-control @error('nik') is-invalid @enderror" maxlength="16" required value="{{ old('nik', $petani->nik) }}">
                @error('nik')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-3">
                <label>Nama Petani</label>
                <input type="text"
                       name="nama_petani"
                       class="form-control"
                       value="{{ $petani->nama_petani }}"
                       required>
            </div>

            <div class="mb-3">
                <label>Poktan ID</label>
                <input type="text" name="poktan_id" class="form-control" value="{{ $petani->poktan_id }}" required>
            </div>


            <div class="mb-3">
                <label>C1 - Luas Lahan</label>
                <select name="luas_lahan" class="form-control" required>
                    <option value="≤ 0,5 Ha" {{ $petani->luas_lahan == '≤ 0,5 Ha' ? 'selected' : '' }}>≤ 0,5 Ha</option>
                    <option value="0,6 – 1 Ha" {{ $petani->luas_lahan == '0,6 – 1 Ha' ? 'selected' : '' }}>0,6 – 1 Ha</option>
                    <option value="1,1 – 2 Ha" {{ $petani->luas_lahan == '1,1 – 2 Ha' ? 'selected' : '' }}>1,1 – 2 Ha</option>
                </select>
            </div>

            <div class="mb-3">
                <label>C2 - Jenis Komoditas</label>
                <select name="jenis_komoditas" class="form-control" required>
                    <option value="Padi" {{ $petani->jenis_komoditas == 'Padi' ? 'selected' : '' }}>Padi</option>
                    <option value="Jagung" {{ $petani->jenis_komoditas == 'Jagung' ? 'selected' : '' }}>Jagung</option>
                    <option value="Tebu Rakyat" {{ $petani->jenis_komoditas == 'Tebu Rakyat' ? 'selected' : '' }}>Tebu Rakyat</option>
                    <option value="Bawang Merah" {{ $petani->jenis_komoditas == 'Bawang Merah' ? 'selected' : '' }}>Bawang Merah</option>
                </select>
            </div>

            <div class="mb-3">
                <label>C3 - Kelengkapan Berkas</label>
                <select name="kelengkapan_berkas" class="form-control" required>
                    <option value="Lengkap" {{ $petani->kelengkapan_berkas == 'Lengkap' ? 'selected' : '' }}>Lengkap</option>
                    <option value="Kurang Lengkap" {{ $petani->kelengkapan_berkas == 'Kurang Lengkap' ? 'selected' : '' }}>Kurang Lengkap</option>
                    <option value="Tidak Lengkap" {{ $petani->kelengkapan_berkas == 'Tidak Lengkap' ? 'selected' : '' }}>Tidak Lengkap</option>
                </select>
            </div>

            <div class="mb-3">
                <label>C4 - Kebutuhan Pupuk</label>

                <select name="subkriteria_pupuk[]"
                        class="form-control"
                        required>
                    <option value="">-- Pilih Kebutuhan Pupuk --</option>

                    @foreach($subkriteria_c4 as $sub)
                        <option value="{{ $sub->id }}"
                            {{ isset($penilaian_terpilih) && in_array($sub->id, $penilaian_terpilih) ? 'selected' : '' }}>
                            {{ $sub->nama_subkriteria }} (Skor Bobot: {{ $sub->bobot }})
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="mb-3">
                <label>C5 - Subsektor Pertanian</label>
                <select name="subsektor_pertanian" class="form-control" required>
                    <option value="Tanaman Pangan" {{ $petani->subsektor_pertanian == 'Tanaman Pangan' ? 'selected' : '' }}>Tanaman Pangan</option>
                    <option value="Hortikultura" {{ $petani->subsektor_pertanian == 'Hortikultura' ? 'selected' : '' }}>Hortikultura</option>
                    <option value="Perkebunan" {{ $petani->subsektor_pertanian == 'Perkebunan' ? 'selected' : '' }}>Perkebunan</option>
                </select>
            </div>

            <button class="btn btn-success">
                Update
            </button>

            <a href="/petani" class="btn btn-secondary">
                Kembali
            </a>

        </form>

    </div>
</div>

@endsection