<?php
require_once __DIR__ . "/../config/auth.php";
require_once __DIR__ . "/../models/Content.php";

require_admin(); // ✅ hanya admin boleh publish

$model = new Content();

// ✅ VALIDASI INPUT
if (!isset($_POST['content_id'], $_POST['status'])) {
    echo json_encode([
        'status' => false,
        'message' => 'content_id dan status wajib diisi'
    ]);
    exit;
}

$content_id = (int) $_POST['content_id'];
$status     = $_POST['status'] === 'true' || $_POST['status'] == 1;

$result = $model->publish($content_id, $status);

if ($result) {
    echo json_encode([
        'status' => true,
        'message' => $status ? 'Konten berhasil dipublish' : 'Konten berhasil diunpublish'
    ]);
} else {
    echo json_encode([
        'status' => false,
        'message' => 'Gagal mengubah status konten'
    ]);
}
