<?php
header('Content-Type: application/json');
require_once __DIR__ . "/../config/koneksi.php";
require_once __DIR__ . "/../models/Content.php";
require_once __DIR__ . "/../config/auth.php";

// Proteksi: Hanya Admin
require_role(['admin']);

$model = new Content();
$data = $model->getAll();

echo json_encode(['status' => 'success', 'data' => $data]);
?>