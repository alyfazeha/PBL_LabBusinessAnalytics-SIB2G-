<?php
//Menampilkan semua users
require_once __DIR__ . "/../models/User.php";

$userModel = new User();
$users = $userModel->all();

header('Content-Type: application/json');
echo json_encode($users);
