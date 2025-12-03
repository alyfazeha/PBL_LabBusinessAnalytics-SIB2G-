<?php
require_once __DIR__ . "/../config/database.php";

session_start();

if (!isset($_SESSION['user_id'])) {
    die("Unauthorized access.");
}

$user_id = $_SESSION['user_id'];
$role    = $_SESSION['role'];
$nidn    = $_SESSION['nidn'] ?? null;
$nim     = $_SESSION['nim'] ?? null;

$db  = new Database();
$conn = $db->getConnection();

/* ==========================================================
   QUERY SESUAI ROLE
========================================================== */

if ($role === 'admin') {
    // admin → semua booking
    $sql = "
        SELECT b.*, s.nama_sarana, u.username AS peminjam
        FROM bookings b
        LEFT JOIN sarana s ON b.sarana_id = s.sarana_id
        LEFT JOIN users u ON b.created_by = u.user_id
        ORDER BY b.tanggal DESC, b.start_time
    ";
    $stmt = $conn->prepare($sql);

} elseif ($role === 'dosen') {
    // dosen → booking yang memilih dia sebagai penanggung jawab
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
    // mahasiswa → booking miliknya
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
?>