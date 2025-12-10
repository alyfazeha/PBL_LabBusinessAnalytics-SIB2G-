<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }

require_once __DIR__ . "/../../config/auth.php";
require_once __DIR__ . "/BookingController.php";

// PERBAIKAN: Gunakan format standar require_role
if (function_exists('require_role')) {
    require_role(['admin']);
}

$controller = new BookingController();

$booking_id = $_POST['booking_id'];
$admin_id   = $_SESSION['user_id'];
$reason     = $_POST['reason'];

$response = $controller->rejectBooking($booking_id, $admin_id, $reason);

echo json_encode($response);
?>