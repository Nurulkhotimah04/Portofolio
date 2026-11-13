@extends('layouts.app')

@section('content')

<h1 class="h3 mb-4 text-gray-800">
    Edit Penilaian Petani
</h1>

<div class="card shadow">

    <div class="card-body">

        <div class="alert alert-info">
            <strong>Petani:</strong>
            {{ $petani->nik }} - {{ $petani->nama_petani }}
        </div>

        <form action="/penilaian/{{ $petani->id }}" method="POST">

            @csrf
            @method('PUT')

            @foreach($kriteria as $k)

            <div class="mb-3">

                <label class="font-weight-bold">
                    {{ $k->kode_kriteria }} - {{ $k->nama_kriteria }}
                </label>

                {{-- CEK APAKAH KRITERIA C4 (Checkbox) ATAU BUKAN (Dropdown) --}}
                @if($k->kode_kriteria == 'C4') 
                    
                    <div class="mt-2">
                        @foreach($k->subkriteria as $sub)
                        <div class="form-check mb-2">
                            {{-- Name menggunakan array [] --}}
                            <input class="form-check-input" type="checkbox" 
                                   name="subkriteria[{{ $k->id }}][]" 
                                   value="{{ $sub->id }}" 
                                   id="sub_{{ $sub->id }}"
                                   {{-- Pengecekan checked menggunakan in_array --}}
                                   {{ isset($penilaianLama[$k->id]) && in_array($sub->id, $penilaianLama[$k->id]) ? 'checked' : '' }}>
                            
                            <label class="form-check-label" for="sub_{{ $sub->id }}">
                                {{ $sub->nama_subkriteria }} - Bobot {{ $sub->bobot }}
                            </label>
                        </div>
                        @endforeach
                    </div>

                @else
                    
                    {{-- TAMPILAN DROPDOWN UNTUK SELAIN C4 --}}
                    <select name="subkriteria[{{ $k->id }}]" class="form-control" required>
                        <option value="">-- Pilih Subkriteria --</option>
                        
                        @foreach($k->subkriteria as $sub)
                        <option value="{{ $sub->id }}"
                            {{-- Pengecekan selected juga menggunakan in_array karena struktur data di controller berupa array --}}
                            {{ isset($penilaianLama[$k->id]) && in_array($sub->id, $penilaianLama[$k->id]) ? 'selected' : '' }}>
                            
                            {{ $sub->nama_subkriteria }} - Bobot {{ $sub->bobot }}
                            
                        </option>
                        @endforeach
                    </select>

                @endif

            </div>

            @endforeach

            <div class="mt-4">
                <button type="submit" class="btn btn-success">
                    Update Penilaian
                </button>

                <a href="/penilaian" class="btn btn-secondary">
                    Kembali
                </a>
            </div>

        </form>

    </div>

</div>

@endsection