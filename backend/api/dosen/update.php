<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

header("Content-Type: application/json");
ini_set('display_errors', 0);
error_reporting(E_ALL);

try {
    // FIX PATH: Mundur 2 langkah (Perlu ditambahkan lagi di sini)
    require_once __DIR__ . "/../../config/database.php";
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

    // --- NORMALISASI NIP DI SINI ---
    $input_nip = trim($_POST['nip'] ?? $currentDosen['nip']);
    // Jika input NIP kosong, strip (-), atau sama dengan string 'NULL', set ke NULL
    if ($input_nip === "" || $input_nip === "-" || strtoupper($input_nip) === 'NULL') {
        $normalized_nip = null;
    } else {
        $normalized_nip = $input_nip;
    }
    // -----------------------------


    // --- LOGIKA UPDATE FOTO (VERSI FULL FIX) ---
    $final_foto_path = $currentDosen['foto_path']; // Default: Gunakan path yang sudah ada di DB

    // 1. Prioritas Utama: Cek jika ada upload FILE fisik baru
    if (isset($_FILES['foto_file']) && $_FILES['foto_file']['error'] === UPLOAD_ERR_OK) {
        $fileTmpPath = $_FILES['foto_file']['tmp_name'];
        $fileName = $_FILES['foto_file']['name'];
        $fileNameCmps = explode(".", $fileName);
        $fileExtension = strtolower(end($fileNameCmps));

        $allowedfileExtensions = array('jpg', 'gif', 'png', 'jpeg', 'webp');
        if (in_array($fileExtension, $allowedfileExtensions)) {
            $newFileName = $nidn . '_' . time() . '.' . $fileExtension;

            $projectRoot = dirname(__DIR__, 3);
            $uploadFileDir = $projectRoot . '/frontend/assets/uploads/dosen/';

            if (!is_dir($uploadFileDir)) {
                mkdir($uploadFileDir, 0777, true);
            }

            $dest_path = $uploadFileDir . $newFileName;

            if (move_uploaded_file($fileTmpPath, $dest_path)) {
                $final_foto_path = 'assets/uploads/dosen/' . $newFileName;
            }
        }
    }
    // 2. Prioritas Kedua: Jika tidak ada file, cek apakah ada input URL baru dan TIDAK KOSONG
    elseif (isset($_POST['foto_path']) && trim($_POST['foto_path']) !== "") {
        $final_foto_path = trim($_POST['foto_path']);
    }

    // JIKA KEDUANYA KOSONG:
    // $final_foto_path akan tetap berisi nilai dari $currentDosen['foto_path']
    // Sehingga foto lama TIDAK AKAN TERHAPUS.

    // --- DATA FINAL UNTUK UPDATE ---
    $data = [
        'nama'             => trim($_POST['nama'] ?? $currentDosen['nama']),
        'jabatan'          => trim($_POST['jabatan'] ?? $currentDosen['jabatan']),
        'email'            => trim($_POST['email'] ?? $currentDosen['email']),
        'foto_path'        => $final_foto_path,
        'researchgate_url' => trim($_POST['researchgate_url'] ?? $currentDosen['researchgate_url']),
        'scholar_url'      => trim($_POST['scholar_url'] ?? $currentDosen['scholar_url']),
        'sinta_url'        => trim($_POST['sinta_url'] ?? $currentDosen['sinta_url']),
        'nip'              => $normalized_nip, // MENGGUNAKAN NIP YANG SUDAH DINORMALISASI
        'prodi'            => trim($_POST['prodi'] ?? $currentDosen['prodi']),
        'pendidikan'       => trim($_POST['pendidikan'] ?? $currentDosen['pendidikan']),
        'sertifikasi'      => trim($_POST['sertifikasi'] ?? $currentDosen['sertifikasi']),
        'mata_kuliah'      => trim($_POST['mata_kuliah'] ?? $currentDosen['mata_kuliah']),
    ];

    $success = $dosenModel->update($nidn, $data);

    if ($success) {
        echo json_encode(['success' => true, 'message' => 'Dosen berhasil diperbarui']);
    } else {
        throw new Exception('Gagal memperbarui data dosen.');
    }
} catch (Exception $e) {
    http_response_code(500);
    // Tambahkan penanganan untuk duplikasi NIP yang diisi
    if (strpos($e->getMessage(), 'Duplicate entry') !== false && strpos($e->getMessage(), 'nip') !== false) {
        $message = 'Gagal: NIP yang Anda masukkan sudah digunakan Dosen lain.';
    } else {
        $message = $e->getMessage();
    }

    echo json_encode(['success' => false, 'message' => $message]);
}
