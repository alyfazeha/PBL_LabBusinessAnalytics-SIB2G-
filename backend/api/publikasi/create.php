<?php
// Matikan semua output error HTML agar tidak merusak JSON
ini_set('display_errors', 0);
error_reporting(E_ALL);

if (session_status() === PHP_SESSION_NONE) { session_start(); }
header('Content-Type: application/json');
header("Access-Control-Allow-Origin: *");

require_once __DIR__ . "/../../config/database.php";
require_once __DIR__ . "/../../models/Publikasi.php";
require_once __DIR__ . "/../../config/auth.php";

// ======================================================
// PERUBAHAN: Tambahkan Pengecekan Role (Admin & Dosen)
// ======================================================
if (function_exists('require_role')) {
    require_role(['admin', 'dosen']);
}

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Method Not Allowed');
    }

    // Tangkap Data
    $data = [
        'judul'         => $_POST['judul'] ?? null,
        'external_link' => $_POST['external_link'] ?? null,
        'kategori_id'   => $_POST['kategori_id'] ?? null,
        'dosen_nidn'    => $_POST['dosen_nidn'] ?? null,
        'focus_id'      => $_POST['focus_id'] ?? null 
    ];

    // Validasi
    if (!$data['judul'] || !$data['kategori_id'] || !$data['dosen_nidn'] || !$data['focus_id']) {
        throw new Exception('Data tidak lengkap. Cek Judul, Kategori, Topik, dan Dosen.');
    }

    $model = new Publikasi();
    $id = $model->create($data);

    if ($id) {
        echo json_encode(['status' => 'success', 'message' => 'Berhasil disimpan', 'id' => $id]);
    } else {
        throw new Exception("Gagal menyimpan ke database (Model return false).");
    }

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>