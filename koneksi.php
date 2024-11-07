<?php
// koneksi.php

$servername = "localhost";  // Ganti dengan alamat server database Anda
$username = "root";         // Ganti dengan username MySQL Anda
$password = "";             // Ganti dengan password MySQL Anda
$dbname = "manajemen_sampah";      // Ganti dengan nama database yang digunakan

// Membuat koneksi
$conn = mysqli_connect($servername, $username, $password, $dbname);

// Cek koneksi
if (!$conn) {
    die("Koneksi gagal: " . mysqli_connect_error());
}
?>
