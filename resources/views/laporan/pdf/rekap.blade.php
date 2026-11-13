<!DOCTYPE html>
<html lang="id">

<head>

    <meta charset="UTF-8">

    <style>

        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 10px;
        }

        h3 {
            text-align: center;
            font-size: 13px;
        }

        table.data {
            width: 100%;
            border-collapse: collapse;
        }

        table.data th,
        table.data td {
            border: 1px solid #000;
            padding: 5px;
        }

        table.data th {
            background: #eee;
        }

        .center {
            text-align: center;
        }

    </style>

</head>

<body>

    @include('laporan.pdf._kop')


    <h3>
        REKAP PRIORITAS PENERIMA SUBSIDI PUPUK PER KECAMATAN
    </h3>


    <p>

        Musim Tanam:
        <strong>{{ $musimDipilih->nama_musim }}</strong>

        &nbsp; | &nbsp;

        Tahun:
        <strong>{{ $periodeDipilih->tahun }}</strong>

    </p>


    <table class="data">

        <thead>

            <tr>

                <th>No</th>
                <th>Kecamatan</th>
                <th>Kandidat</th>
                <th>Kuota</th>
                <th>Sudah Dihitung</th>
                <th>Masuk Kuota</th>
                <th>Di Luar Kuota</th>
                <th>Belum Dihitung</th>
                <th>Kebutuhan Pupuk yang Diajukan</th>

            </tr>

        </thead>


        <tbody>

        @foreach($rekap as $item)

            <tr>

                <td class="center">
                    {{ $loop->iteration }}
                </td>

                <td>
                    {{ $item['kecamatan']->nama_kecamatan }}
                </td>

                <td class="center">
                    {{ $item['kandidat'] }}
                </td>

                <td class="center">
                    {{ $item['kuota'] ?? 'Belum Diatur' }}
                </td>

                <td class="center">
                    {{ $item['sudah_dihitung'] }}
                </td>

                <td class="center">
                    {{ $item['masuk_kuota'] }}
                </td>

                <td class="center">
                    {{ $item['di_luar_kuota'] }}
                </td>

                <td class="center">
                    {{ $item['belum_dihitung'] }}
                </td>

                <td>
                    {{ $item['ringkasan_pupuk'] }}
                    @if($item['detail_pupuk_belum_tercatat'] > 0)
                        <br><small>{{ $item['detail_pupuk_belum_tercatat'] }} alternatif belum memiliki rincian</small>
                    @endif
                </td>

            </tr>

        @endforeach

        </tbody>


        <tfoot>

            <tr style="font-weight:bold;">

                <td colspan="2">
                    TOTAL
                </td>

                <td class="center">
                    {{ $totalKandidat }}
                </td>

                <td class="center">
                    {{ $totalKuota }}
                </td>

                <td class="center">
                    {{ $totalKandidat - $totalBelumDihitung }}
                </td>

                <td class="center">
                    {{ $totalMasuk }}
                </td>

                <td class="center">
                    {{ $totalLuar }}
                </td>

                <td class="center">
                    {{ $totalBelumDihitung }}
                </td>

                <td>
                    <strong>{{ $totalRingkasanPupuk }}</strong><br>
                    <small>{{ $totalDetailPupukTercatat }} alternatif memiliki rincian; {{ $totalDetailPupukBelumTercatat }} belum memiliki rincian.</small>
                </td>

            </tr>

        </tfoot>

    </table>


    @include('laporan.pdf._tanda_tangan')

</body>

</html>