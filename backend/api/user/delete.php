<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

header("Content-Type: application/json");

require_once __DIR__ . "/../../models/User.php";
require_once __DIR__ . "/../../config/auth.php";

require_admin();

$userModel = new User();
$user_id = $_POST['user_id'] ?? null;

if (!$user_id) {
    http_response_code(400);
    echo json_encode(['error' => 'user_id required']);
    exit;
}

$success = $userModel->delete($user_id);

if ($success) {
    echo json_encode(['success' => true, 'message' => 'User deleted']);
} else {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Failed to delete user']);
}
?>