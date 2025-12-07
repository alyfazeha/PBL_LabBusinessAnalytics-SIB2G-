<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

header('Content-Type: application/json');

require_once __DIR__ . "/../../config/koneksi.php";
require_once __DIR__ . "/../../models/Publikasi.php";
require_once __DIR__ . "/../../config/auth.php";

require_role(['admin', 'dosen']);

$model = new Publikasi();
$data = $model->getAll();

// Jika kosong kirim array kosong biar frontend tidak error
if (!$data) $data = [];

echo json_encode(['status' => 'success', 'data' => $data]);
?>