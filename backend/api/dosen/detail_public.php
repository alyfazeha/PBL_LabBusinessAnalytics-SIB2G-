<?php
// backend/api/dosen/detail_public.php - Endpoint Publik untuk Detail Dosen

// Matikan error HTML agar JSON bersih
ini_set('display_errors', 0);
error_reporting(E_ALL);

header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *"); // Mengizinkan akses dari mana saja
// session_start tidak perlu

try {
    // FIX PATH (Mundur 2 langkah: ../../)
    $path_model = __DIR__ . "/../../models/Dosen.php";

    if (!file_exists($path_model)) throw new Exception("File Dosen.php tidak ditemukan.");

    require_once $path_model;

    // *** TIDAK ADA REQUIRE AUTH/ROLE CHECK DI SINI ***

    // 1. Ambil NIDN dari URL
    $nidn = $_GET['nidn'] ?? null;

    if (!$nidn) {
        throw new Exception('Parameter NIDN tidak ditemukan di URL.');
    }

    // 2. Cari Data
    $dosenModel = new Dosen();
    $dosen = $dosenModel->find($nidn);

    if ($dosen) {
        echo json_encode($dosen);
    } else {
        http_response_code(404);
        echo json_encode(['message' => 'Dosen tidak ditemukan.']);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['message' => $e->getMessage()]);
}
