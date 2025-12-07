<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

header('Content-Type: application/json');
// UBAH JADI ../../
require_once __DIR__ . "/../../config/koneksi.php";
require_once __DIR__ . "/../../models/Content.php";

$model = new Content();
// Pastikan fungsi getPublishedOnly() sudah kamu buat di Models/Content.php
$data = $model->getPublishedOnly(); 

echo json_encode(['status' => 'success', 'data' => $data]);
?>
?>