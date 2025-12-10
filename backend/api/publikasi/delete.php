<?php
ini_set('display_errors', 0);
error_reporting(E_ALL);

if (session_status() === PHP_SESSION_NONE) { session_start(); }
header('Content-Type: application/json');
header("Access-Control-Allow-Origin: *");

require_once __DIR__ . "/../../config/database.php";
require_once __DIR__ . "/../../models/Publikasi.php";
require_once __DIR__ . "/../../config/auth.php";

require_role(['admin']); // Aktifkan jika perlu

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit(json_encode(['status' => 'error', 'message' => 'Method Not Allowed']));
}

$id = $_POST['publikasi_id'] ?? $_POST['id'] ?? null;

if (!$id) {
    http_response_code(400);
    exit(json_encode(['status' => 'error', 'message' => 'ID Publikasi wajib dikirim']));
}

try {
    $model = new Publikasi();
    $hapus = $model->delete($id);

    if ($hapus) {
        echo json_encode(['status' => 'success', 'message' => 'Publikasi berhasil dihapus']);
    } else {
        throw new Exception("Gagal menghapus (ID tidak ditemukan)");
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>