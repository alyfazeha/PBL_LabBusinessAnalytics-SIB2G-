<?php
header("Content-Type: application/json");
require_once __DIR__ . "/../models/User.php";
require_once __DIR__ . "/../config/auth.php";
require_admin();

$username = trim($_POST['username'] ?? "");
$password = trim($_POST['password'] ?? "");
$email = trim($_POST['email'] ?? "");
$display_name = trim($_POST['display_name'] ?? "");
$role = trim($_POST['role'] ?? "");

// Validasi input
if ($username === "" || $password === "" || $role === "") {
    echo json_encode([
        "success" => false,
        "message" => "Username, password, dan role wajib diisi."
    ]);
    exit;
}

$userModel = new User();

// Hash password
$hash = password_hash($password, PASSWORD_BCRYPT);

$data = [
    "username"      => $username,
    "password_hash" => $hash,
    "role"          => $role,
    "email"         => $email,
    "display_name"  => $display_name
];

$success = $userModel->create($data);

echo json_encode([
    'success' => $success,
    'message' => $success ? 'User created' : 'Failed to create user'
]);