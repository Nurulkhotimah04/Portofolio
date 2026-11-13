<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Prioritas Subsidi Pupuk</title>
    <style>
        body {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 12px;
            color: #000;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
        }
        .header h3 {
            margin: 0;
            font-size: 16px;
            text-transform: uppercase;
        }
        .header p {
            margin: 5px 0;
            font-size: 14px;
        }
        table.data-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        table.data-table th, table.data-table td {
            border: 1px solid #000;
            padding: 8px;
        }
        table.data-table th {
            background-color: #f2f2f2;
            text-align: center;
            font-weight: bold;
        }
        .text-center { text-align: center; }
        .text-left { text-align: left; }
    </style>
</head>
<body>

    <div class="header">
        <h3>Sistem Penentuan Prioritas Bantuan Subsidi Pupuk</h3>
        <p>Laporan Hasil Perhitungan Metode VIKOR</p>
    </div>

    <table class="data-table">
        <thead>
            <tr>
                <th width="10%">Ranking</th>
                <th width="35%">Nama Petani</th>
                <th width="30%">Nama Poktan</th>
                <th width="25%">Nama Penyuluh</th>
            </tr>
        </thead>
        <tbody>
            @forelse($hasilRanking as $index => $hasil)
            <tr>
                <td class="text-center">{{ $hasil['ranking'] }}</td>
                <td class="text-left">{{ $hasil['nama'] }}</td>
                <td class="text-center">{{ $hasil['nama_poktan'] }}</td>
                <td class="text-center">{{ $hasil['nama_penyuluh'] }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="4" class="text-center">Data laporan belum tersedia.</td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <br>
    
    <table style="width: 100%; border: none; margin-top: 30px; border-collapse: collapse;">
        <tr>
            <td style="width: 60%; border: none;"></td>
            <td style="width: 40%; text-align: center; border: none; font-size: 12px;">
                <p style="margin: 0;">Cirebon, {{ \Carbon\Carbon::now()->translatedFormat('d F Y') }}</p>
                <p style="margin: 0;">Kepala Dinas Pertanian</p>
                
                <br><br><br><br><br>
                
                <p style="font-weight: bold; text-decoration: underline; margin: 0;">
                    {{ $nama_kadis ?: '...................................................' }}
                </p>
                <p style="margin: 0;">
                    NIP. {{ $nip_kadis ?: '...................................................' }}
                </p>
            </td>
        </tr>
    </table>

</body>
</html>