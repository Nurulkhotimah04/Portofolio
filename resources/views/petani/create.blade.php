@extends('layouts.app')

@section('content')

<h1 class="h3 mb-4 text-gray-800">
    Tambah Data Petani
</h1>

<div class="card shadow">

    <div class="card-body">

        <!-- Form Input -->
        <form action="/petani" method="POST">

            <!-- token keamanan laravel -->
            @csrf

            <!-- ID Petani -->
        <div class="mb-3">
            <label>ID Petani / Kode Alternatif</label>
            <input type="text"
                name="kode_alternatif"
                class="form-control"
                value="{{ $kode_alternatif }}"
                readonly>
        </div>

        <div class="mb-3">
        <label>NIK / No. KTP</label>

        <input type="text"
            name="nik"
            class="form-control @error('nik') is-invalid @enderror" maxlength="16" placeholder="Masukkan 16 digit NIK" required value="{{ old('nik') }}">
        @error('nik')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
        </div>

        <!-- Input Nama Petani -->
        <div class="mb-3">
            <label>Nama Petani</label>

            <input type="text"
                name="nama_petani"
                class="form-control">
        </div>

        <div class="mb-3">
            <label>Poktan ID</label>
            <input type="text" name="poktan_id" class="form-control" required>
        </div>


        <!-- Input Luas Lahan -->
        <div class="mb-3">
            <label>C1 - Luas Lahan</label>

            <select name="luas_lahan" class="form-control" required>

                <option value="">-- Pilih Luas Lahan --</option>

                <option value="≤ 0,5 Ha">≤ 0,5 Ha</option>

                <option value="0,6 – 1 Ha">0,6 – 1 Ha</option>

                <option value="1,1 – 2 Ha">1,1 – 2 Ha</option>

            </select>
        </div>

        <!-- Input Jenis Komoditas -->
        <div class="mb-3">
            <label>Jenis Komoditas</label>

            <select name="jenis_komoditas"
                    class="form-control">
                <option value="">-- Pilih Jenis Komoditas --</option>
                <option value="Padi">Padi</option>
                <option value="Jagung">Jagung</option>
                <option value="Tebu Rakyat">Tebu Rakyat</option>
                <option value="Bawang Merah">Bawang Merah</option>
            </select>
        </div>

        <!-- Input Kelengkapan Berkas -->
        <div class="mb-3">
            <label>Kelengkapan Berkas</label>

            <select name="kelengkapan_berkas"
                    class="form-control">
                <option value="">-- Pilih Kelengkapan Berkas--</option>
                <option value="Lengkap">Lengkap</option>
                <option value="Kurang Lengkap">Kurang Lengkap</option>
                <option value="Tidak Lengkap">Tidak Lengkap</option>
            </select>
        </div>

        <!-- Input Kebutuhan Pupuk -->
        <div class="mb-3">
            <label>Kebutuhan Pupuk</label>

            <select name="subkriteria_pupuk[]"
                    class="form-control"
                    required>
                <option value="">-- Pilih Kebutuhan Pupuk --</option>

                @foreach($subkriteria_c4 as $pupuk)
                    <option value="{{ $pupuk->id }}"
                        {{ is_array(old('subkriteria_pupuk')) && in_array($pupuk->id, old('subkriteria_pupuk')) ? 'selected' : '' }}>
                        {{ $pupuk->nama_subkriteria }}
                    </option>
                @endforeach
            </select>

            @error('subkriteria_pupuk')
                <small class="text-danger d-block mt-1">{{ $message }}</small>
            @enderror
        </div>

        <!-- Input Subsektor Pertanian -->
        <div class="mb-3">
            <label>Subsektor Pertanian</label>

            <select name="subsektor_pertanian"
                    class="form-control">
                <option value="">-- Pilih Subsektor Pertanian--</option>
                <option value="Tanaman Pangan">Tanaman Pangan</option>
                <option value="Hortikultura">Hortikultura</option>
                <option value="Perkebunan">Perkebunan</option>
            </select>
        </div>

            <!-- Tombol simpan -->
            <button class="btn btn-success">

                Simpan

            </button>

        </form>

    </div>

</div>

@endsection