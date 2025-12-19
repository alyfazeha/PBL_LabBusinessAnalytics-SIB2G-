<?php
// backend/api/dosen/me.php

// 1. Mulai Session & Konfigurasi
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

header("Content-Type: application/json");
ini_set('display_errors', 0);
error_reporting(E_ALL);

try {
    require_once __DIR__ . "/../../config/database.php";
    require_once __DIR__ . "/../../config/auth.php";

    // 2. Pastikan User Sudah Login
    if (!isset($_SESSION['user_id'])) {
        http_response_code(401);
        throw new Exception("Anda belum login.");
    }

    $user_id = $_SESSION['user_id'];

    // 3. Ambil Data Dosen Berdasarkan User ID Session
    $db = Database::getInstance();
    
    // Kita cari data di tabel dosen yang user_id-nya cocok dengan yang login
    $query = "SELECT * FROM dosen WHERE user_id = :uid LIMIT 1";
    $stmt = $db->prepare($query);
    $stmt->execute([':uid' => $user_id]);
    $dosen = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($dosen) {
        // Jika data ditemukan, kirim sebagai JSON
        echo json_encode([
            'status' => 'success',
            'data' => $dosen
        ]);
    } else {
        // User login, tapi datanya belum ada di tabel dosen (mungkin admin atau data belum lengkap)
        http_response_code(404);
        echo json_encode([
            'status' => 'error', 
            'message' => 'Profil dosen belum dilengkapi. Silakan hubungi Admin.'
        ]);
    }

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>