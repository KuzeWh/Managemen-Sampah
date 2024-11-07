<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

include 'db.php';

$userId = $_SESSION['user_id'];
$userRole = $_SESSION['role'] ?? '';

// Validasi dan sanitasi input tanggal
$filter = "";
$start_date = $end_date = '';
if (isset($_GET['start_date'], $_GET['end_date'])) {
    $start_date = $_GET['start_date'];
    $end_date = $_GET['end_date'];
    
    // Periksa format tanggal
    if (DateTime::createFromFormat('Y-m-d', $start_date) && DateTime::createFromFormat('Y-m-d', $end_date)) {
        $filter = "AND tanggal BETWEEN ? AND ?";
    } else {
        echo "Format tanggal tidak valid!";
        exit();
    }
}

$limit = 10;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

$newReportsCount = 0;
if ($userRole === 'admin') {
    $queryLastSeen = "SELECT last_seen_report FROM users WHERE id = ?";
    $stmt = $conn->prepare($queryLastSeen);
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $lastSeenReportId = $stmt->get_result()->fetch_assoc()['last_seen_report'] ?? 0;
    $stmt->close();

    $sqlNewReports = "SELECT COUNT(id) AS new_reports FROM laporan_sampah WHERE id > ?";
    $stmt = $conn->prepare($sqlNewReports);
    $stmt->bind_param("i", $lastSeenReportId);
    $stmt->execute();
    $newReportsCount = $stmt->get_result()->fetch_assoc()['new_reports'];
    $stmt->close();
}

$sqlCount = "SELECT COUNT(id) AS total FROM laporan_sampah WHERE user_id = ? $filter";
$stmt = $conn->prepare($sqlCount);
if ($filter) {
    $stmt->bind_param("iss", $userId, $start_date, $end_date);
} else {
    $stmt->bind_param("i", $userId);
}
$stmt->execute();
$totalLaporan = $stmt->get_result()->fetch_assoc()['total'];
$totalPages = ceil($totalLaporan / $limit);
$stmt->close();

$sql = "SELECT * FROM laporan_sampah WHERE user_id = ? $filter ORDER BY id DESC LIMIT ? OFFSET ?";
$stmt = $conn->prepare($sql);
if ($filter) {
    $stmt->bind_param("issii", $userId, $start_date, $end_date, $limit, $offset);
} else {
    $stmt->bind_param("iii", $userId, $limit, $offset);
}
$stmt->execute();
$laporan = $stmt->get_result();

$queryUser = "SELECT profile_pic, badge FROM users WHERE id = ?";
$stmt = $conn->prepare($queryUser);
$stmt->bind_param("i", $userId);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$stmt->close();

if ($userId) {
    $query = "SELECT COUNT(*) AS total_laporan FROM laporan WHERE user_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $totalLaporan = $stmt->get_result()->fetch_assoc()['total_laporan'];
    $stmt->close();

    $newBadge = 'Newbie';
    if ($totalLaporan >= 5 && $totalLaporan <= 9) $newBadge = 'Recycler';
    elseif ($totalLaporan >= 10) $newBadge = 'Eco Warrior';

    $queryCurrentBadge = "SELECT badge FROM users WHERE id = ?";
    $stmt = $conn->prepare($queryCurrentBadge);
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $currentBadge = $stmt->get_result()->fetch_assoc()['badge'];
    $stmt->close();

    if ($newBadge !== $currentBadge) {
        $queryUpdateBadge = "UPDATE users SET badge = ? WHERE id = ?";
        $stmt = $conn->prepare($queryUpdateBadge);
        $stmt->bind_param("si", $newBadge, $userId);
        $stmt->execute();
        $stmt->close();
    }
}

$conn->close();
?>


