<?php
header("Content-Type: application/json");
session_start();

// Jika tidak ada user login
if (!isset($_SESSION["user_id"])) {
    echo json_encode([
        "success" => false,
        "message" => "Tidak ada sesi login."
    ]);
    exit;
}

// Hapus semua session
session_unset();
session_destroy();

// Response JSON
echo json_encode([
    "success" => true,
    "message" => "Logout berhasil."
]);
?>