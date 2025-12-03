<?php
header('Content-Type: application/json');

require_once __DIR__ . "/../models/Mahasiswa.php";
require_once __DIR__ . "/../config/auth.php";
require_admin();

$mahasiswaModel = new Mahasiswa();
$mahasiswa = $mahasiswaModel->all();

echo json_encode($mahasiswa);
?>