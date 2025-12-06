<?php
require_once __DIR__ . "/../../config/database.php";

session_start();

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    // Kirim error dalam format JSON juga, jangan die() polosan
    echo json_encode(["error" => "Unauthorized access."]);
    exit;
}

$user_id = $_SESSION['user_id'];
$role    = $_SESSION['role'];
$nidn    = $_SESSION['nidn'] ?? null;
$nim     = $_SESSION['nim'] ?? null;

$db  = new Database();
$conn = $db->getConnection();

/* ==========================================================
   QUERY SESUAI ROLE (LOGIKA KAMU SUDAH BENAR DISINI)
========================================================== */

if ($role === 'admin') {
    $sql = "
        SELECT b.*, s.nama_sarana, u.username AS peminjam
        FROM bookings b
        LEFT JOIN sarana s ON b.sarana_id = s.sarana_id
        LEFT JOIN users u ON b.created_by = u.user_id
        ORDER BY b.tanggal DESC, b.start_time
    ";
    $stmt = $conn->prepare($sql);

} elseif ($role === 'dosen') {
    $sql = "
        SELECT b.*, s.nama_sarana, u.username AS peminjam
        FROM bookings b
        LEFT JOIN sarana s ON b.sarana_id = s.sarana_id
        LEFT JOIN users u ON b.created_by = u.user_id
        WHERE b.booking_dosen_nidn = :nidn
        ORDER BY b.tanggal DESC, b.start_time
    ";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(":nidn", $nidn);

} else {
    $sql = "
        SELECT b.*, s.nama_sarana, u.username AS peminjam
        FROM bookings b
        LEFT JOIN sarana s ON b.sarana_id = s.sarana_id
        LEFT JOIN users u ON b.created_by = u.user_id
        WHERE b.mahasiswa_nim = :nim
        ORDER BY b.tanggal DESC, b.start_time
    ";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(":nim", $nim);
}

$stmt->execute();
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

// PERBAIKAN 3: KIRIM DATANYA KE FRONTEND! (Penting Banget)
echo json_encode($rows);
?>