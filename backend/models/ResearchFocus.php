<?php
class ResearchFocus {
    private $db;

    public function __construct() {
        global $koneksi;
        $this->db = $koneksi;
    }

    public function getAll() {
        // Sesuai screenshot: kolomnya 'focus_id' dan 'nama_fokus'
        $query = "SELECT focus_id, nama_fokus FROM research_focus ORDER BY nama_fokus ASC";
        $result = pg_query($this->db, $query);
        $data = [];
        while ($row = pg_fetch_assoc($result)) {
            $data[] = $row;
        }
        return $data;
    }
}
?>