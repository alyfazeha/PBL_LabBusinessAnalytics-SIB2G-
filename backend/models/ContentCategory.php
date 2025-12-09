<?php
// Panggil database
require_once __DIR__ . '/../config/database.php';

class ContentCategory {
    private $db;

    public function __construct() {
        // Ambil koneksi database yang benar (PDO)
        $this->db = Database::getInstance();
    }

    public function getAll() {
        // PERBAIKAN: Gunakan 'AS' untuk mengubah 'nama' menjadi 'category_name'
        // Sesuaikan 'nama' dengan kolom asli di tabelmu (misal: name, nama_kategori, dsb)
        $query = "SELECT category_id, nama AS category_name FROM content_categories ORDER BY category_id ASC";
        
        $stmt = $this->db->query($query);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>