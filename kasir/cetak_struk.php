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
    <style>
        * {
            box-sizing: border-box;
            font-family: 'Courier New', Courier, monospace;
        }
        body {
            background-color: #FFFDF9;
            margin: 0;
            padding: 20px;
            display: flex;
            flex-direction: column;
            align-items: center;
            color: #4A240A;
        }
        .struk-card {
            background: #fff;
            width: 320px;
            padding: 20px;
            border: 1.5px dashed #7C3E12;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(124, 62, 18, 0.08);
        }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .bold { font-weight: bold; }
        .divider {
            border-top: 1px dashed #7C3E12;
            margin: 10px 0;
        }
        .item-row {
            display: flex;
            justify-content: space-between;
            font-size: 12px;
            margin-bottom: 4px;
        }
        .item-note {
            font-size: 10px;
            color: #7C3E12;
            opacity: 0.8;
            font-style: italic;
            padding-left: 10px;
        }
        .no-print {
            margin-bottom: 20px;
            display: flex;
            gap: 8px;
        }
        .btn-print {
            background-color: #FF7E24;
            color: white;
            padding: 9px 18px;
            border: none;
            border-radius: 10px;
            cursor: pointer;
            font-weight: bold;
            font-size: 13px;
            box-shadow: 0 2px 6px rgba(255, 126, 36, 0.3);
            transition: all 0.2s ease;
            font-family: system-ui, -apple-system, sans-serif;
        }
        .btn-print:hover {
            background-color: #E66810;
        }
        .btn-close {
            background-color: #7C3E12;
            color: white;
            padding: 9px 18px;
            border: none;
            border-radius: 10px;
            cursor: pointer;
            font-weight: bold;
            font-size: 13px;
            transition: all 0.2s ease;
            font-family: system-ui, -apple-system, sans-serif;
        }
        .btn-close:hover {
            background-color: #5C2D0B;
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
        <button class="btn-print" onclick="window.print()">🖨️ Cetak Struk Sekarang</button>
        <button class="btn-close" onclick="window.close()">Tutup</button>
    </div>

    <div class="struk-card">
        <!-- Header Struk -->
        <div class="text-center">
            <h2 style="margin: 0; font-size: 16px; color: #7C3E12; letter-spacing: 0.5px;">RESTO ENAK RASA</h2>
            <p style="margin: 3px 0 1px; font-size: 11px; color: #7C3E12; opacity: 0.85;">Jl. Hackathon Inovasi No. 123</p>
            <p style="margin: 1px 0; font-size: 11px; color: #7C3E12; opacity: 0.85;">Telp: 0812-3456-7890</p>
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
        <div class="item-row bold" style="font-size: 13px; color: #7C3E12;">
            <span>TOTAL TAGIHAN</span>
            <span style="color: #9c5019ff;">Rp <?= number_format($order['total_harga'], 0, ',', '.') ?></span>
        </div>
        <div class="item-row" style="font-size: 11px;">
            <span>Status Bayar</span>
            <span class="bold" style="color: #ff9a51ff;"><?= strtoupper($order['status_pembayaran']) ?></span>
        </div>

        <div class="divider"></div>

        <!-- Footer Struk -->
        <div class="text-center" style="font-size: 11px; color: #7C3E12;">
            <p style="margin: 4px 0; font-weight: 600;">Terima Kasih Atas Kunjungan Anda!</p>
            <p style="margin: 2px 0; font-size: 9px; opacity: 0.7;">Simpan struk ini sebagai bukti pembayaran sah.</p>
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

