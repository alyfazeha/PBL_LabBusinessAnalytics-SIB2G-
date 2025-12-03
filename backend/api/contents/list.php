<?php
require_once __DIR__ . "/../models/Content.php";

$model = new Content();
$data = $model->all();

echo json_encode([
    'status' => true,
    'data' => $data
]);
