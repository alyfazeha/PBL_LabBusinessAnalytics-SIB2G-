<?php
require_once __DIR__ . "/../models/Content.php";
session_start();

$model = new Content();

$data = [
    'slug' => $_POST['slug'],
    'title' => $_POST['title'],
    'excerpt' => $_POST['excerpt'],
    'body' => $_POST['body'],
    'category_id' => $_POST['category_id'],
    'featured_image' => $_POST['featured_image'],
    'admin_id' => $_SESSION['user_id']
];

$result = $model->create($data);

echo json_encode([
    'status' => $result
]);
