<?php
// kasir/cetak_struk.php
// [BAGIAN 3: PROGRAMMER 3] Halaman Cetak Struk Pembelian & Tiket Dapur

require_once __DIR__ . '/../config/koneksi.php';
require_once __DIR__ . '/../config/helper.php';

$order_id = isset($_GET['order_id']) ? (int)$_GET['order_id'] : 0;

if ($order_id <= 0) {
    die("ID Pesanan tidak valid.");
}

$stmt = $pdo->prepare("SELECT * FROM orders WHERE id = ?");
$stmt->execute([$order_id]);
$order = $stmt->fetch();

if (!$order) {
    die("Pesanan tidak ditemukan.");
}

$stmt_items = $pdo->prepare("
    SELECT oi.*, m.nama_menu 
    FROM order_items oi
    JOIN menus m ON oi.menu_id = m.id
    WHERE oi.order_id = ?
");
$stmt_items->execute([$order_id]);
$items = $stmt_items->fetchAll();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Struk #<?= htmlspecialchars($order['kode_pesanan']) ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * {
            box-sizing: border-box;
            font-family: 'Courier New', Courier, monospace;
        }
        body {
            background-color: #f3f4f6;
            margin: 0;
            padding: 20px;
            display: flex;
            flex-direction: column;
            align-items: center;
        }
        .struk-card {
            background: #fff;
            width: 300px;
            padding: 16px;
            border: 1px dashed #9ca3af;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .bold { font-weight: bold; }
        .divider {
            border-top: 1px dashed #000;
            margin: 8px 0;
        }
        .item-row {
            display: flex;
            justify-content: space-between;
            font-size: 12px;
            margin-bottom: 4px;
        }
        .item-note {
            font-size: 10px;
            color: #4b5563;
            font-style: italic;
            padding-left: 10px;
        }
        .no-print {
            margin-bottom: 16px;
        }
        .btn-print {
            background-color: #2563eb;
            color: white;
            padding: 8px 16px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-weight: bold;
            font-size: 13px;
        }

        @media print {
            body {
                background: none;
                padding: 0;
            }
            .no-print {
                display: none !important;
            }
            .struk-card {
                border: none;
                box-shadow: none;
                width: 100%;
                padding: 0;
            }
        }
    </style>
</head>
<body>

    <div class="no-print">
        <button class="btn-print" onclick="window.print()"><i class="fa-solid fa-print"></i> Cetak Struk Sekarang</button>
        <button class="btn-print" style="background:#4b5563;" onclick="window.close()">Tutup</button>
    </div>

    <div class="struk-card">
        <!-- Header Struk -->
        <div class="text-center">
            <h2 style="margin: 0; font-size: 16px;">KanDar</h2>
            <p style="margin: 2px 0; font-size: 11px;">Jl. Hackathon Inovasi No. 123</p>
            <p style="margin: 2px 0; font-size: 11px;">Telp: 0812-3456-7890</p>
        </div>

        <div class="divider"></div>

        <!-- Info Pesanan -->
        <div style="font-size: 11px; line-height: 1.4;">
            <div>No. Order : <b><?= htmlspecialchars($order['kode_pesanan']) ?></b></div>
            <div>Waktu     : <?= date('d/m/Y H:i', strtotime($order['created_at'])) ?></div>
            <div>Meja      : <b>MEJA <?= htmlspecialchars($order['nomor_meja']) ?></b></div>
            <div>Pelanggan : <?= htmlspecialchars($order['nama_pelanggan']) ?></div>
        </div>

        <div class="divider"></div>

        <!-- Rincian Item -->
        <div>
            <?php foreach ($items as $item): ?>
                <div class="item-row">
                    <span><?= htmlspecialchars($item['nama_menu']) ?></span>
                </div>
                <div class="item-row">
                    <span style="padding-left: 10px; color: #4b5563;">
                        <?= $item['jumlah'] ?> x <?= number_format($item['harga_satuan'], 0, ',', '.') ?>
                    </span>
                    <span class="bold"><?= number_format($item['subtotal'], 0, ',', '.') ?></span>
                </div>
                <?php if (!empty($item['catatan'])): ?>
                    <div class="item-note">* <?= htmlspecialchars($item['catatan']) ?></div>
                <?php endif; ?>
            <?php endforeach; ?>
        </div>

        <div class="divider"></div>

        <!-- Total Tagihan -->
        <div class="item-row bold" style="font-size: 13px;">
            <span>TOTAL TAGIHAN</span>
            <span>Rp <?= number_format($order['total_harga'], 0, ',', '.') ?></span>
        </div>
        <div class="item-row" style="font-size: 11px;">
            <span>Status Bayar</span>
            <span class="bold"><?= strtoupper($order['status_pembayaran']) ?></span>
        </div>

        <div class="divider"></div>

        <!-- Footer Struk -->
        <div class="text-center" style="font-size: 11px;">
            <p style="margin: 4px 0;">Terima Kasih Atas Kunjungan Anda!</p>
            <p style="margin: 2px 0; font-size: 9px; color: #6b7280;">Simpan struk ini sebagai bukti pembayaran sah.</p>
        </div>
    </div>

    <!-- Otomatis pemicu print dialog -->
    <script>
        window.onload = function() {
            // Bisa aktifkan window.print() otomatis jika diinginkan
            // window.print();
        }
    </script>
</body>
</html>

