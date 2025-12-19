<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

header("Content-Type: application/json");
ini_set('display_errors', 0);
error_reporting(E_ALL);

// Pastikan koneksi database juga di-include
require_once __DIR__ . "/../../config/database.php"; 
require_once __DIR__ . "/../../models/Dosen.php";
require_once __DIR__ . "/../../config/auth.php";

try {
    if (function_exists('require_role')) {
        require_role(['admin']);
    }

    $dosenModel = new Dosen();
    $db = Database::getInstance(); // Ambil instance database untuk transaksi
    
    $nidn = $_POST['nidn'] ?? null;

    if (!$nidn) {
        throw new Exception('NIDN diperlukan untuk penghapusan.');
    }

    // 1. Cek apakah dosen ada dan ambil user_id
    $currentDosen = $dosenModel->find($nidn);
    if (!$currentDosen) {
        throw new Exception('Dosen tidak ditemukan.');
    }
    $user_id_to_delete = $currentDosen['user_id'];
    $dosen_nama = $currentDosen['nama'];

    // Mulai Transaksi
    $db->beginTransaction();

    try {
        // 2. Hapus Dosen dari tabel dosen
        $successDosen = $dosenModel->delete($nidn); 

        // 3. Hapus User dari tabel users (hanya jika user_id valid)
        $successUser = false;
        if ($user_id_to_delete) {
            $stmt = $db->prepare("DELETE FROM users WHERE user_id = :user_id");
            $successUser = $stmt->execute([':user_id' => $user_id_to_delete]);
        } else {
             // Jika tidak ada user_id, kita anggap sukses menghapus user (karena memang tidak ada)
             $successUser = true;
        }

        if ($successDosen && $successUser) {
            $db->commit(); // Commit jika keduanya sukses
            echo json_encode(['success' => true, 'message' => "Dosen ($dosen_nama) dan akun user berhasil dihapus."]);
        } else {
            $db->rollBack();
            throw new Exception('Gagal menghapus data dosen atau akun user terkait.');
        }

    } catch (Exception $ex) {
        $db->rollBack(); // Rollback jika ada error dalam transaksi
        throw $ex; 
    }

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>