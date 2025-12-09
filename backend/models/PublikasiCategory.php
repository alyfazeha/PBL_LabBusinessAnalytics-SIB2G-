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
        return $data;<?php
// 1. Panggil file database
require_once __DIR__ . '/../config/database.php';

class PublikasiCategory { 
    private $db;

    public function __construct() {
        // 2. Ambil koneksi PDO dari Database.php
        $this->db = Database::getInstance();
    }

    public function getAll() {
        // 3. Query menggunakan gaya PDO
        $query = "SELECT * FROM kategori_publikasi ORDER BY id ASC";
        
        // Eksekusi Query
        $stmt = $this->db->query($query);
        
        // Ambil semua data sebagai array
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>
}
?>