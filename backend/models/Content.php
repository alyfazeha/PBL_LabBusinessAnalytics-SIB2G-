<?php
class Content {
    private $db;

    public function __construct() {
        global $koneksi;
        $this->db = $koneksi;
    }

    // ==========================================
    // BAGIAN 1: PUBLIC
    // ==========================================

    // 1. AMBIL YANG PUBLISHED SAJA (Untuk list_public.php)
    public function getPublishedOnly() {
        // Kolom status diganti jadi 'is_published' (Boolean)
        $query = "SELECT c.*, k.nama as category_name
                  FROM contents c
                  LEFT JOIN content_categories k ON c.category_id = k.category_id
                  WHERE c.is_published = TRUE  
                  ORDER BY c.created_at DESC";

        $result = pg_query($this->db, $query);
        $data = [];
        while ($row = pg_fetch_assoc($result)) {
            $data[] = $row;
        }
        return $data;
    }

    // 2. AMBIL BY SLUG
    public function getBySlug($slug) {
        $query = "SELECT c.*, k.nama as category_name
                  FROM contents c
                  LEFT JOIN content_categories k ON c.category_id = k.category_id
                  WHERE c.slug = $1 AND c.is_published = TRUE"; 
        $result = pg_query_params($this->db, $query, [$slug]);
        return pg_fetch_assoc($result);
    }

    // ==========================================
    // BAGIAN 2: ADMIN
    // ==========================================

    // 3. AMBIL SEMUA
    public function getAll() {
        $query = "SELECT c.*, k.nama as category_name
                  FROM contents c
                  LEFT JOIN content_categories k ON c.category_id = k.category_id
                  ORDER BY c.created_at DESC";

        $result = pg_query($this->db, $query);
        $data = [];
        while ($row = pg_fetch_assoc($result)) {
            $data[] = $row;
        }
        return $data;
    }

    // 4. AMBIL BY ID (Perbaikan: id -> content_id)
    public function getById($id) {
        $query = "SELECT * FROM contents WHERE content_id = $1";
        $result = pg_query_params($this->db, $query, [$id]);
        return pg_fetch_assoc($result);
    }

    // 5. CREATE (Perbaikan: insert is_published & admin_id)
    public function create($data) {
        // Default is_published = FALSE (Pending)
        // Kita set admin_id = 1 dulu (atau ambil dari session jika ada)
        $query = "INSERT INTO contents (title, slug, excerpt, body, category_id, featured_image, is_published, created_at, updated_at, admin_id) 
                  VALUES ($1, $2, $3, $4, $5, $6, FALSE, NOW(), NOW(), 1) RETURNING content_id";
        
        $params = [
            $data['title'],
            $data['slug'],
            $data['excerpt'],
            $data['body'],
            $data['category_id'],
            $data['featured_image']
        ];

        $result = pg_query_params($this->db, $query, $params);
        if ($result) {
            $row = pg_fetch_assoc($result);
            return $row['content_id']; // Return content_id
        }
        return false;
    }

    // 6. UPDATE (Perbaikan: id -> content_id)
    public function update($id, $data) {
        $query = "UPDATE contents 
                  SET title=$1, slug=$2, excerpt=$3, body=$4, category_id=$5, featured_image=$6, updated_at=NOW()
                  WHERE content_id=$7";
        
        $params = [
            $data['title'], $data['slug'], $data['excerpt'], 
            $data['body'], $data['category_id'], $data['featured_image'],
            $id
        ];
        
        return pg_query_params($this->db, $query, $params);
    }

    // 7. UPDATE STATUS PUBLISH (Perbaikan: status -> is_published)
    public function updateStatus($id, $is_published) {
        // $is_published harus TRUE/FALSE
        $query = "UPDATE contents SET is_published = $1, published_at = NOW(), updated_at = NOW() WHERE content_id = $2";
        // Convert ke string 'true'/'false' untuk PostgreSQL
        $val = $is_published ? 'true' : 'false';
        $result = pg_query_params($this->db, $query, [$val, $id]);
        return ($result && pg_affected_rows($result) > 0);
    }

    // 8. DELETE (Perbaikan: id -> content_id)
    public function delete($id) {
        $query = "DELETE FROM contents WHERE content_id = $1";
        return pg_query_params($this->db, $query, [$id]);
    }
}
?>