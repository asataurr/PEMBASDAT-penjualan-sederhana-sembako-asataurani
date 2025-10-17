<?php
// db.php - Koneksi ke database MySQL

$host = 'localhost'; // Sesuaikan jika berbeda
$user = 'root';      // Ganti dengan username database Anda
$pass = '';          // Ganti dengan password database Anda
$db_name = 'toko_asataurani';  // Nama database sesuai permintaan

// Buat koneksi
$conn = new mysqli($host, $user, $pass, $db_name);

// Cek koneksi
if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);
}

// Set karakter set ke utf8mb4 jika diperlukan
$conn->set_charset("utf8mb4");

// Fungsi untuk membersihkan input data dari potensi serangan SQL Injection
function anti_injection($data) {
    global $conn;
    $filter = $conn->real_escape_string(stripslashes(strip_tags(htmlspecialchars($data, ENT_QUOTES))));
    return $filter;
}
?>