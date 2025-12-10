<?php
require_once __DIR__ . "/../../config/database.php";
require_once __DIR__ . "/../../config/auth.php";

require_admin();

if (!isset($_GET['id'])) {
    die(json_encode(["error" => "ID booking tidak ditemukan."]));
}

// PERBAIKAN KONEKSI
$conn = Database::getInstance();

$id = $_GET['id'];

// Pastikan tabel vw_peminjaman_history ada, kalau tidak, ganti 'bookings'
$sql = "SELECT * FROM vw_peminjaman_history WHERE booking_id = :id"; 
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