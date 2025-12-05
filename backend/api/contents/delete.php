<?php
header('Content-Type: application/json');
require_once __DIR__ . "/../config/koneksi.php";
require_once __DIR__ . "/../models/Content.php";
require_once __DIR__ . "/../config/auth.php";

require_role(['admin']);

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit(json_encode(['status' => 'error', 'message' => 'Method Not Allowed']));
}

$id = $_POST['content_id'] ?? null;

if (!$id) {
    http_response_code(400);
    exit(json_encode(['status' => 'error', 'message' => 'ID Required']));
}

$model = new Content();
$delete = $model->delete($id);

if ($delete) {
    echo json_encode(['status' => 'success', 'message' => 'Berita berhasil dihapus']);
} else {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Gagal menghapus']);
}
?>