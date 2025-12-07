<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

header("Content-Type: application/json");
ini_set('display_errors', 0);
error_reporting(E_ALL);

try {
    // FIX PATH: Gunakan ../../ (Naik 2 level)
    require_once __DIR__ . "/../../models/Dosen.php";
    require_once __DIR__ . "/../../config/auth.php";

    // Cek Role
    if (function_exists('require_role')) {
        require_role(['admin', 'dosen']);
    }

    // Ambil Data Input
    $nidn = trim($_POST['nidn'] ?? "");
    $user_id = trim($_POST['user_id'] ?? "");
    $nama = trim($_POST['nama'] ?? "");
    $jabatan = trim($_POST['jabatan'] ?? "");
    $email = trim($_POST['email'] ?? "");
    $foto_path = trim($_POST['foto_path'] ?? "");
    $researchgate_url = trim($_POST['researchgate_url'] ?? "");
    $scholar_url = trim($_POST['scholar_url'] ?? "");
    $sinta_url = trim($_POST['sinta_url'] ?? "");
    $nip = trim($_POST['nip'] ?? "");
    $prodi = trim($_POST['prodi'] ?? "");
    $pendidikan = trim($_POST['pendidikan'] ?? "");
    $sertifikasi = trim($_POST['sertifikasi'] ?? "");
    $mata_kuliah = trim($_POST['mata_kuliah'] ?? "");

    // Validasi
    if ($nidn === "" || $nip === "" || $user_id === "" || $nama === "" || $jabatan === "" || $email === "" || $prodi === "" || $pendidikan === "" || $mata_kuliah === "" ) {
        throw new Exception("Semua field wajib (NIDN, NIP, User ID, Nama, Jabatan, Email, Prodi, Pendidikan, MK) harus diisi.");
    }

    $dosenModel = new Dosen();

    $data = [
        "nidn" => $nidn,
        "user_id" => $user_id,
        "nama" => $nama,
        "jabatan" => $jabatan,
        "email" => $email,
        "foto_path" => $foto_path,
        "researchgate_url" => $researchgate_url,
        "scholar_url" => $scholar_url,
        "sinta_url" => $sinta_url,
        "nip" => $nip,
        "prodi" => $prodi,
        "pendidikan" => $pendidikan,
        "sertifikasi" => $sertifikasi,
        "mata_kuliah" => $mata_kuliah
    ];

    $success = $dosenModel->create($data);

    if ($success) {
        echo json_encode(['success' => true, 'message' => 'Dosen berhasil ditambahkan']);
    } else {
        throw new Exception("Gagal menyimpan data ke database.");
    }

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>