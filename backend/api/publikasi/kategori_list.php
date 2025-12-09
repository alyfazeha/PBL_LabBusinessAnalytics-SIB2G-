<?php

ini_set('display_errors', 0); // Matikan error HTML agar JSON valid
error_reporting(E_ALL);

header('Content-Type: application/json');
header("Access-Control-Allow-Origin: *"); // Tambahkan ini jaga-jaga masalah CORS

require_once __DIR__ . '/../config/database.php';

class PublikasiCategory {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    public function getAll() {
        // Asumsi nama tabel adalah 'publikasi_categories' atau sesuaikan
        $query = "SELECT * FROM publikasi_categories ORDER BY id ASC";
        $stmt = $this->db->query($query);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?><?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
header('Content-Type: application/json');
header("Access-Control-Allow-Origin: *");

// PERBAIKAN: Panggil database.php
require_once __DIR__ . "/../../config/database.php";
require_once __DIR__ . "/../../models/PublikasiCategory.php"; 

try {
    $model = new PublikasiCategory(); 
    $data = $model->getAll();
    if (!$data) $data = [];
    echo json_encode(['status' => 'success', 'data' => $data]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>