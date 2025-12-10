<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

header("Content-Type: application/json");
ini_set('display_errors', 0);
error_reporting(E_ALL);

try {
    // FIX PATH: Mundur 2 langkah
    require_once __DIR__ . "/../../config/database.php";
    require_once __DIR__ . "/../../models/Dosen.php";
    require_once __DIR__ . "/../../config/auth.php";

    if (function_exists('require_role')) {
        require_role(['admin']);
    }

    // 1. Ambil Data Text
    $nidn = trim($_POST['nidn'] ?? "");
    $nama = trim($_POST['nama'] ?? "");
    $email = trim($_POST['email'] ?? "");
    $jabatan = trim($_POST['jabatan'] ?? "");
    $researchgate_url = trim($_POST['researchgate_url'] ?? "");
    $scholar_url = trim($_POST['scholar_url'] ?? "");
    $sinta_url = trim($_POST['sinta_url'] ?? "");

    // --- PERBAIKAN NIP ---
    $nip = trim($_POST['nip'] ?? "");
    // Jika NIP kosong atau isinya cuma strip (-), ubah jadi NULL agar tidak error Unique
    if ($nip === "" || $nip === "-") {
        $nip = null;
    }
    // ---------------------

    $prodi = trim($_POST['prodi'] ?? "");
    $pendidikan = trim($_POST['pendidikan'] ?? "");
    $sertifikasi = trim($_POST['sertifikasi'] ?? "");
    $mata_kuliah = trim($_POST['mata_kuliah'] ?? "");

    // Ambil Link Foto Manual (jika ada)
    $foto_path_input = trim($_POST['foto_path'] ?? "");

    // Validasi Wajib
    if ($nidn === "" || $nama === "" || $email === "") {
        throw new Exception("NIDN, Nama, dan Email wajib diisi.");
    }

    // ---------------------------------------------------------
    // 2. LOGIKA UPLOAD FOTO
    // ---------------------------------------------------------
    $final_foto_path = $foto_path_input; // Pertahankan path lama jika tidak ada upload baru

    // Cek apakah ada file baru yang diupload dan tidak ada error
    if (isset($_FILES['foto_file']) && $_FILES['foto_file']['error'] === UPLOAD_ERR_OK) {

        // 1. Tentukan direktori upload absolut menggunakan __DIR__
        // Asumsi: File PHP ini berada 3 level di bawah direktori frontend/assets/uploads/
        $uploadDir = __DIR__ . '/../../../frontend/assets/uploads/dosen/';

        // 2. Pastikan folder ada
        if (!is_dir($uploadDir)) {
            if (!mkdir($uploadDir, 0777, true)) {
                http_response_code(500);
                exit(json_encode(['status' => 'error', 'message' => "Gagal membuat folder upload di: " . $uploadDir]));
            }
        }

        $fileNameWithExt = $_FILES['foto_file']['name'];
        $fileExtension = pathinfo($fileNameWithExt, PATHINFO_EXTENSION);
        $fileExtensionLower = strtolower($fileExtension);

        $allowedTypes = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

        // 3. Cek format file
        if (in_array($fileExtensionLower, $allowedTypes)) {

            // 4. Buat nama file unik (menggunakan NIDN dan timestamp)
            // Pastikan variabel $nidn sudah tersedia
            if (empty($nidn)) {
                // Jika $nidn tidak tersedia, gunakan id unik saja, atau berikan error
                $newFileName = 'dosen_' . time() . '_' . uniqid() . '.' . $fileExtensionLower;
            } else {
                $newFileName = $nidn . '_' . time() . '.' . $fileExtensionLower;
            }

            $targetFile = $uploadDir . $newFileName;

            // 5. Pindahkan file
            if (move_uploaded_file($_FILES['foto_file']['tmp_name'], $targetFile)) {

                // 6. Simpan path yang dapat dibaca frontend ke database
                // Path ini relatif dari root frontend ke file gambar
                $final_foto_path = '../../assets/uploads/dosen/' . $newFileName;
            } else {
                // Jika gagal upload, kirim error API
                http_response_code(500);
                exit(json_encode(['status' => 'error', 'message' => 'Gagal memindahkan file upload. Cek izin folder.']));
            }
        } else {
            // Jika format tidak didukung, kirim error API
            http_response_code(400);
            exit(json_encode(['status' => 'error', 'message' => 'Format foto tidak didukung.']));
        }
    }
    // Variabel $final_foto_path sekarang siap digunakan untuk query database.

    // 3. Mulai Transaksi Database
    $db = Database::getInstance();
    $db->beginTransaction();

    try {
        $user_id_to_use = null;

        // Cek User Existing
        $stmtCheck = $db->prepare("SELECT user_id FROM users WHERE username = :u LIMIT 1");
        $stmtCheck->execute([':u' => $nidn]);
        $existingUser = $stmtCheck->fetch(PDO::FETCH_ASSOC);

        if ($existingUser) {
            $user_id_to_use = $existingUser['user_id'];
        } else {
            $default_password = password_hash("dosen123", PASSWORD_BCRYPT);
            $sqlUser = "INSERT INTO users (username, password_hash, role, email, display_name, created_at) 
                        VALUES (:username, :pass, 'dosen', :email, :nama, NOW())";
            $stmtUser = $db->prepare($sqlUser);
            $stmtUser->execute([
                ':username' => $nidn,
                ':pass'     => $default_password,
                ':email'    => $email,
                ':nama'     => $nama
            ]);
            $user_id_to_use = $db->lastInsertId();
        }

        // Cek Duplikat Dosen
        $stmtCheckDosen = $db->prepare("SELECT nidn FROM dosen WHERE nidn = :n LIMIT 1");
        $stmtCheckDosen->execute([':n' => $nidn]);
        if ($stmtCheckDosen->fetch()) {
            throw new Exception("Gagal: Data Dosen dengan NIDN $nidn sudah ada.");
        }

        // Simpan Data Dosen
        $sqlDosen = "INSERT INTO dosen (
                        nidn, user_id, nama, jabatan, email, foto_path,
                        researchgate_url, scholar_url, sinta_url, 
                        nip, prodi, pendidikan, sertifikasi, mata_kuliah
                    ) VALUES (
                        :nidn, :user_id, :nama, :jabatan, :email, :foto_path,
                        :researchgate_url, :scholar_url, :sinta_url,
                        :nip, :prodi, :pendidikan, :sertifikasi, :mata_kuliah
                    )";

        $stmtDosen = $db->prepare($sqlDosen);
        $stmtDosen->execute([
            ':nidn' => $nidn,
            ':user_id' => $user_id_to_use,
            ':nama' => $nama,
            ':jabatan' => $jabatan,
            ':email' => $email,
            ':foto_path' => $final_foto_path,
            ':researchgate_url' => $researchgate_url,
            ':scholar_url' => $scholar_url,
            ':sinta_url' => $sinta_url,
            ':nip' => $nip, // Ini sekarang bisa NULL
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
    http_response_code(500);
    if (strpos($e->getMessage(), 'Duplicate entry') !== false) {
        // Cek detail error apakah karena email atau NIDN/NIP lain
        echo json_encode(['success' => false, 'message' => 'Data duplikat terdeteksi (NIDN, Email, atau NIP sudah ada).']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
    }
}
