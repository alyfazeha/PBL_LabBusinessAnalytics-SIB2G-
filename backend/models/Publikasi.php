<?php
// File: models/Publikasi.php

class Publikasi {
    private $db;

    public function __construct() {
        global $koneksi;
        $this->db = $koneksi;
    }

    // ==========================================================
    // BAGIAN 1: FUNGSI UNTUK PUBLIC (Halaman Depan)
    // ==========================================================

    // 1. Ambil HANYA yang statusnya 'published' (Untuk List Public)
    public function getPublishedOnly() {
        $query = "SELECT p.id, p.judul, p.external_link, p.created_at, 
                         k.nama_kategori, 
                         d.nama AS nama_dosen, d.nidn AS dosen_nidn
                  FROM publikasi p
                  LEFT JOIN kategori_publikasi k ON p.kategori_id = k.id
                  LEFT JOIN dosen d ON p.dosen_nidn = d.nidn
                  WHERE p.status = 'published' -- FILTER STATUS
                  ORDER BY p.id DESC";

        $result = pg_query($this->db, $query);
        $data = [];
        while ($row = pg_fetch_assoc($result)) {
            $data[] = $row;
        }
        return $data;
    }

    // 2. Filter Berdasarkan FOKUS RISET
    public function getByFocusId($focus_id) {
        // Query ini menggabungkan Publikasi -> Dosen -> Dosen Focus
        $query = "SELECT p.id, p.judul, p.external_link, p.created_at, 
                         k.nama_kategori, 
                         d.nama AS nama_dosen
                  FROM publikasi p
                  JOIN dosen d ON p.dosen_nidn = d.nidn
                  JOIN dosen_focus df ON d.nidn = df.nidn
                  LEFT JOIN kategori_publikasi k ON p.kategori_id = k.id
                  
                  WHERE p.status = 'published'      -- Hanya tampilkan yang sudah publish
                  AND df.focus_id = $1              -- Filter berdasarkan ID Fokus (AI, IoT, dll)
                  
                  ORDER BY p.id DESC";

        $result = pg_query_params($this->db, $query, [$focus_id]);
        
        $data = [];
        while ($row = pg_fetch_assoc($result)) {
            $data[] = $row;
        }
        return $data;
    }

    // ==========================================================
    // BAGIAN 2: FUNGSI UNTUK ADMIN (Dashboard)
    // ==========================================================

    // 3. Ambil SEMUA Data (Termasuk yang Pending)
    public function getAll() {
        $query = "SELECT p.id, p.judul, p.external_link, p.created_at, p.status, -- Tambah status
                         k.nama_kategori, k.id as kategori_id,
                         d.nama AS nama_dosen, d.nidn AS dosen_nidn
                  FROM publikasi p
                  LEFT JOIN kategori_publikasi k ON p.kategori_id = k.id
                  LEFT JOIN dosen d ON p.dosen_nidn = d.nidn
                  ORDER BY p.id DESC";
        
        $result = pg_query($this->db, $query);
        $data = [];
        while ($row = pg_fetch_assoc($result)) {
            $data[] = $row;
        }
        return $data;
    }

    // 4. Ambil 1 Data by ID (Untuk Form Edit)
    public function getById($id) {
        $query = "SELECT * FROM publikasi WHERE id = $1";
        $result = pg_query_params($this->db, $query, [$id]);
        return pg_fetch_assoc($result);
    }

    // 5. Tambah Data Baru
    public function create($data) {
        // Default status biasanya 'pending' (diatur di database atau di sini)
        $query = "INSERT INTO publikasi (judul, external_link, kategori_id, dosen_nidn, status) 
                  VALUES ($1, $2, $3, $4, 'pending') RETURNING id";
                  
        $params = [
            $data['judul'], 
            $data['external_link'], 
            $data['kategori_id'], 
            $data['dosen_nidn']
        ];
        
        $result = pg_query_params($this->db, $query, $params);
        if ($result) {
            $row = pg_fetch_assoc($result);
            return $row['id'];
        }
        return false;
    }

    // 6. Update Data
    public function update($id, $data) {
        $query = "UPDATE publikasi 
                  SET judul = $1, external_link = $2, kategori_id = $3, dosen_nidn = $4, updated_at = NOW()
                  WHERE id = $5";
                  
        $params = [
            $data['judul'], 
            $data['external_link'], 
            $data['kategori_id'], 
            $data['dosen_nidn'], 
            $id
        ];
        
        $result = pg_query_params($this->db, $query, $params);
        return $result;
    }

    // 7. Update Status (Khusus Verify Admin)
    public function changeStatus($id, $status) {
        $query = "UPDATE publikasi SET status = $1, updated_at = NOW() WHERE id = $2";
        $result = pg_query_params($this->db, $query, [$status, $id]);
        return ($result && pg_affected_rows($result) > 0);
    }

    // 8. Hapus Data
    public function delete($id) {
        $query = "DELETE FROM publikasi WHERE id = $1";
        $result = pg_query_params($this->db, $query, [$id]);
        return ($result && pg_affected_rows($result) > 0);
    }
}
?>