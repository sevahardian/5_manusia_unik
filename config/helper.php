<?php
// config/helper.php
// Fungsi pembantu global untuk seluruh project

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Format angka ke format Rupiah (contoh: Rp 25.000)
 */
function format_rupiah($angka) {
    return 'Rp ' . number_format($angka, 0, ',', '.');
}

/**
 * Membersihkan input form untuk mencegah XSS
 */
function sanitize($input) {
    return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
}

/**
 * Mengirim response JSON (berguna untuk API / AJAX)
 */
function json_response($data, $status_code = 200) {
    http_response_code($status_code);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data);
    exit;
}

/**
 * Cek apakah user sudah login sebagai kasir/admin
 */
function check_auth($required_role = null) {
    if (!isset($_SESSION['user_id'])) {
        header('Location: ../admin/login.php');
        exit;
    }
    if ($required_role && $_SESSION['role'] !== $required_role && $_SESSION['role'] !== 'admin') {
        die('Akses ditolak: Anda tidak memiliki hak akses untuk halaman ini.');
    }
}

