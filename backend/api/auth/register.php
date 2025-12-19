<?php
header("Content-Type: application/json");
session_start();

// AKTIFKAN DEBUGGING untuk melihat error yang membuat users table gagal terisi
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . "/../../models/User.php";

$userModel = new User();

// Ambil POST body
$username = trim($_POST['username'] ?? "");
$password = trim($_POST['password'] ?? "");
$email = trim($_POST['email'] ?? "");
$display_name = trim($_POST['display_name'] ?? "");
$role = trim($_POST['role'] ?? "");

// TANGKAP INPUT KHUSUS
$nim = trim($_POST['nim'] ?? "");
$nidn = trim($_POST['nidn'] ?? "");

// Validasi input wajib
if ($username === "" || $password === "" || $display_name === "") {
    echo json_encode(["success" => false, "message" => "Username, password, dan nama wajib diisi."]);
    exit;
}

// Validasi Role dan NIM/NIDN
$allowed_roles = ["dosen", "mahasiswa"];
if (!in_array($role, $allowed_roles)) {
    echo json_encode(["success" => false, "message" => "Role tidak valid atau belum dipilih."]);
    exit;
}
if ($role === 'mahasiswa' && $nim === "") {
    echo json_encode(["success" => false, "message" => "NIM wajib diisi untuk mahasiswa."]);
    exit;
}
if ($role === 'dosen' && $nidn === "") {
    echo json_encode(["success" => false, "message" => "NIDN wajib diisi untuk dosen."]);
    exit;
}

// Cek Duplikasi Username
if ($userModel->findUserByUsername($username)) {
    echo json_encode(["success" => false, "message" => "Username sudah digunakan. Silakan pilih username lain."]);
    exit;
}

// Hash password
$hash = password_hash($password, PASSWORD_BCRYPT);

$data = [
    "username"      => $username,
    "password_hash" => $hash,
    "role"          => $role,
    "email"         => $email,
    "display_name"  => $display_name,
    "nim"           => $nim,
    "nidn"          => $nidn // Data NIDN/NIM dikirim ke User Model
];

// Create user
$success = $userModel->create($data);

echo json_encode([
    "success" => $success,
    "message" => $success
        ? "Registrasi berhasil. Silakan login."
        : "Registrasi gagal. Cek duplikasi NIDN/NIM atau log server."
]);
