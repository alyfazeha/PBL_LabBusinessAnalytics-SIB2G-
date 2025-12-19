<?php
// backend/api/dosen/list_public.php

// Matikan error HTML agar JSON bersih
ini_set('display_errors', 0);
error_reporting(E_ALL);

header('Content-Type: application/json');
header("Access-Control-Allow-Origin: *"); // Penting agar bisa diakses dari folder public

require_once __DIR__ . "/../../config/database.php";
require_once __DIR__ . "/../../models/Dosen.php";

try {
    // 1. Panggil Model
    $dosenModel = new Dosen();
    
    // 2. Ambil semua data (Fungsi all() sudah ada di Dosen.php kamu)
    $data = $dosenModel->all();

    // 3. Kirim respon JSON
    // Kita bungkus dengan 'success' => true agar cocok dengan logic di index.html
    echo json_encode([
        'success' => true, 
        'data' => $data
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false, 
        'message' => $e->getMessage()
    ]);
}
?>