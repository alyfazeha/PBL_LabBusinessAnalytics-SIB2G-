<?php
// 1. Matikan error text HTML SEBELUM session dimulai
ini_set('display_errors', 0);
error_reporting(E_ALL);

header("Content-Type: application/json");

// 2. Baru mulai session (Cek dulu biar gak muncul Notice)
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

try {
    require_once __DIR__ . "/../../models/Mahasiswa.php";
    require_once __DIR__ . "/../../config/auth.php";

    if (function_exists('require_role2')) {
        require_role2(['admin', 'mahasiswa']);
    }

    $mahasiswaModel = new Mahasiswa();

    // 3. Validasi NIM (Wajib Ada)
    if (!isset($_POST['nim']) || empty($_POST['nim'])) {
        throw new Exception('NIM tidak ditemukan. Pastikan Frontend mengirimkan key "nim".');
    }

    $nim = $_POST['nim'];

    // 4. Cek Data Lama
    $current = $mahasiswaModel->find($nim);
    if (!$current) {
        throw new Exception('Data Mahasiswa tidak ditemukan di database.');
    }

    // 5. Siapkan Data Baru
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

    if ($mahasiswaModel->update($nim, $data)) {
        echo json_encode(['success' => true, 'message' => 'Mahasiswa berhasil diupdate']);
    } else {
        throw new Exception("Gagal update database.");
    }

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>