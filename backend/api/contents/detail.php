<?php
require_once __DIR__ . "/../models/Content.php";

$content_id = $_GET['content_id'];

$model = new Content();
$data = $model->find($content_id);

echo json_encode([
    'status' => true,
    'data' => $data
]);