<!-- (HTML structure remains the same) -->

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootswatch/4.5.2/lux/bootstrap.min.css">
</head>
<body>

    <div class="container mt-5">

        <h2>Dashboard</h2>
        <nav class="mb-4">
            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#tambahLaporanModal">
                Tambah Laporan
            </button>
            <a href="export_csv.php" class="btn btn-secondary">Export ke CSV</a>
            <a href="export_pdf.php" class="btn btn-secondary">Export ke PDF</a>
            <a href="logout.php" class="btn btn-danger">Logout</a>
            <?php if ($userRole === 'admin'): ?>
                <a href="admin_dashboard.php" class="btn btn-warning">Dashboard Admin</a>
            <?php endif; ?>
        </nav>

        <div class="profile mb-4">
            <h4>Foto Profil:</h4>
            <img src="<?= htmlspecialchars($user['profile_pic'] ?? 'default.png'); ?>" alt="Foto Profil"
                class="img-thumbnail" width="100" height="100" onclick="openProfileModal()">
            <p>Badge: <strong><?= $user['badge'] ?? 'Newbie'; ?></strong></p>
        </div>

        <!-- Modal untuk Ganti Foto Profil -->
        <div class="modal fade" id="profileModal" tabindex="-1" role="dialog" aria-labelledby="profileModalLabel" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <form action="update_profile_pic.php" method="post" enctype="multipart/form-data">
                        <div class="modal-header">
                            <h5 class="modal-title" id="profileModalLabel">Ganti Foto Profil</h5>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <div class="modal-body">
                            <input type="file" name="profile_pic" class="form-control" required>
                        </div>
                        <div class="modal-footer">
                            <button type="submit" class="btn btn-primary">Simpan</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <h3>Filter Laporan</h3>
        <form method="GET" class="form-inline mb-4">
            <div class="form-group mr-2">
                <label for="start_date">Dari: </label>
                <input type="date" name="start_date" class="form-control ml-2" required>
            </div>
            <div class="form-group mr-2">
                <label for="end_date">Sampai: </label>
                <input type="date" name="end_date" class="form-control ml-2" required>
            </div>
            <button type="submit" class="btn btn-info">Filter</button>
        </form>

        <h2 class="text-center mb-4">Tabel Laporan Sampah</h2>

        <table class="table table-striped">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Sampah Organik (Kg)</th>
                    <th>Sampah Anorganik (Kg)</th>
                    <th>Sampah Berbahaya (Kg)</th>
                    <th>Tanggal</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = mysqli_fetch_assoc($laporan)) { ?>
                    <tr>
                        <td><?php echo $row['id']; ?></td>
                        <td><?php echo $row['sampah_organik']; ?></td>
                        <td><?php echo $row['sampah_anorganik']; ?></td>
                        <td><?php echo $row['sampah_berbahaya']; ?></td>
                        <td><?php echo $row['tanggal']; ?></td>
                        <td>
                            <!-- Tombol Edit memicu modal -->
                            <button class="btn btn-warning btn-sm" onclick="setEditData(<?php echo $row['id']; ?>, <?php echo $row['sampah_organik']; ?>, <?php echo $row['sampah_anorganik']; ?>, <?php echo $row['sampah_berbahaya']; ?>)" data-bs-toggle="modal" data-bs-target="#editLaporanModal">Edit</button>
                            <!-- Tombol Hapus memicu modal konfirmasi -->
                            <button class="btn btn-danger btn-sm" onclick="confirmDelete(<?php echo $row['id']; ?>)" data-bs-toggle="modal" data-bs-target="#hapusLaporanModal">
                                Hapus
                            </button>
                        </td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>

        <nav aria-label="Page navigation example">
            <ul class="pagination">
                <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                    <li class="page-item <?= $i == $page ? 'active' : '' ?>">
                        <a class="page-link" href="?page=<?= $i ?>&start_date=<?= $start_date ?>&end_date=<?= $end_date ?>"><?= $i ?></a>
                    </li>
                <?php endfor; ?>
            </ul>
        </nav>

        <a href="grafik.php" class="btn btn-info mt-3">Lihat Grafik</a>

    </div>

    <!-- Modal Tambah Laporan -->
    <div class="modal fade" id="tambahLaporanModal" tabindex="-1" aria-labelledby="tambahLaporanModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="tambahLaporanModalLabel">Tambah Laporan Sampah</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="proses_tambah_laporan.php" method="POST">
                    <div class="modal-body">
                        <div class="form-group mb-3">
                            <label for="sampah_organik">Jumlah Sampah Organik (Kg)</label>
                            <input type="number" name="sampah_organik" class="form-control" required>
                        </div>
                        <div class="form-group mb-3">
                            <label for="sampah_anorganik">Jumlah Sampah Anorganik (Kg)</label>
                            <input type="number" name="sampah_anorganik" class="form-control" required>
                        </div>
                        <div class="form-group mb-3">
                            <label for="sampah_berbahaya">Jumlah Sampah Berbahaya (Kg)</label>
                            <input type="number" name="sampah_berbahaya" class="form-control" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                        <button type="submit" class="btn btn-primary">Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Edit Laporan -->
    <div class="modal fade" id="editLaporanModal" tabindex="-1" aria-labelledby="editLaporanModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editLaporanModalLabel">Edit Laporan Sampah</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="proses_edit_laporan.php" method="POST">
                    <div class="modal-body">
                        <input type="hidden" id="edit_id" name="id">
                        
                        <div class="form-group mb-3">
                            <label for="edit_sampah_organik">Jumlah Sampah Organik (Kg)</label>
                            <input type="number" id="edit_sampah_organik" name="sampah_organik" class="form-control" required>
                        </div>
                        <div class="form-group mb-3">
                            <label for="edit_sampah_anorganik">Jumlah Sampah Anorganik (Kg)</label>
                            <input type="number" id="edit_sampah_anorganik" name="sampah_anorganik" class="form-control" required>
                        </div>
                        <div class="form-group mb-3">
                            <label for="edit_sampah_berbahaya">Jumlah Sampah Berbahaya (Kg)</label>
                            <input type="number" id="edit_sampah_berbahaya" name="sampah_berbahaya" class="form-control" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                        <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Konfirmasi Hapus -->
    <div class="modal fade" id="hapusLaporanModal" tabindex="-1" aria-labelledby="hapusLaporanModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="hapusLaporanModalLabel">Konfirmasi Hapus</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    Apakah Anda yakin ingin menghapus laporan ini?
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <a href="#" id="confirmDeleteBtn" class="btn btn-danger">Hapus</a>
                </div>
            </div>
        </div>
    </div>

    <!-- JavaScript untuk Mengisi Data di Modal Edit dan Konfirmasi Hapus -->
    <script>
        function setEditData(id, sampahOrganik, sampahAnorganik, sampahBerbahaya) {
            document.getElementById('edit_id').value = id;
            document.getElementById('edit_sampah_organik').value = sampahOrganik;
            document.getElementById('edit_sampah_anorganik').value = sampahAnorganik;
            document.getElementById('edit_sampah_berbahaya').value = sampahBerbahaya;
        }

        function confirmDelete(id) {
            document.getElementById('confirmDeleteBtn').setAttribute('href', 'delete_laporan.php?id=' + id);
        }

        function markAsRead() {
        fetch('update_last_seen_report.php', {method: 'POST'})
            .then(response => response.text())
            .then(data => {
                location.reload();
            });
        }

            function openProfileModal() {
            var profileModal = new bootstrap.Modal(document.getElementById('profileModal'));
            profileModal.show();
        }

        function markAsRead() {
            $.ajax({
                url: 'mark_notification_read.php',
                type: 'POST',
                data: { action: 'mark_all_as_read' },
                success: function(response) {
                    alert(response); // Menampilkan pesan dari server
                    location.reload(); // Reload halaman agar notifikasi hilang
                },
                error: function() {
                    alert("Gagal menandai notifikasi sebagai telah dibaca.");
                }
            });
        }
    </script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
</body>
</html>
