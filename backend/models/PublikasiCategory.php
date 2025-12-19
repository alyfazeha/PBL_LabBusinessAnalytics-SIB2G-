<?php
require_once __DIR__ . '/../config/database.php';

class PublikasiCategory {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    public function getAll() {
        // Query ambil kategori
        $query = "SELECT * FROM kategori_publikasi ORDER BY id ASC";
        $stmt = $this->db->query($query);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>