<?php
header('Content-Type: application/json');

//Menampilkan semua users
require_once __DIR__ . "/../models/User.php";
require_once __DIR__ . "/../config/auth.php";
require_admin();

$userModel = new User();
$users = $userModel->all();

echo json_encode($users);
?>