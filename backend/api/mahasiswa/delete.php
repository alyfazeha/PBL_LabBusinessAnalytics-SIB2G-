<?php
ini_set('display_errors', 0);
error_reporting(E_ALL);
header("Content-Type: application/json");

require_once __DIR__ . "/../../config/database.php"; 
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
    // Jika user login sebagai mahasiswa, hanya boleh hapus diri sendiri
    if ($_SESSION['role'] === 'mahasiswa') {
        if ($target_nim !== $_SESSION['nim']) {
            http_response_code(403);
            throw new Exception("Keamanan: Anda tidak boleh menghapus akun orang lain.");
        }
    }

    // --- PROSES PENGHAPUSAN BERSIH ---
    $db = Database::getInstance();
    
    // 1. Cari user_id dulu sebelum dihapus
    $stmtGetID = $db->prepare("SELECT user_id, nama FROM mahasiswa WHERE nim = :nim LIMIT 1");
    $stmtGetID->execute([':nim' => $target_nim]);
    $row = $stmtGetID->fetch(PDO::FETCH_ASSOC);
    
    if (!$row) {
        throw new Exception("Mahasiswa dengan NIM $target_nim tidak ditemukan.");
    }

    $userIdToDelete = $row['user_id'];
    $namaToDelete = $row['nama'];

    // 2. Mulai Transaksi
    $db->beginTransaction();

    try {
        // A. [BARU] Hapus Riwayat Peminjaman (Bookings)
        // Ini akan membuat jadwal kembali 'available' karena datanya hilang dari tabel bookings
        $stmtDelBooking = $db->prepare("DELETE FROM bookings WHERE mahasiswa_nim = :nim");
        $stmtDelBooking->execute([':nim' => $target_nim]);

        // B. Hapus Data Mahasiswa (Profil)
        $stmtDelMhs = $db->prepare("DELETE FROM mahasiswa WHERE nim = :nim");
        $successMhs = $stmtDelMhs->execute([':nim' => $target_nim]);

        if (!$successMhs) {
            throw new Exception("Gagal menghapus data profil mahasiswa.");
        }

        // C. Hapus Data User (Akun Login)
        if ($userIdToDelete) {
            $stmtDelUser = $db->prepare("DELETE FROM users WHERE user_id = :uid");
            $stmtDelUser->execute([':uid' => $userIdToDelete]);
        }

        // D. Commit Transaksi
        $db->commit();

        // 3. Logout jika menghapus diri sendiri
        if ($_SESSION['role'] === 'mahasiswa') {
            session_destroy();
        }

        echo json_encode(['success' => true, 'message' => "Data Mahasiswa ($namaToDelete) beserta riwayat peminjamannya berhasil dihapus."]);

    } catch (Exception $ex) {
        $db->rollBack(); // Batalkan semua jika ada error
        throw new Exception('Gagal menghapus: ' . $ex->getMessage());
    }

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>