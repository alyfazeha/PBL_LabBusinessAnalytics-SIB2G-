<?php
// backend/api/booking/detail_booking.php
ini_set('display_errors', 0);
error_reporting(E_ALL);

if (session_status() === PHP_SESSION_NONE) { session_start(); }
header('Content-Type: application/json');
header("Access-Control-Allow-Origin: *");

require_once __DIR__ . "/../../config/database.php"; 

try {
    // 1. Cek Login
    if (!isset($_SESSION['user_id'])) {
        http_response_code(401);
        throw new Exception("Unauthorized. Silakan login.");
    }

    // 2. Ambil ID Booking dari URL
    $bookingId = $_GET['id'] ?? null;
    if (!$bookingId || !is_numeric($bookingId)) {
        http_response_code(400);
        throw new Exception("ID Peminjaman tidak valid.");
    }
    
    $db = Database::getInstance();
    $userId = $_SESSION['user_id'];
    $role = $_SESSION['role'] ?? 'guest';

    // 3. Persiapan Query Detail
    // Mengambil semua kolom bookings (b.*) + detail dari tabel JOIN
    $select_fields = "
        b.*, 
        s.nama_sarana, 
        d.nama AS nama_dosen_pj,
        m.nama AS nama_mahasiswa,
        m.no_hp,
        m.email
    ";

    // MENGGUNAKAN LEFT JOIN untuk memastikan data booking tetap terambil 
    // meskipun data dosen/mahasiswa/sarana ada yang kosong/null
    $join_tables = "
        FROM bookings b
        LEFT JOIN sarana s ON b.sarana_id = s.sarana_id
        LEFT JOIN dosen d ON b.booking_dosen_nidn = d.nidn
        LEFT JOIN mahasiswa m ON b.mahasiswa_nim = m.nim
    ";

    // 4. Tentukan Klausa WHERE (Filter berdasarkan ID dan Hak Akses)
    $where_clause = " WHERE b.booking_id = :id";
    
    // Safety check: Dosen hanya boleh melihat booking yang diampunya, Mahasiswa hanya booking miliknya.
    if ($role === 'dosen') {
        // Asumsi: Kita harus lookup NIDN dulu (jika belum ada di sesi)
        $stmtDosen = $db->prepare("SELECT nidn FROM dosen WHERE user_id = :uid");
        $stmtDosen->execute([':uid' => $userId]);
        $nidnDosen = $stmtDosen->fetchColumn();
        
        if (!$nidnDosen) {
            http_response_code(403);
            throw new Exception("NIDN tidak ditemukan untuk user ini.");
        }
        $where_clause .= " AND b.booking_dosen_nidn = :nidn_dosen";
        $params = [':id' => $bookingId, ':nidn_dosen' => $nidnDosen];

    } elseif ($role === 'mahasiswa') {
        // Asumsi: Kita harus lookup NIM (jika belum ada di sesi)
        $stmtMhs = $db->prepare("SELECT nim FROM mahasiswa WHERE user_id = :uid");
        $stmtMhs->execute([':uid' => $userId]);
        $nimMhs = $stmtMhs->fetchColumn();

        if (!$nimMhs) {
            http_response_code(403);
            throw new Exception("NIM tidak ditemukan untuk user ini.");
        }
        $where_clause .= " AND b.mahasiswa_nim = :nim_mahasiswa";
        $params = [':id' => $bookingId, ':nim_mahasiswa' => $nimMhs];

    } else {
        // Admin atau guest, hanya filter berdasarkan ID (tergantung kebijakan)
        $params = [':id' => $bookingId];
    }
    
    // 5. Eksekusi Query
    $sql = "SELECT {$select_fields} {$join_tables} {$where_clause} LIMIT 1";
    $stmt = $db->prepare($sql);
    $stmt->execute($params);
    $detailData = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$detailData) {
        http_response_code(404);
        throw new Exception("Detail peminjaman tidak ditemukan atau Anda tidak memiliki akses.");
    }

    // 6. Output Data
    echo json_encode([
        'status' => 'success',
        'data' => $detailData
    ]);

} catch (Exception $e) {
    // Pastikan status code 500 terkirim jika terjadi error server
    if (http_response_code() === 200) {
        http_response_code(500);
    }
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>