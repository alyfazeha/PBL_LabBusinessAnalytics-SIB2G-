<?php
header("Content-Type: application/json");
//Menampilkan data user berdasarkan ID
require_once __DIR__ . "/../models/User.php";
require_once __DIR__ . "/../config/auth.php";
require_admin();

$userModel = new User();

$user_id = $_GET['user_id'] ?? null;

if (!$user_id) {
    echo json_encode(['error' => 'user_id required']);
    exit;
}

$user = $userModel->find($user_id);

echo json_encode($user);
?>