<?php
// 1. Panggil database.php
require_once __DIR__ . '/../config/database.php'; 

class Content {
    private $db;

    public function __construct() {
        // 2. Ambil koneksi menggunakan Singleton dari database.php
        $this->db = Database::getInstance();
    }

    // ==========================================
    // BAGIAN 1: PUBLIC
    // ==========================================

    public function getPublishedOnly() {
        $query = "SELECT c.*, k.nama as category_name
                  FROM contents c
                  LEFT JOIN content_categories k ON c.category_id = k.category_id
                  WHERE c.is_published = TRUE  
                  ORDER BY c.created_at DESC";

        // Ganti pg_query dengan PDO query
        $stmt = $this->db->query($query);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getBySlug($slug) {
        // Ganti $1 menjadi ? (Standar PDO)
        $query = "SELECT c.*, k.nama as category_name
                  FROM contents c
                  LEFT JOIN content_categories k ON c.category_id = k.category_id
                  WHERE c.slug = ? AND c.is_published = TRUE";
        
        $stmt = $this->db->prepare($query);
        $stmt->execute([$slug]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // ==========================================
    // BAGIAN 2: ADMIN
    // ==========================================

    public function getAll() {
        $query = "SELECT c.*, k.nama as category_name
                  FROM contents c
                  LEFT JOIN content_categories k ON c.category_id = k.category_id
                  ORDER BY c.created_at DESC";

        $stmt = $this->db->query($query);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getById($id) {
        $query = "SELECT * FROM contents WHERE content_id = ?";
        $stmt = $this->db->prepare($query);
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function create($data) {
        // PDO menggunakan ? untuk parameter binding
        $query = "INSERT INTO contents (title, slug, excerpt, body, category_id, featured_image, is_published, created_at, updated_at, admin_id) 
                  VALUES (?, ?, ?, ?, ?, ?, FALSE, NOW(), NOW(), 1) RETURNING content_id";
        
        $params = [
            $data['title'],
            $data['slug'],
            $data['excerpt'],
            $data['body'],
            $data['category_id'],
            $data['featured_image']
        ];

        $stmt = $this->db->prepare($query);
        if ($stmt->execute($params)) {
            // Ambil ID yang di-return
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            return $row['content_id'];
        }
        return false;
    }

    public function update($id, $data) {
        $query = "UPDATE contents 
                  SET title=?, slug=?, excerpt=?, body=?, category_id=?, featured_image=?, updated_at=NOW()
                  WHERE content_id=?";
        
        $params = [
            $data['title'], $data['slug'], $data['excerpt'], 
            $data['body'], $data['category_id'], $data['featured_image'],
            $id
        ];
        
        $stmt = $this->db->prepare($query);
        return $stmt->execute($params);
    }

    public function updateStatus($id, $is_published) {
        $query = "UPDATE contents SET is_published = ?, published_at = NOW(), updated_at = NOW() WHERE content_id = ?";
        
        // PDO Boolean handling
        $val = $is_published ? 'true' : 'false';
        
        $stmt = $this->db->prepare($query);
        return $stmt->execute([$val, $id]);
    }

    public function delete($id) {
        $query = "DELETE FROM contents WHERE content_id = ?";
        $stmt = $this->db->prepare($query);
        return $stmt->execute([$id]);
    }
}
?>