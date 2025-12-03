<?php
require_once __DIR__ . "/../config/auth.php";
require_once __DIR__ . "/../models/Content.php";

// ✅ hanya admin yang boleh edit
require_admin();

$model = new Content();

// ✅ validasi ID
if (!isset($_POST['content_id'])) {
    echo json_encode([
        'status' => false,
        'message' => 'content_id wajib diisi'
    ]);
    exit;
}

$content_id = (int) $_POST['content_id'];

// ✅ siapkan data aman (boleh sebagian)
$data = [
    'title' => $_POST['title'] ?? '',
    'excerpt' => $_POST['excerpt'] ?? '',
    'body' => $_POST['body'] ?? '',
    'category_id' => $_POST['category_id'] ?? null,
    'featured_image' => $_POST['featured_image'] ?? null
];

$updated = $model->update($content_id, $data);

if ($updated) {
    echo json_encode([
        'status' => true,
        'message' => 'Konten berhasil diperbarui'
    ]);
} else {
    echo json_encode([
        'status' => false,
        'message' => 'Gagal memperbarui konten'
    ]);
}
