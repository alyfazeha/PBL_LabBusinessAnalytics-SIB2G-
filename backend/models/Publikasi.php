<?php
require_once __DIR__ . '/../config/database.php';

class Publikasi {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    // ==========================================================
    // BAGIAN 1: FUNGSI UNTUK PUBLIC
    // ==========================================================

    public function getPublishedOnly() {
        // Query untuk halaman depan (Public)
        // PERUBAHAN: JOIN langsung ke research_focus menggunakan p.focus_id
        $query = "SELECT p.id, p.judul, p.external_link, p.created_at, 
                         k.nama_kategori, 
                         d.nama AS nama_dosen, d.nidn AS dosen_nidn,
                         r.nama_fokus -- Ini sekarang diambil dari inputan publikasi
                  FROM publikasi p
                  LEFT JOIN kategori_publikasi k ON p.kategori_id = k.id
                  LEFT JOIN dosen d ON p.dosen_nidn = d.nidn
                  LEFT JOIN research_focus r ON p.focus_id = r.focus_id -- DIRECT JOIN
                  WHERE p.status = 'published'
                  ORDER BY p.id DESC";

        $stmt = $this->db->query($query);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // ==========================================================
    // BAGIAN 2: FUNGSI UNTUK ADMIN
    // ==========================================================

    public function getAll() {
        // Query untuk Admin Dashboard (Data Publikasi)
        // PERUBAHAN: JOIN langsung ke research_focus menggunakan p.focus_id
        $query = "SELECT p.id, p.judul, p.external_link, p.created_at, p.status,
                         k.nama_kategori, k.id as kategori_id,
                         d.nama AS nama_dosen, d.nidn AS dosen_nidn,
                         r.nama_fokus -- Ini sekarang diambil dari inputan publikasi
                  FROM publikasi p
                  LEFT JOIN kategori_publikasi k ON p.kategori_id = k.id
                  LEFT JOIN dosen d ON p.dosen_nidn = d.nidn
                  LEFT JOIN research_focus r ON p.focus_id = r.focus_id -- DIRECT JOIN
                  ORDER BY p.id DESC";
        
        $stmt = $this->db->query($query);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getById($id) {
        $query = "SELECT * FROM publikasi WHERE id = ?";
        $stmt = $this->db->prepare($query);
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function create($data) {
        // PERUBAHAN: Menambahkan focus_id ke INSERT
        $query = "INSERT INTO publikasi (judul, external_link, kategori_id, dosen_nidn, focus_id, status) 
                  VALUES (?, ?, ?, ?, ?, 'pending') RETURNING id";  
        $params = [
            $data['judul'], 
            $data['external_link'], 
            $data['kategori_id'], 
            $data['dosen_nidn'],
            $data['focus_id'] // Simpan Topik Riset yang dipilih
        ];
        $stmt = $this->db->prepare($query);
        if ($stmt->execute($params)) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            return $row['id'];
        }
        return false;
    }

    public function update($id, $data) {
        // PERUBAHAN: Menambahkan focus_id ke UPDATE
        $query = "UPDATE publikasi 
                  SET judul = ?, external_link = ?, kategori_id = ?, dosen_nidn = ?, focus_id = ?, updated_at = NOW()
                  WHERE id = ?";
        $params = [
            $data['judul'], 
            $data['external_link'], 
            $data['kategori_id'], 
            $data['dosen_nidn'], 
            $data['focus_id'], // Update Topik Riset
            $id
        ];
        $stmt = $this->db->prepare($query);
        return $stmt->execute($params);
    }

    public function changeStatus($id, $status) {
        $query = "UPDATE publikasi SET status = ?, updated_at = NOW() WHERE id = ?";
        $stmt = $this->db->prepare($query);
        return $stmt->execute([$status, $id]);
    }

    public function delete($id) {
        $query = "DELETE FROM publikasi WHERE id = ?";
        $stmt = $this->db->prepare($query);
        return $stmt->execute([$id]);
    }
}
?>