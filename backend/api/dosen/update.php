<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

header("Content-Type: application/json");
ini_set('display_errors', 0);
error_reporting(E_ALL);

try {
    // FIX PATH: Gunakan ../../
    require_once __DIR__ . "/../../models/Dosen.php";
    require_once __DIR__ . "/../../config/auth.php";

    if (function_exists('require_role')) {
        require_role(['admin', 'dosen']);
    }

    $dosenModel = new Dosen();

    // Validasi NIDN
    if (!isset($_POST['nidn'])) {
        throw new Exception('NIDN tidak ditemukan.');
    }

    $nidn = $_POST['nidn'];

    // Cek Data Lama
    $currentDosen = $dosenModel->find($nidn);
    if (!$currentDosen) {
        throw new Exception('Data Dosen tidak ditemukan di database.');
    }

    // Ambil Data Baru (Gunakan data lama jika input kosong)
    $data = [
        'nama' => trim($_POST['nama'] ?? $currentDosen['nama']),
        'jabatan' => trim($_POST['jabatan'] ?? $currentDosen['jabatan']),
        'email' => trim($_POST['email'] ?? $currentDosen['email']),
        'foto_path' => trim($_POST['foto_path'] ?? $currentDosen['foto_path']),
        'researchgate_url' => trim($_POST['researchgate_url'] ?? $currentDosen['researchgate_url']),
        'scholar_url' => trim($_POST['scholar_url'] ?? $currentDosen['scholar_url']),
        'sinta_url' => trim($_POST['sinta_url'] ?? $currentDosen['sinta_url']),
        'nip' => trim($_POST['nip'] ?? $currentDosen['nip']),
        'prodi' => trim($_POST['prodi'] ?? $currentDosen['prodi']),
        'pendidikan' => trim($_POST['pendidikan'] ?? $currentDosen['pendidikan']),
        'sertifikasi' => trim($_POST['sertifikasi'] ?? $currentDosen['sertifikasi']),
        'mata_kuliah' => trim($_POST['mata_kuliah'] ?? $currentDosen['mata_kuliah']),
    ];

    $success = $dosenModel->update($nidn, $data);

    if ($success) {
        echo json_encode(['success' => true, 'message' => 'Dosen berhasil diperbarui']);
    } else {
        throw new Exception('Gagal memperbarui data dosen.');
    }

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>