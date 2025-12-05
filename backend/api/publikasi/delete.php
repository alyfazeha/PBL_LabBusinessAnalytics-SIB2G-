<?php
header('Content-Type: application/json');
require_once __DIR__ . "/../config/koneksi.php";
require_once __DIR__ . "/../models/Publikasi.php";
require_once __DIR__ . "/../config/auth.php";

// Hanya ADMIN yang boleh hapus
require_role(['admin']);

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit(json_encode(['status' => 'error', 'message' => 'Method Not Allowed']));
}

$id = $_POST['publikasi_id'] ?? null;

if (!$id) {
    http_response_code(400);
    exit(json_encode(['status' => 'error', 'message' => 'publikasi_id required']));
}

$model = new Publikasi();
$hapus = $model->delete($id);

if ($hapus) {
    echo json_encode(['status' => 'success', 'message' => 'Data berhasil dihapus']);
} else {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Gagal menghapus (ID tidak ditemukan)']);
}
?>