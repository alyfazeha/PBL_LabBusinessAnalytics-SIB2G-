<?php
header("Content-Type: application/json");
ini_set('display_errors', 0);
error_reporting(E_ALL);

require_once __DIR__ . "/../../config/database.php";
require_once __DIR__ . "/../../models/Mahasiswa.php";
require_once __DIR__ . "/../../config/auth.php";

require_admin();

// --- FUNGSI GENERATE PASSWORD ACAK ---
function generateRandomPassword($length = 8) {
    $chars = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    return substr(str_shuffle($chars), 0, $length);
}

try {
    // 2. Ambil Input
    $nim     = trim($_POST['nim'] ?? "");
    $nama    = trim($_POST['nama'] ?? "");
    $prodi   = trim($_POST['prodi'] ?? "");
    $tingkat = trim($_POST['tingkat'] ?? "");
    $no_hp   = trim($_POST['no_hp'] ?? "");
    $email   = trim($_POST['email'] ?? "");

    // Validasi Dasar
    if ($nim === "" || $nama === "" || $prodi === "" || $tingkat === "") {
        throw new Exception("NIM, Nama, Prodi, dan Tingkat wajib diisi.");
    }

    $db = Database::getInstance();
    $db->beginTransaction(); // Mulai Transaksi

    try {
        $user_id_to_use = null;
        $is_new_account = false;
        
        // --- UBAH DI SINI: Password sekarang otomatis acak 8 karakter ---
        $initial_password = generateRandomPassword(8); 

        // --- A. AUTO CREATE USER (Login Akun) ---
        // Cek apakah akun dengan username NIM sudah ada?
        $stmtCheck = $db->prepare("SELECT user_id FROM users WHERE username = :u LIMIT 1");
        $stmtCheck->execute([':u' => $nim]);
        $existingUser = $stmtCheck->fetch(PDO::FETCH_ASSOC);

        if ($existingUser) {
            // Jika akun sudah ada, pakai ID-nya
            $user_id_to_use = $existingUser['user_id'];
        } else {
            // Jika belum ada, BUAT BARU
            $is_new_account = true;
            $password_hash = password_hash($initial_password, PASSWORD_BCRYPT);
            
            $sqlUser = "INSERT INTO users (username, password_hash, role, email, display_name, created_at) 
                        VALUES (:username, :pass, 'mahasiswa', :email, :nama, NOW())";
            
            $stmtUser = $db->prepare($sqlUser);
            $stmtUser->execute([
                ':username' => $nim,
                ':pass'     => $password_hash,
                ':email'    => $email,
                ':nama'     => $nama
            ]);
            $user_id_to_use = $db->lastInsertId();
        }

        // --- B. CEK DUPLIKAT DATA MAHASISWA ---
        // Pastikan NIM belum terdaftar di tabel mahasiswa
        $stmtCheckMhs = $db->prepare("SELECT nim FROM mahasiswa WHERE nim = :n LIMIT 1");
        $stmtCheckMhs->execute([':n' => $nim]);
        if ($stmtCheckMhs->fetch()) {
            throw new Exception("Data Mahasiswa dengan NIM $nim sudah terdaftar.");
        }

        // --- C. SIMPAN DATA MAHASISWA (UPDATE BAGIAN INI) ---
        // Kita tambahkan 'initial_password' ke query INSERT
        $sqlMhs = "INSERT INTO mahasiswa (
                        nim, user_id, nama, prodi, tingkat, no_hp, email, initial_password
                    ) VALUES (
                        :nim, :user_id, :nama, :prodi, :tingkat, :no_hp, :email, :init_pass
                    )";
        
        $stmtMhs = $db->prepare($sqlMhs);
        $stmtMhs->execute([
            ':nim'      => $nim,
            ':user_id'  => $user_id_to_use, 
            ':nama'     => $nama,
            ':prodi'    => $prodi,
            ':tingkat'  => $tingkat,
            ':no_hp'    => $no_hp,
            ':email'    => $email,
            ':init_pass'=> $initial_password // Simpan password mentah disini
        ]);

        $db->commit();

        // --- Susun Pesan Sukses ---
        $msg = "Mahasiswa berhasil ditambahkan!";
        if ($is_new_account) {
            // Beri tahu admin password defaultnya
            $msg .= " Akun Login dibuat otomatis. Password default: " . $initial_password;
        }

        echo json_encode(['success' => true, 'message' => $msg]);

    } catch (Exception $ex) {
        $db->rollBack();
        throw $ex;
    }

} catch (Exception $e) {
    http_response_code(500);
    $msg = $e->getMessage();
    if (strpos($msg, 'Duplicate entry') !== false) {
        $msg = "Gagal: NIM atau Email sudah digunakan.";
    }
    echo json_encode(['success' => false, 'message' => $msg]);
}
?>