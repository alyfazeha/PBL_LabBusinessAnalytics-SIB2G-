<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

header("Content-Type: application/json");
ini_set('display_errors', 0);
error_reporting(E_ALL);

try {
    // 1. FIX PATH (Pastikan path ini benar di servermu)
    require_once __DIR__ . "/../../config/database.php";
    require_once __DIR__ . "/../../models/Dosen.php";
    require_once __DIR__ . "/../../config/auth.php";

    // 2. Cek Role Admin
    if (function_exists('require_role')) {
        require_role(['admin']);
    } else {
        // Fallback jika fungsi require_role tidak didefinisikan
        if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
            http_response_code(403);
            throw new Exception("Akses ditolak. Hanya Admin yang dapat menambahkan data Dosen.");
        }
    }

    // Pastikan request adalah POST
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        throw new Exception("Metode request tidak diizinkan.");
    }

    // 3. Ambil Data Input (Semua data dari form)
    $nidn = trim($_POST['nidn'] ?? "");
    $nama = trim($_POST['nama'] ?? "");
    $email = trim($_POST['email'] ?? "");
    $jabatan = trim($_POST['jabatan'] ?? "");
    $researchgate_url = trim($_POST['researchgate_url'] ?? "");
    $scholar_url = trim($_POST['scholar_url'] ?? "");
    $sinta_url = trim($_POST['sinta_url'] ?? "");
    $nip = trim($_POST['nip'] ?? "");
    $prodi = trim($_POST['prodi'] ?? "");
    $pendidikan = trim($_POST['pendidikan'] ?? "");
    $sertifikasi = trim($_POST['sertifikasi'] ?? "");
    $mata_kuliah = trim($_POST['mata_kuliah'] ?? "");

    // Inisialisasi foto_path
    $foto_path = "";

    // ==========================================================
    // LOGIKA PENANGANAN FOTO (FILE UPLOAD ATAU URL LINK)
    // ==========================================================

    // Cek apakah ada file yang diunggah dari input 'foto_file'
    if (isset($_FILES['foto_file']) && $_FILES['foto_file']['error'] === UPLOAD_ERR_OK) {

        // --- KASUS 1: File diunggah ---

        // Tentukan direktori upload (Asumsi 3 tingkat ke atas dari api/dosen/create.php adalah root proyek)
        // Jika struktur Anda berbeda, sesuaikan path ini:
        $uploadDir = __DIR__ . "/../../../public/uploads/dosen_photos/";

        // 1. Buat folder jika belum ada
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        $fileTmpPath = $_FILES['foto_file']['tmp_name'];
        $fileName = $_FILES['foto_file']['name'];
        $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

        // 2. Validasi Tipe File (hanya izinkan gambar)
        if (!in_array($fileExtension, ['jpg', 'jpeg', 'png', 'webp'])) {
            throw new Exception("Tipe file tidak didukung. Harap unggah file gambar (JPG, JPEG, PNG, WEBP).");
        }

        // 3. Buat nama file yang unik (NIDN_timestamp.ext)
        $newFileName = $nidn . '_' . time() . '.' . $fileExtension;
        $destPath = $uploadDir . $newFileName;

        // 4. Pindahkan file dari temp ke folder tujuan
        if (move_uploaded_file($fileTmpPath, $destPath)) {
            // Path yang akan disimpan di database (URL publik relatif)
            // Contoh: /public/uploads/dosen_photos/12345_16788888.jpg
            $foto_path = "/public/uploads/dosen_photos/" . $newFileName;
        } else {
            throw new Exception("Gagal memindahkan file foto ke server. Kode error: " . $_FILES['foto_file']['error']);
        }
    } else if (isset($_POST['foto_path']) && trim($_POST['foto_path']) !== "") {

        // --- KASUS 2: Link URL dimasukkan ---
        $foto_path = trim($_POST['foto_path']);
    }
    // ==========================================================
    // AKHIR LOGIKA FOTO
    // ==========================================================

    // 4. Validasi Dasar
    if (empty($nidn) || empty($nama) || empty($email) || empty($jabatan) || empty($prodi)) {
        http_response_code(400);
        throw new Exception("NIDN, Nama, Email, Jabatan, dan Prodi wajib diisi.");
    }

    // ... (Tambahkan validasi NIDN dan Email jika perlu)

    $dosenModel = new Dosen();
    $db = $dosenModel->getDB();
    $db->beginTransaction();

    try {
        // Logika untuk membuat User (jika belum ada)
        // ... (Logika User/Role dipertahankan dari versi lama create.php)

        // Asumsi: Logika ini ada di file lama Anda untuk mengaitkan Dosen dengan User
        $user_id_to_use = null;

        // 5. Query Database
        // Cek apakah user dengan email tersebut sudah ada
        $sqlUserCheck = "SELECT id FROM users WHERE email = :email";
        $stmtUserCheck = $db->prepare($sqlUserCheck);
        $stmtUserCheck->execute([':email' => $email]);
        $existingUser = $stmtUserCheck->fetch(PDO::FETCH_ASSOC);

        if ($existingUser) {
            // User sudah ada, gunakan ID-nya
            $user_id_to_use = $existingUser['id'];
        } else {
            // User belum ada, buat user baru
            $sqlUser = "INSERT INTO users (email, password, role) VALUES (:email, :password, :role)";
            // Set password default, atau kosongkan jika tidak ada login
            $defaultPasswordHash = password_hash(strtolower(str_replace(' ', '', $nama)) . '123', PASSWORD_DEFAULT);
            $stmtUser = $db->prepare($sqlUser);
            $stmtUser->execute([
                ':email' => $email,
                ':password' => $defaultPasswordHash, // Password default
                ':role' => 'dosen'
            ]);
            $user_id_to_use = $db->lastInsertId();
        }

        // Cek apakah Dosen sudah ada (berdasarkan NIDN)
        $sqlDosenCheck = "SELECT nidn FROM dosen WHERE nidn = :nidn";
        $stmtDosenCheck = $db->prepare($sqlDosenCheck);
        $stmtDosenCheck->execute([':nidn' => $nidn]);
        if ($stmtDosenCheck->fetch()) {
            throw new Exception("Dosen dengan NIDN '{$nidn}' sudah terdaftar.");
        }


        // Masukkan data Dosen
        $sqlDosen = "INSERT INTO dosen 
        (nidn, user_id, nama, jabatan, email, foto_path, researchgate_url, scholar_url, sinta_url, nip, prodi, pendidikan, sertifikasi, mata_kuliah) 
        VALUES 
        (:nidn, :user_id, :nama, :jabatan, :email, :foto_path, :researchgate_url, :scholar_url, :sinta_url, :nip, :prodi, :pendidikan, :sertifikasi, :mata_kuliah)";

        $stmtDosen = $db->prepare($sqlDosen);
        $stmtDosen->execute([
            ':nidn' => $nidn,
            ':user_id' => $user_id_to_use,
            ':nama' => $nama,
            ':jabatan' => $jabatan,
            ':email' => $email,
            ':foto_path' => $foto_path, // Menggunakan path yang sudah ditentukan (file/url)
            ':researchgate_url' => $researchgate_url,
            ':scholar_url' => $scholar_url,
            ':sinta_url' => $sinta_url,
            ':nip' => $nip,
            ':prodi' => $prodi,
            ':pendidikan' => $pendidikan,
            ':sertifikasi' => $sertifikasi,
            ':mata_kuliah' => $mata_kuliah
        ]);

        $db->commit();
        echo json_encode(['success' => true, 'message' => 'Berhasil menyimpan data dosen!']);
    } catch (Exception $ex) {
        $db->rollBack();
        throw $ex;
    }
} catch (Exception $e) {
    // Tangkap semua error dan kirim sebagai JSON
    http_response_code(500);

    $message = $e->getMessage();

    // Tangkap error duplicate entry biar pesannya lebih enak dibaca
    if (strpos($message, 'Duplicate entry') !== false) {
        if (strpos($message, 'users_email_unique') !== false) {
            $message = 'Email ini sudah dipakai oleh user lain.';
        } else if (strpos($message, 'dosen.PRIMARY') !== false || strpos($message, 'dosen_nidn_unique') !== false) {
            $message = 'NIDN ini sudah terdaftar di database Dosen.';
        }
    }

    echo json_encode(['success' => false, 'message' => $message]);
}
