<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

header("Content-Type: application/json");

require_once __DIR__ . "/../../models/User.php";
require_once __DIR__ . "/../../config/auth.php";

require_admin();

$username = trim($_POST['username'] ?? "");
$password = trim($_POST['password'] ?? "");
$email = trim($_POST['email'] ?? "");
$display_name = trim($_POST['display_name'] ?? "");
$role = trim($_POST['role'] ?? "");

if ($username === "" || $password === "" || $role === "") {
    http_response_code(400);
    echo json_encode([
        "success" => false,
        "message" => "Username, password, dan role wajib diisi."
    ]);
    exit;
}

$userModel = new User();

$hash = password_hash($password, PASSWORD_BCRYPT);

$data = [
    "username"      => $username,
    "password_hash" => $hash,
    "role"          => $role,
    "email"         => $email,
    "display_name"  => $display_name
];

$success = $userModel->create($data);

if ($success) {
    echo json_encode(['success' => true, 'message' => 'User created']);
} else {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Failed to create user']);
}
?>