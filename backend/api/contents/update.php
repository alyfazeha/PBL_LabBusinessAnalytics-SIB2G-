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

$id = $_POST['content_id'] ?? $_POST['id'] ?? null;
if (!$id) {
    http_response_code(400);
    exit(json_encode(['status' => 'error', 'message' => 'ID Content Required']));
}

$model = new Content();
$oldData = $model->getById($id);

if (!$oldData) {
    http_response_code(404);
    exit(json_encode(['status' => 'error', 'message' => 'Data lama tidak ditemukan']));
}

// Ambil data form
$title = $_POST['title'] ?? $oldData['title'];
$slug = $_POST['slug'] ?? $oldData['slug'];

// Generate slug baru jika title berubah & slug kosong (Opsional)
if (isset($_POST['title']) && empty($_POST['slug'])) {
     $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $title)));
}

// --- LOGIKA UPDATE GAMBAR (SAMA SEPERTI CREATE) ---
$finalImage = $oldData['featured_image']; // Default: Pakai gambar lama

// 1. Cek Upload File Baru
if (isset($_FILES['featured_image_file']) && $_FILES['featured_image_file']['error'] === UPLOAD_ERR_OK) {
    
    // Path Folder: uploads/konten/
    $uploadDir = __DIR__ . '/../../../frontend/assets/uploads/konten/'; 
    if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);

    $fileExtension = pathinfo($_FILES['featured_image_file']['name'], PATHINFO_EXTENSION);
    $fileName = time() . '_' . uniqid() . '.' . $fileExtension;
    $targetFile = $uploadDir . $fileName;
    $allowedTypes = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

    if (in_array(strtolower($fileExtension), $allowedTypes)) {
        if (move_uploaded_file($_FILES['featured_image_file']['tmp_name'], $targetFile)) {
            // Update path database
            $finalImage = '../../assets/uploads/konten/' . $fileName;
        }
    }
} 
// 2. Cek Input URL (Hanya jika tidak ada file yang diupload)
elseif (!empty($_POST['featured_image_url'])) {
    $finalImage = $_POST['featured_image_url'];
}
// --------------------------------------------------

$data = [
    'title' => $title,
    'slug'  => $slug,
    'excerpt' => $_POST['excerpt'] ?? $oldData['excerpt'],
    'body'    => $_POST['body'] ?? $oldData['body'],
    'category_id' => $_POST['category_id'] ?? $oldData['category_id'],
    'featured_image' => $finalImage
];

try {
    if ($model->update($id, $data)) {
        echo json_encode(['status' => 'success', 'message' => 'Berita berhasil diperbarui']);
    } else {
        throw new Exception("Gagal update database");
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>