<?php
session_start();

//Cek apakah user sudah login
function require_login_json()
{
    if (!isset($_SESSION['user_id'])) {
        echo json_encode([
            'status' => 'error',
            'message' => 'Unauthorized: login required'
        ]);
        exit;
    }
}

//Cek apakah user adalah admin
function require_admin()
{
    require_login_json();
    if ($_SESSION['role'] !== 'admin') {
        echo json_encode([
            'status' => 'error',
            'message' => 'Forbidden! admin only'
        ]);
        exit;
    }
}

//require_role(['admin', 'dosen']) untuk publikasi
function require_role($roles = ['admin', 'dosen'])
{
    require_login_json();
    if (!in_array($_SESSION['role'], $roles)) {
        echo json_encode([
            'status' => 'error',
            'message' => 'Forbidden role only for admin and dosen'
        ]);
        exit;
    }
}

//require_role(['admin', 'mahasiswa']) untuk data mahasiswa
function require_role2($roles = ['admin', 'mahasiswa'])
{
    require_login_json();
    if (!in_array($_SESSION['role'], $roles)) {
        echo json_encode([
            'status' => 'error',
            'message' => 'Forbidden role only for admin or mahasiswa'
        ]);
        exit;
    }
}
