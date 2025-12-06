<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . "/BookingController.php";

$controller = new BookingController();

$data = [
    'nim'        => $_POST['nim'] ?? null,
    'nidn'       => $_POST['nidn'] ?? null,
    'sarana_id'  => $_POST['sarana_id'],
    'tanggal'    => $_POST['tanggal'],
    'sks'        => $_POST['sks'],
    'start_time' => $_POST['start_time'],
    'end_time'   => $_POST['end_time'],
    'keperluan'  => $_POST['keperluan'],
    'created_by' => $_SESSION['user_id']
];

$response = $controller->createBooking($data);

echo json_encode($response);
?>