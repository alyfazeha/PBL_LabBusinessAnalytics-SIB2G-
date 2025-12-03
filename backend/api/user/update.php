<?php
require_once "../config/Database.php";
require_once "../models/User.php";

$userModel = new User();

// Validasi harus ada ID
if (!isset($_POST['user_id'])) {
    echo json_encode([
        'status' => 'error',
        'message' => 'User ID tidak ditemukan'
    ]);
    exit;
}

$user_id = $_POST['user_id'];

// Ambil user untuk validasi
$currentUser = $userModel->find($user_id);

if (!$currentUser) {
    echo json_encode([
        'status' => 'error',
        'message' => 'User tidak ditemukan'
    ]);
    exit;
}

// Data update dasar (username, email, role, display_name)
$data = [
    'username'      => $_POST['username'] ?? $currentUser['username'],
    'email'         => $_POST['email'] ?? $currentUser['email'],
    'role'          => $_POST['role'] ?? $currentUser['role'],
    'display_name'  => $_POST['display_name'] ?? $currentUser['display_name'],
];

// Jika password diisi → update
if (!empty($_POST['password'])) {
    $hashed = password_hash($_POST['password'], PASSWORD_BCRYPT);
    $userModel->updatePassword($user_id, $hashed);
}

// Update data lainnya
$updated = $userModel->update($user_id, $data);

if ($updated) {
    echo json_encode([
        'status' => 'success',
        'message' => 'User berhasil diperbarui'
    ]);
} else {
    echo json_encode([
        'status' => 'error',
        'message' => 'Gagal memperbarui user'
    ]);
}