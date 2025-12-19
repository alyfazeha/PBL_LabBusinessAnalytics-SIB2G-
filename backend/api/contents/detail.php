<?php
ini_set('display_errors', 0);
error_reporting(E_ALL);

header('Content-Type: application/json');
header("Access-Control-Allow-Origin: *");

// PERBAIKAN: Gunakan database.php
require_once __DIR__ . "/../../config/database.php";
require_once __DIR__ . "/../../models/Content.php";

$slug = $_GET['slug'] ?? null;
$id   = $_GET['id'] ?? null;

try {
    $model = new Content();
    $data = null;

    if ($slug) {
        $data = $model->getBySlug($slug);
    } elseif ($id) {
        $data = $model->getById($id);
    }

    if ($data) {
        echo json_encode(['status' => 'success', 'data' => $data]);
    } else {
        http_response_code(404);
        echo json_encode(['status' => 'error', 'message' => 'Konten tidak ditemukan']);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>