<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

header("Content-Type: application/json");

require_once __DIR__ . "/../../models/User.php";
require_once __DIR__ . "/../../config/auth.php";

require_admin();

$userModel = new User();

if (!isset($_POST['user_id'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'User ID tidak ditemukan']);
    exit;
}

$user_id = $_POST['user_id'];
$currentUser = $userModel->find($user_id);

if (!$currentUser) {
    http_response_code(404);
    echo json_encode(['success' => false, 'message' => 'User tidak ditemukan']);
    exit;
}

$new_username = trim($_POST['username'] ?? $currentUser['username']);
$new_email = trim($_POST['email'] ?? $currentUser['email']);
$new_role = trim($_POST['role'] ?? $currentUser['role']);
$new_display_name = trim($_POST['display_name'] ?? $currentUser['display_name']);
$new_password = trim($_POST['password'] ?? '');

$data = [
    'username'      => $new_username,
    'email'         => $new_email,
    'role'          => $new_role,
    'display_name'  => $new_display_name ?: null,
];

if ($new_username === "") {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Username tidak boleh kosong.']);
    exit;
}

if ($new_password !== "") {
    $hashed = password_hash($new_password, PASSWORD_BCRYPT);
    $userModel->updatePassword($user_id, $hashed);
}

// Eksekusi Update
$success = $userModel->update($user_id, $data);

// PERBAIKAN BUG: Gunakan $success (bukan $updated)
if ($success) {
    echo json_encode(['status' => 'success', 'message' => 'User berhasil diperbarui']);
} else {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Gagal memperbarui user']);
}
?>