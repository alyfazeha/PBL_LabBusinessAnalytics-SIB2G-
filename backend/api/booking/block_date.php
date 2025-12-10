<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }

require_once __DIR__ . "/../../config/database.php";
require_once __DIR__ . "/../../models/BlockedDate.php";
require_once __DIR__ . "/../../config/auth.php";

require_admin();

// PERBAIKAN KONEKSI
$conn = Database::getInstance();
$blocked = new BlockedDate($conn);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $blocked->create(
        $_POST['sarana_id'],
        $_POST['start_date'],
        $_POST['end_date'],
        $_POST['start_time'],
        $_POST['end_time'],
        $_POST['reason'],
        $_SESSION['user_id']
    );
    echo json_encode(['status' => 'success', 'message' => 'Tanggal berhasil diblokir.']);
    exit;
}

// Jika GET, ambil list
$q = $conn->query("SELECT * FROM sarana ORDER BY nama_sarana");
$sarana_list = $q->fetchAll(PDO::FETCH_ASSOC);
$list = $blocked->getAll();

echo json_encode(['sarana' => $sarana_list, 'blocked_dates' => $list]);
?>