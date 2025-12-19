<?php
// backend/api/publikasi/detail.php

// Matikan error HTML agar JSON tetap bersih
ini_set('display_errors', 0);
error_reporting(E_ALL);

if (session_status() === PHP_SESSION_NONE) { session_start(); }
header('Content-Type: application/json');
header("Access-Control-Allow-Origin: *");

// 1. Panggil Database & Model Publikasi
require_once __DIR__ . "/../../config/database.php";
require_once __DIR__ . "/../../models/Publikasi.php";

// 2. Tangkap ID dari URL (contoh: detail.php?id=12)
$id = $_GET['id'] ?? null;

if (!$id) {
    http_response_code(400);
    exit(json_encode(['status' => 'error', 'message' => 'ID Publikasi diperlukan']));
}

try {
    $model = new Publikasi();
    
    // 3. Panggil fungsi getById (Pastikan fungsi ini ada di Model Publikasi.php)
    $data = $model->getById($id);

    if ($data) {
        echo json_encode(['status' => 'success', 'data' => $data]);
    } else {
        http_response_code(404);
        echo json_encode(['status' => 'error', 'message' => 'Data publikasi tidak ditemukan']);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Server Error: ' . $e->getMessage()]);
}
?>