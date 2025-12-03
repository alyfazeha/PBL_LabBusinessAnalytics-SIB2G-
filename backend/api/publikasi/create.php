<?php
require_once __DIR__ . "/../models/Publikasi.php";
require_once __DIR__ . "/../config/auth.php";
require_role(['admin', 'dosen']);

$model = new Publikasi();

$data = [
    'slug'           => $_POST['slug'],
    'judul'          => $_POST['judul'],
    'abstrak'        => $_POST['abstrak'] ?? null,
    'isi'            => $_POST['isi'] ?? null,
    'kategori_id'    => $_POST['kategori_id'] ?? null,
    'featured_image' => $_POST['featured_image'] ?? null,
    'file_path'      => $_POST['file_path'] ?? null,
    'external_link'  => $_POST['external_link'] ?? null,
    'author_nidn'    => $_POST['author_nidn'],
    'created_by'     => $_POST['created_by']
];

// authors array optional
$authors = isset($_POST['authors']) ? json_decode($_POST['authors'], true) : [];

$pid = $model->create($data, $authors);

echo json_encode([
    'success' => true,
    'publikasi_id' => $pid
]);