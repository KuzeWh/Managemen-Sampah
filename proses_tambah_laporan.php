<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$user_id = $_SESSION['user_id'];

// Pastikan nilai input laporan tersedia
if (isset($_POST['sampah_organik'], $_POST['sampah_anorganik'], $_POST['sampah_berbahaya'])) {
    $sampah_organik = (float) $_POST['sampah_organik'];
    $sampah_anorganik = (float) $_POST['sampah_anorganik'];
    $sampah_berbahaya = (float) $_POST['sampah_berbahaya'];

    // Masukkan data laporan ke tabel laporan_sampah
    $insertLaporan = $conn->prepare("INSERT INTO laporan_sampah (user_id, sampah_organik, sampah_anorganik, sampah_berbahaya, tanggal) VALUES (?, ?, ?, ?, NOW())");
    $insertLaporan->bind_param("iddd", $user_id, $sampah_organik, $sampah_anorganik, $sampah_berbahaya);
    if (!$insertLaporan->execute()) {
        echo "Gagal menambahkan laporan: " . $insertLaporan->error;
        exit();
    }

    // Buat notifikasi laporan baru
    $message = "Laporan baru telah ditambahkan.";
    $createNotification = $conn->prepare("INSERT INTO notifications (user_id, message) VALUES (?, ?)");
    $createNotification->bind_param("is", $user_id, $message);
    if (!$createNotification->execute()) {
        echo "Gagal menambahkan notifikasi: " . $createNotification->error;
        exit();
    }

    // Update jumlah laporan di leaderboard
    $updateLeaderboard = $conn->prepare("INSERT INTO leaderboard (user_id, username, report_count, badge)
                                         VALUES (?, (SELECT username FROM users WHERE id = ?), 1, 'Newbie')
                                         ON DUPLICATE KEY UPDATE report_count = report_count + 1");
    $updateLeaderboard->bind_param("ii", $user_id, $user_id);
    if (!$updateLeaderboard->execute()) {
        echo "Gagal memperbarui leaderboard: " . $updateLeaderboard->error;
        exit();
    }

    // Ambil jumlah laporan terbaru
    $getReportCount = $conn->prepare("SELECT report_count FROM leaderboard WHERE user_id = ?");
    $getReportCount->bind_param("i", $user_id);
    $getReportCount->execute();
    $result = $getReportCount->get_result();
    $report_count = $result->fetch_assoc()['report_count'];

    // Tentukan badge berdasarkan jumlah laporan
    if ($report_count >= 10) {
        $badge = 'Eco Warrior';
    } elseif ($report_count >= 5) {
        $badge = 'Recycler';
    } else {
        $badge = 'Newbie';
    }

    // Update badge di leaderboard
    $updateBadge = $conn->prepare("UPDATE leaderboard SET badge = ? WHERE user_id = ?");
    $updateBadge->bind_param("si", $badge, $user_id);
    if (!$updateBadge->execute()) {
        echo "Gagal memperbarui badge: " . $updateBadge->error;
        exit();
    }

    // Redirect ke dashboard setelah semua operasi berhasil
    header('Location: dashboard.php');
    exit();
} else {
    echo "Data laporan tidak lengkap.";
    exit();
}
?>
