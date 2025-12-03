<?php
require_once __DIR__ . "/../config/auth.php";
require_once __DIR__ . "/../models/Content.php";

// ✅ hanya admin yang boleh hapus
require_admin();

$model = new Content();

// ✅ validasi wajib ada ID
if (!isset($_POST['content_id'])) {
    echo json_encode([
        'status' => false,
        'message' => 'content_id wajib diisi'
    ]);
    exit;
}

$content_id = (int) $_POST['content_id'];

$deleted = $model->delete($content_id);

if ($deleted) {
    echo json_encode([
        'status' => true,
        'message' => 'Konten berhasil dihapus'
    ]);
} else {
    echo json_encode([
        'status' => false,
        'message' => 'Gagal menghapus konten'
    ]);
}
