@extends('layouts.app')

@section('content')

<h1 class="h3 mb-4 text-gray-800">
    Detail Penilaian Petani
</h1>

<div class="card shadow mb-4">

    <div class="card-body">

        <table class="table table-bordered">

            <tr>
                <th width="200">NIK</th>
                <td>{{ $petani->nik }}</td>
            </tr>

            <tr>
                <th>Nama Petani</th>
                <td>{{ $petani->nama_petani }}</td>
            </tr>

            <tr>
                <th>Alamat</th>
                <td>{{ $petani->alamat }}</td>
            </tr>

        </table>

    </div>

</div>

<div class="card shadow">

    <div class="card-body">

        <table class="table table-bordered">

            <thead class="bg-success text-white">
                <tr>
                    <th>No</th>
                    <th>Kriteria</th>
                    <th>Subkriteria</th>
                    <th>Nilai</th>
                </tr>
            </thead>

            <tbody>

                @foreach($penilaian as $item)

                <tr>
                    <td>{{ $loop->iteration }}</td>

                    <td>
                        {{ $item->kriteria->kode_kriteria }}
                        -
                        {{ $item->kriteria->nama_kriteria }}
                    </td>

                    <td>
                        {{ $item->subkriteria->nama_subkriteria }}
                    </td>

                    <td>
                        {{ $item->nilai }}
                    </td>
                </tr>

                @endforeach

            </tbody>

        </table>

        <a href="/penilaian"
           class="btn btn-secondary">
            Kembali
        </a>

    </div>

</div>

@endsection