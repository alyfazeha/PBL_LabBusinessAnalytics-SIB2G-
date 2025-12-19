<?php
ini_set('display_errors', 0);
error_reporting(E_ALL);
header("Content-Type: application/json");

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . "/../../models/Mahasiswa.php";
require_once __DIR__ . "/../../config/auth.php";

require_role2(['admin', 'mahasiswa']);

try {
    $mahasiswaModel = new Mahasiswa();

    // 1. Ambil Input
    $nim_input = $_POST['nim'] ?? null;
    
    if (!$nim_input) {
        throw new Exception("NIM wajib dikirim.");
    }

    // --- LOGIKA MIDDLEWARE/PROTEKSI ---
    if ($_SESSION['role'] === 'mahasiswa') {
        // Cek apakah NIM yang mau diedit adalah NIM dia sendiri?
        if ($nim_input !== $_SESSION['nim']) {
            http_response_code(403);
            throw new Exception("Anda tidak diizinkan mengubah profil mahasiswa lain.");
        }
    }

    // 4. Cek Data Lama
    $current = $mahasiswaModel->find($nim_input);
    if (!$current) {
        throw new Exception('Data Mahasiswa tidak ditemukan di database.');
    }

    // 3. Siapkan Data Baru
    // Menggunakan Null Coalescing (??) agar jika field kosong, pakai data lama
    $data = [
        'nama'    => !empty($_POST['nama']) ? trim($_POST['nama']) : $current['nama'],
        'prodi'   => !empty($_POST['prodi']) ? trim($_POST['prodi']) : $current['prodi'],
        'tingkat' => !empty($_POST['tingkat']) ? trim($_POST['tingkat']) : $current['tingkat'],
        'no_hp'   => !empty($_POST['no_hp']) ? trim($_POST['no_hp']) : $current['no_hp'],
        'email'   => !empty($_POST['email']) ? trim($_POST['email']) : $current['email'],
    ];

    if ($data['nama'] === "" || $data['prodi'] === "" || $data['tingkat'] === "" || $data['no_hp'] === "" ||$data['email'] === "") {
        throw new Exception("Semua field wajib diisi dan tidak boleh kosong.");
    }

    // 4. Update
    $success = $mahasiswaModel->update($nim_input, $data);

    if ($success) {
        echo json_encode(['success' => true, 'message' => 'Profil berhasil diperbarui.']);
    } else {
        throw new Exception('Gagal memperbarui database.');
    }
    
} catch (Exception $e) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>