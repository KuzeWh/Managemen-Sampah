<?php
// delete_laporan.php

include 'koneksi.php'; // Include koneksi ke database

// Ambil ID dari URL
$id = $_GET['id'];

// Query untuk menghapus laporan berdasarkan ID
$sql = "DELETE FROM laporan_sampah WHERE id = $id";

if (mysqli_query($conn, $sql)) {
    // Redirect kembali ke dashboard setelah laporan dihapus
    header('Location: dashboard.php');
} else {
    echo "Error: " . mysqli_error($conn);
}

// Tutup koneksi
mysqli_close($conn);
?>
