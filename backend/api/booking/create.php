<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

header('Content-Type: application/json');

require_once __DIR__ . "/../../config/auth.php";
require_once __DIR__ . "/BookingController.php";

// Pastikan user sudah login (Admin/Dosen/Mahasiswa boleh create)
require_login_json();

if (!isset($_POST['sarana_id']) || !isset($_POST['tanggal']) || !isset($_POST['sks']) || !isset($_SESSION['user_id'])) {
    http_response_code(400);
    echo json_encode(["success" => false, "message" => "Data wajib tidak lengkap."]);
    exit;
}

try {
    $controller = new BookingController();

    $data = [
        'nim'        => $_POST['nim'] ?? null,
        'nidn'       => $_POST['nidn'] ?? null,
        'sarana_id'  => $_POST['sarana_id'],
        'tanggal'    => $_POST['tanggal'],
        'sks'        => (int)$_POST['sks'],
        'start_time' => $_POST['start_time'],
        'end_time'   => $_POST['end_time'],
        'keperluan'  => $_POST['keperluan'],
        'created_by' => $_SESSION['user_id']
    ];

    $response = $controller->createBooking($data);

    if (!$response['success']) {
        http_response_code(400);
    }
    echo json_encode($response);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(["success" => false, "message" => "Server Error: " . $e->getMessage()]);
}
?>