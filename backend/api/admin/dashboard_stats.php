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

// --- [FITUR BARU: DATA UNTUK GRAFIK] ---

    // A. DATA TREN MINGGUAN (7 HARI TERAKHIR)
    $trendLabels = [];
    $trendDataMap = [];
    
    for ($i = 6; $i >= 0; $i--) {
        $date = date('Y-m-d', strtotime("-$i days")); // YYYY-MM-DD
        $label = date('d M', strtotime($date));         
        $trendLabels[] = $label;
        $trendDataMap[$date] = 0; // Default 0
    }

    // Query menghitung jumlah per tanggal
    // Mengambil data mulai dari 6 hari yang lalu
    $startDate = date('Y-m-d', strtotime("-6 days"));
    
    $sqlTrend = "SELECT tanggal, COUNT(*) as total 
                 FROM bookings 
                 WHERE tanggal >= :start_date 
                 GROUP BY tanggal";
    
    $stmtTrend = $db->prepare($sqlTrend);
    $stmtTrend->execute([':start_date' => $startDate]);
    $rowsTrend = $stmtTrend->fetchAll(PDO::FETCH_ASSOC);

    // Masukkan data DB ke map
    foreach ($rowsTrend as $row) {
        $dbDate = $row['tanggal']; // Pastikan format di DB YYYY-MM-DD
        if (isset($trendDataMap[$dbDate])) {
            $trendDataMap[$dbDate] = (int)$row['total'];
        }
    }
    // Ubah map menjadi array angka urut (index)
    $trendValues = array_values($trendDataMap);


    // B. DATA STATUS (PIE CHART)
    $sqlStatus = "SELECT status, COUNT(*) as total FROM bookings GROUP BY status";
    $stmtStatus = $db->query($sqlStatus);
    $rowsStatus = $stmtStatus->fetchAll(PDO::FETCH_ASSOC);

    $statusLabels = [];
    $statusValues = [];
    $statusColors = [];

    foreach ($rowsStatus as $row) {
        $st = ucfirst($row['status']); // Diajukan, Didisetujui, dll
        $statusLabels[] = $st;
        $statusValues[] = (int)$row['total'];

        // Warna Custom
        $stLower = strtolower($st);
        if (strpos($stLower, 'disetujui') !== false) {
            $statusColors[] = '#28a745'; // Hijau
        } elseif (strpos($stLower, 'ditolak') !== false) {
            $statusColors[] = '#dc3545'; // Merah
        } else {
            $statusColors[] = '#ffc107'; // Kuning (Diajukan/Pending)
        }
    }

    // --- OUTPUT JSON GABUNGAN ---
    echo json_encode([
        'success' => true,
        'admin_name' => $adminName,
        'total_peminjaman' => $totalPeminjaman,
        'total_publikasi' => $totalPublikasi,
        'total_konten' => $totalKonten,
        // Data Grafik Baru
        'chart_trend' => [
            'labels' => $trendLabels,
            'data'   => $trendValues
        ],
        'chart_status' => [
            'labels' => $statusLabels,
            'data'   => $statusValues,
            'colors' => $statusColors
        ]
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>