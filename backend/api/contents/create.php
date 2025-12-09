<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

header('Content-Type: application/json');

// 2. Path Mundur 2 Langkah (Agar file ketemu)
require_once __DIR__ . "/../../config/koneksi.php";
require_once __DIR__ . "/../../models/Content.php";
require_once __DIR__ . "/../../config/auth.php";

require_role(['admin']);
// ... (sisa kode ke bawah sudah aman)

require_role(['admin']); // Hanya Admin

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit(json_encode(['status' => 'error', 'message' => 'Method Not Allowed']));
}

// 1. AMBIL DATA DARI POST
$title = $_POST['title'] ?? null;
$body  = $_POST['body'] ?? null;
// Auto excerpt: Ambil 150 karakter pertama dari body jika excerpt kosong
$excerpt = $_POST['excerpt'] ?? substr(strip_tags($body ?? ''), 0, 150); 
$category_id = $_POST['category_id'] ?? null;

// --- BAGIAN IMAGE (URL) ---
// Karena isinya link, kita ambil langsung sebagai text.
$featured_image = $_POST['featured_image'] ?? null; 
// --------------------------

// 2. LOGIC SLUG (Otomatis)
$slug = $_POST['slug'] ?? null;
if (!$slug && $title) {
    // Ubah "Judul Berita" jadi "judul-berita"
    $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $title)));
}

// 3. VALIDASI
if (!$title || !$category_id || !$slug) {
    http_response_code(400);
    exit(json_encode(['status' => 'error', 'message' => 'Judul dan Kategori wajib diisi']));
}

// 4. SIMPAN
$data = [
    'title' => $title,
    'slug' => $slug,
    'excerpt' => $excerpt,
    'body' => $body,
    'category_id' => $category_id,
    'featured_image' => $featured_image // Masuk sebagai string URL
];

$model = new Content();
$id = $model->create($data);

if ($id) {
    http_response_code(201);
    echo json_encode(['status' => 'success', 'message' => 'Berita berhasil dibuat', 'id' => $id]);
} else {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Gagal menyimpan ke database']);
}
?>