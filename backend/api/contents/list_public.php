<?php
header('Content-Type: application/json');
require_once __DIR__ . "/../config/koneksi.php";
require_once __DIR__ . "/../models/Content.php";

$model = new Content();

// JANGAN pakai getAll(), pakai getPublishedOnly()
// Pastikan kamu sudah buat fungsi ini di models/Content.php (mirip di Publikasi)
$data = $model->getPublishedOnly();

echo json_encode(['status' => 'success', 'data' => $data]);
?>