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
    $final_foto_path = $foto_path_input;

    if (isset($_FILES['foto_file']) && $_FILES['foto_file']['error'] === UPLOAD_ERR_OK) {
        $fileTmpPath = $_FILES['foto_file']['tmp_name'];
        $fileName = $_FILES['foto_file']['name'];
        $fileNameCmps = explode(".", $fileName);
        $fileExtension = strtolower(end($fileNameCmps));

        $allowedfileExtensions = array('jpg', 'gif', 'png', 'jpeg', 'webp');
        if (in_array($fileExtension, $allowedfileExtensions)) {
            $newFileName = $nidn . '_' . time() . '.' . $fileExtension;

            // Path Dinamis ke Root Project
            $projectRoot = dirname(__DIR__, 3); 
            $uploadFileDir = $projectRoot . '/frontend/assets/uploads/dosen/';
            
            if (!is_dir($uploadFileDir)) {
                if (!mkdir($uploadFileDir, 0777, true)) {
                    throw new Exception("Gagal membuat folder upload di: " . $uploadFileDir);
                }
            }

            $dest_path = $uploadFileDir . $newFileName;

            if(move_uploaded_file($fileTmpPath, $dest_path)) {
                $final_foto_path = 'assets/uploads/dosen/' . $newFileName;
            } else {
                throw new Exception("Gagal memindahkan file. Cek izin folder.");
            }
        } else {
            throw new Exception("Format foto tidak didukung.");
        }
    }

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
?>