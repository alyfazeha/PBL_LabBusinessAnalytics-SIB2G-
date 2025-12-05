<?php
header("Content-Type: application/json");
require_once __DIR__ . "/../models/Mahasiswa.php";
require_once __DIR__ . "/../config/auth.php";
require_role2(['admin', 'mahasiswa']);

$nim = trim($_POST['nim'] ?? "");
$user_id = trim($_POST['user_id'] ?? ""); // FK dari tabel user
$nama = trim($_POST['nama'] ?? "");
$prodi = trim($_POST['prodi'] ?? "");
$tingkat = trim($_POST['tingkat'] ?? "");
$no_hp = trim($_POST['no_hp'] ?? "");
$email = trim($_POST['email'] ?? "");


// Validasi input wajib
if ($nim === "" || $user_id === "" || $nama === "" || $prodi === "" || $tingkat === "") {
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

$success = $mahasiswaModel->create($data); // Menggunakan Mahasiswa::create()

echo json_encode([
    'success' => $success,
    'message' => $success ? 'Mahasiswa created' : 'Failed to create mahasiswa'
]);
?>