<?php
// Pastikan path ke database.php benar (misal: dari models/ ke config/)
require_once __DIR__ . "/../config/database.php";

// Pastikan path ke model Dosen/Mahasiswa benar
require_once __DIR__ . "/Dosen.php";
require_once __DIR__ . "/Mahasiswa.php";

class User
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    // Fungsi wajib untuk cek duplikasi di register.php
    public function findUserByUsername($username)
    {
        $sql = "SELECT user_id FROM users WHERE username = :u LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['u' => $username]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // FUNGSI CREATE LENGKAP DENGAN TRANSAKSI MULTI-TABEL
    public function create($data)
    {
        $db = $this->db;
        $db->beginTransaction(); // Mulai Transaksi

        try {
            // A. INSERT PERTAMA: KE TABEL USERS 
            $user_sql = "INSERT INTO users (username, password_hash, role, email, display_name)
                    VALUES (:username, :password_hash, :role, :email, :display_name)";

            $user_stmt = $db->prepare($user_sql);
            $user_stmt->execute([
                ':username'      => $data['username'],
                ':password_hash' => $data['password_hash'],
                ':role'          => $data['role'],
                ':email'         => $data['email'],
                ':display_name'  => $data['display_name'] ?? null,
            ]);

            // Ambil ID user yang baru dibuat
            $user_id = $db->lastInsertId();

            if (!$user_id) {
                // Walaupun execute berhasil, tapi ID tidak didapat (kasus jarang)
                throw new Exception("Gagal mendapatkan User ID setelah insert ke users.");
            }

            // B. INSERT KEDUA: KE TABEL DETAIL (DOSEN/MAHASISWA)
            if ($data['role'] === 'dosen') {
                $dosenModel = new Dosen();
                $dosen_data = [
                    'nidn'  => $data['nidn'], // PENTING: data ini harus ada
                    'user_id' => $user_id,
                    'nama'  => $data['display_name'],
                    'email' => $data['email'],
                    // Isi kolom wajib lain dengan default/null agar tidak error NOT NULL
                    'jabatan' => 'Asisten Ahli',
                    'prodi' => 'Teknik Informatika',
                    'foto_path' => null,
                    'nip' => null,
                    'pendidikan' => null,
                    'sertifikasi' => null,
                    'mata_kuliah' => null,
                    'researchgate_url' => null,
                    'scholar_url' => null,
                    'sinta_url' => null,
                ];

                if (!$dosenModel->create($dosen_data)) {
                    // Jika gagal, lempar exception agar di-rollBack
                    throw new Exception("Gagal menyimpan data Dosen detail.");
                }
            } elseif ($data['role'] === 'mahasiswa') {
                $mahasiswaModel = new Mahasiswa();
                $mahasiswa_data = [
                    'nim'     => $data['nim'],
                    'user_id' => $user_id,
                    'nama'    => $data['display_name'],
                    'email'   => $data['email'],
                    'prodi'   => 'Teknik Informatika',
                    'tingkat' => 1,
                    'no_hp'   => null,
                ];

                if (!$mahasiswaModel->create($mahasiswa_data)) {
                    throw new Exception("Gagal menyimpan data Mahasiswa detail.");
                }
            }

            // C. Jika semua sukses, baru Commit
            $db->commit();
            return true;
        } catch (Exception $e) {
            // Jika ada error di salah satu langkah, batalkan semua
            $db->rollBack();
            // error_log("Registrasi Error: " . $e->getMessage()); 
            return false;
        }
    }

    // ... (Lanjutkan dengan fungsi all(), find(), update(), delete(), dll. yang sudah ada)
}
