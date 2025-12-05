<?php
header('Content-Type: application/json');
require_once __DIR__ . "/../config/koneksi.php";
require_once __DIR__ . "/../models/Publikasi.php";
require_once __DIR__ . "/../config/auth.php";

require_role(['admin', 'dosen']);

$model = new Publikasi();
$data = $model->getAll();

echo json_encode(['status' => 'success', 'data' => $data]);
?>