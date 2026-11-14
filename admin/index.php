<?php
// admin/index.php
// Dashboard Utama Administrator & Rekap Penjualan

require_once __DIR__ . '/../config/koneksi.php';
require_once __DIR__ . '/../config/helper.php';

// Pastikan hanya admin yang bisa akses
check_auth('admin');

// 1. Data Statistik Utama
$total_omzet = $pdo->query("SELECT SUM(total_harga) FROM orders WHERE status_pembayaran = 'lunas'")->fetchColumn() ?: 0;
$total_pesanan_lunas = $pdo->query("SELECT COUNT(*) FROM orders WHERE status_pembayaran = 'lunas'")->fetchColumn() ?: 0;
$total_menu = $pdo->query("SELECT COUNT(*) FROM menus")->fetchColumn() ?: 0;
$total_menu_habis = $pdo->query("SELECT COUNT(*) FROM menus WHERE status = 'habis'")->fetchColumn() ?: 0;

// 2. Transaksi Hari Ini
$omzet_hari_ini = $pdo->query("
    SELECT SUM(total_harga) 
    FROM orders 
    WHERE status_pembayaran = 'lunas' AND DATE(created_at) = CURDATE()
")->fetchColumn() ?: 0;

$pesanan_hari_ini = $pdo->query("
    SELECT COUNT(*) 
    FROM orders 
    WHERE DATE(created_at) = CURDATE()
")->fetchColumn() ?: 0;

// 3. Ambil 15 Transaksi Terakhir untuk Tabel Riwayat
$stmt = $pdo->query("
    SELECT * FROM orders 
    ORDER BY created_at DESC 
    LIMIT 15
");
$riwayat_orders = $stmt->fetchAll();

// 4. Menu Terlaris (Top 5 Menu Paling Banyak Dipesan)
$top_menus = $pdo->query("
    SELECT m.nama_menu, m.kategori, SUM(oi.jumlah) as total_terjual, SUM(oi.subtotal) as total_pendapatan
    FROM order_items oi
    JOIN menus m ON oi.menu_id = m.id
    JOIN orders o ON oi.order_id = o.id
    WHERE o.status_pembayaran = 'lunas'
    GROUP BY m.id
    ORDER BY total_terjual DESC
    LIMIT 5
")->fetchAll();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Admin - Resto Enak Rasa</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
    </style>
</head>
<body class="bg-slate-50 text-slate-800 flex flex-col min-h-screen">

    <!-- Navbar Pelanggan & Petugas Persis Landing Page -->
    <header class="sticky top-0 z-40 bg-white/90 backdrop-blur-md border-b border-slate-200">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 py-3.5 flex items-center justify-between">
            <a href="index.php" class="flex items-center space-x-2.5">
                <div class="w-10 h-10 rounded-2xl bg-gradient-to-tr from-orange-600 to-amber-500 text-white flex items-center justify-center font-bold text-lg shadow-md shadow-orange-500/20">
                    <i class="fa-solid fa-utensils"></i>
                </div>
                <div>
                    <span class="font-extrabold text-slate-900 text-lg tracking-tight block leading-tight">Resto Enak Rasa</span>
                    <span class="text-[10px] text-orange-600 font-semibold tracking-wider uppercase">Admin Backoffice</span>
                </div>
            </a>

            <!-- Nav Links -->
            <div class="flex items-center space-x-2 text-xs">
                <a href="index.php" class="px-4 py-2 bg-orange-500 hover:bg-orange-600 text-white font-bold rounded-xl shadow-md shadow-orange-500/20 active:scale-95 transition flex items-center space-x-1.5">
                    <i class="fa-solid fa-chart-pie text-xs"></i>
                    <span>Overview</span>
                </a>
                <a href="menu.php" class="px-3.5 py-2 bg-slate-100 hover:bg-orange-50 hover:text-orange-600 text-slate-700 font-bold rounded-xl border border-slate-200 transition flex items-center space-x-1.5">
                    <i class="fa-solid fa-bowl-food text-xs"></i>
                    <span>Kelola Menu</span>
                </a>
                <a href="../kasir/index.php" class="px-3.5 py-2 bg-slate-100 hover:bg-orange-50 hover:text-orange-600 text-slate-700 font-bold rounded-xl border border-slate-200 transition flex items-center space-x-1.5">
                    <i class="fa-solid fa-cash-register text-xs"></i>
                    <span class="hidden sm:inline">Layar Kasir</span>
                </a>
                <a href="logout.php" onclick="return confirm('Keluar dari sesi Admin?')" class="p-2 text-slate-400 hover:text-rose-600 transition" title="Logout">
                    <i class="fa-solid fa-right-from-bracket text-base"></i>
                </a>
            </div>
        </div>
    </header>

    <main class="max-w-6xl mx-auto px-4 sm:px-6 py-8 flex-1 w-full space-y-8">
        
        <!-- Welcome Banner Persis Style Hero Landing Page -->
        <div class="bg-gradient-to-b from-orange-50/60 to-slate-50 p-6 sm:p-8 rounded-3xl border border-slate-200 flex flex-col md:flex-row md:items-center justify-between gap-6 shadow-sm">
            <div class="space-y-3">
                <div class="inline-flex items-center space-x-2 px-3 py-1.5 rounded-full bg-orange-100/80 text-orange-700 text-xs font-bold border border-orange-200">
                    <span class="w-2 h-2 rounded-full bg-orange-500 animate-pulse"></span>
                    <span>Monitoring Penjualan Real-Time</span>
                </div>
                <h1 class="text-2xl sm:text-3xl font-black text-slate-900">
                    Ringkasan Kinerja Resto
                </h1>
                <p class="text-xs sm:text-sm text-slate-600 max-w-lg leading-relaxed">
                    Pantau omzet harian, transaksi pesanan meja pelanggan, serta kelola katalog menu dalam satu sistem terpadu.
                </p>
            </div>

            <div class="flex items-center gap-3 flex-wrap">
                <a href="menu.php" class="px-5 py-3 bg-orange-500 hover:bg-orange-600 text-white text-xs sm:text-sm font-bold rounded-xl shadow-md shadow-orange-500/20 active:scale-95 transition flex items-center space-x-2">
                    <i class="fa-solid fa-plus text-xs"></i>
                    <span>Tambah Menu Baru</span>
                </a>
                <a href="../pembeli/index.php?meja=01" target="_blank" class="px-4 py-3 bg-white hover:bg-orange-50 hover:text-orange-600 text-slate-700 text-xs sm:text-sm font-bold rounded-xl border border-slate-200 transition flex items-center space-x-2">
                    <i class="fa-solid fa-arrow-up-right-from-square text-xs"></i>
                    <span>Tampilan Pembeli</span>
                </a>
            </div>
        </div>

        <!-- 4 Kartu Statistik -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
            <!-- Omzet Hari Ini -->
            <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm hover:shadow-md transition flex items-center justify-between">
                <div>
                    <span class="text-xs font-bold text-orange-600 uppercase tracking-widest block">Omzet Hari Ini</span>
                    <h3 class="text-2xl font-black text-slate-900 mt-1"><?= format_rupiah($omzet_hari_ini) ?></h3>
                    <span class="text-xs text-slate-500 mt-1 block"><?= $pesanan_hari_ini ?> transaksi hari ini</span>
                </div>
                <div class="w-12 h-12 rounded-2xl bg-orange-100 text-orange-600 flex items-center justify-center text-xl font-black">
                    <i class="fa-solid fa-calendar-day"></i>
                </div>
            </div>

            <!-- Total Keseluruhan Omzet -->
            <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm hover:shadow-md transition flex items-center justify-between">
                <div>
                    <span class="text-xs font-bold text-orange-600 uppercase tracking-widest block">Total Pendapatan</span>
                    <h3 class="text-2xl font-black text-slate-900 mt-1"><?= format_rupiah($total_omzet) ?></h3>
                    <span class="text-xs text-slate-500 mt-1 block"><?= $total_pesanan_lunas ?> pesanan lunas</span>
                </div>
                <div class="w-12 h-12 rounded-2xl bg-orange-100 text-orange-600 flex items-center justify-center text-xl font-black">
                    <i class="fa-solid fa-sack-dollar"></i>
                </div>
            </div>

            <!-- Total Katalog Menu -->
            <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm hover:shadow-md transition flex items-center justify-between">
                <div>
                    <span class="text-xs font-bold text-orange-600 uppercase tracking-widest block">Total Menu</span>
                    <h3 class="text-2xl font-black text-slate-900 mt-1"><?= $total_menu ?> Menu</h3>
                    <span class="text-xs text-slate-500 mt-1 block"><?= $total_menu_habis ?> menu sedang habis</span>
                </div>
                <div class="w-12 h-12 rounded-2xl bg-orange-100 text-orange-600 flex items-center justify-center text-xl font-black">
                    <i class="fa-solid fa-utensils"></i>
                </div>
            </div>

            <!-- Mode Kasir Link -->
            <a href="../kasir/index.php" class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm hover:shadow-md hover:border-orange-300 transition flex items-center justify-between group">
                <div>
                    <span class="text-xs font-bold text-orange-600 uppercase tracking-widest block">Akses Kasir</span>
                    <h3 class="text-base font-black text-slate-900 mt-1 group-hover:text-orange-600 transition">Buka Layar Kasir</h3>
                    <span class="text-xs text-slate-500 mt-1 block">Monitor pesanan & struk</span>
                </div>
                <div class="w-12 h-12 rounded-2xl bg-slate-100 text-slate-600 group-hover:bg-orange-100 group-hover:text-orange-600 flex items-center justify-center text-xl font-black transition">
                    <i class="fa-solid fa-cash-register"></i>
                </div>
            </a>
        </div>

        <!-- Bagian Tabel: Riwayat Transaksi & Top Menu -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            
            <!-- Tabel Riwayat Transaksi Terbaru (2 Kolom) -->
            <div class="lg:col-span-2 bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
                <div class="p-5 border-b border-slate-200 flex items-center justify-between">
                    <div>
                        <span class="text-xs font-bold text-orange-600 uppercase tracking-widest">Aktivitas Terbaru</span>
                        <h3 class="font-black text-base text-slate-900 mt-0.5">Riwayat Pesanan Pelanggan</h3>
                    </div>
                    <a href="../kasir/index.php" class="text-xs font-bold text-orange-600 hover:text-orange-700 inline-flex items-center space-x-1">
                        <span>Buka Kasir</span>
                        <i class="fa-solid fa-arrow-right text-[10px]"></i>
                    </a>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs">
                        <thead class="bg-slate-50 text-slate-500 uppercase border-b border-slate-200 font-bold text-[11px]">
                            <tr>
                                <th class="py-3 px-4">Kode & Meja</th>
                                <th class="py-3 px-4">Pelanggan</th>
                                <th class="py-3 px-4">Total Tagihan</th>
                                <th class="py-3 px-4 text-center">Status</th>
                                <th class="py-3 px-4 text-right">Waktu</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <?php if (empty($riwayat_orders)): ?>
                                <tr>
                                    <td colspan="5" class="py-8 text-center text-slate-400">Belum ada transaksi.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($riwayat_orders as $ro): ?>
                                    <tr class="hover:bg-orange-50/50 transition">
                                        <td class="py-3.5 px-4">
                                            <span class="font-extrabold text-slate-900 block"><?= htmlspecialchars($ro['kode_pesanan']) ?></span>
                                            <span class="text-[10px] font-bold text-orange-600 bg-orange-50 px-2 py-0.5 rounded-full uppercase border border-orange-200 inline-block mt-0.5">
                                                Meja <?= htmlspecialchars($ro['nomor_meja']) ?>
                                            </span>
                                        </td>
                                        <td class="py-3.5 px-4 font-semibold text-slate-700">
                                            <?= htmlspecialchars($ro['nama_pelanggan']) ?>
                                        </td>
                                        <td class="py-3.5 px-4 font-black text-slate-900">
                                            <?= format_rupiah($ro['total_harga']) ?>
                                        </td>
                                        <td class="py-3.5 px-4 text-center">
                                            <?php if ($ro['status_pembayaran'] === 'lunas'): ?>
                                                <span class="px-3 py-1 bg-orange-500 text-white text-[10px] font-bold rounded-lg shadow-sm">
                                                    LUNAS
                                                </span>
                                            <?php else: ?>
                                                <span class="px-2.5 py-1 bg-slate-100 text-slate-600 border border-slate-200 text-[10px] font-bold rounded-lg">
                                                    BELUM BAYAR
                                                </span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="py-3.5 px-4 text-slate-400 text-right">
                                            <?= date('d M, H:i', strtotime($ro['created_at'])) ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Menu Terlaris (1 Kolom) -->
            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-5 flex flex-col justify-between">
                <div>
                    <div class="border-b border-slate-200 pb-3 mb-4">
                        <span class="text-xs font-bold text-orange-600 uppercase tracking-widest">Favorit Pelanggan</span>
                        <h3 class="font-black text-base text-slate-900 mt-0.5">Menu Paling Laris</h3>
                    </div>

                    <div class="space-y-3">
                        <?php if (empty($top_menus)): ?>
                            <p class="text-xs text-slate-400 text-center py-6">Belum ada penjualan menu lunas.</p>
                        <?php else: ?>
                            <?php foreach ($top_menus as $idx => $tm): ?>
                                <div class="flex items-center justify-between text-xs p-2.5 rounded-xl hover:bg-orange-50/50 transition">
                                    <div class="flex items-center space-x-3">
                                        <div class="w-7 h-7 rounded-xl bg-orange-100 text-orange-600 font-black text-xs flex items-center justify-center">
                                            <?= $idx + 1 ?>
                                        </div>
                                        <div>
                                            <h4 class="font-bold text-slate-900 leading-tight"><?= htmlspecialchars($tm['nama_menu']) ?></h4>
                                            <span class="text-[10px] text-slate-400 capitalize"><?= $tm['kategori'] ?></span>
                                        </div>
                                    </div>
                                    <div class="text-right">
                                        <span class="font-black text-orange-600 block"><?= $tm['total_terjual'] ?> porsi</span>
                                        <span class="text-[10px] text-slate-400"><?= format_rupiah($tm['total_pendapatan']) ?></span>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="mt-5 pt-4 border-t border-slate-100">
                    <a href="menu.php" class="w-full py-2.5 bg-slate-100 hover:bg-orange-50 hover:text-orange-600 border border-slate-200 text-slate-700 font-bold text-xs text-center rounded-xl transition block">
                        Kelola Katalog Menu &rarr;
                    </a>
                </div>
            </div>

        </div>

    </main>

    <!-- Footer Persis Landing Page -->
    <footer class="bg-slate-900 text-slate-400 mt-auto pt-8 pb-6 border-t border-slate-800">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 flex flex-col sm:flex-row items-center justify-between text-[11px] text-slate-500 gap-2">
            <span>&copy; <?= date('Y') ?> Resto Enak Rasa. All rights reserved.</span>
            <span>Panel Khusus Administrator Resto</span>
        </div>
    </footer>

</body>
</html>
