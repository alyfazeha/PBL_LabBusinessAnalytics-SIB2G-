<?php
header("Content-Type: application/json");
require_once __DIR__ . "/../models/Dosen.php";
require_once __DIR__ . "/../config/auth.php";
require_role(['admin', 'dosen']); 

$dosenModel = new Dosen();

$nidn = $_POST['nidn'] ?? null; // Menggunakan NIDN

if (!$nidn) {
    echo json_encode(['error' => 'NIDN required']);
    exit;
}

// Ambil dosen untuk mendapatkan user_id (untuk cek kepemilikan)
$currentDosen = $dosenModel->find($nidn);

if (!$currentDosen) {
    echo json_encode(['success' => false, 'message' => 'Dosen tidak ditemukan']);
    exit;
}

$success = $dosenModel->delete($nidn);

echo json_encode([
    'success' => $success,
    'message' => $success ? 'Dosen deleted' : 'Failed to delete dosen'
]);
?>