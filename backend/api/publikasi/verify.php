<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

header('Content-Type: application/json');

require_once __DIR__ . "/../../models/Publikasi.php";
require_once __DIR__ . "/../../config/auth.php";

require_role(['admin']);

$id = $_POST['publikasi_id'] ?? null;
$status = $_POST['status'] ?? null; // 'published' atau 'rejected'

if (!$id || !$status) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'ID dan Status wajib diisi']);
    exit;
}

$model = new Publikasi();
$ok = $model->changeStatus($id, $status);

if ($ok) {
    echo json_encode([
        'status' => 'success',
        'message' => 'Status publikasi berhasil diperbarui'
    ]);
} else {
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => 'Gagal memperbarui status'
    ]);
}
?>