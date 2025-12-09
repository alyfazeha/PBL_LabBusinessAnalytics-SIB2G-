<?php
ini_set('display_errors', 0);
error_reporting(E_ALL);

header('Content-Type: application/json');
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");

// Panggil database.php (bukan koneksi.php)
require_once __DIR__ . "/../../config/database.php"; 
require_once __DIR__ . "/../../models/Content.php";
require_once __DIR__ . "/../../config/auth.php";

// require_role(['admin']); // Aktifkan jika sudah pakai login

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit(json_encode(['status' => 'error', 'message' => 'Method Not Allowed']));
}

$id = $_POST['content_id'] ?? null;

if (!$id) {
    http_response_code(400);
    exit(json_encode(['status' => 'error', 'message' => 'ID Content Required']));
}

try {
    $model = new Content();
    // Hapus data
    $delete = $model->delete($id);

    if ($delete) {
        echo json_encode(['status' => 'success', 'message' => 'Konten berhasil dihapus']);
    } else {
        throw new Exception("Gagal menghapus data dari database");
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>