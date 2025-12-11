<?php
header('Content-Type: application/json');

require_once __DIR__ . "/../../config/auth.php";
require_once __DIR__ . "/BookingController.php";

// --- SECURITY: HANYA ADMIN ---
require_admin(); 

if (!isset($_POST['booking_id'])) {
    http_response_code(400);
    echo json_encode(["success" => false, "message" => "ID booking wajib diisi."]);
    exit;
}

try {
    $controller = new BookingController();
    $booking_id = $_POST['booking_id'];
    $admin_id   = $_SESSION['user_id'];

    $response = $controller->approveBooking($booking_id, $admin_id);

    if (!$response['success']) {
        http_response_code(400);
    }
    echo json_encode($response);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(["success" => false, "message" => $e->getMessage()]);
}
?>