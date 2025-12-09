<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . "/../../config/auth.php";
require_once __DIR__ . "/BookingController.php"; // Ini tetap satu folder
require_admin();

$controller = new BookingController();

$booking_id = $_POST['booking_id'];
$admin_id   = $_SESSION['user_id'];

$response = $controller->approveBooking($booking_id, $admin_id);

echo json_encode($response);
?>