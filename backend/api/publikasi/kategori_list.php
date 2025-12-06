<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

header('Content-Type: application/json');


require_once __DIR__ . "/../../config/koneksi.php";
require_once __DIR__ . "/../../models/PublikasiCategory.php"; 

$model = new PublikasiCategory(); 
$data = $model->getAll();

if (!$data) $data = [];

echo json_encode(['status' => 'success', 'data' => $data]);
?>