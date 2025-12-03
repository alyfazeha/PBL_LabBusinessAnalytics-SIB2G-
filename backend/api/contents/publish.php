<?php
require_once __DIR__ . "/../models/Content.php";

$model = new Content();

$content_id = $_POST['content_id'];
$status     = $_POST['status']; // true / false

$result = $model->publish($content_id, $status);

echo json_encode([
    'status' => $result
]);
