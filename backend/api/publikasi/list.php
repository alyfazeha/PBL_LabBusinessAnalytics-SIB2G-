<?php

ini_set('display_errors', 0); // Matikan error HTML agar JSON valid
error_reporting(E_ALL);

header('Content-Type: application/json');
header("Access-Control-Allow-Origin: *"); // Tambahkan ini jaga-jaga masalah CORS

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

header('Content-Type: application/json');
header("Access-Control-Allow-Origin: *");

// --- PERBAIKAN DI SINI ---
// Ganti koneksi.php menjadi database.php
require_once __DIR__ . "/../../config/database.php"; 
require_once __DIR__ . "/../../models/Publikasi.php";
require_once __DIR__ . "/../../config/auth.php";

require_role(['admin', 'dosen']); // Aktifkan jika auth sudah jalan

try {
    $model = new Publikasi();
    $data = $model->getAll();

    if (!$data) $data = [];

    echo json_encode(['status' => 'success', 'data' => $data]);

} catch (Exception $e) {
    // Tangkap error jika model gagal
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>