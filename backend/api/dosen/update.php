<?php
header("Content-Type: application/json");
require_once __DIR__ . "/../models/Dosen.php";
require_once __DIR__ . "/../config/auth.php";
require_role(['admin', 'dosen']); 

$dosenModel = new Dosen();

// Validasi harus ada NIDN
if (!isset($_POST['nidn'])) {
    echo json_encode([
        'success' => false,
        'message' => 'NIDN tidak ditemukan'
    ]);
    exit;
}

$nidn = $_POST['nidn']; // PK Dosen

// Ambil dosen untuk validasi/default values
$currentDosen = $dosenModel->find($nidn);

if (!$currentDosen) {
    echo json_encode([
        'success' => false,
        'message' => 'Dosen tidak ditemukan'
    ]);
    exit;
}

// Ambil data baru dari POST.
$new_nama = trim($_POST['nama'] ?? $currentDosen['nama']);
$new_jabatan = trim($_POST['jabatan'] ?? $currentDosen['jabatan']);
$new_email = trim($_POST['email'] ?? $currentDosen['email']);
$new_foto_path = trim($_POST['foto_path'] ?? $currentDosen['foto_path']);
$new_researchgate_url = trim($_POST['researchgate_url'] ?? $currentDosen['researchgate_url']);
$new_scholar_url = trim($_POST['scholar_url'] ?? $currentDosen['scholar_url']);
$new_sinta_url = trim($_POST['sinta_url'] ?? $currentDosen['sinta_url']);
$new_nip = trim($_POST['nip'] ?? $currentDosen['nip']);
$new_prodi = trim($_POST['prodi'] ?? $currentDosen['prodi']);
$new_pendidikan = trim($_POST['pendidikan'] ?? $currentDosen['pendidikan']);
$new_sertifikasi = trim($_POST['sertifikasi'] ?? $currentDosen['sertifikasi']);
$new_mata_kuliah = trim($_POST['mata_kuliah'] ?? $currentDosen['mata_kuliah']);

// Data update
$data = [
    'nama' => $new_nama,
    'jabatan' => $new_jabatan,
    'email' => $new_email,
    'foto_path' => $new_foto_path,
    'researchgate_url' => $new_researchgate_url,
    'scholar_url' => $new_scholar_url,
    'sinta_url' => $new_sinta_url,
    'nip' => $new_nip,
    'prodi' => $new_prodi,
    'pendidikan' => $new_pendidikan,
    'sertifikasi' => $new_sertifikasi,
    'mata_kuliah' => $new_mata_kuliah,
];

// Validasi penting
if ($new_jabatan === "" || $new_prodi === "" || $new_email === "" || $new_mata_kuliah === "") {
    echo json_encode([
        'success' => false,
        'message' => 'Jabatan, Prodi, Email, dan Mata Kuliah tidak boleh kosong.'
    ]);
    exit;
}

$success = $dosenModel->update($nidn, $data);

if ($success) {
    echo json_encode([
        'status' => 'success',
        'message' => 'Dosen berhasil diperbarui'
    ]);
} else {
    echo json_encode([
        'status' => 'error',
        'message' => 'Gagal memperbarui dosen'
    ]);
}
?>