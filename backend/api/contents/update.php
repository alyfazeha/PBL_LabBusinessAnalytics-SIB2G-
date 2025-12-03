<?php
require_once __DIR__ . "/../models/Content.php";

$model = new Content();

$content_id = $_POST['content_id'];

$data = [
    'title' => $_POST['title'],
    'excerpt' => $_POST['excerpt'],
    'body' => $_POST['body'],
    'category_id' => $_POST['category_id'],
    'featured_image' => $_POST['featured_image']
];

$result = $model->update($content_id, $data);

echo json_encode([
    'status' => $result
]);
