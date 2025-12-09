<?php
ini_set('display_errors', 0);
error_reporting(E_ALL);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

header('Content-Type: application/json');
require_once __DIR__ . "/../../config/auth.php";
require_once __DIR__ . "/../../models/Content.php";

require_role(['admin']); 
// ... (sisa kode ke bawah aman)

require_role(['admin']); 

$model = new Content();

if (!isset($_POST['content_id'], $_POST['status'])) {
    http_response_code(400); 
    echo json_encode(['status' => false, 'message' => 'content_id dan status wajib diisi']);
    exit;
}

$content_id = (int) $_POST['content_id'];
$input_status = $_POST['status'];

// Logika Boolean:
// Kalau dikirim "true", "1", atau "published" -> Maka TRUE. Selain itu FALSE.
$is_published = ($input_status === 'true' || $input_status == 1 || $input_status === 'published');

// Update Status
$result = $model->updateStatus($content_id, $is_published);

if ($result) {
    echo json_encode([
        'status' => true,
        'message' => "Konten berhasil diubah menjadi " . ($is_published ? 'Published' : 'Pending')
    ]);
} else {
    http_response_code(500);
    echo json_encode(['status' => false, 'message' => 'Gagal mengubah status konten']);
}
?>