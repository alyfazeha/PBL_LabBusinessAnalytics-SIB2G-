<?php
session_start();
header('Content-Type: application/json');

// Cek apakah ada session dan rolenya dosen
if (isset($_SESSION['user_id']) && $_SESSION['role'] === 'dosen') {
    echo json_encode([
        'success' => true,
        'status' => 'success',
        'user_id' => $_SESSION['user_id']
    ]);
} else {
    echo json_encode([
        'success' => false,
        'status' => 'error',
        'message' => 'Bukan dosen atau belum login'
    ]);
}
exit;
