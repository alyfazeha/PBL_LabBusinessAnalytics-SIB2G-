<?php
// backend/api/dosen/get_dashboard_summary.php

ob_start();

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

header("Content-Type: application/json");

ini_set('display_errors', 0); 
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

    // --- A. Data Dosen ---
    $queryDosen = "SELECT nidn, nama FROM dosen WHERE user_id = :uid LIMIT 1"; 
    $stmtDosen = $db->prepare($queryDosen);
    $stmtDosen->execute([':uid' => $user_id]);
    $dosen = $stmtDosen->fetch(PDO::FETCH_ASSOC);

    if (!$dosen) {
        throw new Exception("Data profil dosen tidak ditemukan.");
    }
    
    $dosen_nidn = $dosen['nidn']; 
    $responseData['nama'] = $dosen['nama'];

    // --- B. Total Publikasi ---
    $queryTotalPub = "SELECT COUNT(id) AS total FROM publikasi WHERE dosen_nidn = :nidn";
    $stmtTotalPub = $db->prepare($queryTotalPub);
    $stmtTotalPub->execute([':nidn' => $dosen_nidn]);
    $responseData['total_publikasi'] = $stmtTotalPub->fetch(PDO::FETCH_COLUMN);

    // --- C. Publikasi Pending ---
    $queryPendingPub = "SELECT COUNT(id) AS total FROM publikasi WHERE dosen_nidn = :nidn AND status = 'pending'";
    $stmtPendingPub = $db->prepare($queryPendingPub);
    $stmtPendingPub->execute([':nidn' => $dosen_nidn]);
    $responseData['publikasi_pending'] = $stmtPendingPub->fetch(PDO::FETCH_COLUMN);

    // --- D. Total Peminjaman ---
    $queryTotalLoan = "SELECT COUNT(booking_id) AS total FROM bookings WHERE booking_dosen_nidn = :nidn";
    $stmtTotalLoan = $db->prepare($queryTotalLoan);
    $stmtTotalLoan->execute([':nidn' => $dosen_nidn]);
    $responseData['total_peminjaman'] = $stmtTotalLoan->fetch(PDO::FETCH_COLUMN); 

    // --- E. Publikasi Terbaru (SOLUSI FINAL POSTGRESQL) --- 
    $queryLatestPub = "
        SELECT 
            p.id, 
            p.judul, 
            p.tahun,
            p.status,
            p.created_at, -- Ambil mentah, kita format di PHP
            COALESCE(k.nama_kategori, '-') AS nama_kategori_text, 
            COALESCE(f.nama_fokus, '-') AS nama_fokus_text
        FROM publikasi p
        LEFT JOIN kategori_publikasi k ON p.kategori_id = k.id 
        LEFT JOIN research_focus f ON p.focus_id = f.focus_id 
        WHERE p.dosen_nidn = :nidn 
        ORDER BY p.created_at DESC 
        LIMIT 3
    ";
    
    $stmtLatestPub = $db->prepare($queryLatestPub);
    $stmtLatestPub->execute([':nidn' => $dosen_nidn]);
    $latestPubs = $stmtLatestPub->fetchAll(PDO::FETCH_ASSOC);
    
    // FORMAT TANGGAL VIA PHP (Anti Error Database)
    foreach ($latestPubs as &$pub) {
        if (!empty($pub['created_at'])) {
            $pub['tanggal'] = date('d F Y', strtotime($pub['created_at']));
        } else {
            $pub['tanggal'] = '-';
        }

        $pub['kategori'] = $pub['nama_kategori_text'];
        $pub['fokus_riset'] = $pub['nama_fokus_text'];
        $pub['tahun'] = $pub['tahun'] ?? '-';
        
        unset($pub['created_at']);
        unset($pub['nama_kategori_text']);
        unset($pub['nama_fokus_text']);
    }

    $responseData['latest_publications'] = $latestPubs;

    ob_clean(); 
    echo json_encode([
        'status' => 'success',
        'data' => $responseData
    ]);

} catch (Exception $e) {
    ob_clean();
    http_response_code(500);
    echo json_encode([
        'status' => 'error', 
        'message' => 'Server Error: ' . $e->getMessage()
    ]);
}
?>