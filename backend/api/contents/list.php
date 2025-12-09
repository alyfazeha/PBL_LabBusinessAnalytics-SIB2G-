<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

header('Content-Type: application/json');
// UBAH JADI ../../
require_once __DIR__ . "/../../config/koneksi.php";
require_once __DIR__ . "/../../models/Content.php";
require_once __DIR__ . "/../../config/auth.php";

require_role(['admin']);

$model = new Content();
$data = $model->getAll(); // Pastikan fungsi getAll() ada di Models/Content.php
echo json_encode(['status' => 'success', 'data' => $data]);
