<?php
// 1. Matikan error text HTML paling atas
ini_set('display_errors', 0);
error_reporting(E_ALL);

header("Content-Type: application/json");

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

try {
    require_once __DIR__ . "/../../models/Mahasiswa.php";
    require_once __DIR__ . "/../../config/auth.php";

    if (function_exists('require_role2')) {
        require_role2(['admin', 'mahasiswa']);
    }

    $mahasiswaModel = new Mahasiswa();
    $nim = $_GET['nim'] ?? null;

    if (!$nim) {
        throw new Exception('NIM required');
    }

    $mahasiswa = $mahasiswaModel->find($nim);

    if ($mahasiswa) {
        echo json_encode($mahasiswa);
    } else {
        echo json_encode(['error' => 'Data tidak ditemukan']);
    }

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
?>