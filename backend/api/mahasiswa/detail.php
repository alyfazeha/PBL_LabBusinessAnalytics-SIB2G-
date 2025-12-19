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

    // LOGIKA PENENTUAN NIM (TARGET)
    // 1. Prioritaskan $_GET['nim'] (Cara Admin melihat detail Mhs lain)
    $target_nim = $_GET['nim'] ?? null;

    // 2. Jika tidak ada parameter URL, gunakan NIM dari Session (Cara Mhs melihat profil sendiri)
    if (!$target_nim && isset($_SESSION['nim'])) {
        $target_nim = $_SESSION['nim'];
    }

    if (!$target_nim) {
        throw new Exception("NIM tidak ditemukan (Parameter URL kosong dan bukan Mahasiswa).");
    }

    // --- SECURITY CHECK ---
    // Jika role Mahasiswa, pastikan dia hanya melihat datanya sendiri
    if ($_SESSION['role'] === 'mahasiswa') {
        if ($target_nim !== $_SESSION['nim']) {
            http_response_code(403);
            throw new Exception("Akses Ditolak: Anda tidak boleh melihat profil mahasiswa lain.");
        }
    }
    // Admin bebas (bypass check di atas)

    // --- EKSEKUSI ---
    $data = $mahasiswaModel->find($target_nim);

    if ($data) {
        echo json_encode(['success' => true, 'data' => $data]);
    } else {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Data mahasiswa tidak ditemukan.']);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>