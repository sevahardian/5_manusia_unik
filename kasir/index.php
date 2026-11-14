<?php
// kasir/index.php
// [BAGIAN 3: PROGRAMMER 3] Dashboard Kasir / Dapur - Monitoring Pesanan Realtime
// Didesain Harmonis dengan Tema Landing Page & Admin (Warm Culinary Theme) serta Full Responsive

require_once __DIR__ . '/../config/koneksi.php';
require_once __DIR__ . '/../config/helper.php';

// Pastikan hanya kasir atau admin yang bisa akses
check_auth('kasir');

// Ambil semua pesanan terbaru
$stmt = $pdo->query("
    SELECT * FROM orders 
    ORDER BY CASE 
        WHEN status_pesanan = 'menunggu' THEN 1 
        WHEN status_pesanan = 'dimasak' THEN 2 
        ELSE 3 
    END, id DESC
    LIMIT 60
");
$orders = $stmt->fetchAll();

// Statistik Kilat
$stat_menunggu = $pdo->query("SELECT COUNT(*) FROM orders WHERE status_pesanan = 'menunggu'")->fetchColumn();
$stat_dimasak  = $pdo->query("SELECT COUNT(*) FROM orders WHERE status_pesanan = 'dimasak'")->fetchColumn();
$stat_selesai  = $pdo->query("SELECT COUNT(*) FROM orders WHERE status_pesanan = 'selesai'")->fetchColumn();
$stat_omzet    = $pdo->query("SELECT SUM(total_harga) FROM orders WHERE status_pembayaran = 'lunas'")->fetchColumn() ?: 0;
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Layar Kasir & Dapur - KanDar</title>
    
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        brand: {
                            50: '#fff7ed',
                            100: '#ffedd5',
                            200: '#fed7aa',
                            300: '#fdba74',
                            400: '#fb923c',
                            500: '#f97316',
                            600: '#ea580c',
                            700: '#c2410c',
                            800: '#9a3412',
                            900: '#7c2d12',
                        }
                    }
                }
            }
        }
    </script>

    <!-- Font Awesome Icons & Google Fonts -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">

    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
        .no-scrollbar::-webkit-scrollbar { display: none; }
        .no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
    </style>
