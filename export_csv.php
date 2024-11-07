<?php
// export_csv.php

include 'koneksi.php'; // Koneksi ke database

header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=laporan_sampah.csv');

// Membuat output file
$output = fopen('php://output', 'w');

// Header kolom
fputcsv($output, array('ID', 'Sampah Organik (Kg)', 'Sampah Anorganik (Kg)', 'Sampah Berbahaya (Kg)', 'Tanggal'));

// Ambil semua data dari tabel laporan_sampah
$query = "SELECT * FROM laporan_sampah ORDER BY tanggal DESC";
$result = mysqli_query($conn, $query);

// Tulis data ke CSV
while($row = mysqli_fetch_assoc($result)) {
    fputcsv($output, array($row['id'], $row['sampah_organik'], $row['sampah_anorganik'], $row['sampah_berbahaya'], $row['tanggal']));
}

// Tutup output
fclose($output);
?>
