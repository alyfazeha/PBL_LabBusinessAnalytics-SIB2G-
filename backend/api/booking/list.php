<?php
ini_set('display_errors', 0);
error_reporting(E_ALL);

header('Content-Type: application/json');
header("Access-Control-Allow-Origin: *");

require_once __DIR__ . "/../../config/database.php";

session_start();

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(["error" => "Unauthorized access."]);
    exit;
}

try {
    // PERBAIKAN KONEKSI
    $db = Database::getInstance(); 

    $user_id = $_SESSION['user_id'];
    $role    = $_SESSION['role'];
    $nidn    = $_SESSION['nidn'] ?? null;
    $nim     = $_SESSION['nim'] ?? null;

    if ($role === 'admin') {
        $sql = "SELECT b.*, s.nama_sarana, u.username AS peminjam
                FROM bookings b
                LEFT JOIN sarana s ON b.sarana_id = s.sarana_id
                LEFT JOIN users u ON b.created_by = u.user_id
                ORDER BY b.tanggal DESC, b.start_time";
        $stmt = $db->prepare($sql);
        $stmt->execute();

    } elseif ($role === 'dosen') {
        $sql = "SELECT b.*, s.nama_sarana, u.username AS peminjam
                FROM bookings b
                LEFT JOIN sarana s ON b.sarana_id = s.sarana_id
                LEFT JOIN users u ON b.created_by = u.user_id
                WHERE b.booking_dosen_nidn = :nidn
                ORDER BY b.tanggal DESC, b.start_time";
        $stmt = $db->prepare($sql);
        $stmt->execute([':nidn' => $nidn]);

    } else {
        $sql = "SELECT b.*, s.nama_sarana, u.username AS peminjam
                FROM bookings b
                LEFT JOIN sarana s ON b.sarana_id = s.sarana_id
                LEFT JOIN users u ON b.created_by = u.user_id
                WHERE b.mahasiswa_nim = :nim
                ORDER BY b.tanggal DESC, b.start_time";
        $stmt = $db->prepare($sql);
        $stmt->execute([':nim' => $nim]);
    }

    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($rows);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(["error" => "Server Error: " . $e->getMessage()]);
}
?>