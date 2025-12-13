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
    // 1. Cek Method & Login
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Method Not Allowed');
    }
    if (!isset($_SESSION['user_id']) || !isset($_SESSION['role'])) {
        http_response_code(401);
        throw new Exception("Unauthorized. Silakan login.");
    }

    $db = Database::getInstance();
    $role = $_SESSION['role'];
    
    $id = $_POST['publikasi_id'] ?? $_POST['id'] ?? null;

    // 2. Tangkap Data
    $data = [
        'judul'         => $_POST['judul'] ?? null,
        'external_link' => $_POST['external_link'] ?? null,
        'kategori_id'   => $_POST['kategori_id'] ?? null,
        'focus_id'      => $_POST['focus_id'] ?? null,
        'tahun'         => $_POST['tahun'] ?? null,
        'dosen_nidn'    => $_POST['dosen_nidn'] ?? null,
        'abstrak'       => $_POST['abstrak'] ?? null
    ];

    // 3. Validasi Sederhana
    if (!$id || !$data['judul'] || !$data['kategori_id']) {
        throw new Exception('Data tidak lengkap (ID, Judul, Kategori wajib diisi).');
    }

    // 4. Eksekusi Update Model
    $model = new Publikasi();
    $update = $model->update($id, $data);

    if ($update) {
        
        // 5. LOGIKA WORKFLOW STATUS (Perbaikan Utama!)
        // Jika yang meng-edit adalah Dosen, paksa status kembali ke 'pending'
        if ($role === 'dosen') {
            $updStatus = $db->prepare("UPDATE publikasi SET status = 'pending' WHERE id = :id");
            $updStatus->execute([':id' => $id]);
            $pesan = 'Publikasi berhasil diperbarui. Status diubah menjadi PENDING untuk verifikasi ulang.';
        } else {
            // Jika yang meng-edit Admin, biarkan status tetap (atau sesuai input Admin)
             $pesan = 'Publikasi berhasil diperbarui.';
        }

        echo json_encode(['status' => 'success', 'message' => $pesan]);
    } else {
        throw new Exception("Gagal mengupdate database.");
    }

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>