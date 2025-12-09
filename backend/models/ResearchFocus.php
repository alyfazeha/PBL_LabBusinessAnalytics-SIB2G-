<?php
// backend/api/research/list.php

if (session_status() === PHP_SESSION_NONE) { session_start(); }
header('Content-Type: application/json');
header("Access-Control-Allow-Origin: *");

require_once __DIR__ . "/../../config/database.php";
require_once __DIR__ . "/../../models/ResearchFocus.php"; // Pastikan model ini ada

try {
    $model = new ResearchFocus();
    $data = $model->getAll();
    
    if (!$data) $data = [];
    
    echo json_encode(['status' => 'success', 'data' => $data]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>