<?php
header('Content-Type: application/json');

require_once __DIR__ . "/../../config/auth.php";
require_once __DIR__ . "/BookingController.php";

// Pastikan user sudah login (Admin/Dosen/Mahasiswa boleh create)
require_role2(['admin', 'mahasiswa']);

// --- REVISI LOGIKA VALIDASI BARU ---
$required_fields = ['sarana_id', 'tanggal', 'nidn', 'nim', 'sks', 'start_time', 'keperluan'];

foreach ($required_fields as $field) {
    if (!isset($_POST[$field]) || empty($_POST[$field])) {
        http_response_code(400);
        echo json_encode(["success" => false, "message" => "Semua field wajib diisi!"]);
        exit;
    }
}

// Cek SKS harus positif
if ((int)$_POST['sks'] <= 1) {
    http_response_code(400);
    echo json_encode(["success" => false, "message" => "Jumlah SKS harus lebih dari 1."]);
    exit;
}

try {
    $controller = new BookingController();

    $data = [
        'nim'        => $_POST['nim'],
        'nidn'       => $_POST['nidn'],
        'sarana_id'  => $_POST['sarana_id'],
        'tanggal'    => $_POST['tanggal'],
        'sks'        => (int)$_POST['sks'],
        'start_time' => $_POST['start_time'],
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