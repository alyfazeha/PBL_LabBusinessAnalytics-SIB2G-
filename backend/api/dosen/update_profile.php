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

    // 2. Cari Data Dosen
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
    $prodi = $_POST['prodi'] ?? $currentData['prodi'];
    $jabatan = $_POST['jabatan'] ?? $currentData['jabatan'];
    $email = $_POST['email'] ?? $currentData['email'];
    $pendidikan = $_POST['pendidikan'] ?? $currentData['pendidikan'];
    $sertifikasi = $_POST['sertifikasi'] ?? $currentData['sertifikasi'];
    
    // Link Eksternal
    $sinta = $_POST['sinta_url'] ?? $currentData['sinta_url'];
    $scholar = $_POST['scholar_url'] ?? $currentData['scholar_url'];
    $researchgate = $_POST['researchgate_url'] ?? $currentData['researchgate_url'];
    $scopus = $_POST['scopus_url'] ?? $currentData['scopus_url'];

    // [FIX PATH] Tetapkan fotoPath = foto lama secara default
    $fotoPath = $currentData['foto_path']; 

    // 4. Proses Upload Foto (jika ada)
    if (isset($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
        
        // --- LOGIKA UPLOAD DAN PATH HARUS SAMA DENGAN CREATE.PHP (KONSISTENSI PATH) ---
        $uploadDir = __DIR__ . "/../../../frontend/assets/uploads/dosen/";
        
        // Pastikan direktori ada
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        // Hapus foto lama jika ada (jika path lama tidak kosong)
        if ($currentData['foto_path']) {
            $oldFileName = basename($currentData['foto_path']); 
            if (file_exists($uploadDir . $oldFileName)) {
                unlink($uploadDir . $oldFileName);
            }
        }

        $fileExt = pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION);
        $fileName = 'dosen_' . $nidn . '_' . time() . '.' . $fileExt;
        $targetFile = $uploadDir . $fileName;

        if (move_uploaded_file($_FILES['foto']['tmp_name'], $targetFile)) {
            // Simpan path yang sama dengan yang dibaca Dosen/Admin
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
        'foto_path' => $fotoPath, // Nilai yang benar dari langkah 4
        'researchgate_url' => $researchgate,
        'scholar_url' => $scholar,
        'sinta_url' => $sinta,
        'scopus_url' => $scopus,
        'nip' => $nip,
        'prodi' => $prodi,
        'pendidikan' => $pendidikan,
        'sertifikasi' => $sertifikasi,
        'mata_kuliah' => $currentData['mata_kuliah']
    ];

    if ($dosenModel->update($nidn, $dataUpdate)) {
        echo json_encode(['status' => 'success', 'message' => 'Profil berhasil diperbarui.']);
    } else {
        throw new Exception("Gagal memperbarui data profil di database.");
    }

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>