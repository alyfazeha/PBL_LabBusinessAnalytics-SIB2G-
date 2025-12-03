<?php
require_once __DIR__ . "/../models/Publikasi.php";

$model = new Publikasi();
$data = $model->all();

echo json_encode($data);