<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

header("Content-Type: application/json");
ini_set('display_errors', 0);
error_reporting(E_ALL);

try {
    require_once __DIR__ . "/../../models/Dosen.php";
    require_once __DIR__ . "/../../config/auth.php";

    if (function_exists('require_role')) {
        require_role(['admin', 'dosen']);
    }

    $dosenModel = new Dosen();
    $nidn = $_POST['nidn'] ?? null;

    if (!$nidn) {
        throw new Exception('NIDN required for deletion.');
    }

    // Cek apakah dosen ada
    $currentDosen = $dosenModel->find($nidn);
    if (!$currentDosen) {
        throw new Exception('Dosen tidak ditemukan.');
    }

    $success = $dosenModel->delete($nidn);

    if ($success) {
        echo json_encode(['success' => true, 'message' => 'Dosen deleted successfully']);
    } else {
        throw new Exception('Failed to delete dosen.');
    }

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>