</head>
<body class="bg-slate-50 min-h-screen text-slate-800 flex flex-col antialiased selection:bg-brand-500 selection:text-white">

    <!-- Navbar Kasir Responsive (Warm Culinary Style Persis Landing Page & Admin) -->
    <header class="sticky top-0 z-40 bg-white/95 backdrop-blur-md border-b border-slate-200/80 shadow-sm">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-3 flex items-center justify-between gap-3">
            
            <!-- Brand / Logo -->
            <a href="index.php" class="flex items-center space-x-2.5 sm:space-x-3">
                <div class="w-10 h-10 rounded-2xl bg-gradient-to-tr from-brand-600 to-amber-500 text-white flex items-center justify-center font-bold text-lg shadow-md shadow-brand-500/25 flex-shrink-0">
                    <i class="fa-solid fa-cash-register"></i>
                </div>
                <div>
                    <span class="font-extrabold text-slate-900 text-base sm:text-lg tracking-tight block leading-tight">KanDar</span>
                    <div class="flex items-center space-x-1.5">
                        <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                        <span class="text-[10px] text-brand-600 font-bold uppercase tracking-wider block">Layar Kasir & Dapur</span>
                    </div>
                </div>
            </a>

            <!-- Nav Links Desktop -->
            <div class="hidden md:flex items-center space-x-2 text-xs">
                <!-- Tombol Scan QR Pesanan Utama -->
                <button type="button" onclick="openScannerModal()" class="px-3.5 py-2 bg-brand-500 hover:bg-brand-600 text-white font-extrabold rounded-xl shadow-md shadow-brand-500/25 active:scale-95 transition flex items-center space-x-1.5">
                    <i class="fa-solid fa-qrcode text-xs"></i>
                    <span>Scan QR Validasi</span>
                </button>

                <!-- Tombol Refresh Manual -->
                <button type="button" onclick="fetchOrders()" class="px-3 py-2 bg-slate-100 hover:bg-brand-50 hover:text-brand-600 text-slate-700 font-bold rounded-xl border border-slate-200 transition flex items-center space-x-1" title="Muat Ulang Pesanan">
                    <i class="fa-solid fa-arrows-rotate" id="refresh-icon"></i>
                    <span>Refresh</span>
                </button>

                <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
                    <a href="../admin/index.php" class="px-3.5 py-2 bg-slate-100 hover:bg-brand-50 hover:text-brand-600 text-slate-700 font-bold rounded-xl border border-slate-200 transition flex items-center space-x-1.5">
                        <i class="fa-solid fa-chart-pie text-xs"></i>
                        <span>Panel Admin</span>
                    </a>
                    <a href="../admin/menu.php" class="px-3.5 py-2 bg-slate-100 hover:bg-brand-50 hover:text-brand-600 text-slate-700 font-bold rounded-xl border border-slate-200 transition flex items-center space-x-1.5">
                        <i class="fa-solid fa-bowl-food text-xs"></i>
                        <span>Katalog Menu</span>
                    </a>
                <?php endif; ?>

                <a href="../pembeli/index.php?meja=01" target="_blank" class="px-3 py-2 bg-slate-100 hover:bg-brand-50 hover:text-brand-600 text-slate-700 font-bold rounded-xl border border-slate-200 transition flex items-center space-x-1">
                    <i class="fa-solid fa-arrow-up-right-from-square text-xs"></i>
                    <span>Mode Meja</span>
                </a>

                <a href="../admin/logout.php" onclick="return confirm('Keluar dari sesi Kasir?')" class="p-2 text-slate-400 hover:text-rose-600 transition" title="Logout Kasir">
                    <i class="fa-solid fa-right-from-bracket text-base"></i>
                </a>
            </div>

            <!-- Tombol Aksi Mobile (Scan Cepat + Hamburger Toggle) -->
            <div class="flex items-center space-x-2 md:hidden">
                <button type="button" onclick="openScannerModal()" class="px-3 py-2 bg-brand-500 hover:bg-brand-600 text-white font-extrabold rounded-xl shadow-md shadow-brand-500/25 active:scale-95 transition flex items-center space-x-1 text-xs">
                    <i class="fa-solid fa-qrcode"></i>
                    <span>Scan</span>
                </button>
                <button type="button" onclick="toggleKasirMenu()" class="w-9 h-9 flex items-center justify-center rounded-xl bg-slate-100 text-slate-700 hover:bg-brand-50 hover:text-brand-600 transition text-sm">
                    <i class="fa-solid fa-bars" id="kasir-hamburger-icon"></i>
                </button>
            </div>
        </div>

        <!-- Menu Dropdown Mobile Kasir -->
        <div id="kasir-mobile-menu" class="hidden md:hidden border-t border-slate-100 bg-white/95 px-4 py-3 space-y-2 text-xs font-bold shadow-lg">
            <button type="button" onclick="openScannerModal(); toggleKasirMenu();" class="w-full text-left py-2.5 px-3 rounded-xl bg-brand-50 text-brand-600 flex items-center space-x-2.5">
                <i class="fa-solid fa-qrcode text-sm w-4"></i>
                <span>Scan QR Pembayaran (Kamera)</span>
            </button>
            <button type="button" onclick="fetchOrders()" class="w-full text-left py-2.5 px-3 rounded-xl text-slate-700 hover:bg-brand-50 hover:text-brand-600 transition flex items-center space-x-2.5">
                <i class="fa-solid fa-arrows-rotate text-sm w-4 text-brand-500"></i>
                <span>Refresh Antrean Pesanan</span>
            </button>
            <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
                <a href="../admin/index.php" class="block py-2.5 px-3 rounded-xl text-slate-700 hover:bg-brand-50 hover:text-brand-600 transition flex items-center space-x-2.5">
                    <i class="fa-solid fa-chart-pie text-sm w-4 text-brand-500"></i>
                    <span>Kembali ke Dashboard Admin</span>
                </a>
                <a href="../admin/menu.php" class="block py-2.5 px-3 rounded-xl text-slate-700 hover:bg-brand-50 hover:text-brand-600 transition flex items-center space-x-2.5">
                    <i class="fa-solid fa-bowl-food text-sm w-4 text-brand-500"></i>
                    <span>Kelola Katalog Menu</span>
                </a>
            <?php endif; ?>
            <a href="../pembeli/index.php?meja=01" target="_blank" class="block py-2.5 px-3 rounded-xl text-slate-700 hover:bg-brand-50 hover:text-brand-600 transition flex items-center space-x-2.5">
                <i class="fa-solid fa-arrow-up-right-from-square text-sm w-4 text-brand-500"></i>
                <span>Buka Tampilan Pembeli (Meja 01)</span>
            </a>
            <div class="pt-2 border-t border-slate-100">
                <a href="../admin/logout.php" onclick="return confirm('Keluar dari sesi Kasir?')" class="block py-2 px-3 rounded-xl text-rose-600 hover:bg-rose-50 transition flex items-center space-x-2">
                    <i class="fa-solid fa-right-from-bracket w-4"></i>
                    <span>Logout Kasir</span>
                </a>
            </div>
        </div>
    </header>

    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-5 sm:py-7 flex-1 w-full space-y-6">

        <!-- 4 Kartu Statistik Kilat (2 Kolom di Mobile, 4 di Desktop) -->
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-5">
            <!-- Pesanan Baru (Menunggu) -->
            <div class="bg-white p-3.5 sm:p-5 rounded-3xl border border-slate-200/80 shadow-sm hover:shadow-md transition flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                <div>
                    <span class="text-[10px] sm:text-xs font-bold text-amber-600 uppercase tracking-widest block">Pesanan Baru</span>
                    <h3 id="stat-menunggu" class="text-xl sm:text-3xl font-black text-slate-900 mt-0.5 sm:mt-1"><?= $stat_menunggu ?></h3>
                    <span class="text-[10px] sm:text-xs text-slate-400 mt-0.5 block">Menunggu validasi</span>
                </div>
                <div class="w-10 h-10 sm:w-12 sm:h-12 rounded-2xl bg-amber-50 text-amber-500 flex items-center justify-center text-lg sm:text-xl font-black self-end sm:self-auto flex-shrink-0">
                    <i class="fa-solid fa-bell"></i>
                </div>
            </div>

            <!-- Sedang Dimasak -->
            <div class="bg-white p-3.5 sm:p-5 rounded-3xl border border-slate-200/80 shadow-sm hover:shadow-md transition flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                <div>
                    <span class="text-[10px] sm:text-xs font-bold text-brand-600 uppercase tracking-widest block">Sedang Dimasak</span>
                    <h3 id="stat-dimasak" class="text-xl sm:text-3xl font-black text-slate-900 mt-0.5 sm:mt-1"><?= $stat_dimasak ?></h3>
                    <span class="text-[10px] sm:text-xs text-slate-400 mt-0.5 block">Proses koki dapur</span>
                </div>
                <div class="w-10 h-10 sm:w-12 sm:h-12 rounded-2xl bg-brand-50 text-brand-500 flex items-center justify-center text-lg sm:text-xl font-black self-end sm:self-auto flex-shrink-0">
                    <i class="fa-solid fa-fire-burner"></i>
                </div>
            </div>

            <!-- Pesanan Selesai -->
            <div class="bg-white p-3.5 sm:p-5 rounded-3xl border border-slate-200/80 shadow-sm hover:shadow-md transition flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                <div>
                    <span class="text-[10px] sm:text-xs font-bold text-emerald-600 uppercase tracking-widest block">Selesai Disajikan</span>
                    <h3 id="stat-selesai" class="text-xl sm:text-3xl font-black text-slate-900 mt-0.5 sm:mt-1"><?= $stat_selesai ?></h3>
                    <span class="text-[10px] sm:text-xs text-slate-400 mt-0.5 block">Tuntas diantar</span>
                </div>
                <div class="w-10 h-10 sm:w-12 sm:h-12 rounded-2xl bg-emerald-50 text-emerald-500 flex items-center justify-center text-lg sm:text-xl font-black self-end sm:self-auto flex-shrink-0">
                    <i class="fa-solid fa-circle-check"></i>
                </div>
            </div>

            <!-- Total Omzet Lunas -->
            <div class="bg-white p-3.5 sm:p-5 rounded-3xl border border-slate-200/80 shadow-sm hover:shadow-md transition flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                <div>
                    <span class="text-[10px] sm:text-xs font-bold text-slate-500 uppercase tracking-widest block">Total Omzet Lunas</span>
                    <h3 class="text-base sm:text-2xl font-black text-slate-900 mt-0.5 sm:mt-1 truncate"><?= format_rupiah($stat_omzet) ?></h3>
                    <span class="text-[10px] sm:text-xs text-emerald-600 font-bold mt-0.5 block">Uang masuk</span>
                </div>
                <div class="w-10 h-10 sm:w-12 sm:h-12 rounded-2xl bg-slate-100 text-slate-700 flex items-center justify-center text-lg sm:text-xl font-black self-end sm:self-auto flex-shrink-0">
                    <i class="fa-solid fa-sack-dollar"></i>
                </div>
            </div>
        </div>

        <!-- Filter Tab Antrean & Barcode Shortcut Info -->
        <div class="bg-white p-4 rounded-3xl border border-slate-200/80 shadow-sm flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <!-- Filter Pills -->
            <div class="flex items-center space-x-2 overflow-x-auto no-scrollbar pb-1 sm:pb-0">
                <button type="button" onclick="filterOrderList('semua')" id="tab-order-semua" class="tab-order-btn px-3.5 py-1.5 rounded-xl text-xs font-bold transition bg-brand-500 text-white shadow-md shadow-brand-500/25">
                    Semua (<?= count($orders) ?>)
                </button>
                <button type="button" onclick="filterOrderList('menunggu')" id="tab-order-menunggu" class="tab-order-btn px-3.5 py-1.5 rounded-xl text-xs font-bold transition bg-slate-100 text-slate-600 hover:bg-slate-200">
                    Menunggu (<?= $stat_menunggu ?>)
                </button>
                <button type="button" onclick="filterOrderList('dimasak')" id="tab-order-dimasak" class="tab-order-btn px-3.5 py-1.5 rounded-xl text-xs font-bold transition bg-slate-100 text-slate-600 hover:bg-slate-200">
                    Dimasak (<?= $stat_dimasak ?>)
                </button>
                <button type="button" onclick="filterOrderList('selesai')" id="tab-order-selesai" class="tab-order-btn px-3.5 py-1.5 rounded-xl text-xs font-bold transition bg-slate-100 text-slate-600 hover:bg-slate-200">
                    Selesai (<?= $stat_selesai ?>)
                </button>
            </div>

            <!-- Petunjuk Scanner Gun & Live Polling -->
            <div class="flex items-center space-x-2 text-[11px] text-slate-400 font-semibold self-start sm:self-auto">
                <span class="inline-flex items-center gap-1 bg-slate-100 px-2.5 py-1 rounded-lg border border-slate-200">
                    <i class="fa-solid fa-barcode text-slate-600"></i>
                    <span>Scanner Gun Ready</span>
                </span>
                <span class="inline-flex items-center gap-1 bg-emerald-50 text-emerald-700 px-2.5 py-1 rounded-lg border border-emerald-200">
                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                    <span>Live 3.5s</span>
                </span>
            </div>
        </div>

        <!-- Daftar Kartu Pesanan Masuk (1 Kolom Mobile, 2 Kolom Tablet, 3 Kolom Desktop) -->
        <div id="orders-grid" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 sm:gap-5">
            <?php if (empty($orders)): ?>
                <div class="col-span-full bg-white rounded-3xl p-12 text-center border border-slate-200">
                    <div class="w-14 h-14 bg-slate-100 text-slate-300 rounded-full flex items-center justify-center mx-auto mb-3 text-2xl">
                        <i class="fa-solid fa-inbox"></i>
                    </div>
                    <h3 class="font-extrabold text-base text-slate-800">Belum Ada Pesanan Masuk</h3>
                    <p class="text-xs text-slate-400 mt-1">Saat pelanggan meja mengirim pesanan, daftar akan otomatis muncul di sini secara real-time.</p>
                </div>
            <?php else: ?>
                <?php foreach ($orders as $o): ?>
                    <?php
                    // Ambil items
                    $stmt_i = $pdo->prepare("SELECT oi.*, m.nama_menu FROM order_items oi JOIN menus m ON oi.menu_id = m.id WHERE oi.order_id = ?");
                    $stmt_i->execute([$o['id']]);
                    $items = $stmt_i->fetchAll();

                    // Status style
                    $badge_class = 'bg-amber-100 text-amber-800 border-amber-200';
                    $dot_color   = 'bg-amber-500';
                    if ($o['status_pesanan'] === 'dimasak') {
                        $badge_class = 'bg-brand-100 text-brand-800 border-brand-200';
                        $dot_color   = 'bg-brand-500';
                    } elseif ($o['status_pesanan'] === 'selesai') {
                        $badge_class = 'bg-emerald-100 text-emerald-800 border-emerald-200';
                        $dot_color   = 'bg-emerald-500';
                    } elseif ($o['status_pesanan'] === 'dibatalkan') {
                        $badge_class = 'bg-rose-100 text-rose-800 border-rose-200';
                        $dot_color   = 'bg-rose-500';
                    }
                    ?>
                    <div class="order-card bg-white rounded-3xl border border-slate-200/80 shadow-sm hover:shadow-md transition-all flex flex-col justify-between overflow-hidden" 
                         data-status="<?= htmlspecialchars($o['status_pesanan']) ?>"
                         data-payment="<?= htmlspecialchars($o['status_pembayaran']) ?>">
                        
                        <div>
                            <!-- Header Kartu: Meja, Nama, Kode, Waktu -->
                            <div class="p-4 sm:p-5 border-b border-slate-100 flex items-start justify-between bg-slate-50/70">
                                <div>
                                    <div class="flex items-center space-x-2">
                                        <span class="text-xs font-black px-2.5 py-1 rounded-xl bg-brand-500 text-white shadow-sm shadow-brand-500/20">
                                            MEJA #<?= htmlspecialchars($o['nomor_meja']) ?>
                                        </span>
                                        <span class="text-[11px] font-mono font-bold text-slate-500">
                                            <?= htmlspecialchars($o['kode_pesanan']) ?>
                                        </span>
                                    </div>
                                    <h3 class="font-extrabold text-sm sm:text-base text-slate-900 mt-2 leading-tight">
                                        <?= htmlspecialchars($o['nama_pelanggan']) ?>
                                    </h3>
                                </div>
                                
                                <div class="text-right flex-shrink-0">
                                    <span class="inline-flex items-center gap-1.5 text-[10px] sm:text-[11px] font-bold px-2.5 py-0.5 rounded-full border <?= $badge_class ?> uppercase">
                                        <span class="w-1.5 h-1.5 rounded-full <?= $dot_color ?>"></span>
                                        <span><?= htmlspecialchars($o['status_pesanan']) ?></span>
                                    </span>
                                    <span class="text-[10px] text-slate-400 block mt-1">
                                        <i class="fa-regular fa-clock mr-0.5"></i><?= date('H:i', strtotime($o['created_at'])) ?> WIB
                                    </span>
                                </div>
                            </div>

                            <!-- Rincian Menu Pesanan -->
                            <div class="p-4 sm:p-5 space-y-2.5 text-xs">
                                <span class="text-[10px] uppercase font-bold tracking-wider text-slate-400 block">Daftar Porsi:</span>
                                <?php foreach ($items as $item): ?>
                                    <div class="flex justify-between items-start border-b border-slate-50 pb-2">
                                        <div class="flex-1 pr-2">
                                            <div class="font-bold text-slate-800">
                                                <span class="text-brand-600 font-extrabold mr-1"><?= $item['jumlah'] ?>x</span>
                                                <span><?= htmlspecialchars($item['nama_menu']) ?></span>
                                            </div>
                                            <?php if (!empty($item['catatan'])): ?>
                                                <p class="text-[10px] text-brand-600 font-medium italic mt-0.5 bg-brand-50/80 px-2 py-0.5 rounded-lg border border-brand-100">
                                                    "<?= htmlspecialchars($item['catatan']) ?>"
                                                </p>
                                            <?php endif; ?>
                                        </div>
                                        <span class="font-bold text-slate-700 flex-shrink-0"><?= format_rupiah($item['subtotal']) ?></span>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>

                        <!-- Footer Kartu: Tagihan, Status Pembayaran & Tombol Aksi Dapur -->
                        <div class="p-4 sm:p-5 bg-slate-50/70 border-t border-slate-100 space-y-3">
                            <div class="flex justify-between items-center">
                                <div>
                                    <span class="text-[10px] text-slate-400 uppercase font-bold tracking-wider block">Total Tagihan</span>
                                    <span class="font-black text-sm sm:text-base text-slate-900"><?= format_rupiah($o['total_harga']) ?></span>
                                </div>

                                <div>
                                    <?php if ($o['status_pembayaran'] === 'lunas'): ?>
                                        <span class="px-2.5 py-1 rounded-xl text-[11px] font-black bg-emerald-100 text-emerald-800 border border-emerald-200 flex items-center space-x-1">
                                            <i class="fa-solid fa-check"></i>
                                            <span>LUNAS • <?= htmlspecialchars($o['metode_pembayaran'] ?: 'QRIS') ?></span>
                                        </span>
                                    <?php else: ?>
                                        <form action="update_status.php" method="POST" class="inline">
                                            <input type="hidden" name="order_id" value="<?= $o['id'] ?>">
                                            <input type="hidden" name="action" value="set_lunas">
                                            <button type="submit" class="px-3 py-1.5 rounded-xl text-[11px] font-bold bg-amber-500 hover:bg-amber-600 text-white shadow-sm transition active:scale-95 flex items-center space-x-1" title="Konfirmasi pembayaran tunai atau QR">
                                                <i class="fa-solid fa-money-bill-wave"></i>
                                                <span>Set Lunas</span>
                                            </button>
                                        </form>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <!-- Tombol Alur Dapur (Masak, Selesai, Struk) -->
                            <div class="grid grid-cols-3 gap-2 pt-1 border-t border-slate-200/60">
                                <!-- Mulai Masak -->
                                <form action="update_status.php" method="POST" class="w-full">
                                    <input type="hidden" name="order_id" value="<?= $o['id'] ?>">
                                    <input type="hidden" name="status_pesanan" value="dimasak">
                                    <button type="submit" 
                                            class="w-full py-2 px-1 bg-brand-500 hover:bg-brand-600 text-white rounded-xl text-xs font-bold flex items-center justify-center space-x-1 transition active:scale-95 shadow-sm <?= $o['status_pesanan'] === 'dimasak' ? 'opacity-50 cursor-not-allowed' : '' ?>" 
                                            <?= $o['status_pesanan'] === 'dimasak' ? 'disabled' : '' ?>
                                            title="Tandai pesanan mulai dimasak">
                                        <i class="fa-solid fa-fire text-[10px]"></i>
                                        <span>Masak</span>
                                    </button>
                                </form>

                                <!-- Selesai & Antar -->
                                <form action="update_status.php" method="POST" class="w-full">
                                    <input type="hidden" name="order_id" value="<?= $o['id'] ?>">
                                    <input type="hidden" name="status_pesanan" value="selesai">
                                    <button type="submit" 
                                            class="w-full py-2 px-1 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-xs font-bold flex items-center justify-center space-x-1 transition active:scale-95 shadow-sm <?= $o['status_pesanan'] === 'selesai' ? 'opacity-50 cursor-not-allowed' : '' ?>" 
                                            <?= $o['status_pesanan'] === 'selesai' ? 'disabled' : '' ?>
                                            title="Tandai hidangan siap diantar ke meja">
                                        <i class="fa-solid fa-check text-[10px]"></i>
                                        <span>Selesai</span>
                                    </button>
                                </form>

                                <!-- Cetak Struk -->
                                <a href="cetak_struk.php?order_id=<?= $o['id'] ?>" target="_blank" 
                                   class="py-2 px-1 bg-slate-100 hover:bg-slate-200 text-slate-800 rounded-xl text-xs font-bold flex items-center justify-center space-x-1 transition active:scale-95 border border-slate-200"
                                   title="Cetak struk nota pesanan">
                                    <i class="fa-solid fa-print text-[10px]"></i>
                                    <span>Struk</span>
                                </a>
                            </div>
                        </div>

                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

    </main>

    <!-- Footer Kasir Cerah & Bersih (Bukan Gelap) -->
    <footer class="bg-white text-slate-500 mt-auto pt-6 pb-6 border-t border-slate-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex flex-col sm:flex-row items-center justify-between text-[11px] text-slate-500 gap-2 text-center sm:text-left">
            <span>&copy; <?= date('Y') ?> KanDar. All rights reserved.</span>
            <span class="font-semibold text-brand-600">Panel Kasir & Dapur Layar Sentuh / Desktop</span>
        </div>
    </footer>

    <!-- Script Kasir: Filter Kategori, Toggle Mobile, Auto-Polling & Barcode Scanner Gun -->
    <script>
        function toggleKasirMenu() {
            const menu = document.getElementById('kasir-mobile-menu');
            const icon = document.getElementById('kasir-hamburger-icon');
            if (menu.classList.contains('hidden')) {
                menu.classList.remove('hidden');
                icon.className = 'fa-solid fa-xmark text-brand-600';
            } else {
                menu.classList.add('hidden');
                icon.className = 'fa-solid fa-bars';
            }
        }

        function filterOrderList(status) {
            document.querySelectorAll('.tab-order-btn').forEach(btn => {
                btn.className = 'tab-order-btn px-3.5 py-1.5 rounded-xl text-xs font-bold transition bg-slate-100 text-slate-600 hover:bg-slate-200';
            });
            const activeBtn = document.getElementById(`tab-order-${status}`);
            if (activeBtn) {
                activeBtn.className = 'tab-order-btn px-3.5 py-1.5 rounded-xl text-xs font-bold transition bg-brand-500 text-white shadow-md shadow-brand-500/25';
            }

            const cards = document.querySelectorAll('.order-card');
            cards.forEach(card => {
                const cardStatus = card.dataset.status;
                if (status === 'semua' || cardStatus === status) {
                    card.classList.remove('hidden');
                } else {
                    card.classList.add('hidden');
                }
            });
        }

        function fetchOrders() {
            const icon = document.getElementById('refresh-icon');
            if (icon) icon.classList.add('fa-spin');
            window.location.reload();
        }

        // Live Polling Cek Pesanan Baru Setiap 3.5 Detik
        let currentTotalOrders = <?= count($orders) ?>;
        async function checkIncomingOrders() {
            try {
                const response = await fetch('../api/get_orders.php');
                const result = await response.json();
                if (result.status === 'success') {
                    if (result.count !== currentTotalOrders) {
                        window.location.reload();
                    }
                }
            } catch (e) {
                // Silently ignore
            }
        }
        setInterval(checkIncomingOrders, 3500);

        // Dukungan Barcode Scanner Gun Fisik di Meja Kasir (Scan Langsung Tervalidasi Otomatis)
        let barcodeBuffer = '';
        let barcodeTimer = null;

        document.addEventListener('keydown', function(e) {
            if (e.target.tagName === 'INPUT' || e.target.tagName === 'TEXTAREA') {
                if (e.target.id !== 'manual-order-code') return;
            }

            if (e.key === 'Enter') {
                if (barcodeBuffer.trim().length >= 4) {
                    const scannedCode = barcodeBuffer.trim();
                    barcodeBuffer = '';
                    openScannerModal();
                    processScanAndAutoValidate(scannedCode);
                }
            } else if (e.key.length === 1) {
                barcodeBuffer += e.key;
                clearTimeout(barcodeTimer);
                barcodeTimer = setTimeout(() => { barcodeBuffer = ''; }, 300);
            }
        });
    </script>

    <!-- Modal Scanner QR Code (Kamera & Audio Beep Auto-Validation) -->
    <?php include_once __DIR__ . '/../components/scanner_modal.php'; ?>
</body>
</html>
