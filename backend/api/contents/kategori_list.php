<?php

// backend/api/contents/kategori_list.php

ini_set('display_errors', 0);

error_reporting(E_ALL);

header('Content-Type: application/json');
header("Access-Control-Allow-Origin: *");

require_once __DIR__ . "/../../config/database.php";

try {

    $db = Database::getInstance();
    // PERBAIKAN: Gunakan nama tabel 'content_categories' dan urutkan berdasarkan 'nama'

    $query = "SELECT * FROM content_categories ORDER BY nama ASC"; 
    $stmt = $db->query($query);
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(['status' => 'success', 'data' => $data]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);

}

?>



