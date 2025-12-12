<?php
// delete.php
ini_set('display_errors', 0);
error_reporting(E_ALL);

if (session_status() === PHP_SESSION_NONE) { session_start(); }
header('Content-Type: application/json');
header("Access-Control-Allow-Origin: *");

require_once __DIR__ . "/../../config/database.php";
require_once __DIR__ . "/../../models/Publikasi.php";
require_once __DIR__ . "/../../config/auth.php";

// Pastikan hanya admin/dosen yang boleh akses
require_role(['admin', 'dosen']); 

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit(json_encode(['status' => 'error', 'message' => 'Method Not Allowed']));
}

$id = $_POST['publikasi_id'] ?? $_POST['id'] ?? null;
$role = $_SESSION['role'] ?? null;
$userId = $_SESSION['user_id'] ?? null;

if (!$id) {
    http_response_code(400);
    exit(json_encode(['status' => 'error', 'message' => 'ID Publikasi wajib dikirim']));
}

try {
    $db = Database::getInstance();
    $model = new Publikasi();

    // 1. Ambil detail publikasi
    $dataPublikasi = $model->getById($id); 

    if (!$dataPublikasi) {
        throw new Exception("Publikasi tidak ditemukan.");
    }
    
    // 2. LOGIKA KEAMANAN: Cek Kepemilikan Data jika user adalah DOSEN
    if ($role === 'dosen') {
        // Ambil NIDN dosen yang sedang login
        $stmtDosen = $db->prepare("SELECT nidn FROM dosen WHERE user_id = :uid");
        $stmtDosen->execute([':uid' => $userId]);
        $dosenNidn = $stmtDosen->fetchColumn();

        // Verifikasi: Apakah NIDN di publikasi sama dengan NIDN yang login?
        if ($dataPublikasi['dosen_nidn'] !== $dosenNidn) {
            http_response_code(403);
            throw new Exception("Akses ditolak. Anda tidak memiliki izin menghapus publikasi ini.");
        }
    }
    // Jika role-nya Admin, atau jika dosen sudah diverifikasi kepemilikannya, proses lanjut.

    // 3. Eksekusi Hapus
    $hapus = $model->delete($id);

    if ($hapus) {
        echo json_encode(['status' => 'success', 'message' => 'Publikasi berhasil dihapus']);
    } else {
        // PERBAIKAN: Harus ada pesan error yang dikirim ke frontend
        throw new Exception("Gagal menghapus.");
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>