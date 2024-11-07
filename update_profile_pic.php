<?php
session_start();
include 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['profile_pic']) && isset($_SESSION['user_id'])) {
    $userId = $_SESSION['user_id'];

    $targetDir = "uploads/";
    $targetFile = $targetDir . basename($_FILES["profile_pic"]["name"]);
    $imageFileType = strtolower(pathinfo($targetFile, PATHINFO_EXTENSION));

    // Validasi file gambar
    $check = getimagesize($_FILES["profile_pic"]["tmp_name"]);
    if ($check === false) {
        echo "File bukan gambar.";
        exit;
    }

    if (!move_uploaded_file($_FILES["profile_pic"]["tmp_name"], $targetFile)) {
        echo "Terjadi kesalahan saat mengunggah gambar.";
        exit;
    }

    // Update path gambar di database
    $updateQuery = "UPDATE users SET profile_pic = ? WHERE id = ?";
    $stmt = $conn->prepare($updateQuery);
    $stmt->bind_param("si", $targetFile, $userId);
    $stmt->execute();

    header('Location: dashboard.php');
    exit();
}
?>
