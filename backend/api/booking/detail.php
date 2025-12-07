<?php
require_once __DIR__ . "/../../config/database.php";

$db  = new Database();
$conn = $db->getConnection();

if (!isset($_GET['id'])) {
    die("ID booking tidak ditemukan.");
}

$id = $_GET['id'];

$sql = "SELECT * FROM vw_peminjaman_history WHERE booking_id = :id";
$stmt = $conn->prepare($sql);
$stmt->bindParam(":id", $id);
$stmt->execute();

$data = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$data) {
    die("Data booking tidak ditemukan.");
}

// Disarankan output JSON
echo json_encode($data);
?>