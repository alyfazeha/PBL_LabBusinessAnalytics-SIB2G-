<?php
header('Content-Type: application/json');
require_once __DIR__ . "/../config/koneksi.php";
require_once __DIR__ . "/../models/Publikasi.php";

$model = new Publikasi();

// Tangkap filter dari URL
$focus_id = $_GET['focus_id'] ?? null;

if ($focus_id) {
    // Kalau user pilih topik tertentu, panggil fungsi filter tadi
    $data = $model->getByFocusId($focus_id);
} else {
    // Kalau tidak pilih topik (default), panggil semua data published
    $data = $model->getPublishedOnly(); 
}

echo json_encode(['status' => 'success', 'data' => $data]);
?>