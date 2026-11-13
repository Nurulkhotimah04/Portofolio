@extends('layouts.app')

@section('content')
<div class="container-fluid">

    <div class="card border-0 shadow-sm mb-4 bg-success" style="border-radius: 15px;">
        <div class="card-body p-4 text-white">
            <h2 class="font-weight-bold mb-1">Hasil Keputusan (Ranking)</h2>
            <p class="mb-0 text-white-50">Diurutkan berdasarkan nilai kompromi Q terendah hasil perhitungan engine VIKOR.</p>
        </div>
    </div>

    <div class="row">
        
        <div class="col-xl-6 col-lg-6 mb-4">
            <h5 class="font-weight-bold text-gray-800 mb-3"><i class="fas fa-trophy text-warning mr-2"></i> Leaderboard Petani (Top 3)</h5>
            
            @foreach(array_slice($rankingData, 0, 3) as $index => $hasil)
                @php
                    // Menentukan warna badge berdasarkan urutan
                    if($index == 0) { $bg = 'bg-warning'; $text = 'text-dark'; $border = 'border-left-warning'; }
                    elseif($index == 1) { $bg = 'bg-secondary'; $text = 'text-white'; $border = 'border-left-secondary'; }
                    else { $bg = 'bg-danger'; $text = 'text-white'; $border = 'border-left-danger'; } // Bronze effect
                @endphp
                
                <div class="card {{ $border }} shadow-sm mb-3" style="border-radius: 12px; border-width: 0 0 0 5px;">
                    <div class="card-body p-3">
                        <div class="row align-items-center">
                            <div class="col-auto">
                                <div class="{{ $bg }} {{ $text }} font-weight-bold d-flex justify-content-center align-items-center rounded" style="width: 50px; height: 50px; font-size: 1.2rem;">
                                    {{ $index + 1 }}
                                </div>
                            </div>
                            <div class="col">
                                <h5 class="font-weight-bold text-dark mb-1">{{ $hasil->petani->nama_petani ?? $hasil['nama_petani'] ?? 'Nama Petani' }}</h5>
                                <span class="badge badge-success px-2 py-1"><i class="fas fa-check-circle mr-1"></i> Direkomendasikan</span>
                            </div>
                            <div class="col-auto text-right">
                                <h4 class="font-weight-bold text-dark mb-0">{{ number_format($hasil->nilai_akhir ?? $hasil['nilai_akhir'] ?? 0, 4) }}</h4>
                                <small class="text-muted font-weight-bold">SKOR VIKOR (Q)</small>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="col-xl-6 col-lg-6 mb-4">
            <h5 class="font-weight-bold text-gray-800 mb-3"><i class="fas fa-chart-bar text-success mr-2"></i> Distribusi Nilai (Top 10)</h5>
            <div class="card shadow-sm border-0" style="border-radius: 12px; height: 85%;">
                <div class="card-body">
                    <canvas id="rankingChart"></canvas>
                    <p class="text-center text-muted small mt-3 mb-0">Semakin kecil nilai Q, semakin diprioritaskan untuk mendapatkan subsidi.</p>
                </div>
            </div>
        </div>
    </div>

    <div class="card shadow-sm border-0 mb-4" style="border-radius: 12px;">
        <div class="card-header bg-white py-3" style="border-radius: 12px 12px 0 0;">
            <h6 class="m-0 font-weight-bold text-success"><i class="fas fa-list mr-2"></i>Daftar Lengkap Hasil Ranking</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover" id="dataTable" width="100%" cellspacing="0">
                    <thead class="bg-light text-dark">
                        <tr>
                            <th width="5%" class="text-center">Rank</th>
                            <th width="20%">Nama Petani</th>
                            <th width="20%">Kelompok Tani</th>
                            <th width="15%" class="text-center">Nilai S</th>
                            <th width="15%" class="text-center">Nilai R</th>
                            <th width="15%" class="text-center font-weight-bold text-success">Skor VIKOR (Q)</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($rankingData as $index => $hasil)
                        <tr>
                            <td class="text-center font-weight-bold">{{ $index + 1 }}</td>
                            <td>{{ $hasil->petani->nama_petani ?? $hasil['nama_petani'] ?? '-' }}</td>
                            <td>{{ $hasil->petani->poktan->nama_poktan ?? $hasil['nama_poktan'] ?? '-' }}</td>
                            <td class="text-center">{{ number_format($hasil->nilai_s ?? $hasil['nilai_s'] ?? 0, 4) }}</td>
                            <td class="text-center">{{ number_format($hasil->nilai_r ?? $hasil['nilai_r'] ?? 0, 4) }}</td>
                            <td class="text-center font-weight-bold text-success">{{ number_format($hasil->nilai_akhir ?? $hasil['nilai_akhir'] ?? 0, 4) }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div>

<link href="{{ asset('vendor/datatables/dataTables.bootstrap4.min.css') }}" rel="stylesheet">
<script src="{{ asset('vendor/datatables/jquery.dataTables.min.js') }}"></script>
<script src="{{ asset('vendor/datatables/dataTables.bootstrap4.min.js') }}"></script>
<script src="{{ asset('vendor/chart.js/Chart.min.js') }}"></script>

<script>
    // Inisialisasi Tabel Pencarian & Pagination
    $(document).ready(function() {
        $('#dataTable').DataTable({
            "language": {
                "url": "//cdn.datatables.net/plug-ins/1.10.24/i18n/Indonesian.json" // Mengubah teks search/next menjadi Bahasa Indonesia
            }
        });
    });

    // Inisialisasi Grafik Chart.js
    // Mengambil maksimal 10 data teratas dari array $rankingData agar grafik tidak padat
    @php
        $top10 = array_slice($rankingData, 0, 10);
        $labels = [];
        $values = [];
        foreach($top10 as $item) {
            $labels[] = $item->petani->nama_petani ?? $item['nama_petani'] ?? 'N/A';
            $values[] = $item->nilai_akhir ?? $item['nilai_akhir'] ?? 0;
        }
    @endphp

    var labels = {!! json_encode($labels) !!};
    var dataPoints = {!! json_encode($values) !!};

    var ctx = document.getElementById("rankingChart").getContext('2d');
    var rankingChart = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: labels,
            datasets: [{
                label: "Skor VIKOR (Q)",
                backgroundColor: "#1cc88a",
                hoverBackgroundColor: "#17a673",
                borderColor: "#1cc88a",
                data: dataPoints,
            }],
        },
        options: {
            maintainAspectRatio: false,
            layout: {
                padding: { left: 10, right: 25, top: 25, bottom: 0 }
            },
            scales: {
                xAxes: [{
                    gridLines: { display: false, drawBorder: false },
                    ticks: { maxTicksLimit: 10 }
                }],
                yAxes: [{
                    ticks: {
                        beginAtZero: true,
                        maxTicksLimit: 5,
                        padding: 10
                    },
                    gridLines: {
                        color: "rgb(234, 236, 244)",
                        zeroLineColor: "rgb(234, 236, 244)",
                        drawBorder: false,
                        borderDash: [2],
                        zeroLineBorderDash: [2]
                    }
                }],
            },
            legend: { display: false },
            tooltips: {
                titleMarginBottom: 10,
                titleFontColor: '#6e707e',
                titleFontSize: 14,
                backgroundColor: "rgb(255,255,255)",
                bodyFontColor: "#858796",
                borderColor: '#dddfeb',
                borderWidth: 1,
                xPadding: 15,
                yPadding: 15,
                displayColors: false,
                caretPadding: 10,
            },
        }
    });
</script>
@endsection