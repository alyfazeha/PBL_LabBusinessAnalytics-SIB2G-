<?php
require_once __DIR__ . "/../models/Publikasi.php";
require_once __DIR__ . "/../config/auth.php";
require_role(['admin', 'dosen']);

$id = $_POST['publikasi_id'] ?? null;

if (!$id) {
    echo json_encode(['error' => 'publikasi_id required']);
    exit;
}

$model = new Publikasi();
$ok = $model->delete($id);

echo json_encode([
    'success' => $ok
]);