<?php
// backend/api/publikasi/list_public.php

ini_set('display_errors', 0); // Matikan error HTML agar JSON valid
error_reporting(E_ALL);

header('Content-Type: application/json');
header("Access-Control-Allow-Origin: *");

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Fix Path ../../
require_once __DIR__ . "/../../config/database.php"; // Ganti koneksi.php jadi database.php
require_once __DIR__ . "/../../models/Publikasi.php";

try {
    $model = new Publikasi();

    // Ambil data yang sudah dipublikasikan saja
    $data = $model->getPublishedOnly();

    if (!$data) $data = [];

    // Pastikan output formatnya sesuai yang diharapkan JavaScript
    echo json_encode(['status' => 'success', 'data' => $data]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => 'Gagal mengambil data publikasi: ' . $e->getMessage()
    ]);
}
