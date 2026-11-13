<!DOCTYPE html>
<html lang="id">

<head>

    <meta charset="UTF-8">

    <style>

        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 7px;
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
            padding: 4px;
        }

        table.data th {
            background: #eee;
            text-align: center;
        }

        .center {
            text-align: center;
        }

    </style>

</head>

<body>

    @include('laporan.pdf._kop')


    <h3>
        {{ $judulLaporan }}
    </h3>


    <table class="data">

        <thead>

            <tr>

                <th>No</th>
                <th>Tanggal</th>
                <th>NIK</th>
                <th>Nama Petani</th>
                <th>Poktan</th>
                <th>Kecamatan</th>
                <th>Musim</th>
                <th>Tahun</th>
                <th>Penyuluh</th>
                <th>Input</th>
                <th>C3 Diajukan</th>
                <th>C3 Hasil Admin</th>
                <th>Kebutuhan Pupuk yang Diajukan</th>
                <th>Status</th>
                <th>Catatan Admin</th>

            </tr>

        </thead>


        <tbody>

        @forelse($pengajuan as $item)

            <tr>

                <td class="center">
                    {{ $loop->iteration }}
                </td>

                <td>
                    {{ $item->tanggal_pengajuan
                        ? $item->tanggal_pengajuan->format('d-m-Y')
                        : '-' }}
                </td>

                <td>
                    {{ $item->nik }}
                </td>

                <td>
                    {{ $item->nama_petani }}
                </td>

                <td>
                    {{ optional($item->poktan)->nama_poktan ?? '-' }}
                </td>

                <td>
                    {{ optional(optional($item->poktan)->kecamatan)->nama_kecamatan ?? '-' }}
                </td>

                <td>
                    {{ optional($item->musimTanam)->kode_musim ?? '-' }}
                </td>

                <td>
                    {{ optional($item->periode)->tahun ?? '-' }}
                </td>

                <td>
                    {{ optional($item->penyuluh)->nama_penyuluh ?? '-' }}
                </td>

                <td>
                    {{ $item->asal_input === 'import'
                        ? 'Excel'
                        : 'Manual' }}
                </td>

                <td>{{ $item->kelengkapan_berkas ?? '-' }}</td>

                <td>
                    @if($item->c3_terverifikasi)
                        {{ $item->kelengkapan_berkas_verifikasi }}
                    @elseif($item->status === 'disetujui')
                        Tidak tercatat (data lama)
                    @else
                        Belum diverifikasi
                    @endif
                </td>

                <td>{{ $item->ringkasan_pupuk }}</td>

                <td>
                    {{ $item->status_label }}
                </td>

                <td>
                    {{ $item->catatan_admin ?? '-' }}
                </td>

            </tr>

        @empty

            <tr>

                <td colspan="15"
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