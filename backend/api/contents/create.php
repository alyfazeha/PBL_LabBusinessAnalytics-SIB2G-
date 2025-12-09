<?php
ini_set('display_errors', 0);
error_reporting(E_ALL);

header('Content-Type: application/json');
header("Access-Control-Allow-Origin: *");

require_once __DIR__ . "/../../config/database.php";
require_once __DIR__ . "/../../models/Content.php";
require_once __DIR__ . "/../../config/auth.php"; 

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit(json_encode(['status' => 'error', 'message' => 'Method Not Allowed']));
}

$title = $_POST['title'] ?? null;
$body  = $_POST['body'] ?? null;
$category_id = $_POST['category_id'] ?? null;
$excerpt = $_POST['excerpt'] ?? substr(strip_tags($body ?? ''), 0, 150);
$slug = $_POST['slug'] ?? null;

// --- LOGIKA GAMBAR HYBRID (FILE ATAU URL) ---
$finalImage = null; // Variable penampung hasil akhir

// 1. Cek apakah ada FILE yang diupload?
if (isset($_FILES['featured_image_file']) && $_FILES['featured_image_file']['error'] === UPLOAD_ERR_OK) {
    
    // --- UPDATE PATH DI SINI ---
    // Masuk ke folder 'uploads/konten'
    $uploadDir = __DIR__ . '/../../../frontend/assets/uploads/konten/'; 
    
    // Pastikan folder ada (recursive = true akan membuat folder 'konten' jika belum ada)
    if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);

    $fileExtension = pathinfo($_FILES['featured_image_file']['name'], PATHINFO_EXTENSION);
    // Nama file unik
    $fileName = time() . '_' . uniqid() . '.' . $fileExtension;
    $targetFile = $uploadDir . $fileName;
    
    $allowedTypes = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

    if (in_array(strtolower($fileExtension), $allowedTypes)) {
        if (move_uploaded_file($_FILES['featured_image_file']['tmp_name'], $targetFile)) {
            // --- UPDATE PATH DATABASE ---
            // Path yang disimpan di database agar bisa dibaca oleh frontend HTML
            $finalImage = '../../assets/uploads/konten/' . $fileName;
        } else {
             // Jika gagal upload, kirim error
             http_response_code(500);
             exit(json_encode(['status' => 'error', 'message' => 'Gagal memindahkan file upload']));
        }
    } else {
        http_response_code(400);
        exit(json_encode(['status' => 'error', 'message' => 'Format file tidak didukung']));
    }
} 

// 2. Jika tidak ada file upload, Cek apakah ada URL?
if (empty($finalImage) && !empty($_POST['featured_image_url'])) {
    $finalImage = $_POST['featured_image_url'];
}
// --------------------------------------------

// Buat Slug Otomatis
if (!$slug && $title) {
    $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $title)));
}

if (!$title || !$category_id) {
    http_response_code(400);
    exit(json_encode(['status' => 'error', 'message' => 'Judul dan Kategori wajib diisi']));
}

$data = [
    'title' => $title,
    'slug' => $slug,
    'excerpt' => $excerpt,
    'body' => $body,
    'category_id' => $category_id,
    'featured_image' => $finalImage // Path file yang baru
];

try {
    $model = new Content();
    $id = $model->create($data);

    if ($id) {
        http_response_code(201);
        echo json_encode(['status' => 'success', 'message' => 'Berita berhasil dibuat', 'id' => $id]);
    } else {
        throw new Exception("Gagal menyimpan ke database");
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>