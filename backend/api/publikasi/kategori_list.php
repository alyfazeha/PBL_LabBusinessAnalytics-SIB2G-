<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
header('Content-Type: application/json');
header("Access-Control-Allow-Origin: *");

// Panggil Model yang baru kita perbaiki di atas
require_once __DIR__ . "/../../config/database.php";
require_once __DIR__ . "/../../models/PublikasiCategory.php"; 

try {
    $model = new PublikasiCategory(); 
    $data = $model->getAll(); // Memanggil fungsi dari Model
    
    if (!$data) $data = [];
    
    echo json_encode(['status' => 'success', 'data' => $data]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>