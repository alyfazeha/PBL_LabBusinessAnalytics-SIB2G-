<?php
// backend/api/publikasi/update.php

ini_set('display_errors', 0);
error_reporting(E_ALL);

if (session_status() === PHP_SESSION_NONE) { session_start(); }
header('Content-Type: application/json');
header("Access-Control-Allow-Origin: *");

require_once __DIR__ . "/../../config/database.php";
require_once __DIR__ . "/../../models/Publikasi.php";

try {
    // 1. Cek Method
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Method Not Allowed');
    }

    // 2. Cek Login (Boleh Admin atau Dosen)
    if (!isset($_SESSION['user_id']) || !isset($_SESSION['role'])) {
        http_response_code(401);
        throw new Exception("Unauthorized. Silakan login.");
    }

    // Tangkap ID
    $id = $_POST['publikasi_id'] ?? $_POST['id'] ?? null;

    // 3. Tangkap Data
    // Kita gunakan Null Coalescing Operator (??) agar tidak error jika ada field kosong
    $data = [
        'judul'         => $_POST['judul'] ?? null,
        'external_link' => $_POST['external_link'] ?? null,
        'kategori_id'   => $_POST['kategori_id'] ?? null,
        'focus_id'      => $_POST['focus_id'] ?? null,
        'tahun'         => $_POST['tahun'] ?? null,
        'dosen_nidn'    => $_POST['dosen_nidn'] ?? null // Ini dikirim via input hidden dari frontend
    ];

    // 4. Validasi Sederhana
    if (!$id || !$data['judul'] || !$data['kategori_id']) {
        throw new Exception('Data tidak lengkap (ID, Judul, Kategori wajib diisi).');
    }

    // 5. Eksekusi Update
    $model = new Publikasi();
    $update = $model->update($id, $data);

    if ($update) {
        // [OPSIONAL] Jika Dosen yang edit, kembalikan status ke Pending agar Admin cek ulang?
        // Jika ingin fitur itu, uncomment baris di bawah:
        // if ($_SESSION['role'] === 'dosen') {
        //     $db = Database::getInstance();
        //     $db->query("UPDATE publikasi SET status = 'pending' WHERE id = $id");
        // }

        echo json_encode(['status' => 'success', 'message' => 'Publikasi berhasil diperbarui']);
    } else {
        throw new Exception("Gagal mengupdate database.");
    }

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>