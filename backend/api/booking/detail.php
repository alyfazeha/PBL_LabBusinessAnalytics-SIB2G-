<?php
// backend/api/peminjaman/detail.php
ini_set('display_errors', 0);
header('Content-Type: application/json');

require_once __DIR__ . "/../../config/database.php";
require_once __DIR__ . "/../../config/auth.php";

// HAPUS require_admin(); agar mahasiswa bisa lihat
require_login_json(); // Ganti jadi require login biasa

if (!isset($_GET['id'])) {
    http_response_code(400);
    echo json_encode(["error" => "ID booking wajib diisi."]);
    exit;
}

try {
    $id = $_GET['id'];
    $conn = Database::getInstance();

    // Query Detail
    $sql = "SELECT b.*, s.nama_sarana, u.username as peminjam 
            FROM bookings b
            LEFT JOIN sarana s ON b.sarana_id = s.sarana_id
            LEFT JOIN users u ON b.created_by = u.user_id
            WHERE booking_id = :id";
            
    $stmt = $conn->prepare($sql);
    $stmt->execute([":id" => $id]);
    $data = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$data) {
        http_response_code(404);
        echo json_encode(["status" => "error", "message" => "Data tidak ditemukan."]);
        exit;
    }

    // PROTEKSI: Hanya Admin ATAU Pemilik Booking yang boleh lihat
    if ($_SESSION['role'] !== 'admin' && $_SESSION['user_id'] != $data['created_by']) {
        http_response_code(403);
        echo json_encode(["status" => "error", "message" => "Anda tidak berhak melihat data ini."]);
        exit;
    }

    echo json_encode($data); // Kirim data langsung (sesuai JS kamu)

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(["status" => "error", "message" => $e->getMessage()]);
}
?>