<?php
require_once __DIR__ . "/../config/Database.php";

class Publikasi
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    // Create publikasi + authors list
    public function create($data, $authors)
    {
        $sql = "INSERT INTO publikasi 
                (slug, judul, abstrak, isi, kategori_id, featured_image, file_path, external_link, 
                 author_nidn, created_by)
                VALUES 
                (:slug, :judul, :abstrak, :isi, :kategori_id, :featured_image, :file_path, :external_link,
                 :author_nidn, :created_by)
                RETURNING publikasi_id";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':slug'           => $data['slug'],
            ':judul'          => $data['judul'],
            ':abstrak'        => $data['abstrak'],
            ':isi'            => $data['isi'],
            ':kategori_id'    => $data['kategori_id'],
            ':featured_image' => $data['featured_image'],
            ':file_path'      => $data['file_path'],
            ':external_link'  => $data['external_link'],
            ':author_nidn'    => $data['author_nidn'],  // first author
            ':created_by'     => $data['created_by'],
        ]);

        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $publikasi_id = $result['publikasi_id'];

        // Insert multiple authors (if provided)
        if (!empty($authors)) {
            $sqlAuth = "INSERT INTO publication_authors 
                        (publikasi_id, nidn, author_order)
                        VALUES (:pid, :nidn, :order)";

            $stmtAuth = $this->db->prepare($sqlAuth);

            $order = 1;
            foreach ($authors as $nidn) {
                $stmtAuth->execute([
                    ':pid'   => $publikasi_id,
                    ':nidn'  => $nidn,
                    ':order' => $order++
                ]);
            }
        }

        return $publikasi_id;
    }

    public function all()
    {
        $sql = "SELECT p.*, k.nama AS kategori, d.nama AS author_name
                FROM publikasi p
                LEFT JOIN kategori_publikasi k ON k.kategori_id = p.kategori_id
                LEFT JOIN dosen d ON d.nidn = p.author_nidn
                ORDER BY p.publikasi_id DESC";

        return $this->db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }

    public function find($id)
    {
        // Basic info
        $sql = "SELECT p.*, k.nama AS kategori, d.nama AS author_name
                FROM publikasi p
                LEFT JOIN kategori_publikasi k ON k.kategori_id = p.kategori_id
                LEFT JOIN dosen d ON d.nidn = p.author_nidn
                WHERE p.publikasi_id = :id";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id' => $id]);
        $pub = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$pub) return null;

        // Authors list
        $sql2 = "SELECT pa.nidn, d.nama 
                 FROM publication_authors pa
                 LEFT JOIN dosen d ON d.nidn = pa.nidn
                 WHERE pa.publikasi_id = :id
                 ORDER BY pa.author_order ASC";

        $stmt2 = $this->db->prepare($sql2);
        $stmt2->execute([':id' => $id]);

        $pub['authors'] = $stmt2->fetchAll(PDO::FETCH_ASSOC);

        return $pub;
    }

    public function delete($id)
    {
        $sql = "DELETE FROM publikasi WHERE publikasi_id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([':id' => $id]);
    }

    public function verify($id, $user_id, $status)
    {
        $sql = "UPDATE publikasi 
                SET status = :status,
                    verified_by = :uid,
                    verified_at = NOW()
                WHERE publikasi_id = :id";

        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':status' => $status,
            ':uid'    => $user_id,
            ':id'     => $id
        ]);
    }
}