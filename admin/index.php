<?php
// admin/index.php
// [BAGIAN 1: PROGRAMMER 1] Dashboard Utama Administrator & Rekap Penjualan

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
</head>
<body class="bg-slate-100 min-h-screen text-slate-800 flex flex-col">

    <!-- Top Navbar Admin -->
    <header class="bg-slate-900 text-white shadow-md sticky top-0 z-30">
        <div class="max-w-7xl mx-auto px-4 py-3 flex items-center justify-between">
            <div class="flex items-center space-x-3">
                <div class="w-10 h-10 rounded-xl bg-emerald-600 text-white flex items-center justify-center font-bold text-lg shadow-md shadow-emerald-500/20">
                    <i class="fa-solid fa-chart-line"></i>
                </div>
                <div>
                    <h1 class="font-bold text-base leading-tight">Admin Backoffice</h1>
                    <span class="text-xs text-slate-400">Selamat datang, <b><?= htmlspecialchars($_SESSION['nama'] ?? 'Admin') ?></b> (Administrator)</span>
                </div>
            </div>

            <!-- Navigasi Menu Admin -->
            <div class="flex items-center space-x-2 text-xs">
                <a href="index.php" class="px-3 py-2 rounded-lg bg-emerald-600 text-white font-bold flex items-center space-x-1.5 shadow-sm">
                    <i class="fa-solid fa-house"></i>
                    <span class="hidden sm:inline">Overview</span>
                </a>
                <a href="menu.php" class="px-3 py-2 rounded-lg bg-slate-800 hover:bg-slate-700 text-slate-200 font-semibold flex items-center space-x-1.5 transition">
                    <i class="fa-solid fa-bowl-food"></i>
                    <span>Kelola Menu</span>
                </a>
                <a href="../kasir/index.php" class="px-3 py-2 rounded-lg bg-slate-800 hover:bg-slate-700 text-slate-200 font-semibold flex items-center space-x-1.5 transition">
                    <i class="fa-solid fa-cash-register"></i>
                    <span class="hidden sm:inline">Layar Kasir</span>
                </a>
                <a href="logout.php" onclick="return confirm('Keluar dari sesi Admin?')" class="px-3 py-2 rounded-lg bg-rose-600/80 hover:bg-rose-600 text-white font-bold flex items-center space-x-1.5 transition ml-2">
                    <i class="fa-solid fa-right-from-bracket"></i>
                    <span class="hidden sm:inline">Logout</span>
                </a>
            </div>
        </div>
    </header>

    <main class="max-w-7xl mx-auto px-4 py-6 flex-1 w-full space-y-6">
        
        <!-- Welcome Banner -->
        <div class="bg-gradient-to-r from-emerald-600 to-teal-700 rounded-2xl p-6 text-white shadow-lg shadow-emerald-600/10 flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <h2 class="text-xl font-black">Ringkasan Kinerja Penjualan</h2>
                <p class="text-emerald-100 text-xs mt-1">Pantau total omzet, transaksi harian, dan menu favorit pelanggan secara real-time.</p>
            </div>
            <div class="flex items-center gap-3">
                <a href="menu.php" class="px-4 py-2 bg-white text-emerald-800 hover:bg-emerald-50 rounded-xl text-xs font-bold transition flex items-center space-x-1.5 shadow-sm">
                    <i class="fa-solid fa-plus-circle"></i>
                    <span>Tambah Menu Baru</span>
                </a>
                <a href="../pembeli/index.php?meja=01" target="_blank" class="px-4 py-2 bg-emerald-800/60 hover:bg-emerald-800 text-white border border-emerald-400/30 rounded-xl text-xs font-bold transition flex items-center space-x-1.5">
                    <i class="fa-solid fa-arrow-up-right-from-square"></i>
                    <span>Buka Tampilan Pembeli</span>
                </a>
            </div>
        </div>

        <!-- 4 Kartu Statistik -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <!-- Omzet Hari Ini -->
            <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm flex items-center justify-between">
                <div>
                    <span class="text-[11px] font-bold text-emerald-600 uppercase tracking-wider block">Omzet Hari Ini</span>
                    <h3 class="text-2xl font-black text-slate-900 mt-1"><?= format_rupiah($omzet_hari_ini) ?></h3>
                    <span class="text-[11px] text-slate-400 mt-0.5 block"><?= $pesanan_hari_ini ?> transaksi hari ini</span>
                </div>
                <div class="w-12 h-12 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-xl">
                    <i class="fa-solid fa-calendar-day"></i>
                </div>
            </div>

            <!-- Total Keseluruhan Omzet -->
            <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm flex items-center justify-between">
                <div>
                    <span class="text-[11px] font-bold text-blue-600 uppercase tracking-wider block">Total Pendapatan</span>
                    <h3 class="text-2xl font-black text-slate-900 mt-1"><?= format_rupiah($total_omzet) ?></h3>
                    <span class="text-[11px] text-slate-400 mt-0.5 block"><?= $total_pesanan_lunas ?> pesanan lunas</span>
                </div>
                <div class="w-12 h-12 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center text-xl">
                    <i class="fa-solid fa-sack-dollar"></i>
                </div>
            </div>

            <!-- Total Katalog Menu -->
            <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm flex items-center justify-between">
                <div>
                    <span class="text-[11px] font-bold text-amber-600 uppercase tracking-wider block">Total Menu Aktif</span>
                    <h3 class="text-2xl font-black text-slate-900 mt-1"><?= $total_menu ?> Menu</h3>
                    <span class="text-[11px] text-rose-500 mt-0.5 block"><?= $total_menu_habis ?> menu sedang habis</span>
                </div>
                <div class="w-12 h-12 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center text-xl">
                    <i class="fa-solid fa-utensils"></i>
                </div>
            </div>

            <!-- Mode Kasir Link -->
            <a href="../kasir/index.php" class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm flex items-center justify-between hover:border-indigo-400 transition group">
                <div>
                    <span class="text-[11px] font-bold text-indigo-600 uppercase tracking-wider block">Akses Kasir</span>
                    <h3 class="text-base font-black text-slate-900 mt-1 group-hover:text-indigo-600 transition">Buka Antrean & Cetak</h3>
                    <span class="text-[11px] text-slate-400 mt-0.5 block">Monitor pesanan dapur</span>
                </div>
                <div class="w-12 h-12 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center text-xl group-hover:scale-110 transition">
                    <i class="fa-solid fa-cash-register"></i>
                </div>
            </a>
        </div>

        <!-- Bagian Tabel: Riwayat Transaksi & Top Menu -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            
            <!-- Tabel Riwayat Transaksi Terbaru (2 Kolom) -->
            <div class="lg:col-span-2 bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
                <div class="p-4 border-b border-slate-100 flex items-center justify-between">
                    <div>
                        <h3 class="font-bold text-sm text-slate-900">Riwayat Pesanan Terbaru</h3>
                        <p class="text-xs text-slate-400">15 transaksi terakhir yang masuk ke sistem</p>
                    </div>
                    <a href="../kasir/index.php" class="text-xs text-blue-600 hover:underline font-semibold">Ke Kasir &rarr;</a>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs">
                        <thead class="bg-slate-50 text-slate-500 uppercase border-b border-slate-100 font-semibold">
                            <tr>
                                <th class="p-3">Kode / Meja</th>
                                <th class="p-3">Pelanggan</th>
                                <th class="p-3">Total Tagihan</th>
                                <th class="p-3">Status Bayar</th>
                                <th class="p-3">Waktu</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <?php if (empty($riwayat_orders)): ?>
                                <tr>
                                    <td colspan="5" class="p-6 text-center text-slate-400">Belum ada transaksi.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($riwayat_orders as $ro): ?>
                                    <tr class="hover:bg-slate-50 transition">
                                        <td class="p-3">
                                            <span class="font-bold text-slate-800 block"><?= htmlspecialchars($ro['kode_pesanan']) ?></span>
                                            <span class="text-[10px] font-bold text-orange-600 bg-orange-50 px-1.5 py-0.5 rounded border border-orange-200">
                                                Meja #<?= htmlspecialchars($ro['nomor_meja']) ?>
                                            </span>
                                        </td>
                                        <td class="p-3 font-semibold text-slate-700">
                                            <?= htmlspecialchars($ro['nama_pelanggan']) ?>
                                        </td>
                                        <td class="p-3 font-bold text-slate-900">
                                            <?= format_rupiah($ro['total_harga']) ?>
                                        </td>
                                        <td class="p-3">
                                            <?php if ($ro['status_pembayaran'] === 'lunas'): ?>
                                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800">
                                                    LUNAS
                                                </span>
                                            <?php else: ?>
                                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-100 text-amber-800">
                                                    BELUM BAYAR
                                                </span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="p-3 text-slate-400 text-[11px]">
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
                    <div class="border-b border-slate-100 pb-3 mb-4">
                        <h3 class="font-bold text-sm text-slate-900 flex items-center space-x-2">
                            <i class="fa-solid fa-trophy text-amber-500"></i>
                            <span>Menu Terlaris</span>
                        </h3>
                        <p class="text-xs text-slate-400">Berdasarkan pesanan lunas</p>
                    </div>

                    <div class="space-y-3">
                        <?php if (empty($top_menus)): ?>
                            <p class="text-xs text-slate-400 text-center py-6">Belum ada data penjualan menu lunas.</p>
                        <?php else: ?>
                            <?php foreach ($top_menus as $idx => $tm): ?>
                                <div class="flex items-center justify-between text-xs border-b border-slate-50 pb-2">
                                    <div class="flex items-center space-x-2.5">
                                        <span class="w-5 h-5 rounded-full bg-slate-100 text-slate-600 font-bold flex items-center justify-center text-[10px]">
                                            <?= $idx + 1 ?>
                                        </span>
                                        <div>
                                            <h4 class="font-bold text-slate-800"><?= htmlspecialchars($tm['nama_menu']) ?></h4>
                                            <span class="text-[10px] text-slate-400 capitalize"><?= $tm['kategori'] ?></span>
                                        </div>
                                    </div>
                                    <div class="text-right">
                                        <span class="font-bold text-emerald-600 block"><?= $tm['total_terjual'] ?> terjual</span>
                                        <span class="text-[10px] text-slate-400"><?= format_rupiah($tm['total_pendapatan']) ?></span>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="mt-5 pt-4 border-t border-slate-100">
                    <a href="menu.php" class="w-full block py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs text-center rounded-xl transition">
                        Kelola Semua Menu &rarr;
                    </a>
                </div>
            </div>

        </div>

    </main>

    <footer class="bg-white border-t border-slate-200 py-3 text-center text-xs text-slate-400">
        Panel Admin Resto & Cafe &bull; PHP Native &bull; <?= date('Y') ?>
    </footer>
</body>
</html>

