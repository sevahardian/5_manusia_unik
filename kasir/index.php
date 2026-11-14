<?php
// kasir/index.php
// [BAGIAN 3: PROGRAMMER 3] Dashboard Kasir / Dapur - Monitoring Pesanan Realtime

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
    LIMIT 50
");
$orders = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Kasir & Dapur - Live Orders</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        brand: {
                            dark: '#7C3E12',
                            orange: '#FF7E24',
                            peach: '#FFC079',
                            cream: '#FFEFAB',
                            creamLight: '#FFF9EE',
                            bg: '#FFFDF9',
                            espresso: '#4A240A'
                        },
                        orange: {
                            50: '#FFF9EE',
                            100: '#FFEFAB',
                            200: '#FFE2B8',
                            300: '#FFC079',
                            400: '#FFA04E',
                            500: '#FF7E24',
                            600: '#E66810',
                            700: '#7C3E12',
                            800: '#5C2D0B',
                            900: '#4A240A'
                        }
                    }
                }
            }
        }
    </script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; background-color: #FFFDF9; color: #4A240A; }
    </style>
</head>
<body class="bg-[#FFFDF9] min-h-screen text-[#4A240A]">

    <!-- Navbar Kasir -->
    <header class="bg-[#7C3E12] text-[#FFEFAB] shadow-lg sticky top-0 z-30 border-b border-[#5C2D0B]">
        <div class="max-w-7xl mx-auto px-4 py-3 flex items-center justify-between">
            <div class="flex items-center space-x-3">
                <div class="w-10 h-10 rounded-2xl bg-gradient-to-tr from-[#FF7E24] to-[#FFC079] text-[#7C3E12] flex items-center justify-center font-bold text-lg shadow-md">
                    <i class="fa-solid fa-cash-register"></i>
                </div>
                <div>
                    <h1 class="font-extrabold text-base leading-tight text-white">Dashboard Kasir & Dapur</h1>
                    <div class="flex items-center space-x-2 text-xs text-[#FFEFAB]/80">
                        <span class="inline-block w-2 h-2 rounded-full bg-[#FFC079] animate-ping"></span>
                        <span id="live-indicator">Live Polling Aktif (5s)</span>
                    </div>
                </div>
            </div>

            <div class="flex items-center space-x-2 text-xs">
                <!-- Tombol Scan QR Pesanan -->
                <button onclick="openScannerModal()" class="px-3.5 py-2 rounded-xl bg-[#FF7E24] hover:bg-[#E66810] text-white font-bold flex items-center space-x-1.5 shadow-md shadow-[#FF7E24]/30 active:scale-95 transition">
                    <i class="fa-solid fa-qrcode text-sm"></i>
                    <span>Scan QR Pesanan</span>
                </button>

                <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
                    <a href="../admin/index.php" class="px-3.5 py-2 rounded-xl bg-[#5C2D0B] hover:bg-[#4A240A] text-[#FFEFAB] font-semibold border border-[#FFC079]/30 flex items-center space-x-1.5 transition">
                        <i class="fa-solid fa-chart-pie"></i>
                        <span>Panel Admin</span>
                    </a>
                <?php endif; ?>
                <button onclick="fetchOrders()" class="p-2 rounded-xl bg-[#5C2D0B] hover:bg-[#4A240A] text-[#FFEFAB] border border-[#FFC079]/30 text-xs font-bold transition flex items-center space-x-1" title="Refresh Sekarang">
                    <i class="fa-solid fa-arrows-rotate" id="refresh-icon"></i>
                    <span class="hidden sm:inline">Refresh</span>
                </button>
                <a href="../admin/logout.php" onclick="return confirm('Keluar dari sesi Kasir?')" class="px-3 py-2 rounded-xl bg-rose-500/20 hover:bg-rose-600 text-rose-200 hover:text-white border border-rose-500/30 font-bold flex items-center space-x-1.5 transition">
                    <i class="fa-solid fa-right-from-bracket"></i>
                    <span class="hidden sm:inline">Logout</span>
                </a>
            </div>
        </div>
    </header>

    <main class="max-w-7xl mx-auto px-4 py-6">
        <!-- Statistik Kilat -->
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
            <?php
            $stat_menunggu = $pdo->query("SELECT COUNT(*) FROM orders WHERE status_pesanan = 'menunggu'")->fetchColumn();
            $stat_dimasak  = $pdo->query("SELECT COUNT(*) FROM orders WHERE status_pesanan = 'dimasak'")->fetchColumn();
            $stat_selesai  = $pdo->query("SELECT COUNT(*) FROM orders WHERE status_pesanan = 'selesai'")->fetchColumn();
            $stat_omzet    = $pdo->query("SELECT SUM(total_harga) FROM orders WHERE status_pembayaran = 'lunas'")->fetchColumn() ?: 0;
            ?>
            <div class="bg-white p-4 rounded-2xl border border-[#FFC079]/40 shadow-sm hover:border-[#FF7E24] transition flex items-center justify-between">
                <div>
                    <p class="text-xs font-bold text-[#FF7E24] uppercase">Pesanan Baru</p>
                    <h3 id="stat-menunggu" class="text-2xl font-black text-[#7C3E12] mt-1"><?= $stat_menunggu ?></h3>
                </div>
                <div class="w-12 h-12 rounded-xl bg-[#FFEFAB] text-[#FF7E24] flex items-center justify-center text-xl shadow-sm">
                    <i class="fa-solid fa-bell"></i>
                </div>
            </div>
            <div class="bg-white p-4 rounded-2xl border border-[#FFC079]/40 shadow-sm hover:border-[#FF7E24] transition flex items-center justify-between">
                <div>
                    <p class="text-xs font-bold text-[#7C3E12] uppercase">Sedang Dimasak</p>
                    <h3 id="stat-dimasak" class="text-2xl font-black text-[#7C3E12] mt-1"><?= $stat_dimasak ?></h3>
                </div>
                <div class="w-12 h-12 rounded-xl bg-[#FFC079]/40 text-[#7C3E12] flex items-center justify-center text-xl shadow-sm">
                    <i class="fa-solid fa-fire-burner"></i>
                </div>
            </div>
            <div class="bg-white p-4 rounded-2xl border border-[#FFC079]/40 shadow-sm hover:border-[#FF7E24] transition flex items-center justify-between">
                <div>
                    <p class="text-xs font-bold text-[#7C3E12] uppercase">Selesai</p>
                    <h3 id="stat-selesai" class="text-2xl font-black text-[#7C3E12] mt-1"><?= $stat_selesai ?></h3>
                </div>
                <div class="w-12 h-12 rounded-xl bg-[#FFEFAB] text-[#7C3E12] flex items-center justify-center text-xl shadow-sm">
                    <i class="fa-solid fa-circle-check"></i>
                </div>
            </div>
            <div class="bg-white p-4 rounded-2xl border border-[#FFC079]/40 shadow-sm hover:border-[#FF7E24] transition flex items-center justify-between">
                <div>
                    <p class="text-xs font-bold text-[#FF7E24] uppercase">Total Omzet</p>
                    <h3 class="text-xl font-black text-[#7C3E12] mt-1"><?= format_rupiah($stat_omzet) ?></h3>
                </div>
                <div class="w-12 h-12 rounded-xl bg-[#FFEFAB] text-[#FF7E24] flex items-center justify-center text-xl shadow-sm">
                    <i class="fa-solid fa-wallet"></i>
                </div>
            </div>
        </div>

        <!-- Order List Grid -->
        <h2 class="font-bold text-lg text-[#7C3E12] mb-4 flex items-center space-x-2">
            <span>Antrean Pesanan Masuk</span>
            <span class="text-xs bg-[#FFEFAB] text-[#7C3E12] border border-[#FFC079]/50 px-2.5 py-0.5 rounded-full font-bold"><?= count($orders) ?></span>
        </h2>

        <div id="orders-grid" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
            <?php foreach ($orders as $o): ?>
                <?php
                // Ambil items
                $stmt_i = $pdo->prepare("SELECT oi.*, m.nama_menu FROM order_items oi JOIN menus m ON oi.menu_id = m.id WHERE oi.order_id = ?");
                $stmt_i->execute([$o['id']]);
                $items = $stmt_i->fetchAll();

                // Style badge status pesanan
                $status_bg = 'bg-[#FFEFAB] text-[#7C3E12] border-[#FFC079]';
                if ($o['status_pesanan'] === 'dimasak') $status_bg = 'bg-[#FFC079]/40 text-[#7C3E12] border-[#FFC079]';
                if ($o['status_pesanan'] === 'selesai') $status_bg = 'bg-[#FFEFAB] text-[#7C3E12] border-[#FFC079]';
                if ($o['status_pesanan'] === 'dibatalkan') $status_bg = 'bg-rose-100 text-rose-800 border-rose-300';
                ?>
                <div class="bg-white rounded-2xl border border-[#FFC079]/40 shadow-sm hover:shadow-md hover:border-[#FF7E24] transition overflow-hidden flex flex-col justify-between">
                    <div>
                        <!-- Header Kartu -->
                        <div class="p-4 border-b border-[#FFC079]/20 flex items-center justify-between bg-[#FFFDF9]">
                            <div>
                                <span class="text-xs font-bold px-2.5 py-1 rounded-lg bg-[#FFEFAB] text-[#7C3E12] border border-[#FFC079]">
                                    <i class="fa-solid fa-chair text-[10px] mr-1 text-[#FF7E24]"></i>MEJA <?= htmlspecialchars($o['nomor_meja']) ?>
                                </span>
                                <h3 class="font-bold text-sm text-[#7C3E12] mt-2"><?= htmlspecialchars($o['nama_pelanggan']) ?></h3>
                                <p class="text-[11px] text-[#7C3E12]/60 font-mono"><?= htmlspecialchars($o['kode_pesanan']) ?></p>
                            </div>
                            <div class="text-right">
                                <span class="inline-block text-[11px] font-bold px-2.5 py-0.5 rounded-full border <?= $status_bg ?> capitalize mb-1">
                                    <?= htmlspecialchars($o['status_pesanan']) ?>
                                </span>
                                <p class="text-[11px] text-[#7C3E12]/60"><i class="fa-regular fa-clock mr-1 text-[#FF7E24]"></i><?= date('H:i', strtotime($o['created_at'])) ?></p>
                            </div>
                        </div>

                        <!-- Daftar Menu Pesanan -->
                        <div class="p-4 space-y-2 text-xs">
                            <?php foreach ($items as $item): ?>
                                <div class="flex justify-between items-start border-b border-[#FFC079]/15 pb-1.5">
                                    <div>
                                        <span class="font-bold text-[#7C3E12]"><?= $item['jumlah'] ?>x</span>
                                        <span class="text-[#7C3E12] font-medium"><?= htmlspecialchars($item['nama_menu']) ?></span>
                                        <?php if (!empty($item['catatan'])): ?>
                                            <p class="text-[10px] text-[#FF7E24] font-semibold italic pl-4">Note: <?= htmlspecialchars($item['catatan']) ?></p>
                                        <?php endif; ?>
                                    </div>
                                    <span class="font-semibold text-[#7C3E12]/80"><?= format_rupiah($item['subtotal']) ?></span>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <!-- Footer Kartu & Aksi Kasir -->
                    <div class="p-4 bg-[#FFEFAB]/20 border-t border-[#FFC079]/20">
                        <div class="flex justify-between items-center mb-3">
                            <div>
                                <span class="text-[11px] text-[#7C3E12]/60 block">Total Tagihan:</span>
                                <span class="font-black text-sm text-[#7C3E12]"><?= format_rupiah($o['total_harga']) ?></span>
                            </div>
                            <div>
                                <?php if ($o['status_pembayaran'] === 'lunas'): ?>
                                    <span class="px-2.5 py-1 rounded-lg text-[11px] font-bold bg-[#FFEFAB] text-[#7C3E12] border border-[#FFC079]">
                                        <i class="fa-solid fa-check mr-1 text-[#FF7E24]"></i>LUNAS
                                    </span>
                                <?php else: ?>
                                    <form action="update_status.php" method="POST" class="inline">
                                        <input type="hidden" name="order_id" value="<?= $o['id'] ?>">
                                        <input type="hidden" name="action" value="set_lunas">
                                        <button type="submit" class="px-2.5 py-1 rounded-lg text-[11px] font-bold bg-[#FF7E24] hover:bg-[#7C3E12] text-white shadow-sm shadow-[#FF7E24]/20 transition">
                                            <i class="fa-solid fa-money-bill mr-1"></i>Set Lunas
                                        </button>
                                    </form>
                                <?php endif; ?>
                            </div>
                        </div>

                        <!-- Tombol Alur Dapur & Cetak -->
                        <div class="grid grid-cols-3 gap-2">
                            <!-- Update ke Dimasak -->
                            <form action="update_status.php" method="POST" class="w-full">
                                <input type="hidden" name="order_id" value="<?= $o['id'] ?>">
                                <input type="hidden" name="status_pesanan" value="dimasak">
                                <button type="submit" class="w-full py-1.5 px-2 bg-[#FF7E24] hover:bg-[#E66810] text-white rounded-lg text-xs font-semibold flex items-center justify-center space-x-1 shadow-sm shadow-[#FF7E24]/20 transition <?= $o['status_pesanan'] === 'dimasak' ? 'opacity-50 cursor-not-allowed' : '' ?>" <?= $o['status_pesanan'] === 'dimasak' ? 'disabled' : '' ?>>
                                    <i class="fa-solid fa-fire text-[10px]"></i>
                                    <span>Masak</span>
                                </button>
                            </form>

                            <!-- Update ke Selesai -->
                            <form action="update_status.php" method="POST" class="w-full">
                                <input type="hidden" name="order_id" value="<?= $o['id'] ?>">
                                <input type="hidden" name="status_pesanan" value="selesai">
                                <button type="submit" class="w-full py-1.5 px-2 bg-[#7C3E12] hover:bg-[#5C2D0B] text-white rounded-lg text-xs font-semibold flex items-center justify-center space-x-1 shadow-sm transition <?= $o['status_pesanan'] === 'selesai' ? 'opacity-50 cursor-not-allowed' : '' ?>" <?= $o['status_pesanan'] === 'selesai' ? 'disabled' : '' ?>>
                                    <i class="fa-solid fa-check text-[10px]"></i>
                                    <span>Selesai</span>
                                </button>
                            </form>

                            <!-- Cetak Struk -->
                            <a href="cetak_struk.php?order_id=<?= $o['id'] ?>" target="_blank" 
                               class="py-1.5 px-2 bg-[#FFEFAB] hover:bg-[#FFC079] text-[#7C3E12] border border-[#FFC079] rounded-lg text-xs font-bold flex items-center justify-center space-x-1 transition shadow-sm">
                                <i class="fa-solid fa-print text-[10px]"></i>
                                <span>Struk</span>
                            </a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </main>

    <!-- Script Polling Otomatis Tiap 5 Detik -->
    <script>
        async function fetchOrders() {
            const icon = document.getElementById('refresh-icon');
            icon.classList.add('fa-spin');

            try {
                const response = await fetch('../api/get_orders.php');
                const result = await response.json();
                if (result.status === 'success') {
                    // Update tampilan realtime tanpa refresh halaman jika jumlah order bertambah
                    // Untuk kemudahan hackathon, jika ada pesanan baru kita update badge
                    console.log('Polling berhasil, order count:', result.count);
                }
            } catch (e) {
                console.error('Polling gagal:', e);
            } finally {
                setTimeout(() => icon.classList.remove('fa-spin'), 600);
            }
        }

        // Jalankan polling setiap 5 detik
        setInterval(fetchOrders, 5000);
    </script>

    <!-- Modal Scanner QR Code -->
    <?php include_once __DIR__ . '/../components/scanner_modal.php'; ?>
</body>
</html>

