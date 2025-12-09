<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

header("Content-Type: application/json");
ini_set('display_errors', 0);
error_reporting(E_ALL);

try {
    require_once __DIR__ . "/../../models/Dosen.php";
    require_once __DIR__ . "/../../config/auth.php";

    if (function_exists('require_role')) {
        require_role(['admin', 'dosen']);
    }

    $dosenModel = new Dosen();

    if (!isset($_POST['nidn'])) {
        throw new Exception('NIDN tidak ditemukan.');
    }

    $nidn = $_POST['nidn'];
    $currentDosen = $dosenModel->find($nidn);
    if (!$currentDosen) {
        throw new Exception('Data Dosen tidak ditemukan.');
    }

    // --- LOGIKA UPDATE FOTO (YANG DIPERBAIKI) ---
    $final_foto_path = $currentDosen['foto_path'];

    if (isset($_FILES['foto_file']) && $_FILES['foto_file']['error'] === UPLOAD_ERR_OK) {
        $fileTmpPath = $_FILES['foto_file']['tmp_name'];
        $fileName = $_FILES['foto_file']['name'];
        $fileNameCmps = explode(".", $fileName);
        $fileExtension = strtolower(end($fileNameCmps));

        $allowedfileExtensions = array('jpg', 'gif', 'png', 'jpeg', 'webp');
        if (in_array($fileExtension, $allowedfileExtensions)) {
            $newFileName = $nidn . '_' . time() . '.' . $fileExtension;
            
            // PATH DINAMIS (SAMA SEPERTI CREATE)
            $projectRoot = dirname(__DIR__, 3); 
            $uploadFileDir = $projectRoot . '/frontend/assets/uploads/dosen/';
            
            if (!is_dir($uploadFileDir)) {
                if (!mkdir($uploadFileDir, 0777, true)) {
                    throw new Exception("Gagal membuat folder upload.");
                }
            }

            $dest_path = $uploadFileDir . $newFileName;

            if(move_uploaded_file($fileTmpPath, $dest_path)) {
                $final_foto_path = 'assets/uploads/dosen/' . $newFileName;
            } else {
                throw new Exception("Gagal mengupload file foto.");
            }
        }
    } 
    elseif (!empty($_POST['foto_path'])) {
        $final_foto_path = trim($_POST['foto_path']);
    }

    $data = [
        'nama'          => trim($_POST['nama'] ?? $currentDosen['nama']),
        'jabatan'       => trim($_POST['jabatan'] ?? $currentDosen['jabatan']),
        'email'         => trim($_POST['email'] ?? $currentDosen['email']),
        'foto_path'     => $final_foto_path,
        'researchgate_url' => trim($_POST['researchgate_url'] ?? $currentDosen['researchgate_url']),
        'scholar_url'   => trim($_POST['scholar_url'] ?? $currentDosen['scholar_url']),
        'sinta_url'     => trim($_POST['sinta_url'] ?? $currentDosen['sinta_url']),
        'nip'           => trim($_POST['nip'] ?? $currentDosen['nip']),
        'prodi'         => trim($_POST['prodi'] ?? $currentDosen['prodi']),
        'pendidikan'    => trim($_POST['pendidikan'] ?? $currentDosen['pendidikan']),
        'sertifikasi'   => trim($_POST['sertifikasi'] ?? $currentDosen['sertifikasi']),
        'mata_kuliah'   => trim($_POST['mata_kuliah'] ?? $currentDosen['mata_kuliah']),
    ];

    $success = $dosenModel->update($nidn, $data);

    if ($success) {
        echo json_encode(['success' => true, 'message' => 'Dosen berhasil diperbarui']);
    } else {
        throw new Exception('Gagal memperbarui data dosen.');
    }

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>