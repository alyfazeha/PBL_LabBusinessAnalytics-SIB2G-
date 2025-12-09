<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

header("Content-Type: application/json");

require_once __DIR__ . "/../../models/Mahasiswa.php";
require_once __DIR__ . "/../../config/auth.php";

// Pastikan require_role2 ada di auth.php
require_role2(['admin', 'mahasiswa']);

$nim     = trim($_POST['nim'] ?? "");
$user_id = trim($_POST['user_id'] ?? "");
$nama    = trim($_POST['nama'] ?? "");
$prodi   = trim($_POST['prodi'] ?? "");
$tingkat = trim($_POST['tingkat'] ?? "");
$no_hp   = trim($_POST['no_hp'] ?? "");
$email   = trim($_POST['email'] ?? "");

if ($nim === "" || $user_id === "" || $nama === "" || $prodi === "" || $tingkat === "") {
    http_response_code(400);
    echo json_encode([
        "success" => false,
        "message" => "NIM, User ID, Nama, Prodi, dan Tingkat wajib diisi."
    ]);
    exit;
}

$mahasiswaModel = new Mahasiswa();

$data = [
    "nim"           => $nim,
    "user_id"       => $user_id,
    "nama"          => $nama,
    "prodi"         => $prodi,
    "tingkat"       => $tingkat,
    "no_hp"         => $no_hp,
    "email"         => $email
];

// Cek apakah create berhasil
if ($mahasiswaModel->create($data)) {
    echo json_encode([
        'success' => true,
        'message' => 'Mahasiswa berhasil dibuat'
    ]);
} else {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Gagal membuat mahasiswa (Mungkin NIM sudah ada)'
    ]);
}
