<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header('Location: login.php');
    exit();
}

include 'koneksi.php';

$user_id = $_GET['user_id'];
$queryUser = "SELECT username FROM users WHERE id = $user_id";
$resultUser = mysqli_query($conn, $queryUser);
$user = mysqli_fetch_assoc($resultUser);

$queryReports = "SELECT * FROM laporan_sampah WHERE user_id = $user_id ORDER BY tanggal DESC";
$resultReports = mysqli_query($conn, $queryReports);
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Pengguna</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootswatch/4.5.2/lux/bootstrap.min.css">
</head>
<body>
    <div class="container mt-5">
        <h2>Laporan Sampah dari: <?= htmlspecialchars($user['username']); ?></h2>
        
        <a href="admin_dashboard.php" class="btn btn-secondary mb-4">Kembali ke Dashboard Admin</a>

        <table class="table table-striped">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Sampah Organik (Kg)</th>
                    <th>Sampah Anorganik (Kg)</th>
                    <th>Sampah Berbahaya (Kg)</th>
                    <th>Tanggal</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = mysqli_fetch_assoc($resultReports)): ?>
                    <tr>
                        <td><?= $row['id']; ?></td>
                        <td><?= $row['sampah_organik']; ?></td>
                        <td><?= $row['sampah_anorganik']; ?></td>
                        <td><?= $row['sampah_berbahaya']; ?></td>
                        <td><?= $row['tanggal']; ?></td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</body>
</html>
