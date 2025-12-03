<?php
require_once __DIR__ . "/../models/Publikasi.php";

$id = $_GET['publikasi_id'] ?? null;

if (!$id) {
    echo json_encode(['error' => 'publikasi_id required']);
    exit;
}

$model = new Publikasi();
$data = $model->find($id);

echo json_encode($data);