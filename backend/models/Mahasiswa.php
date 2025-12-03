<?php
require_once __DIR__ . "/../config/Database.php";

class Mahasiswa
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    // Create mahasiswa
    public function create($data)
    {
        $sql = "INSERT INTO mahasiswa (nim, user_id, nama, prodi, tingkat, no_hp, email)
                VALUES (:nim, :user_id, :nama, :prodi, :tingkat, :no_hp, :email)";

        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':nim'    => $data['nim'],
            ':user_id'=> $data['user_id'],
            ':nama'   => $data['nama'],
            ':prodi'  => $data['prodi'],
            ':tingkat'=> $data['tingkat'],
            ':no_hp'  => $data['no_hp'],
            ':email'  => $data['email'],
        ]);
    }

    public function all()
    {
        $sql = "SELECT m.*, u.username, u.email AS user_email
                FROM mahasiswa m
                LEFT JOIN users u ON u.user_id = m.user_id
                ORDER BY m.nim DESC";

        return $this->db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }

    public function find($nim)
    {
        $sql = "SELECT m.*, u.username
                FROM mahasiswa m
                LEFT JOIN users u ON u.user_id = m.user_id
                WHERE m.nim = :nim";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([':nim' => $nim]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function update($nim, $data)
    {
        $sql = "UPDATE mahasiswa
                SET nama = :nama,
                    prodi = :prodi,
                    tingkat = :tingkat,
                    no_hp = :no_hp,
                    email = :email
                WHERE nim = :nim";

        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':nama'   => $data['nama'],
            ':prodi'  => $data['prodi'],
            ':tingkat'=> $data['tingkat'],
            ':no_hp'  => $data['no_hp'],
            ':email'  => $data['email'],
            ':nim'    => $nim
        ]);
    }

    public function delete($nim)
    {
        $sql = "DELETE FROM mahasiswa WHERE nim = :nim";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([':nim' => $nim]);
    }
}
?>