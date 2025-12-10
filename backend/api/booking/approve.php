<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

header('Content-Type: application/json');

require_once __DIR__ . "/../../config/auth.php";
require_once __DIR__ . "/BookingController.php"; // Ini tetap satu folder
require_admin();

if (!isset($_POST['booking_id'])) {
    http_response_code(400);
    echo json_encode(["success" => false, "message" => "ID booking wajib diisi."]);
    exit;
}

$controller = new BookingController();

$booking_id = $_POST['booking_id'];
$admin_id   = $_SESSION['user_id'];

$response = $controller->approveBooking($booking_id, $admin_id);

// Kirim response code 400 jika gagal
if (!$response['success']) {
    http_response_code(400);
}

echo json_encode($response);
?>