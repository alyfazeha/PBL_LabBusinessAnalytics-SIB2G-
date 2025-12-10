<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

header('Content-Type: application/json');

require_once __DIR__ . "/../../config/database.php";
require_once __DIR__ . "/../../config/auth.php";
require_login_json();

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(["error" => "Unauthorized access."]);
    exit;
}

$role    = $_SESSION['role'];
$nidn    = $_SESSION['nidn'] ?? null;
$nim     = $_SESSION['nim'] ?? null;

// Mengambil koneksi PDO dari Singleton
$conn = Database::getInstance();

/* ==========================================================\r\n   QUERY SESUAI ROLE\r\n========================================================== */

$sql = "
    SELECT b.*, s.nama_sarana, u.username AS peminjam, u.role AS role_peminjam
    FROM bookings b
    LEFT JOIN sarana s ON b.sarana_id = s.sarana_id
    LEFT JOIN users u ON b.created_by = u.user_id
";

$where = "WHERE 1=1";
$params = [];

if ($role === 'dosen') {
    $where .= " AND b.booking_dosen_nidn = :nidn";
    $params[':nidn'] = $nidn;

} elseif ($role === 'mahasiswa') {
    // Jika user adalah mahasiswa, hanya tampilkan booking yang dia buat sendiri
    $where .= " AND b.mahasiswa_nim = :nim";
    $params[':nim'] = $nim;
}

$sql .= $where . " ORDER BY b.tanggal DESC, b.start_time";

$stmt = $conn->prepare($sql);

try {
    $stmt->execute($params);
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode(['status' => 'success', 'data' => $data]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["status" => "error", "message" => "Gagal menjalankan query list booking.", "db_error" => $e->getMessage()]);
}
?>