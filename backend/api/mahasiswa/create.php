<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

header("Content-Type: application/json");
ini_set('display_errors', 0);
error_reporting(E_ALL);

try {
    // 1. FIX PATH (Mundur 2 langkah)
    require_once __DIR__ . "/../../config/database.php";
    require_once __DIR__ . "/../../models/Mahasiswa.php";
    require_once __DIR__ . "/../../config/auth.php";

    // Cek Role (Sesuaikan dengan auth.php kamu)
    if (function_exists('require_role2')) {
        require_role2(['admin']);
    }

    // 2. Ambil Input (Tanpa user_id dari POST)
    $nim     = trim($_POST['nim'] ?? "");
    $nama    = trim($_POST['nama'] ?? "");
    $prodi   = trim($_POST['prodi'] ?? "");
    $tingkat = trim($_POST['tingkat'] ?? "");
    $no_hp   = trim($_POST['no_hp'] ?? "");
    $email   = trim($_POST['email'] ?? "");

    // Validasi: HAPUS pengecekan user_id disini
    if ($nim === "" || $nama === "" || $prodi === "" || $tingkat === "") {
        throw new Exception("NIM, Nama, Prodi, dan Tingkat wajib diisi.");
    }

    $db = Database::getInstance();
    $db->beginTransaction();

    try {
        $user_id_to_use = null;

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
            // Password default: mahasiswa123
            $default_pass = password_hash("mahasiswa123", PASSWORD_BCRYPT);
            
            $sqlUser = "INSERT INTO users (username, password_hash, role, email, display_name, created_at) 
                        VALUES (:username, :pass, 'mahasiswa', :email, :nama, NOW())";
            
            $stmtUser = $db->prepare($sqlUser);
            $stmtUser->execute([
                ':username' => $nim,
                ':pass'     => $default_pass,
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

        // --- C. SIMPAN DATA MAHASISWA ---
        $sqlMhs = "INSERT INTO mahasiswa (
                        nim, user_id, nama, prodi, tingkat, no_hp, email
                   ) VALUES (
                        :nim, :user_id, :nama, :prodi, :tingkat, :no_hp, :email
                   )";
        
        $stmtMhs = $db->prepare($sqlMhs);
        $stmtMhs->execute([
            ':nim'      => $nim,
            ':user_id'  => $user_id_to_use, // ID Otomatis
            ':nama'     => $nama,
            ':prodi'    => $prodi,
            ':tingkat'  => $tingkat,
            ':no_hp'    => $no_hp,
            ':email'    => $email
        ]);

        $db->commit();
        echo json_encode(['success' => true, 'message' => 'Mahasiswa berhasil ditambahkan!']);

    } catch (Exception $ex) {
        $db->rollBack();
        throw $ex;
    }

} catch (Exception $e) {
    http_response_code(500);
    $msg = $e->getMessage();
    // Pesan error duplicate yang lebih rapi
    if (strpos($msg, 'Duplicate entry') !== false) {
        $msg = "Gagal: NIM atau Email sudah digunakan.";
    }
    echo json_encode(['success' => false, 'message' => $msg]);
}
?>