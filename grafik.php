<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Grafik Laporan Sampah</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootswatch/4.5.2/lux/bootstrap.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        .chart-container {
            width: 1000px;
            height: 500px;
            margin: 0 auto;
            position: relative;
        }
        canvas {
            box-shadow: 0px 10px 20px rgba(0, 0, 0, 0.15);
        }
    </style>
</head>
<body>

<div class="container mt-5">
    <h2 class="text-center mb-4">Grafik Laporan Sampah Plastik</h2>

    <!-- Tombol Kembali ke Dashboard -->
    <div class="text-center mb-3">
        <a href="dashboard.php" class="btn btn-primary">Kembali ke Dashboard</a>
    </div>

    <div class="chart-container">
        <canvas id="myPieChart"></canvas>
    </div>

    <?php
    // Koneksi ke database dan hitung data untuk kesimpulan
    include 'koneksi.php';

    // Query untuk mendapatkan total sampah per kategori
    $queryTotal = "SELECT SUM(sampah_organik) AS total_organik, SUM(sampah_anorganik) AS total_anorganik, SUM(sampah_berbahaya) AS total_berbahaya FROM laporan_sampah";
    $resultTotal = mysqli_query($conn, $queryTotal);
    $totalRow = mysqli_fetch_assoc($resultTotal);

    // Query untuk mendapatkan tanggal dengan laporan terbanyak
    $queryTanggal = "SELECT tanggal, COUNT(*) AS jumlah_laporan FROM laporan_sampah GROUP BY tanggal ORDER BY jumlah_laporan DESC LIMIT 1";
    $resultTanggal = mysqli_query($conn, $queryTanggal);
    $tanggalRow = mysqli_fetch_assoc($resultTanggal);

    // Kesimpulan
    $kesimpulan = [
        "Tanggal laporan terbanyak" => $tanggalRow['tanggal'] ?? 'Data tidak tersedia',
        "Sampah organik terbanyak" => $totalRow['total_organik'] ?? 0,
        "Sampah anorganik terbanyak" => $totalRow['total_anorganik'] ?? 0,
        "Sampah berbahaya terbanyak" => $totalRow['total_berbahaya'] ?? 0
    ];
    ?>

    <div class="mt-5">
        <h4>Kesimpulan</h4>
        <ul>
            <li><strong>Tanggal dengan laporan terbanyak:</strong> <?= $kesimpulan["Tanggal laporan terbanyak"]; ?></li>
            <li><strong>Total sampah organik (Kg):</strong> <?= $kesimpulan["Sampah organik terbanyak"]; ?></li>
            <li><strong>Total sampah anorganik (Kg):</strong> <?= $kesimpulan["Sampah anorganik terbanyak"]; ?></li>
            <li><strong>Total sampah berbahaya (Kg):</strong> <?= $kesimpulan["Sampah berbahaya terbanyak"]; ?></li>
        </ul>
    </div>
</div>

<script>
    // Data untuk grafik dari database
    const dataLaporan = {
        labels: [
            'Sampah Organik (Kg)',
            'Sampah Anorganik (Kg)',
            'Sampah Berbahaya (Kg)'
        ],
        datasets: [{
            data: [<?php echo $totalRow['total_organik'] . ', ' . $totalRow['total_anorganik'] . ', ' . $totalRow['total_berbahaya']; ?>],
            backgroundColor: [
                'rgba(75, 192, 192, 0.8)',
                'rgba(255, 159, 64, 0.8)',
                'rgba(255, 99, 132, 0.8)'
            ],
            borderColor: [
                'rgba(75, 192, 192, 1)',
                'rgba(255, 159, 64, 1)',
                'rgba(255, 99, 132, 1)'
            ],
            borderWidth: 2,
            hoverOffset: 10
        }]
    };

    const config = {
        type: 'doughnut',
        data: dataLaporan,
        options: {
            responsive: true,
            plugins: {
                legend: {
                    position: 'top',
                },
                tooltip: {
                    enabled: true
                }
            },
            animation: {
                animateRotate: true,
                animateScale: true
            },
            cutout: '50%',
            maintainAspectRatio: false
        },
    };

    const myPieChart = new Chart(
        document.getElementById('myPieChart'),
        config
    );
</script>

</body>
</html>
