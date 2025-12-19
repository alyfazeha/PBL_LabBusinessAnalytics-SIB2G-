<?php
header('Content-Type: application/json');

require_once __DIR__ . "/../../config/database.php";
require_once __DIR__ . "/../../config/auth.php";

require_admin();
try {
    $db = Database::getInstance();

    // --- 1. LOGIKA REFRESH MATERIALIZED VIEW ---
    // Jika frontend mengirim param ?refresh=true
    if (isset($_GET['refresh']) && $_GET['refresh'] === 'true') {
        try {
            $db->exec("REFRESH MATERIALIZED VIEW vw_peminjaman_history");
        } catch (Exception $e) {
            // Abaikan error refresh jika database tidak support, lanjut ambil data
            // (Opsional: log error)
        }
    }

    // --- 2. QUERY DATA ---
    $sql = "SELECT * FROM vw_peminjaman_history WHERE 1=1";
    $params = [];

    // Filter berdasarkan Status (Diajukan, Disetujui, Ditolak)
    if (!empty($_GET['status'])) {
        $sql .= " AND status = :status";
        $params[':status'] = $_GET['status'];
    }

    // Search berdasarkan NIM Mahasiswa
    if (!empty($_GET['search'])) {
        $sql .= " AND mahasiswa_nim LIKE :search";
        $params[':search'] = "%" . $_GET['search'] . "%";
    }

    // Urutkan (Default dari view sudah desc, tapi kita pastikan lagi)
    $sql .= " ORDER BY tanggal DESC, created_at DESC";

    $stmt = $db->prepare($sql);
    $stmt->execute($params);
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'success' => true,
        'data' => $data
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>