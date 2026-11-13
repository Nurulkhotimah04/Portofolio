@extends('layouts.app')

@section('content')

<div class="container-fluid py-3">

    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h4 class="font-weight-bold text-gray-800 mb-1">
                Perhitungan Bobot Subkriteria
            </h4>
            <p class="text-muted mb-0">
                Data subkriteria yang digunakan dalam pembobotan ANP.
            </p>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger">
            {{ session('error') }}
        </div>
    @endif

    @foreach($kriteria as $item)

        <div class="card shadow-sm mb-3 border-0">

            <div class="card-header bg-white py-2">
                <div class="d-flex align-items-center">
                    <span class="badge badge-success mr-2 px-3 py-2">
                        {{ $item->kode_kriteria }}
                    </span>

                    <strong class="text-gray-800">
                        {{ $item->nama_kriteria }}
                    </strong>
                </div>
            </div>

            <div class="card-body p-0">

                <div class="table-responsive">

                    <table class="table table-sm table-hover mb-0">

                        <thead class="bg-light">
                            <tr>
                                <th width="70">No</th>
                                <th width="150">Kode</th>
                                <th>Subkriteria</th>
                            </tr>
                        </thead>

                        <tbody>

                            @forelse($item->subkriteria as $no => $sub)

                                <tr>
                                    <td>
                                        {{ $no + 1 }}
                                    </td>

                                    <td>
                                        <strong>
                                            {{ $sub->kode_subkriteria }}
                                        </strong>
                                    </td>

                                    <td>
                                        {{ $sub->nama_subkriteria }}
                                    </td>
                                </tr>

                            @empty

                                <tr>
                                    <td colspan="3"
                                        class="text-center text-muted py-3">
                                        Belum terdapat subkriteria.
                                    </td>
                                </tr>

                            @endforelse

                        </tbody>

                    </table>

                </div>

            </div>

        </div>

    @endforeach


    @if($kriteria->count() > 0)

        <div class="text-right mt-3">

            <a href="{{ route('anp.hasil_subkriteria') }}"
               class="btn btn-success px-4">

                <i class="fas fa-calculator mr-2"></i>
                Lanjut Perhitungan Bobot

            </a>

        </div>

    @endif

</div>

<style>
    .table th,
    .table td {
        vertical-align: middle;
    }

    .card {
        border-radius: 10px;
    }

    .badge-success {
        background-color: #16a34a;
    }

    .table {
        font-size: 14px;
    }
</style>

@endsection