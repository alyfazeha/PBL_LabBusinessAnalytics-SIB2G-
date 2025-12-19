<?php
// backend/api/publikasi/list_dosen.php

ini_set('display_errors', 0);
error_reporting(E_ALL);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

header('Content-Type: application/json');
header("Access-Control-Allow-Origin: *");

require_once __DIR__ . "/../../config/database.php";

try {
    // 1. Cek Login
    if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'dosen') {
        http_response_code(403);
        throw new Exception("Unauthorized. Akses khusus Dosen.");
    }

    $db = Database::getInstance();
    $userId = $_SESSION['user_id'];

    // 2. Ambil NIDN Dosen dari User ID
    $stmtUser = $db->prepare("SELECT nidn FROM dosen WHERE user_id = :uid");
    $stmtUser->execute([':uid' => $userId]);
    $dosen = $stmtUser->fetch(PDO::FETCH_ASSOC);

    if (!$dosen) {
        throw new Exception("NIDN tidak ditemukan.");
    }

    $nidn = $dosen['nidn'];

    // 3. Ambil Publikasi milik NIDN tersebut
    // Kita JOIN dengan kategori & research_focus agar tampil nama, bukan ID
    $sql = "SELECT p.*, 
                   k.nama_kategori, 
                   r.nama_fokus 
            FROM publikasi p
            LEFT JOIN kategori_publikasi k ON p.kategori_id = k.id
            LEFT JOIN research_focus r ON p.focus_id = r.focus_id
            WHERE p.dosen_nidn = :nidn
            ORDER BY p.created_at DESC";

    $stmt = $db->prepare($sql);
    $stmt->execute([':nidn' => $nidn]);
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(['status' => 'success', 'data' => $data]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>