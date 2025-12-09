<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

header('Content-Type: application/json');
ini_set('display_errors', 0); 

require_once __DIR__ . "/../../models/Mahasiswa.php";
require_once __DIR__ . "/../../config/auth.php";

// Pastikan fungsi require_admin() ada di auth.php
require_admin();

$mahasiswaModel = new Mahasiswa();
$data = $mahasiswaModel->all();

// Kirim array kosong jika data tidak ada (biar frontend gak error)
if (!$data) $data = [];

echo json_encode($data);
?>