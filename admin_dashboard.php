<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header('Location: login.php');
}
include 'koneksi.php';

// Ambil semua pengguna dengan peran 'user'
$sql = "SELECT * FROM users WHERE role = 'user'";
$users = mysqli_query($conn, $sql);

// Ambil semua laporan sampah
$sql_laporan = "SELECT * FROM laporan_sampah ORDER BY tanggal DESC";
$laporanPengguna = mysqli_query($conn, $sql_laporan);

// Ambil log aktivitas
$logQuery = "SELECT * FROM activity_log ORDER BY timestamp DESC LIMIT 5";
$logResult = mysqli_query($conn, $logQuery);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootswatch/4.5.2/lux/bootstrap.min.css">
</head>
<body>
    <div class="container mt-5">
        <h2>Admin Dashboard</h2>
        <a href="dashboard.php" class="btn btn-primary mb-4">Kembali ke Dashboard User</a>
        
        <h3>Daftar Pengguna</h3>
        <table class="table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Username</th>
                    <th>Foto Profil</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php while($user = mysqli_fetch_assoc($users)) { ?>
                    <tr>
                        <td><?= $user['id']; ?></td>
                        <td><?= $user['username']; ?></td>
                        <td>
                            <?php if (!empty($user['profile_pic'])): ?>
                                <img src="<?= htmlspecialchars($user['profile_pic']); ?>" alt="Foto Profil" class="img-thumbnail" width="50" height="50">
                            <?php else: ?>
                                <img src="uploads/default.png" alt="Foto Profil Default" class="img-thumbnail" width="50" height="50">
                            <?php endif; ?>
                        </td>
                        <td>
                            <a href="view_user_reports.php?user_id=<?= $user['id']; ?>" class="btn btn-info btn-sm">Lihat Laporan</a>
                            <a href="delete_user.php?id=<?= $user['id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Yakin ingin menghapus akun ini?');">Hapus Pengguna</a>
                        </td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>

        <h3>Semua Laporan Sampah</h3>
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
                <?php while ($row = mysqli_fetch_assoc($laporanPengguna)) { ?>
                    <tr>
                        <td><?= $row['id']; ?></td>
                        <td><?= $row['sampah_organik']; ?></td>
                        <td><?= $row['sampah_anorganik']; ?></td>
                        <td><?= $row['sampah_berbahaya']; ?></td>
                        <td><?= $row['tanggal']; ?></td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>

        <div class="container mt-5">
            <h2>Notifikasi Terbaru</h2>
            <?php if (mysqli_num_rows($logResult) > 0): ?>
                <ul>
                    <?php while ($log = mysqli_fetch_assoc($logResult)): ?>
                        <li><?= $log['timestamp'] ?> - <?= htmlspecialchars($log['action']); ?></li>
                    <?php endwhile; ?>
                </ul>
            <?php else: ?>
                <p>Tidak ada log aktivitas terbaru.</p>
            <?php endif; ?>
        </div>

        <h3>Leaderboard</h3>
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>Username</th>
                    <th>Jumlah Laporan</th>
                    <th>Badge</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $leaderboardQuery = "SELECT u.username, l.report_count, l.badge
                                    FROM leaderboard l
                                    JOIN users u ON l.user_id = u.id
                                    ORDER BY l.report_count DESC";
                $leaders = $conn->query($leaderboardQuery);

                while ($leader = mysqli_fetch_assoc($leaders)) {
                    echo "<tr>
                            <td>{$leader['username']}</td>
                            <td>{$leader['report_count']}</td>
                            <td>{$leader['badge']}</td>
                        </tr>";
                }
                ?>
            </tbody>
        </table>
    </div>
</body>
</html>
