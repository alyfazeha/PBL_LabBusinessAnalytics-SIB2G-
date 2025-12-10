<?php
require_once __DIR__ . "/../../config/database.php";
require_once __DIR__ . "/../../config/auth.php";

// Cek Login (Semua role boleh lihat detail asalkan login)
if (session_status() === PHP_SESSION_NONE) { session_start(); }
if (!isset($_SESSION['user_id'])) {
    die(json_encode(["error" => "Unauthorized"]));
}

// PERBAIKAN KONEKSI
$conn = Database::getInstance();

if (!isset($_GET['id'])) {
    die(json_encode(["error" => "ID booking tidak ditemukan."]));
}

$id = $_GET['id'];

// Pastikan tabel vw_peminjaman_history ada, kalau tidak, ganti 'bookings'
$sql = "SELECT * FROM bookings WHERE booking_id = :id"; 
$stmt = $conn->prepare($sql);
$stmt->bindParam(":id", $id);
$stmt->execute();

$data = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$data) {
    echo json_encode(["error" => "Data booking tidak ditemukan."]);
} else {
    echo json_encode($data);
}
?>