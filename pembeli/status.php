<?php
// pembeli/status.php
// Halaman Pelacakan Status Pesanan & Validasi Pembayaran Anti-Fake Order

require_once __DIR__ . '/../config/koneksi.php';
require_once __DIR__ . '/../config/helper.php';

$order_id = isset($_GET['order_id']) ? (int)$_GET['order_id'] : 0;

if ($order_id <= 0) {
    header('Location: index.php');
    exit;
}

// Handle simulasi bayar / demo validasi QRIS langsung dari HP
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

// Generate data isi QR Code Validasi Pesanan
$qr_content = "RESTO-ENAK-RASA:ORDER=" . $order['kode_pesanan'] . ":MEJA=" . $order['nomor_meja'] . ":TOTAL=" . $order['total_harga'];
$qr_url = "https://api.qrserver.com/v1/create-qr-code/?size=300x300&margin=10&data=" . urlencode($qr_content);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Status & Pembayaran Pesanan #<?= htmlspecialchars($order['kode_pesanan']) ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
    </style>
</head>
<body class="bg-gradient-to-b from-orange-50/60 to-slate-50 min-h-screen pb-24 text-slate-800">

    <!-- Top Bar -->
    <header class="sticky top-0 z-40 bg-white/90 backdrop-blur-md border-b border-slate-200 px-4 py-3 shadow-sm">
        <div class="max-w-md mx-auto flex items-center justify-between">
            <a href="index.php?meja=<?= htmlspecialchars($order['nomor_meja']) ?>" class="text-xs text-orange-600 hover:text-orange-700 font-bold flex items-center space-x-1 transition">
                <i class="fa-solid fa-arrow-left"></i>
                <span>Katalog Menu</span>
            </a>
            <div class="flex items-center space-x-2">
                <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full bg-orange-50 text-orange-600 font-bold text-xs border border-orange-200">
                    <i class="fa-solid fa-chair text-[10px]"></i> Meja #<?= htmlspecialchars($order['nomor_meja']) ?>
                </span>
                <span id="auto-refresh-badge" class="text-[11px] text-slate-400 flex items-center space-x-1">
                    <i class="fa-solid fa-rotate fa-spin text-[10px] text-orange-500"></i>
                    <span class="text-[10px]">Live</span>
                </span>
            </div>
        </div>
    </header>

    <main class="max-w-md mx-auto px-4 mt-5 space-y-4">

        <!-- Banner Anti-Fake Order / Validasi Status Pembayaran -->
        <?php if ($order['status_pembayaran'] !== 'lunas'): ?>
            <div class="bg-amber-50 border border-amber-200 rounded-2xl p-4 shadow-sm">
                <div class="flex items-start space-x-3">
                    <div class="w-8 h-8 rounded-xl bg-amber-500 text-white flex items-center justify-center font-bold text-sm flex-shrink-0 mt-0.5 shadow-sm">
                        <i class="fa-solid fa-shield-halved"></i>
                    </div>
                    <div>
                        <h3 class="font-extrabold text-xs text-amber-900 uppercase tracking-wide">Validasi Pembayaran Diperlukan</h3>
                        <p class="text-xs text-amber-800 mt-1 leading-relaxed">
                            Untuk mencegah <b>pemesanan fiktif</b>, pesanan ini <b>baru akan dimasak oleh dapur</b> setelah Anda menyelesaikan pembayaran via <b>QRIS</b> atau <b>Tunai di Kasir</b>.
                        </p>
                    </div>
                </div>
            </div>
        <?php else: ?>
            <div class="bg-emerald-50 border border-emerald-200 rounded-2xl p-4 shadow-sm flex items-center space-x-3">
                <div class="w-8 h-8 rounded-xl bg-emerald-500 text-white flex items-center justify-center font-bold text-sm flex-shrink-0 shadow-sm">
                    <i class="fa-solid fa-check"></i>
                </div>
                <div>
                    <h3 class="font-extrabold text-xs text-emerald-900 uppercase tracking-wide">Pembayaran Terverifikasi (LUNAS)</h3>
                    <p class="text-xs text-emerald-700 mt-0.5">Pesanan Anda telah divalidasi dan aman dari pembatalan.</p>
                </div>
            </div>
        <?php endif; ?>

        <!-- Kartu Utama: Validasi QR & Pembayaran -->
        <div class="bg-white rounded-3xl p-6 border border-slate-200 shadow-xl text-center relative overflow-hidden">
            
            <?php if ($order['status_pembayaran'] !== 'lunas'): ?>
                <!-- Navigasi Tab Pilihan Metode Pembayaran -->
                <div class="flex bg-slate-100 p-1 rounded-xl mb-5 text-xs font-bold">
                    <button type="button" id="tab-btn-qris" onclick="switchPaymentTab('qris')" class="flex-1 py-2 rounded-lg bg-white text-orange-600 shadow-sm transition flex items-center justify-center space-x-1.5">
                        <i class="fa-solid fa-qrcode"></i>
                        <span>Bayar QRIS</span>
                    </button>
                    <button type="button" id="tab-btn-cash" onclick="switchPaymentTab('cash')" class="flex-1 py-2 rounded-lg text-slate-600 hover:text-slate-900 transition flex items-center justify-center space-x-1.5">
                        <i class="fa-solid fa-money-bill-wave"></i>
                        <span>Bayar Tunai di Kasir</span>
                    </button>
                </div>

                <!-- Tampilan 1: QRIS -->
                <div id="view-qris" class="space-y-4">
                    <div>
                        <span class="text-[10px] font-black tracking-widest text-orange-600 uppercase bg-orange-50 px-2.5 py-1 rounded-full border border-orange-200">
                            Scan QRIS Pembayaran
                        </span>
                        <h2 class="text-lg font-black text-slate-900 mt-2">Scan & Bayar Otomatis</h2>
                        <p class="text-xs text-slate-500 mt-0.5">Bisa pakai GoPay, OVO, Dana, ShopeePay, BCA, atau Livin'</p>
                    </div>

                    <!-- QR Code Frame -->
                    <div class="relative w-56 h-56 mx-auto bg-white p-3 rounded-2xl border-2 border-orange-500/80 shadow-md">
                        <!-- Corner Accents -->
                        <div class="absolute -top-1 -left-1 w-4 h-4 border-t-4 border-l-4 border-orange-600 rounded-tl"></div>
                        <div class="absolute -top-1 -right-1 w-4 h-4 border-t-4 border-r-4 border-orange-600 rounded-tr"></div>
                        <div class="absolute -bottom-1 -left-1 w-4 h-4 border-b-4 border-l-4 border-orange-600 rounded-bl"></div>
                        <div class="absolute -bottom-1 -right-1 w-4 h-4 border-b-4 border-r-4 border-orange-600 rounded-br"></div>

                        <img src="<?= $qr_url ?>" alt="QRIS Validasi" class="w-full h-full object-contain rounded-xl">
                    </div>

                    <!-- Total Nominal Tagihan -->
                    <div class="bg-orange-50/70 border border-orange-100 rounded-2xl p-3.5">
                        <span class="text-[11px] text-slate-500 block">Total Nominal Tagihan:</span>
                        <span class="text-2xl font-black text-orange-600 block mt-0.5"><?= format_rupiah($order['total_harga']) ?></span>
                        <span class="text-[10px] font-mono font-bold text-slate-400 mt-0.5 block"><?= htmlspecialchars($order['kode_pesanan']) ?></span>
                    </div>

                    <!-- Tombol Simulasi Pembayaran QRIS (Sangat berguna saat demo/testing) -->
                    <form action="status.php?order_id=<?= $order['id'] ?>" method="POST">
                        <input type="hidden" name="simulasi_bayar" value="1">
                        <button type="submit" class="w-full py-3 bg-orange-500 hover:bg-orange-600 text-white font-extrabold rounded-xl text-xs shadow-md shadow-orange-500/20 active:scale-95 transition flex items-center justify-center space-x-2">
                            <i class="fa-solid fa-circle-check"></i>
                            <span>Simulasi: Saya Sudah Bayar QRIS</span>
                        </button>
                    </form>
                </div>

                <!-- Tampilan 2: Tunai / Cash di Kasir -->
                <div id="view-cash" class="space-y-4 hidden">
                    <div>
                        <span class="text-[10px] font-black tracking-widest text-slate-600 uppercase bg-slate-100 px-2.5 py-1 rounded-full border border-slate-200">
                            QR Validasi Kasir
                        </span>
                        <h2 class="text-lg font-black text-slate-900 mt-2">Tunjukkan QR Ini ke Kasir</h2>
                        <p class="text-xs text-slate-500 mt-0.5">Petugas kasir akan men-scan atau memvalidasi pembayaran tunai Anda</p>
                    </div>

                    <!-- QR Validasi Kasir -->
                    <div class="relative w-56 h-56 mx-auto bg-white p-3 rounded-2xl border-2 border-slate-300 shadow-md">
                        <img src="<?= $qr_url ?>" alt="QR Validasi Kasir" class="w-full h-full object-contain rounded-xl">
                    </div>

                    <!-- Info Tagihan Tunai -->
                    <div class="bg-slate-50 border border-slate-200 rounded-2xl p-3.5 text-left space-y-1.5 text-xs">
                        <div class="flex justify-between">
                            <span class="text-slate-500">Nomor Meja:</span>
                            <span class="font-bold text-orange-600">Meja #<?= htmlspecialchars($order['nomor_meja']) ?></span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-slate-500">Kode Pesanan:</span>
                            <span class="font-mono font-bold text-slate-800"><?= htmlspecialchars($order['kode_pesanan']) ?></span>
                        </div>
                        <div class="flex justify-between border-t border-slate-200 pt-1.5 font-bold">
                            <span class="text-slate-700">Total Uang Pas:</span>
                            <span class="text-sm font-black text-slate-900"><?= format_rupiah($order['total_harga']) ?></span>
                        </div>
                    </div>

                    <div class="p-3 bg-amber-50 border border-amber-200 rounded-xl text-[11px] text-amber-800 text-left flex items-start space-x-2">
                        <i class="fa-solid fa-bell text-amber-600 mt-0.5"></i>
                        <span>Segera bayar ke kasir agar pesanan tidak dibatalkan otomatis oleh sistem.</span>
                    </div>
                </div>

            <?php else: ?>
                <!-- Keadaan Jika Sudah LUNAS / Sedang Dimasak / Selesai -->
                <div class="py-4 space-y-4">
                    <?php if ($order['status_pesanan'] === 'selesai'): ?>
                        <div class="w-20 h-20 rounded-full bg-emerald-100 text-emerald-600 flex items-center justify-center text-4xl mx-auto shadow-inner animate-bounce">
                            <i class="fa-solid fa-bell-concierge"></i>
                        </div>
                        <div>
                            <span class="inline-block px-3.5 py-1 bg-emerald-100 text-emerald-800 text-xs font-black rounded-full mb-1 border border-emerald-200 uppercase tracking-wide">
                                🎉 Makanan Siap Disajikan
                            </span>
                            <h2 class="text-xl font-black text-slate-900">Pesanan Telah Selesai!</h2>
                            <p class="text-xs text-slate-500 mt-1">Pelayan kami sedang mengantarkan hidangan ke meja Anda. Selamat menikmati!</p>
                        </div>
                    <?php else: ?>
                        <div class="w-20 h-20 rounded-full bg-orange-100 text-orange-600 flex items-center justify-center text-4xl mx-auto shadow-inner animate-pulse">
                            <i class="fa-solid fa-fire-burner"></i>
                        </div>
                        <div>
                            <span class="inline-block px-3.5 py-1 bg-orange-100 text-orange-800 text-xs font-black rounded-full mb-1 border border-orange-200 uppercase tracking-wide">
                                🔥 Sedang Dimasak di Dapur
                            </span>
                            <h2 class="text-xl font-black text-slate-900">Koki Sedang Memasak!</h2>
                            <p class="text-xs text-slate-500 mt-1">Pembayaran telah sah divalidasi. Hidangan Anda sedang dipersiapkan hangat & higienis.</p>
                        </div>
                    <?php endif; ?>

                    <div class="bg-slate-50 border border-slate-200 rounded-2xl p-4 flex items-center justify-between text-left">
                        <div>
                            <span class="text-[11px] text-slate-400 block">Kode Struk & Validasi:</span>
                            <span class="font-mono font-bold text-slate-800 text-sm"><?= htmlspecialchars($order['kode_pesanan']) ?></span>
                        </div>
                        <div class="text-right">
                            <span class="text-[11px] text-slate-400 block">Status Pembayaran:</span>
                            <span class="font-black text-emerald-600 text-sm flex items-center justify-end gap-1">
                                <i class="fa-solid fa-circle-check text-xs"></i> LUNAS (<?= htmlspecialchars($order['metode_pembayaran'] ?: 'QRIS') ?>)
                            </span>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

        </div>

        <!-- Status Alur Dapur Realtime -->
        <div class="bg-white rounded-3xl p-5 border border-slate-200 shadow-sm">
            <h3 class="font-extrabold text-xs text-slate-900 uppercase tracking-wider mb-4 flex items-center space-x-1.5">
                <i class="fa-solid fa-fire-burner text-orange-500"></i>
                <span>Proses Dapur & Pelayanan:</span>
            </h3>

            <div class="space-y-4 relative">
                <!-- Garis Penghubung Alur -->
                <div class="absolute left-4 top-4 bottom-4 w-0.5 bg-slate-200 -z-0"></div>

                <!-- Tahap 1: Validasi Pembayaran -->
                <div class="flex items-center space-x-3.5 relative z-10">
                    <div class="w-8 h-8 rounded-full flex items-center justify-center font-bold text-xs shadow-sm <?= $order['status_pembayaran'] === 'lunas' ? 'bg-emerald-500 text-white' : 'bg-orange-500 text-white animate-pulse' ?>">
                        <i class="fa-solid <?= $order['status_pembayaran'] === 'lunas' ? 'fa-check' : 'fa-receipt' ?>"></i>
                    </div>
                    <div>
                        <h4 class="font-bold text-xs <?= $order['status_pembayaran'] === 'lunas' ? 'text-slate-900' : 'text-orange-600' ?>">
                            <?= $order['status_pembayaran'] === 'lunas' ? 'Pembayaran Tervalidasi' : 'Menunggu Validasi Bayar / QR' ?>
                        </h4>
                        <p class="text-[11px] text-slate-400">Kasir memvalidasi transaksi Anda</p>
                    </div>
                </div>

                <!-- Tahap 2: Sedang Dimasak -->
                <div class="flex items-center space-x-3.5 relative z-10">
                    <div class="w-8 h-8 rounded-full flex items-center justify-center font-bold text-xs shadow-sm <?= in_array($order['status_pesanan'], ['dimasak', 'selesai']) ? 'bg-orange-500 text-white' : 'bg-slate-200 text-slate-400' ?>">
                        <i class="fa-solid fa-fire-burner"></i>
                    </div>
                    <div>
                        <h4 class="font-bold text-xs <?= $order['status_pesanan'] === 'dimasak' ? 'text-orange-600 font-black' : 'text-slate-700' ?>">
                            <?= $order['status_pesanan'] === 'dimasak' ? 'Sedang Dimasak Koki Resto 🔥' : 'Antrean Memasak Dapur' ?>
                        </h4>
                        <p class="text-[11px] text-slate-400">Hidangan disiapkan secara higienis</p>
                    </div>
                </div>

                <!-- Tahap 3: Disajikan -->
                <div class="flex items-center space-x-3.5 relative z-10">
                    <div class="w-8 h-8 rounded-full flex items-center justify-center font-bold text-xs shadow-sm <?= $order['status_pesanan'] === 'selesai' ? 'bg-emerald-500 text-white' : 'bg-slate-200 text-slate-400' ?>">
                        <i class="fa-solid fa-bell-concierge"></i>
                    </div>
                    <div>
                        <h4 class="font-bold text-xs <?= $order['status_pesanan'] === 'selesai' ? 'text-emerald-700 font-black' : 'text-slate-700' ?>">
                            Makanan Diantar ke Meja Anda
                        </h4>
                        <p class="text-[11px] text-slate-400">Pelayan mengantarkan tepat ke meja #<?= htmlspecialchars($order['nomor_meja']) ?></p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Detail Rincian Menu Dipesan -->
        <div class="bg-white rounded-3xl p-5 border border-slate-200 shadow-sm">
            <h3 class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-3">Item Menu yang Dipesan:</h3>
            <div class="space-y-3 mb-4">
                <?php foreach ($items as $item): ?>
                    <div class="flex justify-between items-start text-xs border-b border-slate-100 pb-2.5 last:border-b-0 last:pb-0">
                        <div>
                            <span class="font-extrabold text-slate-900"><?= $item['jumlah'] ?>x <?= htmlspecialchars($item['nama_menu']) ?></span>
                            <?php if (!empty($item['catatan'])): ?>
                                <p class="text-[11px] text-orange-600 italic mt-0.5">Note: "<?= htmlspecialchars($item['catatan']) ?>"</p>
                            <?php endif; ?>
                        </div>
                        <span class="font-bold text-slate-800"><?= format_rupiah($item['subtotal']) ?></span>
                    </div>
                <?php endforeach; ?>
            </div>

            <!-- Total Ringkasan -->
            <div class="border-t border-slate-100 pt-3 flex justify-between items-center text-xs">
                <span class="font-semibold text-slate-500">Total Tagihan:</span>
                <span class="text-base font-black text-orange-600"><?= format_rupiah($order['total_harga']) ?></span>
            </div>
        </div>

        <!-- Tombol Aksi Tambahan -->
        <div class="space-y-2 pt-2">
            <a href="index.php?meja=<?= htmlspecialchars($order['nomor_meja']) ?>" 
               class="block w-full py-3 bg-white hover:bg-orange-50 hover:text-orange-600 text-slate-700 font-bold rounded-2xl text-xs text-center border border-slate-200 shadow-sm transition">
                + Tambah Pesanan / Menu Lain
            </a>
        </div>

    </main>

    <script>
        function switchPaymentTab(tab) {
            const btnQris = document.getElementById('tab-btn-qris');
            const btnCash = document.getElementById('tab-btn-cash');
            const viewQris = document.getElementById('view-qris');
            const viewCash = document.getElementById('view-cash');

            if (tab === 'qris') {
                btnQris.className = 'flex-1 py-2 rounded-lg bg-white text-orange-600 shadow-sm transition flex items-center justify-center space-x-1.5';
                btnCash.className = 'flex-1 py-2 rounded-lg text-slate-600 hover:text-slate-900 transition flex items-center justify-center space-x-1.5';
                viewQris.classList.remove('hidden');
                viewCash.classList.add('hidden');
            } else {
                btnCash.className = 'flex-1 py-2 rounded-lg bg-white text-orange-600 shadow-sm transition flex items-center justify-center space-x-1.5';
                btnQris.className = 'flex-1 py-2 rounded-lg text-slate-600 hover:text-slate-900 transition flex items-center justify-center space-x-1.5';
                viewCash.classList.remove('hidden');
                viewQris.classList.add('hidden');
            }
        }

        // Live polling otomatis setiap 2 detik agar status langsung berubah saat admin/kasir memvalidasi/memasak/menyelesaikan pesanan
        const currentPaidStatus = "<?= $order['status_pembayaran'] ?>";
        const currentOrderStatus = "<?= $order['status_pesanan'] ?>";

        async function checkLiveStatus() {
            try {
                const res = await fetch(`../api/check_order_status.php?order_id=<?= $order['id'] ?>`);
                const json = await res.json();
                if (json.status === 'success' && json.data) {
                    if (json.data.status_pembayaran !== currentPaidStatus || json.data.status_pesanan !== currentOrderStatus) {
                        // Status berubah seketika, reload halaman untuk update tampilan!
                        window.location.reload();
                    }
                }
            } catch (err) {
                // Silently ignore network hiccup
            }
        }

        setInterval(checkLiveStatus, 2000);
    </script>
</body>
</html>
