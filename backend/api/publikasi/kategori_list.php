<?php
// File: publikasi/kategori_list.php
header('Content-Type: application/json');
require_once __DIR__ . "/../config/koneksi.php";

// PANGGIL FILE YANG BENAR
require_once __DIR__ . "/../models/PublikasiCategory.php"; 

// PANGGIL CLASS YANG BENAR
$model = new PublikasiCategory(); 
$data = $model->getAll();

echo json_encode(['status' => 'success', 'data' => $data]);
?>