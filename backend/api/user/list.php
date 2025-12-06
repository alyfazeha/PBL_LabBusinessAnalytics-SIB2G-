<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

header('Content-Type: application/json');
ini_set('display_errors', 0);

require_once __DIR__ . "/../../models/User.php";
require_once __DIR__ . "/../../config/auth.php";

require_admin();

$userModel = new User();
$users = $userModel->all();

if (!$users) $users = [];

echo json_encode($users);
?>