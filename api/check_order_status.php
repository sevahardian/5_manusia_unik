<?php
// api/check_order_status.php
// Endpoint ringan untuk live polling status pesanan di sisi pembeli

require_once __DIR__ . '/../config/koneksi.php';
require_once __DIR__ . '/../config/helper.php';

header('Content-Type: application/json; charset=utf-8');

$order_id = isset($_GET['order_id']) ? (int)$_GET['order_id'] : 0;

if ($order_id <= 0) {
    json_response(['status' => 'error', 'message' => 'ID pesanan tidak valid!'], 400);
}

try {
    $stmt = $pdo->prepare("
        SELECT id, kode_pesanan, nomor_meja, total_harga, status_pembayaran, status_pesanan, metode_pembayaran, updated_at 
        FROM orders 
        WHERE id = ?
    ");
    $stmt->execute([$order_id]);
    $order = $stmt->fetch();

    if (!$order) {
        json_response(['status' => 'error', 'message' => 'Pesanan tidak ditemukan!'], 404);
    }

    json_response([
        'status' => 'success',
        'data'   => [
            'id'                => (int)$order['id'],
            'kode_pesanan'      => $order['kode_pesanan'],
            'nomor_meja'        => $order['nomor_meja'],
            'status_pembayaran' => $order['status_pembayaran'],
            'status_pesanan'    => $order['status_pesanan'],
            'metode_pembayaran' => $order['metode_pembayaran'],
            'updated_at'        => $order['updated_at']
        ]
    ]);
} catch (Exception $e) {
    json_response(['status' => 'error', 'message' => $e->getMessage()], 500);
}
