<?php
// pembeli/index.php
// [BAGIAN 2: PROGRAMMER 2] Halaman Utama Pembeli - Scan QR Meja & Katalog Menu

require_once __DIR__ . '/../config/koneksi.php';
require_once __DIR__ . '/../config/helper.php';

// Ambil nomor meja dari query parameter (misal ?meja=03) atau dari session
if (isset($_GET['meja']) && !empty($_GET['meja'])) {
    $_SESSION['nomor_meja'] = sanitize($_GET['meja']);
}

$meja = isset($_SESSION['nomor_meja']) ? $_SESSION['nomor_meja'] : '01';

// Ambil filter kategori jika ada
$kategori = isset($_GET['kategori']) ? sanitize($_GET['kategori']) : 'semua';

if ($kategori !== 'semua') {
    $stmt = $pdo->prepare("SELECT * FROM menus WHERE kategori = ? AND status = 'tersedia' ORDER BY id DESC");
    $stmt->execute([$kategori]);
} else {
    $stmt = $pdo->query("SELECT * FROM menus WHERE status = 'tersedia' ORDER BY kategori ASC, id DESC");
}
$daftar_menu = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Menu Resto - Meja <?= htmlspecialchars($meja) ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .no-scrollbar::-webkit-scrollbar { display: none; }
        .no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
    </style>
