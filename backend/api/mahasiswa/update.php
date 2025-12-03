<?php
header("Content-Type: application/json");
require_once __DIR__ . "/../models/Mahasiswa.php";
require_once __DIR__ . "/../config/auth.php";
require_role2(['admin', 'mahasiswa']);

$mahasiswaModel = new Mahasiswa();

// Validasi harus ada NIM
if (!isset($_POST['nim'])) {
    echo json_encode([
        'success' => false,
        'message' => 'NIM tidak ditemukan'
    ]);
    exit;
}

$nim = $_POST['nim']; // PK Mahasiswa

// Ambil mahasiswa untuk validasi/default values
$currentMahasiswa = $mahasiswaModel->find($nim);

if (!$currentMahasiswa) {
    echo json_encode([
        'success' => false,
        'message' => 'Mahasiswa tidak ditemukan'
    ]);
    exit;
}

// Ambil data baru dari POST. User ID dan NIM tidak diupdate.
$new_nama = trim($_POST['nama'] ?? $currentMahasiswa['nama']);
$new_prodi = trim($_POST['prodi'] ?? $currentMahasiswa['prodi']);
$new_tingkat = trim($_POST['tingkat'] ?? $currentMahasiswa['tingkat']);
$new_no_hp = trim($_POST['no_hp'] ?? $currentMahasiswa['no_hp']);
$new_email = trim($_POST['email'] ?? $currentMahasiswa['email']);


// Data update (sesuai field yang bisa diubah di model::update)
$data = [
    'nama'          => $new_nama,
    'prodi'         => $new_prodi,
    'tingkat'       => $new_tingkat,
    'no_hp'         => $new_no_hp,
    'email'         => $new_email,
];

// Validasi penting
if ($new_nama === "" || $new_prodi === "" || $new_tingkat === "") {
    echo json_encode([
        'success' => false,
        'message' => 'Nama, Prodi, dan Tingkat tidak boleh kosong.'
    ]);
    exit;
}

$success = $mahasiswaModel->update($nim, $data); // Menggunakan Mahasiswa::update($nim, $data)

if ($success) {
    echo json_encode([
        'status' => 'success',
        'message' => 'Mahasiswa berhasil diperbarui'
    ]);
} else {
    echo json_encode([
        'status' => 'error',
        'message' => 'Gagal memperbarui mahasiswa'
    ]);
}
?>