<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

header('Content-Type: application/json');
ini_set('display_errors', 0);
error_reporting(E_ALL);

try {
    $path_auth = __DIR__ . "/../../config/auth.php";   
    $path_model = __DIR__ . "/../../models/Dosen.php"; 

    // Cek lagi apakah file benar-benar ada di jalur baru ini
    if (!file_exists($path_auth)) {
        throw new Exception("File auth.php tidak ditemukan di jalur: " . $path_auth);
    }
    if (!file_exists($path_model)) {
        throw new Exception("File Dosen.php tidak ditemukan di jalur: " . $path_model);
    }

    require_once $path_auth;
    require_once $path_model;

    // Cek Role
    if (function_exists('require_role')) {
        require_role(['admin', 'dosen', 'mahasiswa']);
    } else {
        if (!isset($_SESSION['role']) || !in_array($_SESSION['role'], ['admin', 'dosen'])) {
            http_response_code(403);
            throw new Exception("Akses ditolak. Silakan login sebagai Admin.");
        }
    }

    // Ambil Data
    $dosenModel = new Dosen();
    $dosen = $dosenModel->all();

    if (!$dosen) $dosen = [];

    echo json_encode($dosen);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        "error" => true,
        "message" => $e->getMessage()
    ]);
}
?>