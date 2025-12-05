<?php
header("Content-Type: application/json");
session_start();

require_once __DIR__ . "/../../config/database.php";

// Ambil POST body
$username = trim($_POST['username'] ?? "");
$password = trim($_POST['password'] ?? "");

// Validasi awal
if ($username === "" || $password === "") {
    echo json_encode([
        "success" => false,
        "message" => "Username dan password wajib diisi."
    ]);
    exit;
}

// Koneksi database menggunakan Singleton
$conn = Database::getInstance();

$sql = "SELECT user_id, username, password_hash, role, email, display_name 
        FROM users 
        WHERE username = :u
        LIMIT 1";

$stmt = $conn->prepare($sql);
$stmt->execute(['u' => $username]);
$user = $stmt->fetch(PDO::FETCH_ASSOC); // mengambil data dalam bentuk array asosiatif

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
$_SESSION["display_name"] = $user["display_name"];
$_SESSION["email"] = $user["email"];

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
