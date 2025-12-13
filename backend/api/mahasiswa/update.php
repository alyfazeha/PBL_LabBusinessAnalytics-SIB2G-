<?php
ini_set('display_errors', 0);
error_reporting(E_ALL);

header("Content-Type: application/json");

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

try {
    require_once __DIR__ . "/../../models/Mahasiswa.php";
    require_once __DIR__ . "/../../config/auth.php";

    require_admin();

    $mahasiswaModel = new Mahasiswa();

    // 1. Ambil NIM (Ini adalah kunci, TIDAK BOLEH BERUBAH)
    $nim = $_POST['nim'] ?? null;

    if (empty($nim)) {
        throw new Exception('NIM wajib dikirim.');
    }

    // 2. Cek Data Lama
    $current = $mahasiswaModel->find($nim);
    if (!$current) {
        throw new Exception("Data Mahasiswa dengan NIM $nim tidak ditemukan.");
    }

    // 3. Siapkan Data Baru (HANYA Data profil, NIM TIDAK dimasukkan ke sini)
    $data = [
        'nama'    => trim($_POST['nama'] ?? $current['nama']),
        'prodi'   => trim($_POST['prodi'] ?? $current['prodi']),
        'tingkat' => trim($_POST['tingkat'] ?? $current['tingkat']),
        'no_hp'   => trim($_POST['no_hp'] ?? $current['no_hp']),
        'email'   => trim($_POST['email'] ?? $current['email']),
    ];

    if ($data['nama'] === "" || $data['prodi'] === "") {
        throw new Exception("Nama dan Prodi tidak boleh kosong.");
    }
    
    // 4. Update
    // Parameter 1: NIM (kunci WHERE)
    // Parameter 2: Data yang mau diubah (SET nama=..., prodi=...)
    if ($mahasiswaModel->update($nim, $data)) {
        echo json_encode(['success' => true, 'message' => 'Data mahasiswa berhasil diperbarui.']);
    } else {
        throw new Exception("Gagal update database.");
    }
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>