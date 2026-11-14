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

    <!-- Navbar Admin Responsive -->
    <header class="sticky top-0 z-40 bg-white/95 backdrop-blur-md border-b border-slate-200">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 py-3 flex items-center justify-between">
            
            <!-- Brand / Logo -->
            <a href="index.php" class="flex items-center space-x-2 sm:space-x-2.5">
                <div class="w-9 h-9 sm:w-10 sm:h-10 rounded-2xl bg-gradient-to-tr from-orange-600 to-amber-500 text-white flex items-center justify-center font-bold text-base sm:text-lg shadow-md shadow-orange-500/20 flex-shrink-0">
                    <i class="fa-solid fa-utensils"></i>
                </div>
                <div>
                    <span class="font-extrabold text-slate-900 text-base sm:text-lg tracking-tight block leading-tight">Resto Enak Rasa</span>
                    <span class="text-[9px] sm:text-[10px] text-orange-600 font-semibold tracking-wider uppercase block">Admin Backoffice</span>
                </div>
            </a>

            <!-- Nav Links Desktop -->
            <div class="hidden md:flex items-center space-x-2 text-xs">
                <!-- Tombol Scan QR Pesanan -->
                <button type="button" onclick="openScannerModal()" class="px-3.5 py-2 bg-orange-500 hover:bg-orange-600 text-white font-bold rounded-xl shadow-md shadow-orange-500/20 active:scale-95 transition flex items-center space-x-1.5">
                    <i class="fa-solid fa-qrcode text-xs"></i>
                    <span>Scan QR</span>
                </button>
                <a href="index.php" class="px-3.5 py-2 bg-orange-500 text-white font-bold rounded-xl shadow-md shadow-orange-500/20 transition flex items-center space-x-1.5">
                    <i class="fa-solid fa-chart-pie text-xs"></i>
                    <span>Overview</span>
                </a>
                <a href="menu.php" class="px-3.5 py-2 bg-slate-100 hover:bg-orange-50 hover:text-orange-600 text-slate-700 font-bold rounded-xl border border-slate-200 transition flex items-center space-x-1.5">
                    <i class="fa-solid fa-bowl-food text-xs"></i>
                    <span>Kelola Menu</span>
                </a>
                <a href="../kasir/index.php" class="px-3.5 py-2 bg-slate-100 hover:bg-orange-50 hover:text-orange-600 text-slate-700 font-bold rounded-xl border border-slate-200 transition flex items-center space-x-1.5">
                    <i class="fa-solid fa-cash-register text-xs"></i>
                    <span>Layar Kasir</span>
                </a>
                <a href="logout.php" onclick="return confirm('Keluar dari sesi Admin?')" class="p-2 text-slate-400 hover:text-rose-600 transition" title="Logout">
                    <i class="fa-solid fa-right-from-bracket text-base"></i>
                </a>
            </div>

            <!-- Tombol Aksi Mobile (Scan QR Cepat + Hamburger Toggle) -->
            <div class="flex items-center space-x-2 md:hidden">
                <button type="button" onclick="openScannerModal()" class="px-3 py-1.5 bg-orange-500 hover:bg-orange-600 text-white font-bold rounded-xl shadow-md shadow-orange-500/20 active:scale-95 transition flex items-center space-x-1 text-xs">
                    <i class="fa-solid fa-qrcode"></i>
                    <span>Scan</span>
                </button>
                <button type="button" onclick="toggleAdminMenu()" class="w-9 h-9 flex items-center justify-center rounded-xl bg-slate-100 text-slate-700 hover:bg-orange-50 hover:text-orange-600 transition text-sm">
                    <i class="fa-solid fa-bars" id="admin-hamburger-icon"></i>
                </button>
            </div>
        </div>

        <!-- Menu Dropdown Mobile Admin -->
        <div id="admin-mobile-menu" class="hidden md:hidden border-t border-slate-100 bg-white/95 px-4 py-3 space-y-2 text-xs font-bold shadow-lg">
            <a href="index.php" class="block py-2.5 px-3 rounded-xl bg-orange-50 text-orange-600 flex items-center space-x-2.5">
                <i class="fa-solid fa-chart-pie text-sm w-4"></i>
                <span>Overview Dashboard</span>
            </a>
            <a href="menu.php" class="block py-2.5 px-3 rounded-xl text-slate-700 hover:bg-orange-50 hover:text-orange-600 transition flex items-center space-x-2.5">
                <i class="fa-solid fa-bowl-food text-sm w-4 text-orange-500"></i>
                <span>Kelola Katalog Menu</span>
            </a>
            <a href="../kasir/index.php" class="block py-2.5 px-3 rounded-xl text-slate-700 hover:bg-orange-50 hover:text-orange-600 transition flex items-center space-x-2.5">
                <i class="fa-solid fa-cash-register text-sm w-4 text-orange-500"></i>
                <span>Layar Kasir & Dapur</span>
            </a>
            <a href="../pembeli/index.php?meja=01" target="_blank" class="block py-2.5 px-3 rounded-xl text-slate-700 hover:bg-orange-50 hover:text-orange-600 transition flex items-center space-x-2.5">
                <i class="fa-solid fa-arrow-up-right-from-square text-sm w-4 text-orange-500"></i>
                <span>Preview Tampilan Pembeli</span>
            </a>
            <div class="pt-2 border-t border-slate-100">
                <a href="logout.php" onclick="return confirm('Keluar dari sesi Admin?')" class="block py-2 px-3 rounded-xl text-rose-600 hover:bg-rose-50 transition flex items-center space-x-2">
                    <i class="fa-solid fa-right-from-bracket w-4"></i>
                    <span>Logout Admin</span>
                </a>
            </div>
        </div>
    </header>

    <main class="max-w-6xl mx-auto px-4 sm:px-6 py-6 sm:py-8 flex-1 w-full space-y-6 sm:space-y-8">
        
        <!-- Welcome Banner Persis Style Hero Landing Page -->
        <div class="bg-gradient-to-b from-orange-50/60 to-slate-50 p-5 sm:p-7 md:p-8 rounded-3xl border border-slate-200 flex flex-col md:flex-row md:items-center justify-between gap-5 sm:gap-6 shadow-sm">
            <div class="space-y-2 sm:space-y-3">
                <div class="inline-flex items-center space-x-2 px-3 py-1.5 rounded-full bg-orange-100/80 text-orange-700 text-[11px] sm:text-xs font-bold border border-orange-200">
                    <span class="w-2 h-2 rounded-full bg-orange-500 animate-pulse"></span>
                    <span>Monitoring Penjualan Real-Time</span>
                </div>
                <h1 class="text-xl sm:text-2xl md:text-3xl font-black text-slate-900 leading-tight">
                    Ringkasan Kinerja Resto
                </h1>
                <p class="text-xs sm:text-sm text-slate-600 max-w-lg leading-relaxed">
                    Pantau omzet harian, transaksi pesanan meja pelanggan, serta kelola katalog menu dalam satu sistem terpadu.
                </p>
            </div>

            <div class="grid grid-cols-1 sm:flex sm:items-center gap-2.5 w-full md:w-auto">
                <button type="button" onclick="openScannerModal()" class="w-full sm:w-auto px-4 py-2.5 sm:px-5 sm:py-3 bg-orange-500 hover:bg-orange-600 text-white text-xs sm:text-sm font-bold rounded-xl shadow-md shadow-orange-500/20 active:scale-95 transition flex items-center justify-center space-x-2">
                    <i class="fa-solid fa-qrcode text-sm"></i>
                    <span>Scan QR Validasi</span>
                </button>
                <a href="menu.php" class="w-full sm:w-auto px-4 py-2.5 sm:py-3 bg-white hover:bg-orange-50 hover:text-orange-600 text-slate-700 text-xs sm:text-sm font-bold rounded-xl border border-slate-200 transition flex items-center justify-center space-x-2">
                    <i class="fa-solid fa-plus text-xs"></i>
                    <span>Tambah Menu</span>
                </a>
                <a href="../pembeli/index.php?meja=01" target="_blank" class="w-full sm:w-auto px-4 py-2.5 sm:py-3 bg-white hover:bg-orange-50 hover:text-orange-600 text-slate-700 text-xs sm:text-sm font-bold rounded-xl border border-slate-200 transition flex items-center justify-center space-x-2">
                    <i class="fa-solid fa-arrow-up-right-from-square text-xs"></i>
                    <span>Tampilan Pembeli</span>
                </a>
            </div>
        </div>

        <!-- 4 Kartu Statistik (2 Kolom di Mobile, 4 di Desktop) -->
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-5">
            <!-- Omzet Hari Ini -->
            <div class="bg-white p-3.5 sm:p-5 rounded-2xl border border-slate-200 shadow-sm hover:shadow-md transition flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                <div>
                    <span class="text-[10px] sm:text-xs font-bold text-orange-600 uppercase tracking-widest block">Omzet Hari Ini</span>
                    <h3 class="text-base sm:text-2xl font-black text-slate-900 mt-0.5 sm:mt-1"><?= format_rupiah($omzet_hari_ini) ?></h3>
                    <span class="text-[10px] sm:text-xs text-slate-500 mt-0.5 block truncate"><?= $pesanan_hari_ini ?> transaksi hari ini</span>
                </div>
                <div class="w-10 h-10 sm:w-12 sm:h-12 rounded-xl sm:rounded-2xl bg-orange-100 text-orange-600 flex items-center justify-center text-base sm:text-xl font-black self-end sm:self-auto flex-shrink-0">
                    <i class="fa-solid fa-calendar-day"></i>
                </div>
            </div>

            <!-- Total Keseluruhan Omzet -->
            <div class="bg-white p-3.5 sm:p-5 rounded-2xl border border-slate-200 shadow-sm hover:shadow-md transition flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                <div>
                    <span class="text-[10px] sm:text-xs font-bold text-orange-600 uppercase tracking-widest block">Total Omzet</span>
                    <h3 class="text-base sm:text-2xl font-black text-slate-900 mt-0.5 sm:mt-1"><?= format_rupiah($total_omzet) ?></h3>
                    <span class="text-[10px] sm:text-xs text-slate-500 mt-0.5 block truncate"><?= $total_pesanan_lunas ?> pesanan lunas</span>
                </div>
                <div class="w-10 h-10 sm:w-12 sm:h-12 rounded-xl sm:rounded-2xl bg-orange-100 text-orange-600 flex items-center justify-center text-base sm:text-xl font-black self-end sm:self-auto flex-shrink-0">
                    <i class="fa-solid fa-sack-dollar"></i>
                </div>
            </div>

            <!-- Total Katalog Menu -->
            <div class="bg-white p-3.5 sm:p-5 rounded-2xl border border-slate-200 shadow-sm hover:shadow-md transition flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                <div>
                    <span class="text-[10px] sm:text-xs font-bold text-orange-600 uppercase tracking-widest block">Total Menu</span>
                    <h3 class="text-base sm:text-2xl font-black text-slate-900 mt-0.5 sm:mt-1"><?= $total_menu ?> Menu</h3>
                    <span class="text-[10px] sm:text-xs text-rose-500 font-semibold mt-0.5 block truncate"><?= $total_menu_habis ?> menu habis</span>
                </div>
                <div class="w-10 h-10 sm:w-12 sm:h-12 rounded-xl sm:rounded-2xl bg-orange-100 text-orange-600 flex items-center justify-center text-base sm:text-xl font-black self-end sm:self-auto flex-shrink-0">
                    <i class="fa-solid fa-utensils"></i>
                </div>
            </div>

            <!-- Mode Kasir Link -->
            <a href="../kasir/index.php" class="bg-white p-3.5 sm:p-5 rounded-2xl border border-slate-200 shadow-sm hover:shadow-md hover:border-orange-300 transition flex flex-col sm:flex-row sm:items-center justify-between gap-2 group">
                <div>
                    <span class="text-[10px] sm:text-xs font-bold text-orange-600 uppercase tracking-widest block">Akses Kasir</span>
                    <h3 class="text-sm sm:text-base font-black text-slate-900 mt-0.5 sm:mt-1 group-hover:text-orange-600 transition">Buka Kasir</h3>
                    <span class="text-[10px] sm:text-xs text-slate-500 mt-0.5 block truncate">Antrean & Dapur &rarr;</span>
                </div>
                <div class="w-10 h-10 sm:w-12 sm:h-12 rounded-xl sm:rounded-2xl bg-slate-100 text-slate-600 group-hover:bg-orange-100 group-hover:text-orange-600 flex items-center justify-center text-base sm:text-xl font-black self-end sm:self-auto flex-shrink-0 transition">
                    <i class="fa-solid fa-cash-register"></i>
                </div>
            </a>
        </div>

        <!-- Bagian Tabel: Riwayat Transaksi & Top Menu -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 items-start">
            
            <!-- Tabel Riwayat Transaksi Terbaru (2 Kolom di Desktop) -->
            <div class="lg:col-span-2 bg-white rounded-3xl border border-slate-200 shadow-sm overflow-hidden">
                <div class="p-4 sm:p-5 border-b border-slate-200 flex items-center justify-between gap-2">
                    <div>
                        <span class="text-[10px] sm:text-xs font-bold text-orange-600 uppercase tracking-widest">Aktivitas Terkini</span>
                        <h3 class="font-black text-sm sm:text-base text-slate-900 mt-0.5">Riwayat Pesanan Pelanggan</h3>
                    </div>
                    <a href="../kasir/index.php" class="text-xs font-bold text-orange-600 hover:text-orange-700 inline-flex items-center space-x-1 flex-shrink-0">
                        <span>Ke Kasir</span>
                        <i class="fa-solid fa-arrow-right text-[10px]"></i>
                    </a>
                </div>

                <!-- Kontainer Tabel dengan Responsif Horizontal Scroll -->
                <div class="overflow-x-auto" style="-webkit-overflow-scrolling: touch;">
                    <table class="w-full text-left text-xs min-w-[620px]">
                        <thead class="bg-slate-50 text-slate-500 uppercase border-b border-slate-200 font-bold text-[11px]">
                            <tr>
                                <th class="py-3 px-4">Kode & Meja</th>
                                <th class="py-3 px-3">Pelanggan</th>
                                <th class="py-3 px-3">Total Tagihan</th>
                                <th class="py-3 px-3 text-center">Pembayaran</th>
                                <th class="py-3 px-3 text-center">Status Dapur</th>
                                <th class="py-3 px-4 text-right">Waktu</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <?php if (empty($riwayat_orders)): ?>
                                <tr>
                                    <td colspan="6" class="py-10 text-center text-slate-400">Belum ada transaksi.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($riwayat_orders as $ro): ?>
                                    <tr class="hover:bg-orange-50/50 transition">
                                        <td class="py-3 px-4">
                                            <span class="font-extrabold text-slate-900 block"><?= htmlspecialchars($ro['kode_pesanan']) ?></span>
                                            <span class="text-[10px] font-bold text-orange-600 bg-orange-50 px-2 py-0.5 rounded-full uppercase border border-orange-200 inline-block mt-0.5">
                                                Meja <?= htmlspecialchars($ro['nomor_meja']) ?>
                                            </span>
                                        </td>
                                        <td class="py-3 px-3 font-semibold text-slate-700">
                                            <?= htmlspecialchars($ro['nama_pelanggan']) ?>
                                        </td>
                                        <td class="py-3 px-3 font-black text-slate-900">
                                            <?= format_rupiah($ro['total_harga']) ?>
                                        </td>
                                        <td class="py-3 px-3 text-center">
                                            <?php if ($ro['status_pembayaran'] === 'lunas'): ?>
                                                <span class="px-2.5 py-1 bg-orange-500 text-white text-[10px] font-bold rounded-lg shadow-sm inline-block">
                                                    LUNAS
                                                </span>
                                            <?php else: ?>
                                                <form action="../kasir/update_status.php" method="POST" class="inline">
                                                    <input type="hidden" name="order_id" value="<?= $ro['id'] ?>">
                                                    <input type="hidden" name="action" value="set_lunas">
                                                    <button type="submit" class="px-2.5 py-1 bg-amber-500 hover:bg-amber-600 text-white font-bold text-[10px] rounded-lg shadow-sm transition active:scale-95" title="Klik untuk validasi pembayaran">
                                                        Set Lunas
                                                    </button>
                                                </form>
                                            <?php endif; ?>
                                        </td>
                                        <td class="py-3 px-3 text-center">
                                            <div class="inline-flex items-center gap-1">
                                                <?php if ($ro['status_pesanan'] === 'menunggu'): ?>
                                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-slate-100 text-slate-600 border border-slate-200">
                                                        Menunggu
                                                    </span>
                                                    <form action="../kasir/update_status.php" method="POST" class="inline">
                                                        <input type="hidden" name="order_id" value="<?= $ro['id'] ?>">
                                                        <input type="hidden" name="status_pesanan" value="dimasak">
                                                        <button type="submit" class="px-2 py-0.5 rounded bg-orange-500 hover:bg-orange-600 text-white font-bold text-[10px] transition active:scale-95" title="Mulai masak">
                                                            Masak
                                                        </button>
                                                    </form>
                                                <?php elseif ($ro['status_pesanan'] === 'dimasak'): ?>
                                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-orange-100 text-orange-800 border border-orange-200">
                                                        Dimasak
                                                    </span>
                                                    <form action="../kasir/update_status.php" method="POST" class="inline">
                                                        <input type="hidden" name="order_id" value="<?= $ro['id'] ?>">
                                                        <input type="hidden" name="status_pesanan" value="selesai">
                                                        <button type="submit" class="px-2 py-0.5 rounded bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-[10px] transition active:scale-95" title="Selesaikan & antar">
                                                            Selesai
                                                        </button>
                                                    </form>
                                                <?php elseif ($ro['status_pesanan'] === 'selesai'): ?>
                                                    <span class="px-2.5 py-0.5 rounded text-[10px] font-bold bg-emerald-100 text-emerald-800 border border-emerald-200">
                                                        <i class="fa-solid fa-check mr-0.5"></i> Selesai
                                                    </span>
                                                <?php else: ?>
                                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-rose-100 text-rose-800">
                                                        Batal
                                                    </span>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                        <td class="py-3 px-4 text-slate-400 text-right">
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
            <div class="bg-white rounded-3xl border border-slate-200 shadow-sm p-5 flex flex-col justify-between">
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
                                        <div class="w-7 h-7 rounded-xl bg-orange-100 text-orange-600 font-black text-xs flex items-center justify-center flex-shrink-0">
                                            <?= $idx + 1 ?>
                                        </div>
                                        <div class="min-w-0">
                                            <h4 class="font-bold text-slate-900 leading-tight truncate"><?= htmlspecialchars($tm['nama_menu']) ?></h4>
                                            <span class="text-[10px] text-slate-400 capitalize"><?= $tm['kategori'] ?></span>
                                        </div>
                                    </div>
                                    <div class="text-right flex-shrink-0 ml-2">
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

    <!-- Footer Cerah & Bersih (Bukan Gelap) -->
    <footer class="bg-white text-slate-500 mt-auto pt-6 pb-6 border-t border-slate-200">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 flex flex-col sm:flex-row items-center justify-between text-[11px] text-slate-500 gap-2 text-center sm:text-left">
            <span>&copy; <?= date('Y') ?> Resto Enak Rasa. All rights reserved.</span>
            <span class="font-semibold text-orange-600">Panel Khusus Administrator Resto</span>
        </div>
    </footer>

    <!-- Script Toggle Navigasi Mobile Admin -->
    <script>
        function toggleAdminMenu() {
            const menu = document.getElementById('admin-mobile-menu');
            const icon = document.getElementById('admin-hamburger-icon');
            if (menu.classList.contains('hidden')) {
                menu.classList.remove('hidden');
                icon.className = 'fa-solid fa-xmark text-orange-600';
            } else {
                menu.classList.add('hidden');
                icon.className = 'fa-solid fa-bars';
            }
        }
    </script>

    <!-- Modal Scanner QR Code -->
    <?php include_once __DIR__ . '/../components/scanner_modal.php'; ?>
</body>
</html>
