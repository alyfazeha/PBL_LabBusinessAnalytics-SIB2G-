<?php
// 1. Mulai Session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

header("Content-Type: application/json");
ini_set('display_errors', 0);
error_reporting(E_ALL);

try {
    // 2. FIX PATH (Mundur 2 langkah: ../../)
    $path_model = __DIR__ . "/../../models/Dosen.php";
    $path_auth  = __DIR__ . "/../../config/auth.php";

    // Cek file ada atau tidak (Debugging)
    if (!file_exists($path_model)) throw new Exception("File Dosen.php tidak ditemukan di: $path_model");
    if (!file_exists($path_auth)) throw new Exception("File auth.php tidak ditemukan di: $path_auth");

    require_once $path_model;
    require_once $path_auth;

    // 3. Cek Role
    if (function_exists('require_role')) {
        require_role(['admin', 'dosen']);
    }

    // 4. Ambil NIDN dari URL
    $nidn = $_GET['nidn'] ?? null;

    if (!$nidn) {
        throw new Exception('Parameter NIDN tidak ditemukan di URL.');
    }

    // 5. Cari Data
    $dosenModel = new Dosen();
    $dosen = $dosenModel->find($nidn);

    if ($dosen) {
        echo json_encode($dosen);
    } else {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Data Dosen tidak ditemukan di database.']);
    }

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Server Error: ' . $e->getMessage()]);
}
?>