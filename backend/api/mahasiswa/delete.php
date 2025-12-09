<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

header("Content-Type: application/json");

require_once __DIR__ . "/../../models/Mahasiswa.php";
require_once __DIR__ . "/../../config/auth.php";

require_role2(['admin', 'mahasiswa']);

$mahasiswaModel = new Mahasiswa();

$nim = $_POST['nim'] ?? null; // Menggunakan NIM

if (!$nim) {
    echo json_encode(['error' => 'NIM required']);
    exit;
}

$success = $mahasiswaModel->delete($nim); // Menggunakan Mahasiswa::delete($nim)

echo json_encode([
    'success' => $success,
    'message' => $success ? 'Mahasiswa deleted' : 'Failed to delete mahasiswa'
]);
?>