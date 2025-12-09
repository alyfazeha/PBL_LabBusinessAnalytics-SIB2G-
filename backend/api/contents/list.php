<?php
ini_set('display_errors', 0); // Matikan error HTML agar JSON valid
error_reporting(E_ALL);

header('Content-Type: application/json');
header("Access-Control-Allow-Origin: *"); // Tambahkan ini jaga-jaga masalah CORS

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

try {
    // --- PERBAIKAN DI SINI ---
    // Ganti 'koneksi.php' menjadi 'database.php'
    require_once __DIR__ . "/../../config/database.php"; 
    require_once __DIR__ . "/../../models/Content.php";
    require_once __DIR__ . "/../../config/auth.php"; // Jika butuh auth
    // -------------------------

    // Cek Role (Jika perlu admin)
    // if (function_exists('require_role')) {
    //    require_role(['admin']);
    // }

    $model = new Content();
    $data = $model->getAll();

    echo json_encode(['status' => 'success', 'data' => $data]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>