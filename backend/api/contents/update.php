<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

header('Content-Type: application/json');
require_once __DIR__ . "/../../config/koneksi.php";
require_once __DIR__ . "/../../models/Content.php";
require_once __DIR__ . "/../../config/auth.php";

require_role(['admin']);
// ... (sisa kode ke bawah aman)

require_role(['admin']);

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit(json_encode(['status' => 'error', 'message' => 'Method Not Allowed']));
}

$id = $_POST['content_id'] ?? $_POST['id'] ?? null;
if (!$id) {
    http_response_code(400);
    exit(json_encode(['status' => 'error', 'message' => 'ID Content Required']));
}

// Ambil data lama
$model = new Content();
$oldData = $model->getById($id);

if (!$oldData) {
    http_response_code(404);
    exit(json_encode(['status' => 'error', 'message' => 'Data tidak ditemukan']));
}

// --- LOGIC UPDATE IMAGE (URL) ---
$inputImage = $_POST['featured_image'] ?? null;

// Jika input diisi, pakai yang baru. Jika kosong, pakai yang lama.
$finalImage = !empty($inputImage) ? $inputImage : $oldData['featured_image'];
// --------------------------------

// Logic Update Lainnya
$title = $_POST['title'] ?? $oldData['title'];
$slug = $_POST['slug'] ?? $oldData['slug'];

if (isset($_POST['title']) && empty($_POST['slug'])) {
     $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $title)));
}

$data = [
    'title' => $title,
    'slug'  => $slug,
    'excerpt' => $_POST['excerpt'] ?? $oldData['excerpt'],
    'body'    => $_POST['body'] ?? $oldData['body'],
    'category_id' => $_POST['category_id'] ?? $oldData['category_id'],
    'featured_image' => $finalImage // Update URL
];

$update = $model->update($id, $data);

if ($update) {
    echo json_encode(['status' => 'success', 'message' => 'Berita berhasil diupdate']);
} else {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Gagal update']);
}
?>