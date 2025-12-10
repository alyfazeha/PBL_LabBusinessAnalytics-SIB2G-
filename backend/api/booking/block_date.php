<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

header('Content-Type: application/json');

require_once __DIR__ . "/../../config/database.php";
require_once __DIR__ . "/../../models/BlockedDate.php";
require_once __DIR__ . "/../../config/auth.php";

// --- SECURITY: HANYA ADMIN ---
require_admin();
// -----------------------------

try {
    // Gunakan Singleton
    $conn = Database::getInstance();
    $blocked = new BlockedDate($conn);
    $response = ['status' => 'success', 'message' => ''];

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $sarana_id  = $_POST['sarana_id'] ?? null;
        $start_date = $_POST['start_date'] ?? null;
        $end_date   = $_POST['end_date'] ?? null;
        $start_time = $_POST['start_time'] ?? null;
        $end_time   = $_POST['end_time'] ?? null;
        $reason     = $_POST['reason'] ?? null;
        $created_by = $_SESSION['user_id'];

        if (!$sarana_id || !$start_date || !$end_date || !$start_time || !$end_time || !$reason) {
            http_response_code(400);
            $response = ['status' => 'error', 'message' => 'Semua field wajib diisi.'];
        } else {
            $ok = $blocked->create($sarana_id, $start_date, $end_date, $start_time, $end_time, $reason, $created_by);
            if ($ok) {
                $response['message'] = "Tanggal berhasil diblokir.";
            } else {
                http_response_code(500);
                $response = ['status' => 'error', 'message' => "Gagal memblokir tanggal."];
            }
        }
    } elseif ($_SERVER['REQUEST_METHOD'] === 'GET') {
        $list = $blocked->getAll();
        $response['data'] = $list;
        $response['message'] = "Data blocked date berhasil diambil.";
    } else {
        http_response_code(405);
        $response = ['status' => 'error', 'message' => 'Method Not Allowed'];
    }

    echo json_encode($response);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>