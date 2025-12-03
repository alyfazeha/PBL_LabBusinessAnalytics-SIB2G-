<?php
header("Content-Type: application/json");
require_once __DIR__ . "/../models/Dosen.php";
require_once __DIR__ . "/../config/auth.php";
require_role(['admin', 'dosen']);

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

// Validasi input wajib minimal
if ($nidn === "" || $nip === "" || $user_id === "" || $nama === "" || $jabatan === "" || $email === "" || $prodi === "" || $pendidikan === "" || $mata_kuliah === "" ) {
    echo json_encode([
        "success" => false,
        "message" => "NIDN, NIP, User ID, Nama, Jabatan, Email, Prodi, Pendidikan, dan Mata Kuliah wajib diisi."
    ]);
    exit;
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

echo json_encode([
    'success' => $success,
    'message' => $success ? 'Dosen created' : 'Failed to create dosen'
]);
?>