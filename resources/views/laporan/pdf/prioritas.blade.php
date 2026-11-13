<!DOCTYPE html>
<html lang="id">

<head>

    <meta charset="UTF-8">

    <title>
        Laporan Prioritas
    </title>

    <style>

        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 9px;
            color: #000;
        }

        h3 {
            text-align: center;
            margin: 5px 0 3px;
            font-size: 13px;
        }

        table.data {
            width: 100%;
            border-collapse: collapse;
        }

        table.data th,
        table.data td {
            border: 1px solid #000;
            padding: 4px;
        }

        table.data th {
            text-align: center;
            background: #eee;
        }

        .center {
            text-align: center;
        }

        .info {
            margin-bottom: 10px;
            line-height: 1.6;
        }

        .signature {
            margin-top: 25px;
            width: 100%;
        }

    </style>

</head>


<body>

    @include('laporan.pdf._kop')


    <h3>
        {{ $judulLaporan }}
    </h3>


    <div class="info">

        Musim Tanam:
        <strong>{{ $musimDipilih->nama_musim }}</strong>

        &nbsp; | &nbsp;

        Tahun:
        <strong>{{ $periodeDipilih->tahun }}</strong>

        &nbsp; | &nbsp;

        Kecamatan:
        <strong>{{ $kecamatanDipilih->nama_kecamatan }}</strong>

        &nbsp; | &nbsp;

        Kuota:
        <strong>
            {{ $kuota ?? 'Belum Diatur' }}
        </strong>

    </div>


    <table class="data">

        <thead>

            <tr>

                <th>No</th>
                <th>Rank</th>
                <th>Status</th>
                <th>Kode</th>
                <th>NIK</th>
                <th>Nama Petani</th>
                <th>Poktan</th>
                <th>Komoditas</th>
                <th>Kebutuhan Pupuk yang Diajukan</th>
                <th>S</th>
                <th>R</th>
                <th>Q</th>

            </tr>

        </thead>


        <tbody>

        @forelse($hasil as $item)

            @php

                $masuk =
                    $kuota !== null
                    &&
                    $item->ranking <= $kuota;

            @endphp


            <tr>

                <td class="center">
                    {{ $loop->iteration }}
                </td>

                <td class="center">
                    {{ $item->ranking }}
                </td>

                <td class="center">

                    @if($kuota === null)
                        Kuota Belum Diatur
                    @elseif($masuk)
                        Masuk Kuota
                    @else
                        Di Luar Kuota
                    @endif

                </td>

                <td>
                    {{ optional($item->alternatifMusim)->kode_alternatif ?? '-' }}
                </td>

                <td>
                    {{ optional($item->petani)->nik ?? '-' }}
                </td>

                <td>
                    {{ optional($item->petani)->nama_petani ?? '-' }}
                </td>

                <td>
                    {{ optional(optional($item->alternatifMusim)->poktan)->nama_poktan ?? '-' }}
                </td>

                <td>
                    {{ optional($item->alternatifMusim)->jenis_komoditas ?? '-' }}
                </td>

                <td>
                    {{ optional($item->alternatifMusim)->ringkasan_pupuk ?? '-' }}
                </td>

                <td class="center">
                    {{ number_format($item->nilai_s, 6) }}
                </td>

                <td class="center">
                    {{ number_format($item->nilai_r, 6) }}
                </td>

                <td class="center">
                    {{ number_format($item->nilai_q, 6) }}
                </td>

            </tr>

        @empty

            <tr>

                <td colspan="12"
                    class="center">

                    Tidak ada data.

                </td>

            </tr>

        @endforelse

        </tbody>

    </table>


    @include('laporan.pdf._tanda_tangan')

</body>

</html>