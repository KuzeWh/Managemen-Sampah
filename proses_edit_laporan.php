<?php
// proses_edit_laporan.php

include 'koneksi.php'; // Koneksi ke database

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'];
    $sampah_organik = $_POST['sampah_organik'];
    $sampah_anorganik = $_POST['sampah_anorganik'];
    $sampah_berbahaya = $_POST['sampah_berbahaya'];

    // Update data laporan di database
    $sql = "UPDATE laporan_sampah 
            SET sampah_organik = '$sampah_organik', 
                sampah_anorganik = '$sampah_anorganik', 
                sampah_berbahaya = '$sampah_berbahaya', 
                tanggal = NOW() 
            WHERE id = $id";

    if (mysqli_query($conn, $sql)) {
        header('Location: dashboard.php'); // Kembali ke dashboard setelah update
    } else {
        echo "Error: " . $sql . "<br>" . mysqli_error($conn);
    }

    // Tutup koneksi
    mysqli_close($conn);
}
?>
