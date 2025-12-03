<?php
require_once __DIR__ . "/BookingController.php";
require_once __DIR__ . "/../config/auth.php";
require_admin();

session_start();

$controller = new BookingController();

$booking_id = $_POST['booking_id'];
$admin_id   = $_SESSION['user_id'];

$response = $controller->approveBooking($booking_id, $admin_id);

echo json_encode($response);