<?php
require_once __DIR__ . "/../models/User.php";

$userModel = new User();

$data = [
    'username'      => $_POST['username'],
    'password_hash' => password_hash($_POST['password'], PASSWORD_BCRYPT),
    'role'          => $_POST['role'],
    'email'         => $_POST['email'],
    'display_name'  => $_POST['display_name'] ?? null
];

$success = $userModel->create($data);

echo json_encode([
    'success' => $success,
    'message' => $success ? 'User created' : 'Failed to create user'
]);