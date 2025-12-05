<?php
header('Content-Type: application/json');
require_once __DIR__ . "/../config/koneksi.php";
require_once __DIR__ . "/../models/ResearchFocus.php";

$model = new ResearchFocus();
$data = $model->getAll();

echo json_encode(['status' => 'success', 'data' => $data]);
?>