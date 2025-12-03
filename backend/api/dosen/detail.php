<?php
header("Content-Type: application/json");
require_once __DIR__ . "/../models/Dosen.php";
require_once __DIR__ . "/../config/auth.php";
require_role(['admin', 'dosen']);

$dosenModel = new Dosen();

$nidn = $_GET['nidn'] ?? null;

if (!$nidn) {
    echo json_encode(['error' => 'NIDN required']);
    exit;
}

$dosen = $dosenModel->find($nidn);

echo json_encode($dosen);
?>