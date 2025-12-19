<?php
ini_set('display_errors', 0);
header('Content-Type: application/json');
require_once __DIR__ . "/../../config/database.php";

try {
    $db = Database::getInstance();
    // Sesuaikan nama tabel dengan database kamu (sarana / ruangan)
    $stmt = $db->query("SELECT * FROM sarana ORDER BY nama_sarana ASC");
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode(['status' => 'success', 'data' => $data]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>