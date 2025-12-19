<?php
require_once __DIR__ . '/../config/database.php';

class ResearchFocus {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    public function getAll() {
        // Query ambil data fokus riset
        $query = "SELECT focus_id, nama_fokus FROM research_focus ORDER BY nama_fokus ASC";
        $stmt = $this->db->query($query);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>