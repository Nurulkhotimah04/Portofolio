@extends('layouts.app')

@section('content')

<style>

    .anp-wrapper {
        padding: 15px 20px;
    }

    .anp-header {
        margin-bottom: 15px;
    }

    .anp-title {
        font-size: 21px;
        font-weight: 700;
        color: #2d3748;
        margin-bottom: 3px;
    }

    .anp-subtitle {
        font-size: 13px;
        color: #718096;
        margin: 0;
    }

    .kriteria-card {
        background: #fff;
        border-radius: 10px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, .06);
        margin-bottom: 14px;
        overflow: hidden;
    }

    .kriteria-header {
        background: #f7fafc;
        padding: 10px 15px;
        border-bottom: 1px solid #edf2f7;
        display: flex;
        align-items: center;
    }

    .kriteria-name {
        font-size: 15px;
        font-weight: 700;
        color: #2d3748;
        margin: 0;
    }

    .kriteria-code {
        display: inline-block;
        background: #e6fffa;
        color: #047857;
        padding: 3px 8px;
        border-radius: 5px;
        font-size: 12px;
        font-weight: 700;
        margin-right: 7px;
    }

    .table-anp {
        width: 100%;
        margin: 0;
        border-collapse: collapse;
    }

    .table-anp th {
        background: #f8fafc;
        color: #4a5568;
        font-size: 12px;
        font-weight: 700;
        padding: 8px 10px;
        border-bottom: 1px solid #e2e8f0;
    }

    .table-anp td {
        font-size: 12px;
        padding: 8px 10px;
        border-bottom: 1px solid #edf2f7;
        color: #4a5568;
    }

    .table-anp tr:last-child td {
        border-bottom: none;
    }

    .text-center {
        text-align: center;
    }

    .text-right {
        text-align: right;
    }

    .bobot {
        font-weight: 700;
        color: #047857;
    }

    .total-row {
        background: #f8fafc;
        font-weight: 700;
    }

    .total-row td {
        color: #2d3748;
        padding: 8px 10px;
    }

    .bottom-action {
        display: flex;
        justify-content: flex-end;
        align-items: center;
        gap: 8px;
        margin-top: 15px;
        margin-bottom: 10px;
    }

    .btn-vikor {
        background: #047857;
        color: white;
        border: none;
        padding: 8px 15px;
        border-radius: 6px;
        font-size: 12px;
        font-weight: 600;
        text-decoration: none;
    }

    .btn-vikor:hover {
        background: #065f46;
        color: white;
        text-decoration: none;
    }

    .alert {
        font-size: 12px;
        padding: 8px 12px;
        margin-bottom: 12px;
    }

</style>


<div class="anp-wrapper">


    {{-- ===================================================== --}}
    {{-- HEADER --}}
    {{-- ===================================================== --}}

    <div class="anp-header">

        <h1 class="anp-title">
            Hasil Bobot Subkriteria
        </h1>

        <p class="anp-subtitle">
            Bobot lokal subkriteria berdasarkan hasil perhitungan ANP.
        </p>

    </div>


    {{-- ===================================================== --}}
    {{-- PESAN BERHASIL --}}
    {{-- ===================================================== --}}

    @if(session('success'))

        <div class="alert alert-success">

            <i class="fas fa-check-circle"></i>

            {{ session('success') }}

        </div>

    @endif


    {{-- ===================================================== --}}
    {{-- PESAN ERROR --}}
    {{-- ===================================================== --}}

    @if(session('error'))

        <div class="alert alert-danger">

            <i class="fas fa-exclamation-circle"></i>

            {{ session('error') }}

        </div>

    @endif


    {{-- ===================================================== --}}
    {{-- TAMPILKAN SEMUA KRITERIA --}}
    {{-- ===================================================== --}}

    @foreach($kriteria as $item)


        @php

            /*
            |--------------------------------------------------------------------------
            | HITUNG TOTAL BOBOT
            |--------------------------------------------------------------------------
            */

            $totalBobot = 0;

            foreach ($item->subkriteria as $sub) {

                $totalBobot += (float) $sub->bobot;

            }

        @endphp


        <div class="kriteria-card">


            {{-- ================================================= --}}
            {{-- HEADER KRITERIA --}}
            {{-- ================================================= --}}

            <div class="kriteria-header">

                <h5 class="kriteria-name">

                    <span class="kriteria-code">

                        {{ strtoupper(trim($item->kode_kriteria)) }}

                    </span>

                    {{ $item->nama_kriteria }}

                </h5>

            </div>


            {{-- ================================================= --}}
            {{-- TABEL SUBKRITERIA --}}
            {{-- ================================================= --}}

            <table class="table-anp">

                <thead>

                    <tr>

                        <th
                            width="8%"
                            class="text-center"
                        >
                            No
                        </th>

                        <th width="20%">
                            Kode
                        </th>

                        <th>
                            Subkriteria
                        </th>

                        <th
                            width="22%"
                            class="text-right"
                        >
                            Bobot
                        </th>

                    </tr>

                </thead>


                <tbody>


                    @forelse($item->subkriteria as $index => $sub)


                        <tr>


                            {{-- NO --}}

                            <td class="text-center">

                                {{ $index + 1 }}

                            </td>


                            {{-- KODE SUBKRITERIA --}}

                            <td>

                                <strong>

                                    {{ $sub->kode_subkriteria }}

                                </strong>

                            </td>


                            {{-- NAMA SUBKRITERIA --}}

                            <td>

                                {{ $sub->nama_subkriteria }}

                            </td>


                            {{-- BOBOT DARI DATABASE --}}

                            <td class="text-right bobot">

                                {{ number_format(
                                    (float) $sub->bobot,
                                    8,
                                    ',',
                                    '.'
                                ) }}

                            </td>


                        </tr>


                    @empty


                        <tr>

                            <td
                                colspan="4"
                                class="text-center text-muted"
                            >

                                Belum ada data subkriteria.

                            </td>

                        </tr>


                    @endforelse


                    {{-- ================================================= --}}
                    {{-- TOTAL --}}
                    {{-- ================================================= --}}

                    <tr class="total-row">

                        <td
                            colspan="3"
                            class="text-right"
                        >

                            Total Bobot

                        </td>

                        <td class="text-right">

                            {{ number_format(
                                $totalBobot,
                                8,
                                ',',
                                '.'
                            ) }}

                        </td>

                    </tr>


                </tbody>

            </table>

        </div>


    @endforeach


    {{-- ===================================================== --}}
    {{-- TOMBOL AKSI --}}
    {{-- ===================================================== --}}

    <div class="bottom-action">


        {{-- ================================================= --}}
        {{-- SIMPAN BOBOT ANP --}}
        {{-- ================================================= --}}

        <form
            action="{{ route('anp.simpan_bobot_subkriteria') }}"
            method="POST"
        >

            @csrf

            <button
                type="submit"
                class="btn btn-success"
                onclick="return confirm('Simpan bobot hasil ANP ke database?')"
            >

                <i class="fas fa-save"></i>

                Simpan Bobot ANP

            </button>

        </form>


        {{-- ================================================= --}}
        {{-- LANJUT VIKOR --}}
        {{-- ================================================= --}}

        <a
            href="{{ route('vikor.index') }}"
            class="btn-vikor"
        >

            Lanjut Perhitungan VIKOR

            <i class="fas fa-arrow-right ml-1"></i>

        </a>


    </div>


</div>

@endsection