<?php
// backend/api/dosen/update_profile.php
ini_set('display_errors', 0);
error_reporting(E_ALL);

header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . "/../../config/database.php";
require_once __DIR__ . "/../../models/Dosen.php";

try {
    // 1. Cek Login
    if (!isset($_SESSION['user_id'])) {
        throw new Exception("Unauthorized. Silakan login.");
    }

    $db = Database::getInstance();
    $dosenModel = new Dosen();
    $userId = $_SESSION['user_id'];

    // 2. Cari NIDN Dosen
    $stmt = $db->prepare("SELECT * FROM dosen WHERE user_id = :uid");
    $stmt->execute([':uid' => $userId]);
    $currentData = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$currentData) {
        throw new Exception("Data dosen tidak ditemukan.");
    }

    $nidn = $currentData['nidn'];

    // 3. Ambil Data Input
    $nama = $_POST['nama'] ?? $currentData['nama'];
    $nip = $_POST['nip'] ?? $currentData['nip'];
    $jabatan = $_POST['jabatan'] ?? $currentData['jabatan'];
    $email = $_POST['email'] ?? $currentData['email'];
    $prodi = $_POST['prodi'] ?? $currentData['prodi'];
    $pendidikan = $_POST['pendidikan'] ?? $currentData['pendidikan'];
    
    // Link Eksternal
    $sinta = $_POST['sinta_url'] ?? $currentData['sinta_url'];
    $scholar = $_POST['scholar_url'] ?? $currentData['scholar_url'];
    $researchgate = $_POST['researchgate_url'] ?? $currentData['researchgate_url'];
    
    // [BARU] Tambahkan scopus jika kolomnya ada di database
    // $scopus = $_POST['scopus_url'] ?? $currentData['scopus_url']; 

    // 4. Handle Upload Foto
    $fotoPath = $currentData['foto_path'];

    if (isset($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
        
        // [PERBAIKAN UTAMA] Tambahkan '/frontend' agar lokasi simpan sesuai dengan lokasi baca HTML
        $uploadDir = __DIR__ . '/../../../frontend/assets/uploads/dosen/';
        
        // Buat folder jika belum ada
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        $fileExt = pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION);
        $fileName = 'dosen_' . $nidn . '_' . time() . '.' . $fileExt;
        $targetFile = $uploadDir . $fileName;

        if (move_uploaded_file($_FILES['foto']['tmp_name'], $targetFile)) {
            // Simpan path relatif untuk database
            // Frontend HTML/JS nanti akan membersihkan path ini (mengambil nama file saja)
            $fotoPath = '../../assets/uploads/dosen/' . $fileName;
        } else {
            throw new Exception("Gagal mengupload foto. Cek permission folder.");
        }
    }

    // 5. Update Database
    $dataUpdate = [
        'nama' => $nama,
        'jabatan' => $jabatan,
        'email' => $email,
        'foto_path' => $fotoPath,
        'researchgate_url' => $researchgate,
        'scholar_url' => $scholar,
        'sinta_url' => $sinta,
        'nip' => $nip,
        'prodi' => $prodi,
        'pendidikan' => $pendidikan,
        'sertifikasi' => $currentData['sertifikasi'],
        'mata_kuliah' => $currentData['mata_kuliah']
    ];

    if ($dosenModel->update($nidn, $dataUpdate)) {
        echo json_encode(['status' => 'success', 'message' => 'Profil berhasil diperbarui.']);
    } else {
        throw new Exception("Gagal mengupdate database.");
    }

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>