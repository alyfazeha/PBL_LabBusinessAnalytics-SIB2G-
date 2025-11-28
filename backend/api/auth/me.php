<?php
header("Content-Type: application/json");
session_start();

// Jika user belum login
if (!isset($_SESSION["user_id"])) {
    echo json_encode([
        "authenticated" => false,
        "message" => "User belum login."
    ]);
    exit;
}

// Jika login → kirim data user
echo json_encode([
    "authenticated" => true,
    "user" => [
        "user_id" => $_SESSION["user_id"],
        "username" => $_SESSION["username"],
        "role" => $_SESSION["role"],
        "display_name" => $_SESSION["display_name"],
        "email" => $_SESSION["email"]
    ]
]);