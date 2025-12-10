<?php
header('Content-Type: application/json');

require_once __DIR__ . "/../../config/auth.php";
require_once __DIR__ . "/BookingController.php";
require_login_json();

// Tambahkan validasi data POST yang wajib
if (!isset($_POST['sarana_id']) || !isset($_POST['tanggal']) || !isset($_POST['sks']) || !isset($_SESSION['user_id'])) {
    http_response_code(400);
    echo json_encode(["success" => false, "message" => "Data wajib (sarana, tanggal, sks, user login) tidak lengkap."]);
    exit;
}

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

// Kirim response code 400 jika gagal
if (!$response['success']) {
    http_response_code(400);
}

echo json_encode($response);
?>