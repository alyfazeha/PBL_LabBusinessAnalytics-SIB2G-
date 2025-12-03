<?php
header("Content-Type: application/json");
session_start();

require_once "../../config/database.php";

// Ambil JSON body
$payload = json_decode(file_get_contents("php://input"), true);

$username = trim($payload['username'] ?? "");
$password = trim($payload['password'] ?? "");

// Validasi awal
if ($username === "" || $password === "") {
    echo json_encode([
        "success" => false,
        "message" => "Username dan password wajib diisi."
    ]);
    exit;
}

// Koneksi database akan mencoba terhubung. 
// Jika gagal, database.php akan menangkap error
$db = new Database();
$conn = $db->getConnection(); 

$sql = "SELECT user_id, username, password_hash, role, display_name 
        FROM users 
        WHERE username = :u
        LIMIT 1";

$stmt = $conn->prepare($sql);
$stmt->execute(['u' => $username]);
$user = $stmt->fetch(PDO::FETCH_ASSOC); // Pastikan mengambil data dalam bentuk array asosiatif

// Username tidak ditemukan
if (!$user) {
    echo json_encode([
        "success" => false,
        "message" => "Username atau password salah."
    ]);
    exit;
}

// Password salah
if (!password_verify($password, $user["password_hash"])) {
    echo json_encode([
        "success" => false,
        "message" => "Username atau password salah."
    ]);
    exit;
}

// Simpan sesi
$_SESSION["user_id"] = $user["user_id"];
$_SESSION["role"] = $user["role"];
$_SESSION["username"] = $user["username"];

// Output JSON ke frontend
echo json_encode([
    "success" => true,
    "message" => "Login berhasil.",
    "data" => [
        "user_id" => $user["user_id"],
        "username" => $user["username"],
        "display_name" => $user["display_name"],
        "role" => $user["role"]
    ]
]);