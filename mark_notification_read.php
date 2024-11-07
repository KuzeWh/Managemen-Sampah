<?php
include 'koneksi.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'mark_all_as_read') {
    // Update semua laporan sebagai telah dibaca
    $sql = "UPDATE notifications SET is_read = 1 WHERE is_read = 0";
    $result = $conn->query($sql);

    if ($result) {
        echo "Semua notifikasi telah ditandai sebagai telah dibaca.";
    } else {
        echo "Gagal menandai notifikasi.";
    }

    $conn->close();
}
?>
