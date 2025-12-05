<?php
header('Content-Type: application/json');
require_once __DIR__ . "/../config/koneksi.php";
require_once __DIR__ . "/../models/Publikasi.php";

$id = $_GET['id'] ?? null;

if (!$id) {
    http_response_code(400);
    exit(json_encode(['status' => 'error', 'message' => 'ID required']));
}

$model = new Publikasi();
$data = $model->getById($id);

if ($data) {
    echo json_encode(['status' => 'success', 'data' => $data]);
} else {
    http_response_code(404);
    echo json_encode(['status' => 'error', 'message' => 'Data tidak ditemukan']);
}
?>