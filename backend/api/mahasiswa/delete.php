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
    $target_nim = $_POST['nim'] ?? null;

    if (!$target_nim) {
        throw new Exception("NIM wajib dikirim.");
    }

    // --- LOGIKA MIDDLEWARE ---
    if ($_SESSION['role'] === 'mahasiswa') {
        // Hanya boleh hapus diri sendiri
        if ($target_nim !== $_SESSION['nim']) {
            http_response_code(403);
            throw new Exception("Keamanan: Anda tidak boleh menghapus akun orang lain.");
        }
    }

    // 5. Eksekusi Hapus
    $success = $mahasiswaModel->delete($target_nim);

    if ($success) {
        // Jika mahasiswa menghapus diri sendiri, hancurkan session (Logout)
        if ($_SESSION['role'] === 'mahasiswa') {
            session_destroy();
        }
        echo json_encode(['success' => true, 'message' => 'Data mahasiswa berhasil dihapus.']);
    } else {
        throw new Exception('Gagal menghapus data. Pastikan data tidak terikat dengan peminjaman aktif.');
    }

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>