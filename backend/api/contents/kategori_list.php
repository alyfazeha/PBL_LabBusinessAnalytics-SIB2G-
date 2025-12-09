<?php
// Matikan error text agar tidak merusak JSON
ini_set('display_errors', 0);
error_reporting(E_ALL);

header('Content-Type: application/json');
header("Access-Control-Allow-Origin: *");

// PERBAIKAN: Arahkan ke database.php dan model yang benar
require_once __DIR__ . "/../../config/database.php";
require_once __DIR__ . "/../../models/ContentCategory.php";

try {
    $model = new ContentCategory();
    $data = $model->getAll();

    // Jika data kosong, kirim array kosong []
    if (!$data) $data = [];

    // Kirim data ke frontend
    echo json_encode(['status' => 'success', 'data' => $data]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>