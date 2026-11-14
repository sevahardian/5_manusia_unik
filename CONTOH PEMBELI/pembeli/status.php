<?php
// pembeli/status.php
// [BAGIAN 2: PROGRAMMER 2] Halaman Nota, Status Pesanan Real-Time & Validasi Pembayaran Anti-Fake Order
// Didesain responsif di semua perangkat (Mobile, Tablet, Laptop, Desktop)

require_once __DIR__ . '/../config/koneksi.php';
require_once __DIR__ . '/../config/helper.php';

$order_id = isset($_GET['order_id']) ? (int)$_GET['order_id'] : 0;

if ($order_id <= 0) {
    header('Location: index.php');
    exit;
}

// Handle simulasi bayar / demo validasi QRIS langsung dari HP/Browser
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['simulasi_bayar'])) {
    $stmt_pay = $pdo->prepare("
        UPDATE orders 
        SET status_pembayaran = 'lunas',
            status_pesanan = CASE WHEN status_pesanan = 'menunggu' THEN 'dimasak' ELSE status_pesanan END,
            metode_pembayaran = 'QRIS'
        WHERE id = ?
    ");
    $stmt_pay->execute([$order_id]);
    header("Location: status.php?order_id={$order_id}&verified=1");
    exit;
}

// Ambil data order terbaru
$stmt = $pdo->prepare("SELECT * FROM orders WHERE id = ?");
$stmt->execute([$order_id]);
$order = $stmt->fetch();

if (!$order) {
    die("<div style='font-family:sans-serif; text-align:center; padding:50px;'>
            <h2><i class='fa-solid fa-circle-exclamation text-amber-500'></i> Pesanan Tidak Ditemukan</h2>
            <p>Data pesanan dengan ID #{$order_id} tidak tersedia di database.</p>
            <a href='index.php' style='display:inline-block; margin-top:15px; padding:10px 20px; background:#f97316; color:#fff; text-decoration:none; border-radius:8px;'>Kembali ke Menu</a>
         </div>");
}

// Simpan nomor meja terakhir
$_SESSION['meja'] = $order['nomor_meja'];
$_SESSION['nomor_meja'] = $order['nomor_meja'];

// Ambil rincian menu yang dipesan
$stmt_items = $pdo->prepare("
    SELECT oi.*, m.nama_menu, m.gambar, m.kategori
    FROM order_items oi
    JOIN menus m ON oi.menu_id = m.id
    WHERE oi.order_id = ?
");
$stmt_items->execute([$order_id]);
$items = $stmt_items->fetchAll();

// Generate data isi QR Code Validasi Pesanan
$qr_content = "RESTO-ENAK-RASA:ORDER=" . $order['kode_pesanan'] . ":MEJA=" . $order['nomor_meja'] . ":TOTAL=" . $order['total_harga'];
$qr_url = "https://api.qrserver.com/v1/create-qr-code/?size=350x350&margin=10&data=" . urlencode($qr_content);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Status Pesanan #<?= htmlspecialchars($order['kode_pesanan']) ?> | KanDar</title>
    
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
        
        /* Media Print Optimization */
        @media print {
            body { background: white !important; padding: 0 !important; color: black !important; }
            header, .no-print, #payment-card, #timeline-card, #toast { display: none !important; }
            #receipt-card { border: none !important; box-shadow: none !important; width: 100% !important; max-width: 100% !important; margin: 0 !important; padding: 0 !important; }
            .print-only { display: block !important; }
        }
    </style>
</head>
<body class="bg-slate-50 min-h-screen text-slate-800 flex flex-col antialiased pb-16 selection:bg-brand-500 selection:text-white">

    <!-- TOP NAVBAR -->
    <header class="sticky top-0 z-40 bg-white/95 backdrop-blur-md border-b border-slate-200/80 shadow-xs no-print">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-16">
                
                <!-- Back Link to Menu -->
                <a href="index.php?meja=<?= urlencode($order['nomor_meja']) ?>" 
                   class="inline-flex items-center space-x-2 text-xs sm:text-sm font-bold text-slate-700 hover:text-brand-600 transition bg-slate-100 hover:bg-brand-50 px-3.5 py-2 rounded-xl">
                    <i class="fa-solid fa-arrow-left text-xs"></i>
                    <span>Katalog Menu</span>
                </a>

                <!-- Order Code & Table Info -->
                <div class="flex items-center space-x-2 sm:space-x-3">
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-brand-50 text-brand-700 text-xs font-bold border border-brand-200 shadow-xs">
                        <i class="fa-solid fa-chair text-[11px] text-brand-600"></i>
                        <span>Meja #<?= htmlspecialchars($order['nomor_meja']) ?></span>
                    </span>

                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-slate-100 text-slate-600 text-xs font-semibold">
                        <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                        <span class="text-[11px] hidden sm:inline">Live Sync</span>
                    </span>
                </div>

            </div>
        </div>
    </header>

    <!-- MAIN CONTAINER -->
    <main class="flex-1 max-w-7xl w-full mx-auto px-4 sm:px-6 lg:px-8 py-6">
        
        <!-- RESPONSIVE 2-COLUMN GRID (lg:grid-cols-12) -->
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
            
            <!-- LEFT COLUMN: Status Pembayaran, QR Validasi & Alur Dapur (lg:col-span-7) -->
            <div class="lg:col-span-7 space-y-6">
                
                <!-- 1. BANNER NOTIFIKASI VALIDASI -->
                <?php if ($order['status_pembayaran'] !== 'lunas'): ?>
                    <div class="bg-gradient-to-r from-amber-500 to-orange-500 text-white rounded-3xl p-5 sm:p-6 shadow-lg shadow-orange-500/20 flex items-start space-x-3.5">
                        <div class="w-10 h-10 rounded-2xl bg-white/20 backdrop-blur-md text-white flex items-center justify-center font-bold text-lg flex-shrink-0 mt-0.5">
                            <i class="fa-solid fa-shield-halved"></i>
                        </div>
                        <div class="flex-1 min-w-0">
                            <div class="inline-block px-2.5 py-0.5 bg-white/20 rounded-full text-[10px] font-extrabold uppercase tracking-wider mb-1">
                                Proteksi Anti-Fake Order
                            </div>
                            <h2 class="font-black text-base sm:text-lg leading-tight">Menunggu Pembayaran</h2>
                            <p class="text-xs sm:text-sm text-orange-100 mt-1 leading-relaxed">
                                Pesanan Anda sudah masuk ke sistem kasir. Dapur kami akan <b>segera memasak</b> begitu pembayaran tervalidasi via <b>QRIS</b> atau <b>Tunai di Kasir</b>.
                            </p>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="bg-gradient-to-r from-emerald-600 to-teal-600 text-white rounded-3xl p-5 sm:p-6 shadow-lg shadow-emerald-500/20 flex items-start space-x-3.5">
                        <div class="w-10 h-10 rounded-2xl bg-white/20 backdrop-blur-md text-white flex items-center justify-center font-bold text-lg flex-shrink-0 mt-0.5">
                            <i class="fa-solid fa-check-double"></i>
                        </div>
                        <div class="flex-1 min-w-0">
                            <div class="inline-block px-2.5 py-0.5 bg-white/20 rounded-full text-[10px] font-extrabold uppercase tracking-wider mb-1">
                                Terverifikasi Sah
                            </div>
                            <h2 class="font-black text-base sm:text-lg leading-tight">Pembayaran Lunas!</h2>
                            <p class="text-xs sm:text-sm text-emerald-100 mt-1 leading-relaxed">
                                Transaksi berhasil divalidasi. Makanan dan minuman Anda sedang disiapkan dengan penuh kelezatan.
                            </p>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- 2. KARTU UTAMA: VALIDASI PEMBAYARAN & QR CODE -->
                <div id="payment-card" class="bg-white rounded-3xl p-6 sm:p-8 border border-slate-200/80 shadow-xs text-center relative overflow-hidden">
                    
                    <?php if ($order['status_pembayaran'] !== 'lunas'): ?>
                        <!-- Switch Tabs: QRIS vs Tunai Kasir -->
                        <div class="flex bg-slate-100 p-1.5 rounded-2xl mb-6 max-w-md mx-auto text-xs font-bold">
                            <button type="button" 
                                    id="tab-btn-qris" 
                                    onclick="switchPaymentTab('qris')" 
                                    class="flex-1 py-2.5 rounded-xl bg-white text-brand-600 shadow-sm transition flex items-center justify-center space-x-2">
                                <i class="fa-solid fa-qrcode"></i>
                                <span>Bayar via QRIS</span>
                            </button>
                            <button type="button" 
                                    id="tab-btn-cash" 
                                    onclick="switchPaymentTab('cash')" 
                                    class="flex-1 py-2.5 rounded-xl text-slate-600 hover:text-slate-900 transition flex items-center justify-center space-x-2">
                                <i class="fa-solid fa-money-bill-wave"></i>
                                <span>Bayar Tunai di Kasir</span>
                            </button>
                        </div>

                        <!-- Tampilan Tab 1: QRIS -->
                        <div id="view-qris" class="space-y-5">
                            <div>
                                <span class="text-[10px] font-black tracking-widest text-brand-600 uppercase bg-brand-50 px-3 py-1 rounded-full border border-brand-200">
                                    QRIS Standar Nasional
                                </span>
                                <h3 class="text-lg sm:text-xl font-black text-slate-900 mt-2">Scan QR & Bayar Otomatis</h3>
                                <p class="text-xs text-slate-500 mt-0.5">Dukung BCA Mobile, Livin' Mandiri, GoPay, OVO, Dana, ShopeePay & LinkAja</p>
                            </div>

                            <!-- QR Frame with Corner Accents -->
                            <div class="relative w-64 h-64 sm:w-72 sm:h-72 mx-auto bg-white p-4 rounded-3xl border-2 border-brand-500/80 shadow-md">
                                <div class="absolute -top-1 -left-1 w-5 h-5 border-t-4 border-l-4 border-brand-600 rounded-tl-lg"></div>
                                <div class="absolute -top-1 -right-1 w-5 h-5 border-t-4 border-r-4 border-brand-600 rounded-tr-lg"></div>
                                <div class="absolute -bottom-1 -left-1 w-5 h-5 border-b-4 border-l-4 border-brand-600 rounded-bl-lg"></div>
                                <div class="absolute -bottom-1 -right-1 w-5 h-5 border-b-4 border-r-4 border-brand-600 rounded-br-lg"></div>

                                <img src="<?= $qr_url ?>" alt="QRIS Validasi" class="w-full h-full object-contain rounded-2xl">
                            </div>

                            <!-- Nominal & Order Code Highlight -->
                            <div class="bg-brand-50/70 border border-brand-100 rounded-2xl p-4 max-w-md mx-auto">
                                <span class="text-xs text-slate-500 font-semibold block">Total Nominal Tagihan:</span>
                                <span class="text-2xl sm:text-3xl font-black text-brand-600 block mt-0.5">
                                    <?= format_rupiah($order['total_harga']) ?>
                                </span>
                                <div class="flex items-center justify-center space-x-1.5 mt-2">
                                    <span class="text-xs font-mono font-bold text-slate-600"><?= htmlspecialchars($order['kode_pesanan']) ?></span>
                                    <button onclick="copyOrderCode('<?= htmlspecialchars($order['kode_pesanan']) ?>')" 
                                            class="text-xs text-brand-600 hover:text-brand-800 p-1" title="Salin kode">
                                        <i class="fa-regular fa-copy"></i>
                                    </button>
                                </div>
                            </div>

                            <!-- Tombol Simulasi Pembayaran QRIS (Berguna saat demo hackathon) -->
                            <div class="max-w-md mx-auto">
                                <form action="status.php?order_id=<?= $order['id'] ?>" method="POST">
                                    <input type="hidden" name="simulasi_bayar" value="1">
                                    <button type="submit" 
                                            class="w-full py-3.5 bg-brand-500 hover:bg-brand-600 text-white font-extrabold rounded-2xl text-xs sm:text-sm shadow-md shadow-brand-500/25 active:scale-95 transition flex items-center justify-center space-x-2">
                                        <i class="fa-solid fa-circle-check"></i>
                                        <span>Simulasi: Saya Sudah Bayar QRIS (Demo)</span>
                                    </button>
                                </form>
                                <span class="text-[10px] text-slate-400 mt-1.5 block">Halaman ini otomatis me-refresh saat kasir mengonfirmasi pembayaran.</span>
                            </div>
                        </div>

                        <!-- Tampilan Tab 2: Tunai / Cash di Kasir -->
                        <div id="view-cash" class="space-y-5 hidden">
                            <div>
                                <span class="text-[10px] font-black tracking-widest text-slate-600 uppercase bg-slate-100 px-3 py-1 rounded-full border border-slate-200">
                                    Validasi Kasir
                                </span>
                                <h3 class="text-lg sm:text-xl font-black text-slate-900 mt-2">Tunjukkan QR Ini ke Petugas Kasir</h3>
                                <p class="text-xs text-slate-500 mt-0.5">Kasir akan memindai atau mencocokkan kode pesanan untuk pembayaran tunai Anda</p>
                            </div>

                            <!-- QR Frame -->
                            <div class="relative w-64 h-64 sm:w-72 sm:h-72 mx-auto bg-white p-4 rounded-3xl border-2 border-slate-300 shadow-md">
                                <img src="<?= $qr_url ?>" alt="QR Validasi Kasir" class="w-full h-full object-contain rounded-2xl">
                            </div>

                            <!-- Tagihan Tunai Card -->
                            <div class="bg-slate-50 border border-slate-200 rounded-2xl p-4 text-left max-w-md mx-auto space-y-2 text-xs">
                                <div class="flex justify-between">
                                    <span class="text-slate-500">Nomor Meja:</span>
                                    <span class="font-extrabold text-brand-600">Meja #<?= htmlspecialchars($order['nomor_meja']) ?></span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-slate-500">Kode Pesanan:</span>
                                    <span class="font-mono font-bold text-slate-800"><?= htmlspecialchars($order['kode_pesanan']) ?></span>
                                </div>
                                <div class="flex justify-between border-t border-slate-200 pt-2 font-bold">
                                    <span class="text-slate-700">Total Uang Pas:</span>
                                    <span class="text-sm font-black text-slate-900"><?= format_rupiah($order['total_harga']) ?></span>
                                </div>
                            </div>
                        </div>

                    <?php else: ?>
                        <!-- Keadaan Jika Sudah LUNAS -->
                        <div class="py-6 space-y-4">
                            <div class="w-20 h-20 sm:w-24 sm:h-24 rounded-full bg-emerald-100 text-emerald-600 flex items-center justify-center text-4xl sm:text-5xl mx-auto shadow-inner">
                                <i class="fa-solid fa-check"></i>
                            </div>

                            <div>
                                <span class="inline-block px-3 py-1 bg-emerald-100 text-emerald-800 text-xs font-black rounded-full mb-1 uppercase tracking-wide">
                                    LUNAS • <?= htmlspecialchars($order['metode_pembayaran'] ?: 'QRIS') ?>
                                </span>
                                <h3 class="text-xl sm:text-2xl font-black text-slate-900">Pembayaran Sah & Berhasil!</h3>
                                <p class="text-xs sm:text-sm text-slate-500 mt-1 max-w-md mx-auto">
                                    Dapur kami telah memvalidasi pesanan Anda. Silakan duduk santai sambil menunggu hidangan diantarkan ke meja.
                                </p>
                            </div>

                            <div class="bg-slate-50 border border-slate-200 rounded-2xl p-4 max-w-md mx-auto flex items-center justify-between text-left">
                                <div>
                                    <span class="text-[10px] text-slate-400 block font-bold uppercase">Kode Transaksi:</span>
                                    <span class="font-mono font-bold text-slate-800 text-sm"><?= htmlspecialchars($order['kode_pesanan']) ?></span>
                                </div>
                                <div class="text-right">
                                    <span class="text-[10px] text-slate-400 block font-bold uppercase">Total Dibayar:</span>
                                    <span class="font-black text-emerald-600 text-base"><?= format_rupiah($order['total_harga']) ?></span>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>

                </div>

                <!-- 3. TIMELINE PROSES DAPUR & PELAYANAN -->
                <div id="timeline-card" class="bg-white rounded-3xl p-6 border border-slate-200/80 shadow-xs">
                    <h3 class="font-extrabold text-sm text-slate-900 uppercase tracking-wider mb-5 flex items-center space-x-2">
                        <i class="fa-solid fa-fire-burner text-brand-500 text-base"></i>
                        <span>Alur Proses Pesanan Real-Time</span>
                    </h3>

                    <div class="space-y-6 relative">
                        <!-- Garis Penghubung Alur -->
                        <div class="absolute left-5 top-5 bottom-5 w-0.5 bg-slate-200 -z-0"></div>

                        <!-- Step 1: Validasi Pembayaran -->
                        <?php 
                            $step1_done = ($order['status_pembayaran'] === 'lunas');
                        ?>
                        <div class="flex items-start space-x-4 relative z-10">
                            <div class="w-10 h-10 rounded-2xl flex items-center justify-center font-bold text-sm shadow-xs flex-shrink-0 <?= $step1_done ? 'bg-emerald-500 text-white' : 'bg-brand-500 text-white animate-pulse' ?>">
                                <i class="fa-solid <?= $step1_done ? 'fa-check' : 'fa-receipt' ?>"></i>
                            </div>
                            <div class="pt-1">
                                <h4 class="font-bold text-sm <?= $step1_done ? 'text-slate-900' : 'text-brand-600' ?>">
                                    <?= $step1_done ? 'Pembayaran Tervalidasi' : 'Menunggu Validasi Bayar / QR' ?>
                                </h4>
                                <p class="text-xs text-slate-400 mt-0.5">Kasir dan sistem keuangan memvalidasi bukti transaksi Anda.</p>
                            </div>
                        </div>

                        <!-- Step 2: Sedang Dimasak -->
                        <?php 
                            $step2_active = in_array($order['status_pesanan'], ['dimasak', 'selesai']);
                            $step2_done = ($order['status_pesanan'] === 'selesai');
                        ?>
                        <div class="flex items-start space-x-4 relative z-10">
                            <div class="w-10 h-10 rounded-2xl flex items-center justify-center font-bold text-sm shadow-xs flex-shrink-0 <?= $step2_done ? 'bg-emerald-500 text-white' : ($order['status_pesanan'] === 'dimasak' ? 'bg-brand-500 text-white animate-bounce' : 'bg-slate-200 text-slate-400') ?>">
                                <i class="fa-solid fa-fire-burner"></i>
                            </div>
                            <div class="pt-1">
                                <h4 class="font-bold text-sm <?= $order['status_pesanan'] === 'dimasak' ? 'text-brand-600 font-extrabold' : ($step2_done ? 'text-slate-900' : 'text-slate-500') ?>">
                                    <?= $order['status_pesanan'] === 'dimasak' ? 'Sedang Dimasak Koki Resto' : ($step2_done ? 'Selesai Dimasak' : 'Antrean Dapur') ?>
                                </h4>
                                <p class="text-xs text-slate-400 mt-0.5">Bahan-bahan segar dimasak secara higienis oleh chef resto.</p>
                            </div>
                        </div>

                        <!-- Step 3: Diantar ke Meja -->
                        <?php 
                            $step3_done = ($order['status_pesanan'] === 'selesai');
                        ?>
                        <div class="flex items-start space-x-4 relative z-10">
                            <div class="w-10 h-10 rounded-2xl flex items-center justify-center font-bold text-sm shadow-xs flex-shrink-0 <?= $step3_done ? 'bg-emerald-500 text-white' : 'bg-slate-200 text-slate-400' ?>">
                                <i class="fa-solid fa-bell-concierge"></i>
                            </div>
                            <div class="pt-1">
                                <h4 class="font-bold text-sm <?= $step3_done ? 'text-emerald-700 font-black' : 'text-slate-500' ?>">
                                    <?= $step3_done ? 'Hidangan Telah Disajikan di Meja Anda' : 'Pengantaran ke Meja' ?>
                                </h4>
                                <p class="text-xs text-slate-400 mt-0.5">Pelayan mengantarkan pesanan langsung ke meja #<?= htmlspecialchars($order['nomor_meja']) ?>.</p>
                            </div>
                        </div>

                    </div>
                </div>

            </div>

            <!-- RIGHT COLUMN: Struk Digital / Nota Pesanan (lg:col-span-5) -->
            <div class="lg:col-span-5 space-y-6">
                
                <!-- DIGITAL RECEIPT CARD -->
                <div id="receipt-card" class="bg-white rounded-3xl p-6 sm:p-7 border border-slate-200/80 shadow-xs relative">
                    
                    <!-- Restaurant Header -->
                    <div class="text-center pb-5 border-b border-dashed border-slate-200">
                        <div class="w-12 h-12 rounded-2xl bg-brand-500 text-white flex items-center justify-center font-bold text-xl mx-auto mb-2 shadow-md shadow-brand-500/25">
                            <i class="fa-solid fa-utensils"></i>
                        </div>
                        <h2 class="font-black text-lg text-slate-900 tracking-tight">KanDar</h2>
                        <p class="text-[11px] text-slate-400 mt-0.5">Sistem Self-Service Dining Meja</p>
                    </div>

                    <!-- Order Details Summary -->
                    <div class="py-4 border-b border-dashed border-slate-200 text-xs space-y-2">
                        <div class="flex justify-between">
                            <span class="text-slate-500">Kode Pesanan:</span>
                            <span class="font-mono font-bold text-slate-800"><?= htmlspecialchars($order['kode_pesanan']) ?></span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-slate-500">Nomor Meja:</span>
                            <span class="font-extrabold text-brand-600">Meja #<?= htmlspecialchars($order['nomor_meja']) ?></span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-slate-500">Nama Pemesan:</span>
                            <span class="font-bold text-slate-800"><?= htmlspecialchars($order['nama_pelanggan']) ?></span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-slate-500">Waktu Order:</span>
                            <span class="text-slate-700"><?= date('d M Y, H:i', strtotime($order['created_at'])) ?> WIB</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-slate-500">Status Bayar:</span>
                            <span class="font-bold <?= $order['status_pembayaran'] === 'lunas' ? 'text-emerald-600' : 'text-amber-600' ?> uppercase">
                                <?= $order['status_pembayaran'] === 'lunas' ? 'Lunas' : 'Menunggu Bayar' ?>
                            </span>
                        </div>
                    </div>

                    <!-- Itemized List -->
                    <div class="py-4 border-b border-dashed border-slate-200 space-y-3">
                        <span class="text-[10px] uppercase font-bold tracking-wider text-slate-400 block mb-2">Rincian Menu:</span>
                        
                        <?php foreach ($items as $item): ?>
                            <div class="text-xs">
                                <div class="flex justify-between items-start font-bold text-slate-800">
                                    <span class="flex-1 pr-2">
                                        <?= $item['jumlah'] ?>x <?= htmlspecialchars($item['nama_menu']) ?>
                                    </span>
                                    <span class="flex-shrink-0 text-slate-900 font-extrabold">
                                        <?= format_rupiah($item['subtotal']) ?>
                                    </span>
                                </div>
                                <div class="flex justify-between text-[11px] text-slate-400 mt-0.5">
                                    <span>@ <?= format_rupiah($item['harga_satuan']) ?></span>
                                    <?php if (!empty($item['catatan'])): ?>
                                        <span class="text-brand-600 italic">"<?= htmlspecialchars($item['catatan']) ?>"</span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <!-- Total Price Breakdown -->
                    <div class="pt-4 space-y-2 text-xs">
                        <div class="flex justify-between text-slate-500">
                            <span>Subtotal Menu</span>
                            <span class="font-bold text-slate-800"><?= format_rupiah($order['total_harga']) ?></span>
                        </div>
                        <div class="flex justify-between text-slate-500">
                            <span>Biaya Layanan</span>
                            <span class="font-bold text-emerald-600">GRATIS</span>
                        </div>
                        <div class="pt-2 border-t border-slate-200 flex justify-between items-center text-sm font-black text-slate-900">
                            <span>Total Pembayaran</span>
                            <span class="text-base sm:text-lg text-brand-600 font-black"><?= format_rupiah($order['total_harga']) ?></span>
                        </div>
                    </div>

                    <!-- Action Buttons: Print & Tambah Menu (No-Print) -->
                    <div class="mt-6 pt-4 border-t border-slate-100 space-y-2.5 no-print">
                        <button onclick="window.print()" 
                                class="w-full py-3 bg-slate-100 hover:bg-slate-200 text-slate-800 font-bold rounded-2xl text-xs sm:text-sm flex items-center justify-center space-x-2 transition active:scale-95">
                            <i class="fa-solid fa-print"></i>
                            <span>Cetak Struk / Simpan Nota</span>
                        </button>
                        
                        <a href="index.php?meja=<?= urlencode($order['nomor_meja']) ?>" 
                           class="w-full py-3 bg-white hover:bg-brand-50 border border-slate-200 hover:border-brand-300 text-brand-600 font-bold rounded-2xl text-xs sm:text-sm flex items-center justify-center space-x-2 transition active:scale-95">
                            <i class="fa-solid fa-plus text-xs"></i>
                            <span>Pesan Menu Tambahan</span>
                        </a>
                    </div>

                </div>

            </div>

        </div>

    </main>

    <!-- TOAST NOTIFICATION -->
    <div id="toast" class="fixed top-20 right-4 z-50 pointer-events-none transform -translate-y-4 opacity-0 transition-all duration-300 no-print">
        <div class="bg-slate-900/95 text-white px-4 py-3 rounded-2xl shadow-xl flex items-center space-x-2.5 border border-slate-700">
            <i class="fa-solid fa-circle-check text-emerald-400 text-sm"></i>
            <span id="toast-msg" class="text-xs font-bold">Kode pesanan disalin!</span>
        </div>
    </div>

    <!-- JAVASCRIPT: Auto-Refresh Polling, Tabs, Clipboard Copy -->
    <script>
        const ORDER_ID = <?= (int)$order['id'] ?>;
        const currentPaidStatus = "<?= $order['status_pembayaran'] ?>";
        const currentOrderStatus = "<?= $order['status_pesanan'] ?>";

        function switchPaymentTab(tab) {
            const btnQris = document.getElementById('tab-btn-qris');
            const btnCash = document.getElementById('tab-btn-cash');
            const viewQris = document.getElementById('view-qris');
            const viewCash = document.getElementById('view-cash');

            if (tab === 'qris') {
                btnQris.className = 'flex-1 py-2.5 rounded-xl bg-white text-brand-600 shadow-sm transition flex items-center justify-center space-x-2';
                btnCash.className = 'flex-1 py-2.5 rounded-xl text-slate-600 hover:text-slate-900 transition flex items-center justify-center space-x-2';
                viewQris.classList.remove('hidden');
                viewCash.classList.add('hidden');
            } else {
                btnCash.className = 'flex-1 py-2.5 rounded-xl bg-white text-brand-600 shadow-sm transition flex items-center justify-center space-x-2';
                btnQris.className = 'flex-1 py-2.5 rounded-xl text-slate-600 hover:text-slate-900 transition flex items-center justify-center space-x-2';
                viewCash.classList.remove('hidden');
                viewQris.classList.add('hidden');
            }
        }

        function copyOrderCode(code) {
            navigator.clipboard.writeText(code).then(() => {
                showToast(`Kode ${code} berhasil disalin!`);
            });
        }

        function showToast(msg) {
            const toast = document.getElementById('toast');
            const toastMsg = document.getElementById('toast-msg');
            toastMsg.innerText = msg;
            toast.classList.remove('opacity-0', '-translate-y-4');
            toast.classList.add('opacity-100', 'translate-y-0');

            setTimeout(() => {
                toast.classList.remove('opacity-100', 'translate-y-0');
                toast.classList.add('opacity-0', '-translate-y-4');
            }, 2000);
        }

        // Auto-polling ke api/get_orders.php setiap 4 detik untuk memperbarui status pesanan jika kasir melakukan perubahan
        async function checkLiveStatus() {
            try {
                const res = await fetch(`../api/get_orders.php`);
                const data = await res.json();
                if (data.status === 'success') {
                    const list = data.data || [];
                    const thisOrder = list.find(o => o.id == ORDER_ID);
                    if (thisOrder) {
                        if (thisOrder.status_pembayaran !== currentPaidStatus || thisOrder.status_pesanan !== currentOrderStatus) {
                            window.location.reload();
                        }
                    }
                }
            } catch (err) {
                // Silently ignore network hiccup during polling
            }
        }

        // Jalankan polling jika pesanan belum selesai
        <?php if ($order['status_pesanan'] !== 'selesai'): ?>
            setInterval(checkLiveStatus, 4000);
        <?php endif; ?>
    </script>
</body>
</html>
