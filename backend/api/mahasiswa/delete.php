<?php
// 1. Matikan error text HTML (Wajib di baris paling atas)
ini_set('display_errors', 0);
error_reporting(E_ALL);

header("Content-Type: application/json");

// 2. Mulai Session (Cek dulu)
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

try {
    // 3. Fix Path (Mundur 2 langkah)
    require_once __DIR__ . "/../../models/Mahasiswa.php";
    require_once __DIR__ . "/../../config/auth.php";

    // Cek Role
    if (function_exists('require_role2')) {
        require_role2(['admin', 'mahasiswa']);
    }

    $mahasiswaModel = new Mahasiswa();

    // 4. Ambil NIM dari POST
    $nim = $_POST['nim'] ?? null;

    if (!$nim) {
        throw new Exception('NIM required for deletion.');
    }

    // 5. Eksekusi Hapus
    $success = $mahasiswaModel->delete($nim);

    if ($success) {
        echo json_encode(['success' => true, 'message' => 'Mahasiswa berhasil dihapus']);
    } else {
        // Jika gagal (biasanya karena NIM dipakai di tabel lain seperti Peminjaman)
        throw new Exception('Gagal menghapus. Pastikan mahasiswa ini tidak sedang meminjam barang/ruangan.');
    }

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>