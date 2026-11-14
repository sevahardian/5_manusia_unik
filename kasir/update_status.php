<?php
// kasir/update_status.php
// [BAGIAN 3: PROGRAMMER 3] Update Status Pesanan & Pembayaran Kasir

require_once __DIR__ . '/../config/koneksi.php';
require_once __DIR__ . '/../config/helper.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit;
}

$order_id = isset($_POST['order_id']) ? (int)$_POST['order_id'] : 0;
$action   = isset($_POST['action']) ? $_POST['action'] : '';

if ($order_id <= 0) {
    header('Location: index.php');
    exit;
}

try {
    if ($action === 'set_lunas') {
        $stmt = $pdo->prepare("
            UPDATE orders 
            SET status_pembayaran = 'lunas',
                status_pesanan = CASE WHEN status_pesanan = 'menunggu' THEN 'dimasak' ELSE status_pesanan END
            WHERE id = ?
        ");
        $stmt->execute([$order_id]);
    } elseif (isset($_POST['status_pesanan'])) {
        $status_pesanan = sanitize($_POST['status_pesanan']);
        $allowed = ['menunggu', 'dimasak', 'selesai', 'dibatalkan'];
        if (in_array($status_pesanan, $allowed)) {
            $stmt = $pdo->prepare("UPDATE orders SET status_pesanan = ? WHERE id = ?");
            $stmt->execute([$status_pesanan, $order_id]);
        }
    }
} catch (Exception $e) {
    // Error handling
}

$redirect = !empty($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : 'index.php';
header("Location: $redirect");
exit;

