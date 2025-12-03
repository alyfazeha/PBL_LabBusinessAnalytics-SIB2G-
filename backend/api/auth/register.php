<?php
header("Content-Type: application/json");
session_start();

require_once __DIR__ . "/../../models/User.php";

$userModel = new User();

$username = trim($_POST['username'] ?? "");
$password = trim($_POST['password'] ?? "");
$email = trim($_POST['email'] ?? "");
$display_name = trim($_POST['display_name'] ?? "");
$role = trim($_POST['role'] ?? "");

// Validasi input
if ($username === "" || $password === "") {
    echo json_encode([
        "success" => false,
        "message" => "Username dan password wajib diisi."
    ]);
    exit;
}

// Role yang boleh dipilih publik
$allowed_roles = ["dosen", "mahasiswa"];

if (!in_array($role, $allowed_roles)) {
    echo json_encode([
        "success" => false,
        "message" => "Role tidak valid."
    ]);
    exit;
}

// Hash password
$hash = password_hash($password, PASSWORD_BCRYPT);

$data = [
    "username"      => $username,
    "password_hash" => $hash,
    "role"          => $role,
    "email"         => $email,
    "display_name"  => $display_name
];

// Create user
$success = $userModel->create($data);

echo json_encode([
    "success" => $success,
    "message" => $success ? "Registrasi berhasil." : "Registrasi gagal."
]);