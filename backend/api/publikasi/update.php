<?php
// Matikan error HTML
ini_set('display_errors', 0);
error_reporting(E_ALL);

if (session_status() === PHP_SESSION_NONE) { session_start(); }
header('Content-Type: application/json');
header("Access-Control-Allow-Origin: *");

require_once __DIR__ . "/../../config/database.php";
require_once __DIR__ . "/../../models/Publikasi.php";
require_once __DIR__ . "/../../config/auth.php";

// Cek Method
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit(json_encode(['status' => 'error', 'message' => 'Method Not Allowed']));
}

// Tangkap ID (Bisa 'id' atau 'publikasi_id' tergantung frontend)
$id = $_POST['publikasi_id'] ?? $_POST['id'] ?? null;

// Tangkap Data Baru
$data = [
    'judul'         => $_POST['judul'] ?? null,
    'external_link' => $_POST['external_link'] ?? null,
    'kategori_id'   => $_POST['kategori_id'] ?? null,
    'dosen_nidn'    => $_POST['dosen_nidn'] ?? null,
    'focus_id'      => $_POST['focus_id'] ?? null // <--- WAJIB ADA
];

// Validasi
if (!$id || !$data['judul'] || !$data['kategori_id'] || !$data['focus_id']) {
    http_response_code(400);
    exit(json_encode(['status' => 'error', 'message' => 'Data tidak lengkap (ID, Judul, Kategori, Topik Riset wajib diisi)']));
}

try {
    $model = new Publikasi();
    $update = $model->update($id, $data);

    if ($update) {
        echo json_encode(['status' => 'success', 'message' => 'Publikasi berhasil diperbarui']);
    } else {
        throw new Exception("Gagal update database.");
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>