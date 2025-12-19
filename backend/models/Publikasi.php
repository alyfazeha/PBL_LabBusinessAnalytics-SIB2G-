<?php
require_once __DIR__ . '/../config/database.php';

class Publikasi {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    // ==========================================================
    // BAGIAN 1: FUNGSI UNTUK PUBLIC (Halaman Depan)
    // ==========================================================
    public function getPublishedOnly() {
        // Mengambil data yang statusnya 'published'
        // Menggunakan LEFT JOIN agar jika kategori/dosen dihapus, data publikasi tetap muncul
        $query = "SELECT p.*, 
                         k.nama_kategori, 
                         d.nama AS nama_dosen, d.nidn AS dosen_nidn,
                         r.nama_fokus 
                  FROM publikasi p
                  LEFT JOIN kategori_publikasi k ON p.kategori_id = k.id
                  LEFT JOIN dosen d ON p.dosen_nidn = d.nidn
                  LEFT JOIN research_focus r ON p.focus_id = r.focus_id 
                  WHERE p.status = 'published'
                  ORDER BY p.id DESC";

        $stmt = $this->db->query($query);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // ==========================================================
    // BAGIAN 2: FUNGSI UNTUK ADMIN (Dashboard)
    // ==========================================================
    public function getAll() {
        // Mengambil SEMUA data (termasuk tahun) untuk tabel Admin
        $query = "SELECT p.*, 
                         k.nama_kategori, 
                         d.nama AS nama_dosen, 
                         r.nama_fokus
                  FROM publikasi p
                  LEFT JOIN kategori_publikasi k ON p.kategori_id = k.id
                  LEFT JOIN dosen d ON p.dosen_nidn = d.nidn
                  LEFT JOIN research_focus r ON p.focus_id = r.focus_id 
                  ORDER BY p.id DESC";
        
        $stmt = $this->db->query($query);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getById($id) {
        $query = "SELECT * FROM publikasi WHERE id = :id";
        $stmt = $this->db->prepare($query);
        $stmt->execute([':id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function create($data) {
        // Pastikan kolom 'tahun' masuk di sini
        $sql = "INSERT INTO publikasi (judul, external_link, kategori_id, dosen_nidn, focus_id, tahun, status, created_at) 
                VALUES (:judul, :link, :kat, :nidn, :focus, :tahun, 'pending', NOW())";
        
        $stmt = $this->db->prepare($sql);
        
        return $stmt->execute([
            ':judul' => $data['judul'],
            ':link'  => $data['external_link'],
            ':kat'   => $data['kategori_id'],
            ':nidn'  => $data['dosen_nidn'],
            ':focus' => $data['focus_id'],
            ':tahun' => $data['tahun'] // Pastikan data ini ada
        ]);
    }

    public function update($id, $data) {
        $sql = "UPDATE publikasi SET 
                judul = :judul,
                external_link = :link,
                kategori_id = :kat,
                focus_id = :focus,
                tahun = :tahun,
                dosen_nidn = :nidn,
                updated_at = NOW()
                WHERE id = :id"; // Pastikan WHERE id = :id

        $stmt = $this->db->prepare($sql);
        
        return $stmt->execute([
            ':judul' => $data['judul'],
            ':link'  => $data['external_link'],
            ':kat'   => $data['kategori_id'],
            ':focus' => $data['focus_id'],
            ':tahun' => $data['tahun'],
            ':nidn'  => $data['dosen_nidn'],
            ':id'    => $id
        ]);
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