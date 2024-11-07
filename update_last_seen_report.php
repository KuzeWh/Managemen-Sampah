<?php
session_start();
include 'db.php';

$userId = $_SESSION['user_id'];

// Ambil laporan terbaru
$sql = "SELECT id FROM laporan_sampah WHERE user_id = $userId ORDER BY id DESC LIMIT 1";
$result = $conn->query($sql);
$lastReportId = $result->fetch_assoc()['id'] ?? 0;

// Perbarui last_seen_report di tabel users
$updateSql = "UPDATE users SET last_seen_report = $lastReportId WHERE id = $userId";
$conn->query($updateSql);
?>
