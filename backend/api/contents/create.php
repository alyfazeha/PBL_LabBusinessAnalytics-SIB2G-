<?php
require_once __DIR__ . "/../config/auth.php";
require_once __DIR__ . "/../models/Content.php";

/*
 ✅ Batasi hanya admin
*/
require_role(['admin']);

$model = new Content();

/*
 ✅ Validasi wajib
*/
if (
    empty($_POST['slug']) ||
    empty($_POST['title']) ||
    empty($_POST['body']) ||
    empty($_POST['category_id'])
) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Data wajib tidak boleh kosong'
    ]);
    exit;
}

$data = [
    'slug'           => htmlspecialchars($_POST['slug']),
    'title'          => htmlspecialchars($_POST['title']),
    'excerpt'        => htmlspecialchars($_POST['excerpt'] ?? ''),
    'body'           => $_POST['body'],
    'category_id'    => (int) $_POST['category_id'],
    'featured_image'=> $_POST['featured_image'] ?? null,
    'admin_id'       => $_SESSION['user_id']
];

$result = $model->create($data);

echo json_encode([
    'status' => $result ? 'success' : 'error'
]);
