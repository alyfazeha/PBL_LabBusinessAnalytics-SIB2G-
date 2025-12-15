<?php
// backend/api/dosen/get_dashboard_summary.php

// 1. Mulai Output Buffering untuk mencegah PHP Warnings merusak JSON
ob_start();

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

header("Content-Type: application/json");
ini_set('display_errors', 1); 
error_reporting(E_ALL);

try {
    require_once __DIR__ . "/../../config/database.php";
    require_once __DIR__ . "/../../config/auth.php";

    if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'dosen') {
        http_response_code(401);
        throw new Exception("Sesi Anda telah berakhir. Silakan login sebagai Dosen."); 
    }

    $user_id = $_SESSION['user_id'];
    $db = Database::getInstance();
    $responseData = [
        'nama' => 'Dosen',
        'total_publikasi' => 0,
        'publikasi_pending' => 0,
        'total_peminjaman' => 0,
        'latest_publications' => []
    ];

    // --- A. Dapatkan Data Dosen (NIDN dan Nama) ---
    $queryDosen = "SELECT nidn, nama FROM dosen WHERE user_id = :uid LIMIT 1"; 
    $stmtDosen = $db->prepare($queryDosen);
    $stmtDosen->execute([':uid' => $user_id]);
    $dosen = $stmtDosen->fetch(PDO::FETCH_ASSOC);

    if (!$dosen) {
        http_response_code(404);
        throw new Exception("Data profil dosen tidak ditemukan.");
    }
    
    $dosen_nidn = $dosen['nidn']; 
    $responseData['nama'] = $dosen['nama'];

    // --- B. Hitung Total Publikasi ---
    $queryTotalPub = "SELECT COUNT(id) AS total FROM publikasi WHERE dosen_nidn = :nidn";
    $stmtTotalPub = $db->prepare($queryTotalPub);
    $stmtTotalPub->execute([':nidn' => $dosen_nidn]);
    $responseData['total_publikasi'] = $stmtTotalPub->fetch(PDO::FETCH_COLUMN);

    // --- C. Hitung Publikasi Pending ---
    $queryPendingPub = "SELECT COUNT(id) AS total FROM publikasi WHERE dosen_nidn = :nidn AND status = 'pending'";
    $stmtPendingPub = $db->prepare($queryPendingPub);
    $stmtPendingPub->execute([':nidn' => $dosen_nidn]);
    $responseData['publikasi_pending'] = $stmtPendingPub->fetch(PDO::FETCH_COLUMN);

    // --- D. Hitung Total Peminjaman ---
    // Menggunakan tabel 'bookings' dan kolom 'booking_dosen_nidn'
    $queryTotalLoan = "SELECT COUNT(booking_id) AS total FROM bookings WHERE booking_dosen_nidn = :nidn";
    $stmtTotalLoan = $db->prepare($queryTotalLoan);
    $stmtTotalLoan->execute([':nidn' => $dosen_nidn]);
    $responseData['total_peminjaman'] = $stmtTotalLoan->fetch(PDO::FETCH_COLUMN); 

    // --- E. Ambil 3 Publikasi Terbaru ---
    // Menggunakan TO_CHAR() untuk PostgreSQL dan created_at
    $queryLatestPub = "
        SELECT 
            p.id, 
            p.judul, 
            'Jurnal' AS jenis, 
            p.status, 
            TO_CHAR(p.created_at, 'DD Month YYYY') AS tanggal_format 
        FROM publikasi p
        WHERE p.dosen_nidn = :nidn 
        ORDER BY p.created_at DESC 
        LIMIT 3
    ";
    $stmtLatestPub = $db->prepare($queryLatestPub);
    $stmtLatestPub->execute([':nidn' => $dosen_nidn]);
    
    $latestPubs = $stmtLatestPub->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($latestPubs as &$pub) {
        $pub['tanggal'] = $pub['tanggal_format'];
        unset($pub['tanggal_format']);
    }

    $responseData['latest_publications'] = $latestPubs;

    // 3. Kirim Respon Sukses
    ob_clean(); // HAPUS SEMUA OUTPUT (termasuk warnings/notices)
    echo json_encode([
        'status' => 'success',
        'data' => $responseData
    ]);

} catch (Exception $e) {
    // 4. Kirim Respon Error
    ob_clean(); // HAPUS SEMUA OUTPUT SEBELUMNYA
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Internal Server Error: ' . $e->getMessage()]);
}
?>