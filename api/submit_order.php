<?php
// api/submit_order.php
// [BAGIAN 1: PROGRAMMER 1] Handler untuk menerima pesanan dari pembeli

require_once __DIR__ . '/../config/koneksi.php';
require_once __DIR__ . '/../config/helper.php';

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_response(['status' => 'error', 'message' => 'Hanya menerima request POST!'], 405);
}

// Ambil input JSON atau POST form biasa
$raw_input = file_get_contents('php://input');
$data = json_decode($raw_input, true);

if (!$data) {
    $data = $_POST;
    if (isset($data['items']) && is_string($data['items'])) {
        $data['items'] = json_decode($data['items'], true);
    }
}

$nomor_meja     = isset($data['nomor_meja']) ? trim($data['nomor_meja']) : '';
$nama_pelanggan = !empty($data['nama_pelanggan']) ? trim($data['nama_pelanggan']) : 'Pelanggan Meja ' . $nomor_meja;
$items          = isset($data['items']) && is_array($data['items']) ? $data['items'] : [];

if (empty($nomor_meja)) {
    json_response(['status' => 'error', 'message' => 'Nomor meja tidak boleh kosong!'], 400);
}

if (empty($items)) {
    json_response(['status' => 'error', 'message' => 'Keranjang pesanan masih kosong!'], 400);
}

try {
    $pdo->beginTransaction();

    // 1. Hitung total harga & validasi menu dari database
    $total_harga = 0;
    $order_items_to_insert = [];

    // Siapkan statement pencarian menu
    $stmt_menu = $pdo->prepare("SELECT id, nama_menu, harga, status FROM menus WHERE id = ?");

    foreach ($items as $item) {
        $menu_id = (int)$item['id'];
        $jumlah  = max(1, (int)$item['jumlah']);
        $catatan = isset($item['catatan']) ? trim($item['catatan']) : '';

        $stmt_menu->execute([$menu_id]);
        $menu = $stmt_menu->fetch();

        if (!$menu) {
            throw new Exception("Menu dengan ID #{$menu_id} tidak ditemukan!");
        }

        if ($menu['status'] === 'habis') {
            throw new Exception("Menu '{$menu['nama_menu']}' sedang habis!");
        }

        $harga_satuan = (int)$menu['harga'];
        $subtotal     = $harga_satuan * $jumlah;
        $total_harga += $subtotal;

        $order_items_to_insert[] = [
            'menu_id'      => $menu_id,
            'jumlah'       => $jumlah,
            'harga_satuan' => $harga_satuan,
            'subtotal'     => $subtotal,
            'catatan'      => $catatan
        ];
    }

    // 2. Buat kode pesanan unik (contoh: ORD-20261113-1234)
    $kode_pesanan = 'ORD-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -4));

    // 3. Simpan ke tabel orders
    $stmt_order = $pdo->prepare("
        INSERT INTO orders (kode_pesanan, nomor_meja, nama_pelanggan, total_harga, status_pembayaran, status_pesanan)
        VALUES (?, ?, ?, ?, 'belum_bayar', 'menunggu')
    ");
    $stmt_order->execute([$kode_pesanan, $nomor_meja, $nama_pelanggan, $total_harga]);
    $order_id = $pdo->lastInsertId();

    // 4. Simpan ke tabel order_items
    $stmt_item = $pdo->prepare("
        INSERT INTO order_items (order_id, menu_id, jumlah, harga_satuan, subtotal, catatan)
        VALUES (?, ?, ?, ?, ?, ?)
    ");

    foreach ($order_items_to_insert as $oi) {
        $stmt_item->execute([
            $order_id,
            $oi['menu_id'],
            $oi['jumlah'],
            $oi['harga_satuan'],
            $oi['subtotal'],
            $oi['catatan']
        ]);
    }

    $pdo->commit();

    json_response([
        'status'       => 'success',
        'message'      => 'Pesanan berhasil dikirim ke kasir & dapur!',
        'order_id'     => $order_id,
        'kode_pesanan' => $kode_pesanan,
        'nomor_meja'   => $nomor_meja,
        'total_harga'  => $total_harga
    ], 201);

} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    json_response(['status' => 'error', 'message' => $e->getMessage()], 400);
}

