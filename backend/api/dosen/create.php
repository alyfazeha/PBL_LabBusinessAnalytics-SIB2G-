<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

header("Content-Type: application/json");
ini_set('display_errors', 0);
error_reporting(E_ALL);

// --- FUNGSI GENERATE PASSWORD ACAK (DISALIN DARI MAHASISWA) ---
function generateRandomPassword($length = 8) {
    $chars = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    return substr(str_shuffle($chars), 0, $length);
}

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

    $nip = trim($_POST['nip'] ?? "");
    if ($nip === "" || $nip === "-") {
        $nip = null;
    }

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

    // Ambil Link Foto Manual (jika ada)
    $foto_path_input = trim($_POST['foto_path'] ?? "");

    // ---------------------------------------------------------
    // 2. LOGIKA UPLOAD FOTO 
    // ---------------------------------------------------------
    $final_foto_path = $foto_path_input;

    // Cek apakah ada file baru yang diupload (field name: 'foto_file')
    if (isset($_FILES['foto_file']) && $_FILES['foto_file']['error'] === UPLOAD_ERR_OK) {
        
        $fileUploadInfo = $_FILES['foto_file'];

        // 1. Tentukan direktori upload absolut
        $uploadDir = __DIR__ . '/../../../frontend/assets/uploads/dosen/'; 

        // 2. Pastikan folder ada dan dapat ditulis
        if (!is_dir($uploadDir)) {
            if (!mkdir($uploadDir, 0777, true)) {
                http_response_code(500);
                exit(json_encode(['status' => 'error', 'message' => "Gagal membuat folder upload di: " . $uploadDir . ". Cek izin folder."]));
            }
        }

        $fileNameWithExt = $fileUploadInfo['name'];
        $fileExtension = pathinfo($fileNameWithExt, PATHINFO_EXTENSION);
        $fileExtensionLower = strtolower($fileExtension);
        $allowedTypes = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

        // 3. Cek format file
        if (in_array($fileExtensionLower, $allowedTypes)) {

            // 4. Buat nama file unik
            $newFileName = (empty($nidn) ? 'dosen_' . time() . '_' . uniqid() : $nidn . '_' . time()) . '.' . $fileExtensionLower;
            $targetFile = $uploadDir . $newFileName;

            // 5. Pindahkan file
            if (move_uploaded_file($fileUploadInfo['tmp_name'], $targetFile)) {

                // 6. Simpan path relatif ke database (PATH BERSIH DARI ROOT FRONTEND)
                $final_foto_path = 'assets/uploads/dosen/' . $newFileName; 
            } else {
                // Jika gagal upload, kirim error API
                http_response_code(500);
                exit(json_encode(['status' => 'error', 'message' => 'Gagal memindahkan file upload. Pastikan folder memiliki izin tulis (777).']));
            }
        } else {
            // Jika format tidak didukung, kirim error API
            http_response_code(400);
            exit(json_encode(['status' => 'error', 'message' => 'Format foto tidak didukung.']));
        }
    } else if (isset($_FILES['foto_file']) && $_FILES['foto_file']['error'] !== UPLOAD_ERR_NO_FILE) {
        // Menangani error upload selain file tidak ada (misal ukuran terlalu besar)
        http_response_code(400);
        exit(json_encode(['status' => 'error', 'message' => 'Error upload file: Kode ' . ($_FILES['foto_file']['error']) ]));
    }
    
    // 3. Mulai Transaksi Database
    $db = Database::getInstance();
    $db->beginTransaction();

    try {
        $user_id_to_use = null;
        $is_new_user = false;
        
        // --- BUAT PASSWORD ACAK UNTUK AKUN BARU ---
        $initial_password_text = generateRandomPassword(8); 

        // Cek User Existing
        $stmtCheck = $db->prepare("SELECT user_id FROM users WHERE username = :u LIMIT 1");
        $stmtCheck->execute([':u' => $nidn]);
        $existingUser = $stmtCheck->fetch(PDO::FETCH_ASSOC);

        if ($existingUser) {
            $user_id_to_use = $existingUser['user_id'];
        } else {
            // Logika pembuatan user baru dan password default
            $is_new_user = true;
            $default_password_hash = password_hash($initial_password_text, PASSWORD_BCRYPT);
            
            $sqlUser = "INSERT INTO users (username, password_hash, role, email, display_name, created_at) 
                         VALUES (:username, :pass, 'dosen', :email, :nama, NOW())";
            $stmtUser = $db->prepare($sqlUser);
            $stmtUser->execute([
                ':username' => $nidn,
                ':pass'     => $default_password_hash,
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
                         nip, prodi, pendidikan, sertifikasi, mata_kuliah,
                         initial_password
                    ) VALUES (
                         :nidn, :user_id, :nama, :jabatan, :email, :foto_path,
                         :researchgate_url, :scholar_url, :sinta_url,
                         :nip, :prodi, :pendidikan, :sertifikasi, :mata_kuliah,
                         :init_pass
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
            ':nip' => $nip, 
            ':prodi' => $prodi,
            ':pendidikan' => $pendidikan,
            ':sertifikasi' => $sertifikasi,
            ':mata_kuliah' => $mata_kuliah,
            ':init_pass' => $initial_password_text
        ]);

        $db->commit();
        
        // --- PESAN SUKSES AKHIR ---
        $success_message = 'Berhasil menyimpan data dosen!';
        
        if ($is_new_user) {
            // Memberikan informasi password acak kepada Admin
            $success_message .= " AKUN BARU DIBUAT: Username: $nidn, Password Default: $initial_password_text";
        }
        
        echo json_encode(['success' => true, 'message' => $success_message]);

    } catch (Exception $ex) {
        $db->rollBack();
        http_response_code(500); 
        echo json_encode(['success' => false, 'message' => 'Error Transaksi Database: ' . $ex->getMessage()]);
    }
} catch (Exception $e) {
    $statusCode = http_response_code();
    if ($statusCode === 200) { $statusCode = 500; http_response_code(500); }
    
    $message = 'Error: ' . $e->getMessage();
    if (strpos($message, 'Duplicate entry') !== false || strpos($message, 'dosen_nidn_key') !== false) {
        $message = 'Data duplikat terdeteksi (NIDN, Email, atau NIP sudah ada).';
    }
    
    echo json_encode(['success' => false, 'message' => $message]);
}
?>