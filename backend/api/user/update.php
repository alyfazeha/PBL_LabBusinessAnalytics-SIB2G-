<?php
header("Content-Type: application/json");
require_once __DIR__ . "/../config/database.php";
require_once __DIR__ . "/../models/User.php";
require_once __DIR__ . "/../config/auth.php";
require_admin();

$userModel = new User();

// Validasi harus ada ID
if (!isset($_POST['user_id'])) {
    echo json_encode([
        'success' => false,
        'message' => 'User ID tidak ditemukan'
    ]);
    exit;
}

$user_id = $_POST['user_id'];

// Ambil user untuk validasi
$currentUser = $userModel->find($user_id);

if (!$currentUser) {
    echo json_encode([
        'success' => false,
        'message' => 'User tidak ditemukan'
    ]);
    exit;
}

$new_username = trim($_POST['username'] ?? $currentUser['username']);
$new_email = trim($_POST['email'] ?? $currentUser['email']);
$new_role = trim($_POST['role'] ?? $currentUser['role']);
$new_display_name = trim($_POST['display_name'] ?? $currentUser['display_name']);
$new_password = trim($_POST['password'] ?? '');

// Data update dasar
$data = [
    'username'      => $new_username, // Menggunakan variabel yang sudah di-trim
    'email'         => $new_email,
    'role'          => $new_role,
    'display_name'  => $new_display_name ?: null, // Simpan null jika kosong
];

// Validasi penting: Mencegah update username menjadi kosong
if ($new_username === "") {
    echo json_encode([
        'success' => false,
        'message' => 'Username tidak boleh kosong.'
    ]);
    exit;
}

// Jika password diisi → update
if ($new_password !== "") {
    $hashed = password_hash($new_password, PASSWORD_BCRYPT);
    $userModel->updatePassword($user_id, $hashed);
}

$success = $userModel->update($user_id, $data);
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