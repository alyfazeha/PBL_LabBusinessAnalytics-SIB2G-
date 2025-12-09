<?php
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

$id = $_POST['publikasi_id'] ?? $_POST['id'] ?? null;
$data = [
    'judul'         => $_POST['judul'] ?? null,
    'external_link' => $_POST['external_link'] ?? null,
    'kategori_id'   => $_POST['kategori_id'] ?? null,
    'dosen_nidn'    => $_POST['dosen_nidn'] ?? null
];

if (!$id || !$data['judul']) {
    http_response_code(400);
    exit(json_encode(['status' => 'error', 'message' => 'ID dan Judul wajib diisi']));
}

$model = new Publikasi();
$update = $model->update($id, $data);

if ($update) {
    echo json_encode(['status' => 'success', 'message' => 'Data berhasil diupdate']);
} else {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Gagal update database']);
}
?>