@extends('layouts.app')

@section('content')


<div class="container-fluid px-3">


    {{-- HEADER --}}
    <div class="mb-4">

        <h1 class="h3 mb-1 text-gray-800">

            <i class="fas fa-file-signature text-success mr-2"></i>

            Tambah Pengajuan Petani

        </h1>


        <p class="text-muted mb-0">

            Ajukan calon petani untuk diverifikasi oleh Admin.

        </p>

    </div>



    {{-- INFO --}}
    <div class="alert alert-info">

        <i class="fas fa-info-circle mr-2"></i>

        Petani yang diajukan
        <strong>belum langsung masuk Data Alternatif</strong>.

        Petani akan masuk ke Data Alternatif setelah
        pengajuan disetujui oleh Admin.

    </div>



    {{-- VALIDATION --}}
    @if($errors->any())

        <div class="alert alert-danger">

            <strong>
                Data belum dapat disimpan.
            </strong>

            <ul class="mb-0 mt-2">

                @foreach($errors->all() as $error)

                    <li>
                        {{ $error }}
                    </li>

                @endforeach

            </ul>

        </div>

    @endif



    <div class="card shadow mb-4">

        <div class="card-header bg-success text-white">

            <strong>

                <i class="fas fa-user-plus mr-2"></i>

                Data Calon Petani

            </strong>

        </div>


        <div class="card-body">


            <form
                action="{{ route('pengajuan-petani.store') }}"
                method="POST"
            >

                @csrf



                {{-- NIK --}}
                <div class="form-group">

                    <label for="nik">

                        NIK / No. KTP

                        <span class="text-danger">*</span>

                    </label>


                    <input
                        type="text"
                        name="nik"
                        id="nik"
                        maxlength="16"
                        class="form-control @error('nik') is-invalid @enderror"
                        placeholder="Masukkan 16 digit NIK"
                        value="{{ old('nik') }}"
                        required
                    >


                    @error('nik')

                        <div class="invalid-feedback">

                            {{ $message }}

                        </div>

                    @enderror

                </div>



                {{-- NAMA --}}
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
                        value="{{ old('nama_petani') }}"
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
                                {{ old('poktan_id') == $item->id ? 'selected' : '' }}
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

                        C1 - Luas Lahan

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
                            {{ old('luas_lahan') === '≤ 0,5 Ha' ? 'selected' : '' }}
                        >

                            ≤ 0,5 Ha

                        </option>


                        <option
                            value="0,6 – 1 Ha"
                            {{ old('luas_lahan') === '0,6 – 1 Ha' ? 'selected' : '' }}
                        >

                            0,6 – 1 Ha

                        </option>


                        <option
                            value="1,1 – 2 Ha"
                            {{ old('luas_lahan') === '1,1 – 2 Ha' ? 'selected' : '' }}
                        >

                            1,1 – 2 Ha

                        </option>


                    </select>

                </div>



                {{-- JENIS KOMODITAS --}}
                <div class="form-group">

                    <label for="jenis_komoditas">

                        C2 - Jenis Komoditas

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
                                {{ old('jenis_komoditas') === $komoditas ? 'selected' : '' }}
                            >

                                {{ $komoditas }}

                            </option>

                        @endforeach


                    </select>

                </div>



                {{-- KELENGKAPAN BERKAS --}}
                <div class="form-group">

                    <label for="kelengkapan_berkas">

                        C3 - Kelengkapan Berkas

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
                                {{ old('kelengkapan_berkas') === $berkas ? 'selected' : '' }}
                            >

                                {{ $berkas }}

                            </option>

                        @endforeach

                    </select>

                </div>



                {{-- KEBUTUHAN PUPUK --}}
                <div class="form-group">

                    <label for="subkriteria_pupuk">

                        C4 - Kebutuhan Pupuk

                        <span class="text-danger">*</span>

                    </label>


                    <select
                        name="subkriteria_pupuk[]"
                        id="subkriteria_pupuk"
                        class="form-control @error('subkriteria_pupuk') is-invalid @enderror"
                        required
                    >

                        <option value="">

                            -- Pilih Kebutuhan Pupuk --

                        </option>


                        @foreach($subkriteria_c4 as $pupuk)

                            <option
                                value="{{ $pupuk->id }}"

                                {{
                                    is_array(old('subkriteria_pupuk'))
                                    &&
                                    in_array(
                                        $pupuk->id,
                                        old('subkriteria_pupuk')
                                    )
                                    ? 'selected'
                                    : ''
                                }}
                            >

                                {{ $pupuk->nama_subkriteria }}

                            </option>

                        @endforeach

                    </select>


                    @error('subkriteria_pupuk')

                        <div class="invalid-feedback">

                            {{ $message }}

                        </div>

                    @enderror

                </div>



                {{-- SUBSEKTOR --}}
                <div class="form-group">

                    <label for="subsektor_pertanian">

                        C5 - Subsektor Pertanian

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
                                {{ old('subsektor_pertanian') === $subsektor ? 'selected' : '' }}
                            >

                                {{ $subsektor }}

                            </option>

                        @endforeach

                    </select>

                </div>



                {{-- CATATAN --}}
                <div class="form-group">

                    <label for="catatan_penyuluh">

                        Catatan Penyuluh

                    </label>


                    <textarea
                        name="catatan_penyuluh"
                        id="catatan_penyuluh"
                        rows="4"
                        class="form-control"
                        placeholder="Tambahkan catatan jika diperlukan..."
                    >{{ old('catatan_penyuluh') }}</textarea>

                </div>



                {{-- BUTTON --}}
                <div class="d-flex justify-content-end">


                    <a
                        href="{{ route('pengajuan-petani.index') }}"
                        class="btn btn-secondary mr-2"
                    >

                        <i class="fas fa-arrow-left mr-1"></i>

                        Kembali

                    </a>


                    <button
                        type="submit"
                        class="btn btn-success"
                        onclick="return confirm('Kirim pengajuan petani ini kepada Admin?')"
                    >

                        <i class="fas fa-paper-plane mr-1"></i>

                        Ajukan Petani

                    </button>


                </div>


            </form>

        </div>

    </div>

</div>

@endsection