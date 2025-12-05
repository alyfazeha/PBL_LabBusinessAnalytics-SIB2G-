<?php
header('Content-Type: application/json');
require_once __DIR__ . "/../config/koneksi.php";
require_once __DIR__ . "/../models/ContentCategory.php";

// Tidak perlu require_role (Public)

$model = new ContentCategory();
$data = $model->getAll();

echo json_encode(['status' => 'success', 'data' => $data]);
?>