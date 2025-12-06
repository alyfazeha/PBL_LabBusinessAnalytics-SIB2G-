<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

header('Content-Type: application/json');
require_once __DIR__ . "/../../config/koneksi.php";
require_once __DIR__ . "/../../models/Content.php";

$slug = $_GET['slug'] ?? null;
$id   = $_GET['id'] ?? null;

$model = new Content();
$data = null;

if ($slug) {
    $data = $model->getBySlug($slug);
} elseif ($id) {
    $data = $model->getById($id);
}

if ($data) {
    echo json_encode(['status' => 'success', 'data' => $data]);
} else {
    http_response_code(404);
    echo json_encode(['status' => 'error', 'message' => 'Berita tidak ditemukan']);
}
?>