<?php
header('Content-Type: application/json');

require_once __DIR__ . "/../../config/auth.php";
require_once __DIR__ . "/BookingController.php";

require_admin();

if (!isset($_POST['booking_id']) || !isset($_POST['reason'])) {
    http_response_code(400);
    echo json_encode(["success" => false, "message" => "ID booking dan alasan wajib diisi."]);
    exit;
}

$controller = new BookingController();

$booking_id = $_POST['booking_id'];
$admin_id   = $_SESSION['user_id'];
$reason     = $_POST['reason'];

$response = $controller->rejectBooking($booking_id, $admin_id, $reason);

echo json_encode($response);
?>