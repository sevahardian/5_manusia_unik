<?php
// config/koneksi.php
// File konfigurasi database menggunakan PDO (aman dari SQL Injection)

$db_host = 'localhost';
$db_user = 'root';
$db_pass = '';          // Kosongkan jika pakai XAMPP default, atau sesuaikan
$db_name = 'resto_db';
$db_port = '3306';

try {
    $dsn = "mysql:host={$db_host};port={$db_port};dbname={$db_name};charset=utf8mb4";
    $pdo = new PDO($dsn, $db_user, $db_pass, [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ]);
} catch (PDOException $e) {
    // Tampilkan pesan jika database belum dibuat / koneksi gagal
    die("<div style='font-family: sans-serif; padding: 20px; background: #fee2e2; color: #991b1b; border-radius: 8px;'>
            <h3>⚠️ Gagal Terhubung ke Database!</h3>
            <p>Pastikan MySQL (XAMPP/Laragon) sudah menyala dan database <b>`{$db_name}`</b> sudah di-import dari file <code>database/database.sql</code>.</p>
            <small>Pesan Error: " . htmlspecialchars($e->getMessage()) . "</small>
         </div>");
}

