<?php
require_once __DIR__ . "/../config/Database.php";

class Content
{
    private $db;
    private $table = "contents";

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    // ===========================
    // ✅ ADMIN / DASHBOARD
    // ===========================

    public function all()
    {
        $sql = "SELECT c.*, cc.name AS category_name
                FROM contents c
                LEFT JOIN content_categories cc 
                ON c.category_id = cc.category_id
                ORDER BY c.created_at DESC";

        return $this->db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }

    public function find($content_id)
    {
        $sql = "SELECT * FROM contents WHERE content_id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id' => $content_id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function create($data)
    {
        $sql = "INSERT INTO contents 
                (slug, title, excerpt, body, category_id, featured_image, admin_id)
                VALUES 
                (:slug, :title, :excerpt, :body, :category_id, :featured_image, :admin_id)";

        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':slug' => $data['slug'],
            ':title' => $data['title'],
            ':excerpt' => $data['excerpt'],
            ':body' => $data['body'],
            ':category_id' => $data['category_id'],
            ':featured_image' => $data['featured_image'],
            ':admin_id' => $data['admin_id']
        ]);
    }

    public function update($content_id, $data)
    {
        $sql = "UPDATE contents SET
                title = :title,
                excerpt = :excerpt,
                body = :body,
                category_id = :category_id,
                featured_image = :featured_image,
                updated_at = NOW()
                WHERE content_id = :id";

        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':title' => $data['title'],
            ':excerpt' => $data['excerpt'],
            ':body' => $data['body'],
            ':category_id' => $data['category_id'],
            ':featured_image' => $data['featured_image'],
            ':id' => $content_id
        ]);
    }

    public function publish($content_id, $status)
    {
        $sql = "UPDATE contents 
                SET is_published = :status,
                    published_at = CASE 
                        WHEN :status = true THEN NOW()
                        ELSE NULL
                    END
                WHERE content_id = :id";

        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':status' => $status,
            ':id' => $content_id
        ]);
    }

    public function delete($content_id)
    {
        $sql = "DELETE FROM contents WHERE content_id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([':id' => $content_id]);
    }

    // ===========================
    // ✅ PUBLIK (FRONTEND)
    // ===========================

    public function allPublic()
    {
        $sql = "SELECT c.*, cc.name AS category_name
                FROM contents c
                LEFT JOIN content_categories cc 
                ON c.category_id = cc.category_id
                WHERE c.is_published = true
                ORDER BY c.published_at DESC";

        return $this->db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }

    public function findPublicBySlug($slug)
    {
        $sql = "SELECT c.*, cc.name AS category_name
                FROM contents c
                LEFT JOIN content_categories cc 
                ON c.category_id = cc.category_id
                WHERE c.slug = :slug 
                AND c.is_published = true
                LIMIT 1";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([':slug' => $slug]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}