<?php
// backend/api/booking/list_dosen.php

ini_set('display_errors', 0);
error_reporting(E_ALL); 

if (session_status() === PHP_SESSION_NONE) { session_start(); }
header('Content-Type: application/json');
header("Access-Control-Allow-Origin: *");

require_once __DIR__ . "/../../config/database.php"; 

try {
    // 1. Cek Login & Role
    if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'dosen') {
        http_response_code(403);
        throw new Exception("Akses ditolak. Silakan login sebagai Dosen.");
    }
    
    $db = Database::getInstance();
    $userId = $_SESSION['user_id'];
    
    // 2. Ambil NIDN Dosen yang sedang Login
    $stmtDosen = $db->prepare("SELECT nidn FROM dosen WHERE user_id = :uid");
    $stmtDosen->execute([':uid' => $userId]);
    $nidnDosen = $stmtDosen->fetchColumn();

    if (!$nidnDosen) {
        throw new Exception("Data NIDN Dosen tidak ditemukan. Periksa tabel dosen.");
    }

    // 3. Ambil Data Peminjaman (Query Khusus Dosen Bimbingan)
    $select_fields = "
        b.booking_id AS id, 
        b.tanggal AS tanggal_peminjaman,  
        b.keperluan,
        b.status,
        s.nama_sarana AS aset_dipinjam, 
        m.nim AS nim_mahasiswa,
        m.nama AS nama_mahasiswa
    ";

    $join_tables = "
        FROM bookings b
        LEFT JOIN sarana s ON b.sarana_id = s.sarana_id
        LEFT JOIN mahasiswa m ON b.mahasiswa_nim = m.nim
    ";
    
    // 4. LOGIKA PENCARIAN (Dual Query Logic)
    
    // Ambil keyword pencarian (trim() untuk membersihkan spasi)
    $searchQuery = trim($_GET['search'] ?? ''); 
    
    // Filter Wajib (NIDN Dosen)
    $where_base = " WHERE b.booking_dosen_nidn = :nidn_dosen";
    $params = [':nidn_dosen' => $nidnDosen];
    
    // Cek apakah ada keyword pencarian
    if (!empty($searchQuery)) {
        // --- QUERY DENGAN PENCARIAN ---
        
        $searchTerm = "%{$searchQuery}%"; 
        
        // Tambahkan filter NIM/Nama ke klausa WHERE
        // Menggunakan ILIKE untuk PostgreSQL atau LIKE untuk kompatibilitas MySQL
        $where_search = " AND (m.nim ILIKE :search_term OR m.nama ILIKE :search_term)";
        
        // Tambahkan parameter pencarian ke array params
        $params[':search_term'] = $searchTerm;
        
        // Gabungkan SQL untuk pencarian
        $sql = "
            SELECT {$select_fields}
            {$join_tables}
            {$where_base} {$where_search}
            ORDER BY b.tanggal DESC, b.booking_id DESC
        ";
    } else {
        // --- QUERY LOAD NORMAL (TANPA PENCARIAN) ---
        
        // Gunakan where_base saja
        $sql = "
            SELECT {$select_fields}
            {$join_tables}
            {$where_base}
            ORDER BY b.tanggal DESC, b.booking_id DESC
        ";
    }
    
    // 5. Eksekusi Query
    $stmtPeminjaman = $db->prepare($sql);
    $stmtPeminjaman->execute($params); 
    $peminjamanList = $stmtPeminjaman->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'status' => 'success',
        'data' => $peminjamanList
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>