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
    $nidn = $_GET['nidn'] ?? null;

    if (!$nidn) {
        throw new Exception('NIDN parameter is required.');
    }

    $dosen = $dosenModel->find($nidn);

    if (!$dosen) {
        // Kirim null atau error 404 jika tidak ketemu
        http_response_code(404);
        throw new Exception('Dosen not found.');
    }

    echo json_encode($dosen);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => true, 'message' => $e->getMessage()]);
}
?>