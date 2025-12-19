<?php
// Matikan error HTML
ini_set('display_errors', 0);
error_reporting(E_ALL);

header('Content-Type: application/json');
header("Access-Control-Allow-Origin: *");

require_once __DIR__ . "/../../config/database.php";

session_start();

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(["status" => "error", "message" => "Unauthorized access."]);
    exit;
}

try {
    // 1. Gunakan Singleton
    $db = Database::getInstance();

    $user_id = $_SESSION['user_id'];
    $role    = $_SESSION['role'];
    $nidn    = $_SESSION['nidn'] ?? null;
    $nim     = $_SESSION['nim'] ?? null;

    // ===============================================
    // Persiapan SELECT, JOIN, dan ORDER BY (Disatukan)
    // ===============================================
    $select_fields = "
        b.*, 
        s.nama_sarana, 
        u.username AS peminjam, 
        d.nama AS nama_dosen, 
        m.nama AS nama_mahasiswa
    ";

    $join_tables = "
        FROM bookings b
        JOIN sarana s ON b.sarana_id = s.sarana_id
        JOIN users u ON b.created_by = u.user_id
        JOIN dosen d ON b.booking_dosen_nidn = d.nidn
        JOIN mahasiswa m ON b.mahasiswa_nim = m.nim
    ";

    $order_by = "ORDER BY b.booking_id DESC";

    // 2. Query berdasarkan Role (Memastikan klausa WHERE terpasang)
    if ($role === 'admin') {
        // ADMIN: Lihat semua data
        $sql = "SELECT $select_fields $join_tables $order_by";
        $stmt = $db->prepare($sql);
        $stmt->execute();
    } elseif ($role === 'dosen') {
        // DOSEN: HANYA tampilkan booking yang diampu
        $sql = "SELECT $select_fields $join_tables WHERE b.booking_dosen_nidn = :nidn $order_by";
        $stmt = $db->prepare($sql);
        $stmt->execute([':nidn' => $nidn]);
    } else { // Role Mahasiswa
        // MAHASISWA: HANYA tampilkan booking miliknya
        $sql = "SELECT $select_fields $join_tables WHERE b.mahasiswa_nim = :nim $order_by";
        $stmt = $db->prepare($sql);
        $stmt->execute([':nim' => $nim]);
    }

    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // 3. Output Format yang Benar
    echo json_encode([
        "status" => "success",
        "data" => $rows
    ]);
} catch (Exception $e) {
    // Setel HTTP response code ke 500 (Internal Server Error)
    http_response_code(500);
    echo json_encode([
        "status" => "error",
        "message" => "Server Error: " . $e->getMessage()
    ]);
}
