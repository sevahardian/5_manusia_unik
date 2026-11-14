<?php
// api/scan_validate.php
// Endpoint untuk memproses pemindaian QR Code dari kasir atau admin

require_once __DIR__ . '/../config/koneksi.php';
require_once __DIR__ . '/../config/helper.php';

header('Content-Type: application/json; charset=utf-8');

// Ambil input JSON atau POST / GET
$raw_input = file_get_contents('php://input');
$data = json_decode($raw_input, true) ?: $_POST;
if (empty($data)) {
    $data = $_GET;
}

$qr_raw = trim($data['qr_data'] ?? $data['kode_pesanan'] ?? '');
$action = trim($data['action'] ?? 'check'); // 'check' atau 'validate'

if (empty($qr_raw)) {
    json_response(['status' => 'error', 'message' => 'Data QR atau Kode Pesanan tidak boleh kosong!'], 400);
}

// Ekstrak kode pesanan dari teks QR
// Contoh format QR: RESTO-ENAK-RASA:ORDER=ORD-20261113-1234:MEJA=03:TOTAL=55000
// atau format langsung: ORD-20261113-1234
$kode_pesanan = '';
if (preg_match('/(ORD-[A-Za-z0-9\-]+)/', $qr_raw, $matches)) {
    $kode_pesanan = strtoupper($matches[1]);
} else {
    // Jika user menginput ID angka langsung
    if (is_numeric($qr_raw)) {
        $stmt_by_id = $pdo->prepare("SELECT kode_pesanan FROM orders WHERE id = ?");
        $stmt_by_id->execute([(int)$qr_raw]);
        $kode_pesanan = $stmt_by_id->fetchColumn() ?: '';
    }
}

if (empty($kode_pesanan)) {
    $kode_pesanan = strtoupper($qr_raw);
}

try {
    // Cari order berdasarkan kode_pesanan
    $stmt = $pdo->prepare("SELECT * FROM orders WHERE kode_pesanan = ?");
    $stmt->execute([$kode_pesanan]);
    $order = $stmt->fetch();

    if (!$order) {
        json_response([
            'status' => 'error', 
            'message' => "Pesanan dengan kode \"$kode_pesanan\" tidak ditemukan di database!"
        ], 404);
    }

    // Jika action adalah 'validate' (Set Lunas & Mulai Masak)
    if ($action === 'validate') {
        $stmt_val = $pdo->prepare("
            UPDATE orders 
            SET status_pembayaran = 'lunas',
                status_pesanan = CASE WHEN status_pesanan = 'menunggu' THEN 'dimasak' ELSE status_pesanan END
            WHERE id = ?
        ");
        $stmt_val->execute([$order['id']]);

        // Refresh data order terbaru
        $stmt->execute([$kode_pesanan]);
        $order = $stmt->fetch();
    }

    // Ambil rincian menu pesanan
    $stmt_items = $pdo->prepare("
        SELECT oi.*, m.nama_menu, m.kategori
        FROM order_items oi
        JOIN menus m ON oi.menu_id = m.id
        WHERE oi.order_id = ?
    ");
    $stmt_items->execute([$order['id']]);
    $order['items'] = $stmt_items->fetchAll();
    $order['total_harga_formatted'] = format_rupiah($order['total_harga']);

    json_response([
        'status' => 'success',
        'action' => $action,
        'message' => $action === 'validate' ? 'Pembayaran berhasil divalidasi dan pesanan diteruskan ke dapur!' : 'Data pesanan berhasil ditemukan.',
        'data'   => $order
    ]);

} catch (Exception $e) {
    json_response(['status' => 'error', 'message' => $e->getMessage()], 500);
}
