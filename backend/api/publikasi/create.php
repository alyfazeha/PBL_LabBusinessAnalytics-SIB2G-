<?php
// backend/api/publikasi/create.php
ini_set('display_errors', 0);
error_reporting(E_ALL);

header('Content-Type: application/json');
header("Access-Control-Allow-Origin: *");

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . "/../../config/database.php";
require_once __DIR__ . "/../../models/Publikasi.php";

try {
    // 1. Cek Method
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Method Not Allowed');
    }

    // 2. Cek Login
    if (!isset($_SESSION['user_id']) || !isset($_SESSION['role'])) {
        http_response_code(401);
        throw new Exception("Unauthorized. Silakan login.");
    }

    $db = Database::getInstance();
    $role = $_SESSION['role']; // 'admin' atau 'dosen'
    $userId = $_SESSION['user_id'];

    // Variabel Penampung
    $dosenNidn = null;
    $status = 'pending'; // Default

    // 3. LOGIKA PERCABANGAN (PENTING!)
    if ($role === 'dosen') {
        // --- JIKA DOSEN ---
        // Cari NIDN otomatis dari database (karena form tidak kirim NIDN)
        $stmt = $db->prepare("SELECT nidn FROM dosen WHERE user_id = :uid");
        $stmt->execute([':uid' => $userId]);
        $dosenData = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$dosenData || empty($dosenData['nidn'])) {
            throw new Exception("Data profil dosen belum lengkap (NIDN tidak ditemukan). Hubungi Admin.");
        }
        
        $dosenNidn = $dosenData['nidn'];
        $status = 'pending'; // Dosen wajib pending

    } else if ($role === 'admin') {
        // --- JIKA ADMIN ---
        // Ambil NIDN dari input form dropdown
        $dosenNidn = $_POST['dosen_nidn'] ?? null;
        $status = 'published'; // Admin bisa langsung publish
    }

    // 4. Tangkap Data Lain
    $data = [
        'judul'         => $_POST['judul'] ?? null,
        'external_link' => $_POST['external_link'] ?? null,
        'kategori_id'   => $_POST['kategori_id'] ?? null,
        'focus_id'      => $_POST['focus_id'] ?? null,
        'tahun'         => $_POST['tahun'] ?? date('Y'),
        
        // Data hasil logika di atas
        'dosen_nidn'    => $dosenNidn,
        'status'        => $status 
    ];

    // 5. Validasi Akhir
    if (!$data['judul'] || !$data['kategori_id'] || !$data['focus_id'] || !$data['dosen_nidn']) {
        throw new Exception('Data tidak lengkap. Judul, Kategori, Topik, dan Dosen wajib ada.');
    }

    // 6. Simpan ke Database
    $model = new Publikasi();
    
    // Simpan data (Model akan insert status default 'pending' di query insert-nya)
    $id = $model->create($data); 

    if ($id) {
        // Paksa update status sesuai logika role (override default model)
        $upd = $db->prepare("UPDATE publikasi SET status = :st WHERE id = :id OR id = :id");
        $upd->execute([':st' => $status, ':id' => $id]);

        $pesan = ($role === 'dosen') ? 'Publikasi berhasil diajukan. Menunggu verifikasi.' : 'Publikasi berhasil ditambahkan.';
        
        echo json_encode(['status' => 'success', 'message' => $pesan]);
    } else {
        throw new Exception("Gagal menyimpan ke database.");
    }

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>