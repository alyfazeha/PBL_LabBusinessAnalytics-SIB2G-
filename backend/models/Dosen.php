<?php
require_once __DIR__ . "/../config/database.php";

class Dosen
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    // --- BAGIAN INI TIDAK DIUBAH (SAMA PERSIS DENGAN KODE ASLI) ---
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

    // --- BAGIAN INI DIMODIFIKASI SEDIKIT (AMAN) ---
    // Ditambahkan JOIN ke research_focus agar Fitur Publikasi bisa filter dosen
    public function all()
    {
        // Tetap mengambil d.* (semua data dosen) agar halaman Dosen tidak error
        // Ditambah r.nama_fokus untuk keperluan halaman Publikasi
        $sql = "SELECT d.*, u.username, r.nama_fokus
                FROM dosen d
                LEFT JOIN users u ON u.user_id = d.user_id
                LEFT JOIN dosen_focus df ON d.nidn = df.nidn      
                LEFT JOIN research_focus r ON df.focus_id = r.focus_id 
                ORDER BY d.nidn DESC";

        return $this->db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }

    // --- BAGIAN INI TIDAK DIUBAH (SAMA PERSIS DENGAN KODE ASLI) ---
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

    // --- BAGIAN INI TIDAK DIUBAH (SAMA PERSIS DENGAN KODE ASLI) ---
    // Kolom pendidikan, mata_kuliah, dll TETAP ADA
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
                scopus_url = :scopus_url,  -- WAJIB DITAMBAHKAN
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
            ':scopus_url' => $data['scopus_url'],  // WAJIB DITAMBAHKAN
            ':nip' => $data['nip'],
            ':prodi' => $data['prodi'],
            ':pendidikan' => $data['pendidikan'],
            ':sertifikasi' => $data['sertifikasi'],
            ':mata_kuliah' => $data['mata_kuliah'],
            ':nidn' => $nidn
        ]);
    }
    // --- BAGIAN INI DIMODIFIKASI SEDIKIT (AMAN) ---
    public function delete($nidn)
    {
        // 1. Hapus dulu relasi di dosen_focus agar tidak error database
        $sqlFocus = "DELETE FROM dosen_focus WHERE nidn = :nidn";
        $stmtFocus = $this->db->prepare($sqlFocus);
        $stmtFocus->execute([':nidn' => $nidn]);

        // 2. Baru hapus data dosen (Kode Asli)
        $sql = "DELETE FROM dosen WHERE nidn = :nidn";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([':nidn' => $nidn]);
    }
}
?>