<?php
require_once __DIR__ . "/../models/Content.php";

$model = new Content();

$content_id = $_POST['content_id'];
$result = $model->delete($content_id);

echo json_encode([
    'status' => $result
]);
