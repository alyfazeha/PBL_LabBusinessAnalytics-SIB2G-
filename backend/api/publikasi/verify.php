<?php
require_once __DIR__ . "/../models/Publikasi.php";

$id = $_POST['publikasi_id'] ?? null;
$status = $_POST['status'] ?? null; // published / rejected
$user_id = $_POST['verified_by'] ?? null;

if (!$id || !$status || !$user_id) {
    echo json_encode(['error' => 'Missing required fields']);
    exit;
}

$model = new Publikasi();
$ok = $model->verify($id, $user_id, $status);

echo json_encode([
    'success' => $ok,
    'message' => $ok ? 'Publikasi updated' : 'Failed to update'
]);