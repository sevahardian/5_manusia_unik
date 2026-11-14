<?php
// api/get_orders.php
// [BAGIAN 1 & 3: PROGRAMMER 1 & 3] Endpoint untuk polling pesanan masuk kasir

require_once __DIR__ . '/../config/koneksi.php';
require_once __DIR__ . '/../config/helper.php';

header('Content-Type: application/json; charset=utf-8');

try {
    // Ambil order beserta rincian itemnya
    $stmt = $pdo->query("
        SELECT o.*, 
               DATE_FORMAT(o.created_at, '%H:%i') as jam_order,
               COUNT(oi.id) as total_item
        FROM orders o
        LEFT JOIN order_items oi ON o.id = oi.order_id
        GROUP BY o.id
        ORDER BY o.created_at DESC
        LIMIT 30
    ");
    $orders = $stmt->fetchAll();

    // Ambil rincian detail per order
    $result = [];
    $stmt_items = $pdo->prepare("
        SELECT oi.*, m.nama_menu
        FROM order_items oi
        JOIN menus m ON oi.menu_id = m.id
        WHERE oi.order_id = ?
    ");

    foreach ($orders as $order) {
        $stmt_items->execute([$order['id']]);
        $order['items'] = $stmt_items->fetchAll();
        $order['total_harga_formatted'] = format_rupiah($order['total_harga']);
        $result[] = $order;
    }

    json_response([
        'status' => 'success',
        'data'   => $result,
        'count'  => count($result)
    ]);

} catch (Exception $e) {
    json_response(['status' => 'error', 'message' => $e->getMessage()], 500);
}

