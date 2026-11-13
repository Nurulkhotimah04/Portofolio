@extends('layouts.app')

@section('content')

<div class="container-fluid px-3">

    <div class="mb-4">
        <h1 class="h3 mb-1 text-gray-800">
            Edit Pengajuan Petani
        </h1>

        <p class="text-muted mb-0">
            Perbaiki data pengajuan sesuai catatan dari Admin.
        </p>
    </div>


    {{-- ERROR VALIDATION --}}
    @if ($errors->any())
        <div class="alert alert-danger">
            <strong>Terjadi kesalahan:</strong>

            <ul class="mb-0 mt-2">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif


    {{-- CATATAN ADMIN --}}
    @if($pengajuan->status === 'perlu_perbaikan' && $pengajuan->catatan_admin)

        <div class="alert alert-warning">

            <strong>
                <i class="fas fa-exclamation-triangle mr-1"></i>
                Catatan Perbaikan dari Admin
            </strong>

            <div class="mt-2">
                {{ $pengajuan->catatan_admin }}
            </div>

        </div>

    @endif


    <div class="card shadow mb-4">

        <div class="card-header bg-success text-white">
            <strong>
                <i class="fas fa-edit mr-1"></i>
                Form Edit Pengajuan
            </strong>
        </div>

        <div class="card-body">

            <form
                action="{{ route('pengajuan-petani.update', $pengajuan->id) }}"
                method="POST"
            >

                @csrf
                @method('PUT')


                {{-- NIK --}}
                <div class="form-group">

                    <label for="nik">
                        NIK
                        <span class="text-danger">*</span>
                    </label>

                    <input
                        type="text"
                        name="nik"
                        id="nik"
                        maxlength="16"
                        class="form-control @error('nik') is-invalid @enderror"
                        value="{{ old('nik', $pengajuan->nik) }}"
                        required
                    >

                    @error('nik')
                        <div class="invalid-feedback">
                            {{ $message }}
                        </div>
                    @enderror

                </div>


                {{-- NAMA PETANI --}}
                <div class="form-group">

                    <label for="nama_petani">
                        Nama Petani
                        <span class="text-danger">*</span>
                    </label>

                    <input
                        type="text"
                        name="nama_petani"
                        id="nama_petani"
                        class="form-control @error('nama_petani') is-invalid @enderror"
                        value="{{ old('nama_petani', $pengajuan->nama_petani) }}"
                        required
                    >

                    @error('nama_petani')
                        <div class="invalid-feedback">
                            {{ $message }}
                        </div>
                    @enderror

                </div>


                {{-- POKTAN --}}
                <div class="form-group">

                    <label for="poktan_id">
                        Kelompok Tani / Poktan
                        <span class="text-danger">*</span>
                    </label>

                    <select
                        name="poktan_id"
                        id="poktan_id"
                        class="form-control @error('poktan_id') is-invalid @enderror"
                        required
                    >

                        <option value="">
                            -- Pilih Poktan --
                        </option>

                        @foreach($poktan as $item)

                            <option
                                value="{{ $item->id }}"
                                {{ old('poktan_id', $pengajuan->poktan_id) == $item->id ? 'selected' : '' }}
                            >
                                {{ $item->nama_poktan }}
                            </option>

                        @endforeach

                    </select>

                    @error('poktan_id')
                        <div class="invalid-feedback">
                            {{ $message }}
                        </div>
                    @enderror

                </div>


                {{-- LUAS LAHAN --}}
                <div class="form-group">

                    <label for="luas_lahan">
                        Luas Lahan
                        <span class="text-danger">*</span>
                    </label>

                    <select
                        name="luas_lahan"
                        id="luas_lahan"
                        class="form-control"
                        required
                    >

                        <option value="">
                            -- Pilih Luas Lahan --
                        </option>

                        <option
                            value="≤ 0,5 Ha"
                            {{ old('luas_lahan', $pengajuan->luas_lahan) == '≤ 0,5 Ha' ? 'selected' : '' }}
                        >
                            ≤ 0,5 Ha
                        </option>

                        <option
                            value="0,6 – 1 Ha"
                            {{ old('luas_lahan', $pengajuan->luas_lahan) == '0,6 – 1 Ha' ? 'selected' : '' }}
                        >
                            0,6 – 1 Ha
                        </option>

                        <option
                            value="1,1 – 2 Ha"
                            {{ old('luas_lahan', $pengajuan->luas_lahan) == '1,1 – 2 Ha' ? 'selected' : '' }}
                        >
                            1,1 – 2 Ha
                        </option>

                    </select>

                </div>


                {{-- JENIS KOMODITAS --}}
                <div class="form-group">

                    <label for="jenis_komoditas">
                        Jenis Komoditas
                        <span class="text-danger">*</span>
                    </label>

                    <select
                        name="jenis_komoditas"
                        id="jenis_komoditas"
                        class="form-control"
                        required
                    >

                        <option value="">
                            -- Pilih Jenis Komoditas --
                        </option>

                        @foreach([
                            'Padi',
                            'Jagung',
                            'Tebu Rakyat',
                            'Bawang Merah'
                        ] as $komoditas)

                            <option
                                value="{{ $komoditas }}"
                                {{ old('jenis_komoditas', $pengajuan->jenis_komoditas) == $komoditas ? 'selected' : '' }}
                            >
                                {{ $komoditas }}
                            </option>

                        @endforeach

                    </select>

                </div>


                {{-- KELENGKAPAN BERKAS --}}
                <div class="form-group">

                    <label for="kelengkapan_berkas">
                        Kelengkapan Berkas
                        <span class="text-danger">*</span>
                    </label>

                    <select
                        name="kelengkapan_berkas"
                        id="kelengkapan_berkas"
                        class="form-control"
                        required
                    >

                        <option value="">
                            -- Pilih Kelengkapan Berkas --
                        </option>

                        @foreach([
                            'Lengkap',
                            'Kurang Lengkap',
                            'Tidak Lengkap'
                        ] as $berkas)

                            <option
                                value="{{ $berkas }}"
                                {{ old('kelengkapan_berkas', $pengajuan->kelengkapan_berkas) == $berkas ? 'selected' : '' }}
                            >
                                {{ $berkas }}
                            </option>

                        @endforeach

                    </select>

                </div>


                {{-- KEBUTUHAN PUPUK --}}
                <div class="form-group">

                    <label for="kebutuhan_pupuk">
                        Kebutuhan Pupuk
                        <span class="text-danger">*</span>
                    </label>

                    <select
                        name="kebutuhan_pupuk"
                        id="kebutuhan_pupuk"
                        class="form-control"
                        required
                    >

                        <option value="">
                            -- Pilih Kebutuhan Pupuk --
                        </option>

                        @foreach([
                            'Membutuhkan 1 Pupuk',
                            'Membutuhkan 2 Pupuk',
                            'Membutuhkan 3 Pupuk'
                        ] as $pupuk)

                            <option
                                value="{{ $pupuk }}"
                                {{ old('kebutuhan_pupuk', $pengajuan->kebutuhan_pupuk) == $pupuk ? 'selected' : '' }}
                            >
                                {{ $pupuk }}
                            </option>

                        @endforeach

                    </select>

                </div>


                {{-- SUBSEKTOR --}}
                <div class="form-group">

                    <label for="subsektor_pertanian">
                        Subsektor Pertanian
                        <span class="text-danger">*</span>
                    </label>

                    <select
                        name="subsektor_pertanian"
                        id="subsektor_pertanian"
                        class="form-control"
                        required
                    >

                        <option value="">
                            -- Pilih Subsektor Pertanian --
                        </option>

                        @foreach([
                            'Tanaman Pangan',
                            'Hortikultura',
                            'Perkebunan'
                        ] as $subsektor)

                            <option
                                value="{{ $subsektor }}"
                                {{ old('subsektor_pertanian', $pengajuan->subsektor_pertanian) == $subsektor ? 'selected' : '' }}
                            >
                                {{ $subsektor }}
                            </option>

                        @endforeach

                    </select>

                </div>


                {{-- CATATAN PENYULUH --}}
                <div class="form-group">

                    <label for="catatan_penyuluh">
                        Catatan Penyuluh
                    </label>

                    <textarea
                        name="catatan_penyuluh"
                        id="catatan_penyuluh"
                        rows="4"
                        class="form-control"
                        placeholder="Masukkan catatan jika diperlukan"
                    >{{ old('catatan_penyuluh', $pengajuan->catatan_penyuluh) }}</textarea>

                </div>


                {{-- BUTTON --}}
                <div class="d-flex justify-content-end">

                    <a
                        href="{{ route('pengajuan-petani.show', $pengajuan->id) }}"
                        class="btn btn-secondary mr-2"
                    >
                        <i class="fas fa-arrow-left mr-1"></i>
                        Kembali
                    </a>

                    <button
                        type="submit"
                        class="btn btn-success"
                    >
                        <i class="fas fa-paper-plane mr-1"></i>
                        Simpan & Ajukan Kembali
                    </button>

                </div>

            </form>

        </div>

    </div>

</div>

@endsection