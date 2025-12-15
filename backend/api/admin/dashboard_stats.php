<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");

require_once __DIR__ . "/../../config/database.php";
require_once __DIR__ . "/../../config/auth.php";

require_admin();

try {
    $db = Database::getInstance();
    $userId = $_SESSION['user_id'];

    // 1. AMBIL NAMA ADMIN
    $stmtUser = $db->prepare("SELECT display_name FROM users WHERE user_id = ?");
    $stmtUser->execute([$userId]);
    $user = $stmtUser->fetch(PDO::FETCH_ASSOC);
    $adminName = $user['display_name'] ?? 'Admin';

    // 2. HITUNG TOTAL PEMINJAMAN
    $stmtBooking = $db->query("SELECT COUNT(*) FROM bookings");
    $totalPeminjaman = $stmtBooking->fetchColumn();

    // 3. HITUNG TOTAL PUBLIKASI DOSEN
    try {
        $stmtPublikasi = $db->query("SELECT COUNT(*) FROM publikasi"); 
        $totalPublikasi = $stmtPublikasi->fetchColumn();
    } catch (Exception $e) {
        $totalPublikasi = 0; // Default jika tabel belum ada
    }

    // 4. HITUNG TOTAL BERITA & KONTEN
    try {
        $stmtKonten = $db->query("SELECT COUNT(*) FROM contents"); 
        $totalKonten = $stmtKonten->fetchColumn();
    } catch (Exception $e) {
        $totalKonten = 0; // Default jika tabel belum ada
    }

    echo json_encode([
        'success' => true,
        'data' => [
            'name' => $adminName,
            'peminjaman' => $totalPeminjaman,
            'publikasi' => $totalPublikasi,
            'konten' => $totalKonten
        ]
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>