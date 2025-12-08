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
    }

    // 3. Ambil Data Input
    $nidn = trim($_POST['nidn'] ?? "");
    $nama = trim($_POST['nama'] ?? "");
    $email = trim($_POST['email'] ?? "");
    $jabatan = trim($_POST['jabatan'] ?? "");
    $foto_path = trim($_POST['foto_path'] ?? "");
    $researchgate_url = trim($_POST['researchgate_url'] ?? "");
    $scholar_url = trim($_POST['scholar_url'] ?? "");
    $sinta_url = trim($_POST['sinta_url'] ?? "");
    $nip = trim($_POST['nip'] ?? "");
    $prodi = trim($_POST['prodi'] ?? "");
    $pendidikan = trim($_POST['pendidikan'] ?? "");
    $sertifikasi = trim($_POST['sertifikasi'] ?? "");
    $mata_kuliah = trim($_POST['mata_kuliah'] ?? "");

    // Validasi Wajib
    if ($nidn === "" || $nama === "" || $email === "") {
        throw new Exception("NIDN, Nama, dan Email wajib diisi.");
    }

    $db = Database::getInstance();
    $db->beginTransaction();

    try {
        $user_id_to_use = null;

        // --- LANGKAH PINTAR: Cek apakah User (NIDN) sudah ada? ---
        $stmtCheck = $db->prepare("SELECT user_id FROM users WHERE username = :u LIMIT 1");
        $stmtCheck->execute([':u' => $nidn]);
        $existingUser = $stmtCheck->fetch(PDO::FETCH_ASSOC);

        if ($existingUser) {
            // KASUS A: User sudah ada (Sisa data lama/memang sudah punya akun)
            // Kita pakai ID yang sudah ada itu.
            $user_id_to_use = $existingUser['user_id'];
        } else {
            // KASUS B: User belum ada -> Kita buatkan baru
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
            // Ambil ID user baru
            $user_id_to_use = $db->lastInsertId();
        }

        // --- CEK DATA DOSEN GANDA ---
        // Jangan sampai kita insert ke tabel dosen jika NIDN itu sudah terdaftar sebagai dosen
        $stmtCheckDosen = $db->prepare("SELECT nidn FROM dosen WHERE nidn = :n LIMIT 1");
        $stmtCheckDosen->execute([':n' => $nidn]);
        if ($stmtCheckDosen->fetch()) {
            throw new Exception("Gagal: Data Dosen dengan NIDN $nidn sudah ada di database.");
        }

        // --- SIMPAN DATA DOSEN ---
        // Kita insert manual query disini agar masuk dalam satu transaksi yang sama
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
            ':user_id' => $user_id_to_use, // Gunakan ID hasil logika pintar tadi
            ':nama' => $nama,
            ':jabatan' => $jabatan,
            ':email' => $email,
            ':foto_path' => $foto_path,
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
    http_response_code(500);
    // Tangkap error duplicate entry biar pesannya lebih enak dibaca
    if (strpos($e->getMessage(), 'Duplicate entry') !== false) {
        if (strpos($e->getMessage(), 'users_email_unique') !== false) {
            echo json_encode(['success' => false, 'message' => 'Email ini sudah dipakai oleh user lain.']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Data duplikat terdeteksi. Cek NIDN/NIP.']);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Error Server: ' . $e->getMessage()]);
    }
}
?>