</head>
<body class="bg-slate-50 min-h-screen pb-28 text-slate-800">

    <!-- Top Bar -->
    <header class="sticky top-0 z-30 bg-white/95 backdrop-blur-md border-b border-slate-200 px-4 py-3 shadow-sm">
        <div class="max-w-md mx-auto flex items-center justify-between">
            <div class="flex items-center space-x-3">
                <div class="w-10 h-10 rounded-full bg-orange-100 text-orange-600 flex items-center justify-center font-bold">
                    <i class="fa-solid fa-utensils"></i>
                </div>
                <div>
                    <h1 class="font-bold text-base text-slate-900 leading-tight">Resto Enak Rasa</h1>
                    <span class="inline-flex items-center gap-1 text-xs font-semibold text-orange-600 bg-orange-50 px-2 py-0.5 rounded-full border border-orange-200">
                        <i class="fa-solid fa-chair text-[10px]"></i> Meja #<?= htmlspecialchars($meja) ?>
                    </span>
                </div>
            </div>
            <a href="cart.php" class="relative p-2 text-slate-600 hover:text-orange-600 transition">
                <i class="fa-solid fa-bag-shopping text-2xl"></i>
                <span id="badge-cart" class="hidden absolute top-0 right-0 w-5 h-5 bg-red-500 text-white text-[11px] font-bold rounded-full flex items-center justify-center">0</span>
            </a>
        </div>
    </header>

    <main class="max-w-md mx-auto px-4 mt-4">
        <!-- Banner Promo / Sambutan -->
        <div class="bg-gradient-to-r from-orange-500 to-amber-500 rounded-2xl p-4 text-white shadow-lg shadow-orange-500/20 mb-5">
            <h2 class="font-bold text-lg">Pesan Dari Meja Praktis!</h2>
            <p class="text-xs text-orange-100 mt-1">Pilih menu favoritmu, pesanan akan langsung diteruskan ke kasir & dapur.</p>
        </div>

        <!-- Filter Kategori Tabs -->
        <div class="flex space-x-2 overflow-x-auto no-scrollbar pb-2 mb-4">
            <a href="index.php?kategori=semua" class="px-4 py-2 rounded-full text-xs font-semibold whitespace-nowrap transition <?= $kategori == 'semua' ? 'bg-orange-500 text-white shadow-md shadow-orange-500/30' : 'bg-white text-slate-600 border border-slate-200 hover:bg-slate-100' ?>">
                🔥 Semua Menu
            </a>
            <a href="index.php?kategori=makanan" class="px-4 py-2 rounded-full text-xs font-semibold whitespace-nowrap transition <?= $kategori == 'makanan' ? 'bg-orange-500 text-white shadow-md shadow-orange-500/30' : 'bg-white text-slate-600 border border-slate-200 hover:bg-slate-100' ?>">
                🍛 Makanan
            </a>
            <a href="index.php?kategori=minuman" class="px-4 py-2 rounded-full text-xs font-semibold whitespace-nowrap transition <?= $kategori == 'minuman' ? 'bg-orange-500 text-white shadow-md shadow-orange-500/30' : 'bg-white text-slate-600 border border-slate-200 hover:bg-slate-100' ?>">
                🥤 Minuman
            </a>
            <a href="index.php?kategori=snack" class="px-4 py-2 rounded-full text-xs font-semibold whitespace-nowrap transition <?= $kategori == 'snack' ? 'bg-orange-500 text-white shadow-md shadow-orange-500/30' : 'bg-white text-slate-600 border border-slate-200 hover:bg-slate-100' ?>">
                🍟 Cemilan
            </a>
        </div>

        <!-- Daftar Menu Grid -->
        <div class="space-y-3">
            <?php if (empty($daftar_menu)): ?>
                <div class="bg-white rounded-2xl p-8 text-center border border-slate-200">
                    <i class="fa-solid fa-plate-wheat text-4xl text-slate-300 mb-2"></i>
                    <p class="text-sm text-slate-500">Belum ada menu di kategori ini.</p>
                </div>
            <?php else: ?>
                <?php foreach ($daftar_menu as $m): ?>
                    <div class="bg-white rounded-2xl p-3 border border-slate-200 flex space-x-3 shadow-sm hover:shadow-md transition">
                        <!-- Gambar -->
                        <div class="w-24 h-24 rounded-xl overflow-hidden bg-slate-100 flex-shrink-0 relative">
                            <img src="<?= htmlspecialchars($m['gambar'] ?: 'https://images.unsplash.com/photo-1546069901-ba9599a7e63c?w=300') ?>" 
                                 alt="<?= htmlspecialchars($m['nama_menu']) ?>" 
                                 class="w-full h-full object-cover">
                            <span class="absolute bottom-1 left-1 bg-black/60 text-white text-[10px] px-1.5 py-0.5 rounded backdrop-blur-xs capitalize">
                                <?= $m['kategori'] ?>
                            </span>
                        </div>

                        <!-- Info Menu -->
                        <div class="flex-1 flex flex-col justify-between">
                            <div>
                                <h3 class="font-bold text-sm text-slate-900 leading-tight"><?= htmlspecialchars($m['nama_menu']) ?></h3>
                                <p class="text-xs text-slate-500 line-clamp-2 mt-1"><?= htmlspecialchars($m['deskripsi'] ?: '-') ?></p>
                            </div>
                            <div class="flex items-center justify-between mt-2">
                                <span class="font-bold text-orange-600 text-sm"><?= format_rupiah($m['harga']) ?></span>
                                <button onclick="addToCart(<?= $m['id'] ?>, '<?= addslashes(htmlspecialchars($m['nama_menu'])) ?>', <?= $m['harga'] ?>, '<?= addslashes(htmlspecialchars($m['gambar'])) ?>')" 
                                        class="px-3 py-1.5 rounded-lg bg-orange-500 hover:bg-orange-600 text-white text-xs font-semibold flex items-center space-x-1 shadow-sm active:scale-95 transition">
                                    <i class="fa-solid fa-plus text-[10px]"></i>
                                    <span>Tambah</span>
                                </button>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </main>

    <!-- Floating Bottom Cart Bar -->
    <div id="floating-cart" class="fixed bottom-4 left-4 right-4 max-w-md mx-auto hidden z-40">
        <a href="cart.php" class="bg-slate-900 text-white rounded-2xl p-3.5 flex items-center justify-between shadow-2xl hover:bg-black transition border border-slate-700">
            <div class="flex items-center space-x-3">
                <div class="w-10 h-10 rounded-xl bg-orange-500 text-white flex items-center justify-center font-bold text-sm">
                    <span id="floating-count">0</span>
                </div>
                <div>
                    <div class="text-xs text-slate-300">Total Sementara:</div>
                    <div id="floating-total" class="font-bold text-sm text-orange-400">Rp 0</div>
                </div>
            </div>
            <div class="flex items-center space-x-1.5 text-xs font-bold text-white bg-white/10 px-3 py-2 rounded-xl">
                <span>Lihat Pesanan</span>
                <i class="fa-solid fa-arrow-right text-[10px]"></i>
            </div>
        </a>
    </div>

    <!-- Script Keranjang (LocalStorage) -->
    <script>
        function getCart() {
            return JSON.parse(localStorage.getItem('cart_resto') || '[]');
        }

        function saveCart(cart) {
            localStorage.setItem('cart_resto', JSON.stringify(cart));
            updateCartUI();
        }

        function addToCart(id, name, price, img) {
            let cart = getCart();
            let existing = cart.find(item => item.id === id);

            if (existing) {
                existing.jumlah += 1;
            } else {
                cart.push({ id: id, nama: name, harga: price, gambar: img, jumlah: 1, catatan: '' });
            }
            saveCart(cart);
        }

        function updateCartUI() {
            const cart = getCart();
            const totalCount = cart.reduce((acc, item) => acc + item.jumlah, 0);
            const totalPrice = cart.reduce((acc, item) => acc + (item.harga * item.jumlah), 0);

            const badge = document.getElementById('badge-cart');
            const floating = document.getElementById('floating-cart');
            const floatingCount = document.getElementById('floating-count');
            const floatingTotal = document.getElementById('floating-total');

            if (totalCount > 0) {
                badge.classList.remove('hidden');
                badge.innerText = totalCount;

                floating.classList.remove('hidden');
                floatingCount.innerText = totalCount;
                floatingTotal.innerText = 'Rp ' + totalPrice.toLocaleString('id-ID');
            } else {
                badge.classList.add('hidden');
                floating.classList.add('hidden');
            }
        }

        document.addEventListener('DOMContentLoaded', updateCartUI);
    </script>
</body>
</html>

