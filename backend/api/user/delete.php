<?php
header("Content-Type: application/json");
require_once __DIR__ . "/../models/User.php";
require_once __DIR__ . "/../config/auth.php";
require_admin();

$userModel = new User();

$user_id = $_POST['user_id'] ?? null;

if (!$user_id) {
    echo json_encode(['error' => 'user_id required']);
    exit;
}

$success = $userModel->delete($user_id);

echo json_encode([
    'success' => $success,
    'message' => $success ? 'User deleted' : 'Failed to delete user'
]);
?>