<?php
include 'koneksi.php';

if (isset($_GET['id'])) {
    $user_id = $_GET['id'];

    // Hapus semua notifikasi yang terkait dengan user_id
    $deleteNotifications = "DELETE FROM notifications WHERE user_id = ?";
    $stmt1 = $conn->prepare($deleteNotifications);
    $stmt1->bind_param("i", $user_id);
    $stmt1->execute();

    // Hapus pengguna setelah notifikasi yang terkait dihapus
    $deleteUser = "DELETE FROM users WHERE id = ?";
    $stmt2 = $conn->prepare($deleteUser);
    $stmt2->bind_param("i", $user_id);
    $stmt2->execute();

    if ($stmt2->affected_rows > 0) {
        echo "Pengguna berhasil dihapus.";
    } else {
        echo "Gagal menghapus pengguna.";
    }

    $stmt1->close();
    $stmt2->close();
    $conn->close();
}
?>
