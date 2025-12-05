<?php
require_once __DIR__ . "/../config/database.php";

class Dosen
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function create($data)
    {
        $sql = "INSERT INTO dosen (
                    nidn, user_id, nama, jabatan, email, foto_path,
                    researchgate_url, scholar_url, sinta_url, 
                    nip, prodi, pendidikan, sertifikasi, mata_kuliah
                ) VALUES (
                    :nidn, :user_id, :nama, :jabatan, :email, :foto_path,
                    :researchgate_url, :scholar_url, :sinta_url,
                    :nip, :prodi, :pendidikan, :sertifikasi, :mata_kuliah
                )";

        $stmt = $this->db->prepare($sql);

        return $stmt->execute([
            ':nidn' => $data['nidn'],
            ':user_id' => $data['user_id'],
            ':nama' => $data['nama'],
            ':jabatan' => $data['jabatan'],
            ':email' => $data['email'],
            ':foto_path' => $data['foto_path'],
            ':researchgate_url' => $data['researchgate_url'],
            ':scholar_url' => $data['scholar_url'],
            ':sinta_url' => $data['sinta_url'],
            ':nip' => $data['nip'],
            ':prodi' => $data['prodi'],
            ':pendidikan' => $data['pendidikan'],
            ':sertifikasi' => $data['sertifikasi'],
            ':mata_kuliah' => $data['mata_kuliah'],
        ]);
    }

    public function all()
    {
        $sql = "SELECT d.*, u.username
                FROM dosen d
                LEFT JOIN users u ON u.user_id = d.user_id
                ORDER BY d.nidn DESC";

        return $this->db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }

    public function find($nidn)
    {
        $sql = "SELECT d.*, u.username
                FROM dosen d
                LEFT JOIN users u ON u.user_id = d.user_id
                WHERE d.nidn = :nidn";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([':nidn' => $nidn]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function update($nidn, $data)
    {
        $sql = "UPDATE dosen SET
                nama = :nama,
                jabatan = :jabatan,
                email = :email,
                foto_path = :foto_path,
                researchgate_url = :researchgate_url,
                scholar_url = :scholar_url,
                sinta_url = :sinta_url,
                nip = :nip,
                prodi = :prodi,
                pendidikan = :pendidikan,
                sertifikasi = :sertifikasi,
                mata_kuliah = :mata_kuliah
                WHERE nidn = :nidn";

        $stmt = $this->db->prepare($sql);

        return $stmt->execute([
            ':nama' => $data['nama'],
            ':jabatan' => $data['jabatan'],
            ':email' => $data['email'],
            ':foto_path' => $data['foto_path'],
            ':researchgate_url' => $data['researchgate_url'],
            ':scholar_url' => $data['scholar_url'],
            ':sinta_url' => $data['sinta_url'],
            ':nip' => $data['nip'],
            ':prodi' => $data['prodi'],
            ':pendidikan' => $data['pendidikan'],
            ':sertifikasi' => $data['sertifikasi'],
            ':mata_kuliah' => $data['mata_kuliah'],
            ':nidn' => $nidn
        ]);
    }

    public function delete($nidn)
    {
        $sql = "DELETE FROM dosen WHERE nidn = :nidn";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([':nidn' => $nidn]);
    }
}
