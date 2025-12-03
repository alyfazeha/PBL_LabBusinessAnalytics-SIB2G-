<?php
header('Content-Type: application/json');

require_once __DIR__ . "/../models/Dosen.php";
require_once __DIR__ . "/../config/auth.php";
require_role(['admin', 'dosen']);

$dosenModel = new Dosen();
$dosen = $dosenModel->all();

echo json_encode($dosen);
?>