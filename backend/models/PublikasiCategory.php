<?php
// File: models/PublikasiCategory.php

class PublikasiCategory { // <--- NAMA CLASS DISAMAKAN DENGAN NAMA FILE
    private $db;

    public function __construct() {
        global $koneksi;
        $this->db = $koneksi;
    }

    public function getAll() {
        // Query tetap ke tabel 'kategori_publikasi' (sesuai database)
        $query = "SELECT * FROM kategori_publikasi ORDER BY id ASC";
        $result = pg_query($this->db, $query);
        $data = [];
        while ($row = pg_fetch_assoc($result)) {
            $data[] = $row;
        }
        return $data;
    }
}
?>