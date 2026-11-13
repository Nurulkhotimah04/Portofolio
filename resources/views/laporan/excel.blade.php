<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        table {
            border-collapse: collapse;
            width: 600px; /* Batasi lebar total tabel agar tidak melebar ke kolom A-Z */
        }
        th, td {
            border: 1px solid #000000;
            padding: 8px;
            vertical-align: middle;
            white-space: nowrap; /* Mencegah teks turun ke bawah (keok/wrap) */
        }
    </style>
</head>
<body>
    <table>
        <thead>
            <!-- Kop Judul (Hanya merentang 4 kolom: A sampai D) -->
            <tr>
                <th colspan="4" style="font-weight: bold; text-align: center; font-size: 14pt; background-color: #f8f9fc; white-space: normal;">
                    LAPORAN PRIORITAS PENERIMA BANTUAN SUBSIDI PUPUK PETANI
                </th>
            </tr>
            <tr>
                <th colspan="4" style="font-weight: bold; text-align: center; font-size: 11pt; background-color: #f8f9fc; white-space: normal;">
                    DINAS PERTANIAN KABUPATEN CIREBON
                </th>
            </tr>
            <tr>
                <th colspan="4" style="text-align: center; font-style: italic; background-color: #f8f9fc;">
                    Periode : {{ \Carbon\Carbon::now()->translatedFormat('F Y') }}
                </th>
            </tr>
            
            <!-- Baris Kosong Pemisah -->
            <tr>
                <th colspan="4" style="border: none; background-color: #ffffff;"></th>
            </tr> 

            <!-- Header Tabel (Hijau hanya sampai 4 kolom) -->
            <tr style="background-color: #1cc88a; color: #ffffff; font-weight: bold; text-align: center;">
                <th style="width: 10%;">Ranking</th>
                <th style="width: 35%;">Nama Petani</th>
                <th style="width: 25%;">Nama Poktan</th>
                <th style="width: 30%;">Nama Penyuluh</th>
            </tr>
        </thead>
        <tbody>
            @forelse($hasilRanking as $index => $hasil)
            <tr>
                <td style="text-align: center;">{{ $hasil['ranking'] }}</td>
                <td style="text-align: left;">{{ $hasil['nama'] }}</td>
                <td style="text-align: center;">{{ $hasil['nama_poktan'] }}</td>
                <td style="text-align: center;">{{ $hasil['nama_penyuluh'] }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="4" style="text-align: center;">Data laporan belum tersedia.</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>