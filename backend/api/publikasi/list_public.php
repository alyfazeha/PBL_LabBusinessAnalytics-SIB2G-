<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

header('Content-Type: application/json');

// Fix Path ../../
require_once __DIR__ . "/../../config/koneksi.php";
require_once __DIR__ . "/../../models/Publikasi.php";

$model = new Publikasi();

// Tangkap filter dari URL
$focus_id = $_GET['focus_id'] ?? null;

if ($focus_id) {
    $data = $model->getByFocusId($focus_id);
} else {
    $data = $model->getPublishedOnly(); 
}

if (!$data) $data = [];

echo json_encode(['status' => 'success', 'data' => $data]);
?>