<?php
require_once __DIR__ . "/../config/database.php";
require_once __DIR__ . "/../models/BlockedDate.php";

session_start();

if ($_SESSION['role'] !== 'admin') {
    die("Akses ditolak.");
}

$db  = new Database();
$conn = $db->getConnection();
$blocked = new BlockedDate($conn);

/* ==========================================================
   SUBMIT BLOKIR
========================================================== */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $blocked->create(
        $_POST['sarana_id'],
        $_POST['start_date'],
        $_POST['end_date'],
        $_POST['start_time'],
        $_POST['end_time'],
        $_POST['reason'],
        $_SESSION['user_id']
    );

    echo "<p style='color: green;'>Tanggal berhasil diblokir.</p>";
}

/* ==========================================================
   GET SARANA
========================================================== */
$q = $conn->query("SELECT * FROM sarana ORDER BY nama_sarana");
$sarana_list = $q->fetchAll(PDO::FETCH_ASSOC);

/* ==========================================================
   GET BLOCKED DATES
========================================================== */
$list = $blocked->getAll();
?>