<?php
// export_pdf.php

require('fpdf.php');
include 'koneksi.php'; // Koneksi ke database

// Membuat instance FPDF
class PDF extends FPDF {
    // Header
    function Header() {
        $this->SetFont('Arial', 'B', 14);
        $this->Cell(190, 10, 'Laporan Sampah Plastik', 0, 1, 'C');
        $this->Ln(5);
        
        // Tabel Header
        $this->SetFont('Arial', 'B', 12);
        $this->SetFillColor(200, 220, 255); // Warna latar header
        $this->Cell(10, 10, 'ID', 1, 0, 'C', true);
        $this->Cell(50, 10, 'Sampah Organik (Kg)', 1, 0, 'C', true);
        $this->Cell(50, 10, 'Sampah Anorganik (Kg)', 1, 0, 'C', true);
        $this->Cell(50, 10, 'Sampah Berbahaya (Kg)', 1, 0, 'C', true);
        $this->Cell(30, 10, 'Tanggal', 1, 1, 'C', true);
    }
    
    // Footer
    function Footer() {
        $this->SetY(-15);
        $this->SetFont('Arial', 'I', 8);
        $this->Cell(0, 10, 'Halaman '.$this->PageNo(), 0, 0, 'C');
    }
}

// Membuat instance PDF
$pdf = new PDF('P', 'mm', 'A4');
$pdf->AddPage();

// Ambil semua data dari tabel laporan_sampah
$query = "SELECT * FROM laporan_sampah ORDER BY tanggal DESC";
$result = mysqli_query($conn, $query);

// Isi Tabel
$pdf->SetFont('Arial', '', 12);
while($row = mysqli_fetch_assoc($result)) {
    $pdf->Cell(10, 10, $row['id'], 1, 0, 'C');
    $pdf->Cell(50, 10, $row['sampah_organik'], 1, 0, 'C');
    $pdf->Cell(50, 10, $row['sampah_anorganik'], 1, 0, 'C');
    $pdf->Cell(50, 10, $row['sampah_berbahaya'], 1, 0, 'C');
    $pdf->Cell(30, 10, $row['tanggal'], 1, 1, 'C');
}

// Output PDF
$pdf->Output();
?>
