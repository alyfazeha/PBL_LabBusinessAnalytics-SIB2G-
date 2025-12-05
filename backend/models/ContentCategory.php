<?php
class ContentCategory {
    private $db;

    public function __construct() {
        global $koneksi;
        $this->db = $koneksi;
    }

    public function getAll() {
        // Mengambil category_id dan nama
        $query = "SELECT * FROM content_categories ORDER BY category_id ASC";
        $result = pg_query($this->db, $query);
        $data = [];
        while ($row = pg_fetch_assoc($result)) {
            $data[] = $row;
        }
        return $data;
    }
}
?>