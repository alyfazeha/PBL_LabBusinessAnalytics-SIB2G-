<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

header('Content-Type: application/json');

require_once __DIR__ . "/../../config/database.php";
require_once __DIR__ . "/../../config/auth.php";
require_admin(); // Cek role admin di sini

if (!isset($_GET['id'])) {
    http_response_code(400);
    echo json_encode(["error" => "ID booking wajib diisi."]);
    exit;
}

$id = $_GET['id'];
$conn = Database::getInstance(); // Mengambil koneksi PDO

$sql = "SELECT * FROM vw_peminjaman_history WHERE booking_id = :id"; 
$stmt = $conn->prepare($sql);

try {
    $stmt->execute([":id" => $id]);
    $data = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$data) {
        http_response_code(404);
        echo json_encode(["status" => "error", "message" => "Data booking tidak ditemukan."]);
        exit;
    }

    if ($_SESSION['role'] !== 'admin') {
        http_response_code(403);
        echo json_encode(["error" => "Akses dilarang."]);
        exit;
    }

    echo json_encode(['status' => 'success', 'data' => $data]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["status" => "error", "message" => "Gagal mengambil data detail.", "db_error" => $e->getMessage()]);
}
?>