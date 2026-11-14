<?php
// pembeli/status.php
// [BAGIAN 2: PROGRAMMER 2] Halaman Pelacakan Status Pesanan Pembeli

require_once __DIR__ . '/../config/koneksi.php';
require_once __DIR__ . '/../config/helper.php';

$order_id = isset($_GET['order_id']) ? (int)$_GET['order_id'] : 0;

if ($order_id <= 0) {
    header('Location: index.php');
    exit;
}

// Ambil data order
$stmt = $pdo->prepare("SELECT * FROM orders WHERE id = ?");
$stmt->execute([$order_id]);
$order = $stmt->fetch();

if (!$order) {
    die("Pesanan dengan ID #{$order_id} tidak ditemukan.");
}

// Ambil item order
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
    <meta http-equiv="refresh" content="10"> <!-- Auto refresh tiap 10 detik -->
    <title>Status Pesanan #<?= htmlspecialchars($order['kode_pesanan']) ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-slate-50 min-h-screen pb-20 text-slate-800">

    <header class="bg-white border-b border-slate-200 px-4 py-3 shadow-sm sticky top-0 z-30">
        <div class="max-w-md mx-auto flex items-center justify-between">
            <a href="index.php?meja=<?= htmlspecialchars($order['nomor_meja']) ?>" class="text-xs text-orange-600 font-bold flex items-center space-x-1">
                <i class="fa-solid fa-arrow-left"></i>
                <span>Pesan Menu Lain</span>
            </a>
            <span class="text-xs text-slate-400 flex items-center space-x-1">
                <i class="fa-solid fa-rotate fa-spin text-[10px]"></i>
                <span>Auto Update (10s)</span>
            </span>
        </div>
    </header>

    <main class="max-w-md mx-auto px-4 mt-5 space-y-4">
        <!-- Status Card -->
        <div class="bg-white rounded-2xl p-6 border border-slate-200 shadow-sm text-center">
            <?php if ($order['status_pesanan'] === 'menunggu'): ?>
                <div class="w-20 h-20 rounded-full bg-amber-100 text-amber-600 flex items-center justify-center text-3xl mx-auto mb-4 animate-pulse">
                    <i class="fa-solid fa-clock"></i>
                </div>
                <span class="inline-block px-3 py-1 bg-amber-50 text-amber-700 border border-amber-200 text-xs font-bold rounded-full mb-2">
                    Menunggu Konfirmasi Kasir
                </span>
                <h2 class="text-lg font-bold text-slate-900">Pesanan Diterima!</h2>
                <p class="text-xs text-slate-500 mt-1">Kasir sedang memverifikasi pesanan Anda.</p>

            <?php elseif ($order['status_pesanan'] === 'dimasak'): ?>
                <div class="w-20 h-20 rounded-full bg-blue-100 text-blue-600 flex items-center justify-center text-3xl mx-auto mb-4 animate-bounce">
                    <i class="fa-solid fa-fire-burner"></i>
                </div>
                <span class="inline-block px-3 py-1 bg-blue-50 text-blue-700 border border-blue-200 text-xs font-bold rounded-full mb-2">
                    Sedang Dimasak
                </span>
                <h2 class="text-lg font-bold text-slate-900">Dapur Sedang Menyiapkan!</h2>
                <p class="text-xs text-slate-500 mt-1">Mohon ditunggu sebentar ya, makanan segera disajikan.</p>

            <?php elseif ($order['status_pesanan'] === 'selesai'): ?>
                <div class="w-20 h-20 rounded-full bg-emerald-100 text-emerald-600 flex items-center justify-center text-3xl mx-auto mb-4">
                    <i class="fa-solid fa-circle-check"></i>
                </div>
                <span class="inline-block px-3 py-1 bg-emerald-50 text-emerald-700 border border-emerald-200 text-xs font-bold rounded-full mb-2">
                    Pesanan Selesai / Disajikan
                </span>
                <h2 class="text-lg font-bold text-slate-900">Selamat Menikmati!</h2>
                <p class="text-xs text-slate-500 mt-1">Terima kasih sudah memesan di Resto kami.</p>

            <?php else: ?>
                <div class="w-20 h-20 rounded-full bg-rose-100 text-rose-600 flex items-center justify-center text-3xl mx-auto mb-4">
                    <i class="fa-solid fa-ban"></i>
                </div>
                <h2 class="text-lg font-bold text-rose-600">Pesanan Dibatalkan</h2>
            <?php endif; ?>
        </div>

        <!-- Detail Pesanan Card -->
        <div class="bg-white rounded-2xl p-5 border border-slate-200 shadow-sm">
            <div class="border-b border-slate-100 pb-3 mb-3">
                <div class="flex justify-between items-center text-xs text-slate-500 mb-1">
                    <span>Kode Pesanan:</span>
                    <span class="font-mono font-bold text-slate-800"><?= htmlspecialchars($order['kode_pesanan']) ?></span>
                </div>
                <div class="flex justify-between items-center text-xs text-slate-500 mb-1">
                    <span>Nomor Meja:</span>
                    <span class="font-bold text-orange-600">Meja #<?= htmlspecialchars($order['nomor_meja']) ?></span>
                </div>
                <div class="flex justify-between items-center text-xs text-slate-500">
                    <span>Nama Pelanggan:</span>
                    <span class="font-semibold text-slate-800"><?= htmlspecialchars($order['nama_pelanggan']) ?></span>
                </div>
            </div>

            <!-- List Menu Dipesan -->
            <h3 class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">Item Menu:</h3>
            <div class="space-y-2 mb-4">
                <?php foreach ($items as $item): ?>
                    <div class="flex justify-between items-start text-xs border-b border-slate-50 pb-2">
                        <div>
                            <span class="font-bold text-slate-800"><?= $item['jumlah'] ?>x <?= htmlspecialchars($item['nama_menu']) ?></span>
                            <?php if (!empty($item['catatan'])): ?>
                                <p class="text-[11px] text-slate-400 italic">"<?= htmlspecialchars($item['catatan']) ?>"</p>
                            <?php endif; ?>
                        </div>
                        <span class="font-semibold text-slate-700"><?= format_rupiah($item['subtotal']) ?></span>
                    </div>
                <?php endforeach; ?>
            </div>

            <!-- Total Biaya & Status Pembayaran -->
            <div class="border-t border-slate-100 pt-3 flex justify-between items-center">
                <div>
                    <span class="text-xs text-slate-400 block">Total Pembayaran:</span>
                    <span class="text-base font-bold text-orange-600"><?= format_rupiah($order['total_harga']) ?></span>
                </div>
                <div>
                    <?php if ($order['status_pembayaran'] === 'lunas'): ?>
                        <span class="px-2.5 py-1 bg-emerald-100 text-emerald-800 text-xs font-bold rounded-full">
                            <i class="fa-solid fa-check mr-1"></i>LUNAS
                        </span>
                    <?php else: ?>
                        <span class="px-2.5 py-1 bg-amber-100 text-amber-800 text-xs font-bold rounded-full">
                            <i class="fa-solid fa-hourglass-half mr-1"></i>BAYAR DI KASIR
                        </span>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="text-center">
            <a href="index.php?meja=<?= htmlspecialchars($order['nomor_meja']) ?>" 
               class="inline-block w-full py-3 bg-slate-200 hover:bg-slate-300 text-slate-800 font-bold rounded-xl text-xs transition">
                Kembali ke Katalog Menu
            </a>
        </div>
    </main>
</body>
</html>

