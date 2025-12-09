<?php
// 1. Cek Session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

header('Content-Type: application/json');

require_once __DIR__ . "/../../config/koneksi.php";
require_once __DIR__ . "/../../models/Publikasi.php";
require_once __DIR__ . "/../../config/auth.php";

require_role(['admin', 'dosen']);

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit(json_encode(['status' => 'error', 'message' => 'Method Not Allowed']));
}

$data = [
    'judul'         => $_POST['judul'] ?? null,
    'external_link' => $_POST['external_link'] ?? null,
    'kategori_id'   => $_POST['kategori_id'] ?? null,
    'dosen_nidn'    => $_POST['dosen_nidn'] ?? null
];

if (!$data['judul'] || !$data['kategori_id'] || !$data['dosen_nidn']) {
    http_response_code(400);
    exit(json_encode(['status' => 'error', 'message' => 'Data tidak lengkap (Judul, Kategori, NIDN wajib)']));
}

$model = new Publikasi();
$id = $model->create($data);

if ($id) {
    http_response_code(201);
    echo json_encode(['status' => 'success', 'message' => 'Berhasil disimpan', 'data' => ['id' => $id]]);
} else {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Gagal menyimpan data']);
}
?>