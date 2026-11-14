<?php
// pembeli/index.php
// [BAGIAN 2: PROGRAMMER 2] Halaman Utama Pembeli - Scan QR Meja & Katalog Menu
// Didesain Full Responsif di Segala Device (Mobile, Tablet, Laptop, Desktop)

require_once __DIR__ . '/../config/koneksi.php';
require_once __DIR__ . '/../config/helper.php';

// 1. Deteksi nomor meja dari URL parameter (?meja=03) atau dari session
if (isset($_GET['meja']) && !empty($_GET['meja'])) {
    $meja_input = sanitize($_GET['meja']);
    $_SESSION['meja'] = $meja_input;
    $_SESSION['nomor_meja'] = $meja_input;
}

// Sinkronisasi session meja
$meja = $_SESSION['meja'] ?? $_SESSION['nomor_meja'] ?? '01';

// 2. Kategori filter
$kategori_aktif = isset($_GET['kategori']) ? sanitize($_GET['kategori']) : 'semua';

// Ambil semua menu dari database
$stmt = $pdo->query("SELECT * FROM menus ORDER BY (status = 'tersedia') DESC, kategori ASC, id DESC");
$semua_menu = $stmt->fetchAll();

// Hitung total menu per kategori
$count_kategori = [
    'semua'   => count($semua_menu),
    'makanan' => 0,
    'minuman' => 0,
    'snack'   => 0
];
foreach ($semua_menu as $m) {
    if (isset($count_kategori[$m['kategori']])) {
        $count_kategori[$m['kategori']]++;
    }
}
?>
<!DOCTYPE html>
<html lang="id" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Menu Restoran - Meja <?= htmlspecialchars($meja) ?> | KanDar</title>
    
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
<body class="bg-slate-50 min-h-screen text-slate-800 flex flex-col antialiased pb-28 lg:pb-12 selection:bg-brand-500 selection:text-white">

    <!-- TOP NAVBAR (Responsif: Mobile hingga Desktop) -->
    <header class="sticky top-0 z-40 bg-white/95 backdrop-blur-md border-b border-slate-200/80 shadow-sm">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-16 gap-3">
                
                <!-- Logo Resto & Nama -->
                <div class="flex items-center space-x-3">
                    <a href="../index.php" class="flex items-center space-x-3 group" title="Kembali ke Beranda Resto">
                        <div class="w-10 h-10 rounded-2xl bg-gradient-to-tr from-brand-600 to-amber-500 text-white flex items-center justify-center font-bold text-lg shadow-md shadow-brand-500/25 group-hover:scale-105 transition-transform">
                            <i class="fa-solid fa-utensils"></i>
                        </div>
                        <div>
                            <span class="font-extrabold text-slate-900 text-base sm:text-lg tracking-tight block leading-tight">KanDar</span>
                            <span class="text-[10px] text-brand-600 font-semibold tracking-wider uppercase block">Self-Service Dining</span>
                        </div>
                    </a>
                </div>

                <!-- Bagian Kanan: Indikator Meja, Cek Status & Tombol Keranjang -->
                <div class="flex items-center space-x-2 sm:space-x-3">
                    
                    <!-- Table Badge dengan Tombol Ubah Meja -->
                    <button onclick="openTableModal()" 
                            class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full bg-brand-50 hover:bg-brand-100 text-brand-700 text-xs font-bold border border-brand-200 transition active:scale-95 shadow-sm" 
                            title="Klik untuk ubah nomor meja">
                        <i class="fa-solid fa-chair text-[11px] text-brand-600"></i>
                        <span>Meja <span id="label-table-no"><?= htmlspecialchars($meja) ?></span></span>
                        <i class="fa-solid fa-pen text-[9px] text-brand-500 ml-0.5"></i>
                    </button>

                    <!-- Tombol Keranjang Belanja -->
                    <a href="cart.php" 
                       class="relative p-2.5 rounded-xl bg-slate-100 hover:bg-brand-50 text-slate-700 hover:text-brand-600 transition flex items-center justify-center active:scale-95" 
                       title="Lihat Keranjang Pesanan">
                        <i class="fa-solid fa-bag-shopping text-xl"></i>
                        <span id="badge-cart" class="hidden absolute -top-1 -right-1 w-5 h-5 bg-brand-600 text-white text-[11px] font-extrabold rounded-full flex items-center justify-center shadow-md border-2 border-white">
                            0
                        </span>
                    </a>

                </div>

            </div>
        </div>
    </header>

    <!-- KONTEN UTAMA: Responsif Layout Grid (Desktop 2-Kolom, Mobile/Tablet 1-Kolom) -->
    <main class="flex-1 max-w-7xl w-full mx-auto px-4 sm:px-6 lg:px-8 py-5">
        
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
            
            <!-- KOLOM KIRI (lg:col-span-8): Hero Banner, Filter & Daftar Menu -->
            <div class="lg:col-span-8 space-y-6">
                
                <!-- Hero Banner Sambutan & Info Meja -->
                <div class="relative overflow-hidden bg-gradient-to-r from-brand-600 via-orange-500 to-amber-500 rounded-3xl p-5 sm:p-7 text-white shadow-lg shadow-brand-500/20">
                    <div class="relative z-10 max-w-xl">
                        <div class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-white/20 backdrop-blur-md text-[11px] font-bold tracking-wide uppercase mb-2 text-white">
                            <i class="fa-solid fa-sparkles text-amber-200"></i> Menu Self-Service QR
                        </div>
                        <h1 class="text-xl sm:text-2xl md:text-3xl font-black tracking-tight leading-tight">
                            Selamat Datang di Meja <?= htmlspecialchars($meja) ?>
                        </h1>
                        <p class="text-xs sm:text-sm text-brand-100 mt-1 leading-relaxed">
                            Pesan makanan & minuman langsung dari tempat duduk Anda. Pesanan otomatis masuk ke kasir dan dapur resto.
                        </p>
                    </div>

                    <!-- Hiasan Background Accent -->
                    <div class="absolute -right-6 -bottom-8 opacity-15 text-white pointer-events-none">
                        <i class="fa-solid fa-bowl-food text-9xl"></i>
                    </div>
                </div>

                <!-- Card Pencarian & Kategori Tabs -->
                <div class="bg-white rounded-3xl p-4 sm:p-5 border border-slate-200/80 shadow-sm space-y-4">
                    
                    <!-- Input Pencarian Real-Time -->
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                            <i class="fa-solid fa-magnifying-glass text-sm"></i>
                        </div>
                        <input type="text" 
                               id="search-input" 
                               placeholder="Cari makanan lezat, minuman segar, atau cemilan..." 
                               oninput="filterMenuClient()"
                               class="w-full pl-10 pr-10 py-3 bg-slate-50 border border-slate-200 rounded-2xl text-sm font-medium text-slate-800 placeholder-slate-400 focus:outline-none focus:border-brand-500 focus:bg-white focus:ring-2 focus:ring-brand-500/20 transition">
                        <button id="search-clear" onclick="clearSearch()" class="hidden absolute inset-y-0 right-0 pr-3.5 flex items-center text-slate-400 hover:text-slate-600">
                            <i class="fa-solid fa-circle-xmark text-sm"></i>
                        </button>
                    </div>

                    <!-- Kategori Tab Pills -->
                    <div class="flex items-center space-x-2 overflow-x-auto no-scrollbar pb-1 pt-1">
                        <button onclick="setCategoryFilter('semua')" 
                                id="tab-semua"
                                class="category-tab px-4 py-2 rounded-xl text-xs sm:text-sm font-bold whitespace-nowrap transition-all flex items-center gap-1.5 <?= $kategori_aktif === 'semua' ? 'bg-brand-500 text-white shadow-md shadow-brand-500/25 ring-2 ring-brand-500/20' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' ?>">
                            <span><i class="fa-solid fa-fire text-xs mr-1"></i>Semua</span>
                            <span class="text-[10px] py-0.5 px-1.5 rounded-full <?= $kategori_aktif === 'semua' ? 'bg-white/30 text-white' : 'bg-slate-200 text-slate-600' ?>">
                                <?= $count_kategori['semua'] ?>
                            </span>
                        </button>
                        
                        <button onclick="setCategoryFilter('makanan')" 
                                id="tab-makanan"
                                class="category-tab px-4 py-2 rounded-xl text-xs sm:text-sm font-bold whitespace-nowrap transition-all flex items-center gap-1.5 <?= $kategori_aktif === 'makanan' ? 'bg-brand-500 text-white shadow-md shadow-brand-500/25 ring-2 ring-brand-500/20' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' ?>">
                            <span><i class="fa-solid fa-bowl-food text-xs mr-1"></i>Makanan</span>
                            <span class="text-[10px] py-0.5 px-1.5 rounded-full <?= $kategori_aktif === 'makanan' ? 'bg-white/30 text-white' : 'bg-slate-200 text-slate-600' ?>">
                                <?= $count_kategori['makanan'] ?>
                            </span>
                        </button>

                        <button onclick="setCategoryFilter('minuman')" 
                                id="tab-minuman"
                                class="category-tab px-4 py-2 rounded-xl text-xs sm:text-sm font-bold whitespace-nowrap transition-all flex items-center gap-1.5 <?= $kategori_aktif === 'minuman' ? 'bg-brand-500 text-white shadow-md shadow-brand-500/25 ring-2 ring-brand-500/20' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' ?>">
                            <span><i class="fa-solid fa-mug-hot text-xs mr-1"></i>Minuman</span>
                            <span class="text-[10px] py-0.5 px-1.5 rounded-full <?= $kategori_aktif === 'minuman' ? 'bg-white/30 text-white' : 'bg-slate-200 text-slate-600' ?>">
                                <?= $count_kategori['minuman'] ?>
                            </span>
                        </button>

                        <button onclick="setCategoryFilter('snack')" 
                                id="tab-snack"
                                class="category-tab px-4 py-2 rounded-xl text-xs sm:text-sm font-bold whitespace-nowrap transition-all flex items-center gap-1.5 <?= $kategori_aktif === 'snack' ? 'bg-brand-500 text-white shadow-md shadow-brand-500/25 ring-2 ring-brand-500/20' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' ?>">
                            <span><i class="fa-solid fa-cookie-bite text-xs mr-1"></i>Cemilan</span>
                            <span class="text-[10px] py-0.5 px-1.5 rounded-full <?= $kategori_aktif === 'snack' ? 'bg-white/30 text-white' : 'bg-slate-200 text-slate-600' ?>">
                                <?= $count_kategori['snack'] ?>
                            </span>
                        </button>
                    </div>

                </div>

                <!-- Info Filter Aktif & Jumlah Menu -->
                <div class="flex items-center justify-between text-xs text-slate-500 px-1">
                    <div>
                        Kategori: <span id="label-active-category" class="font-bold text-slate-800 capitalize"><?= $kategori_aktif ?></span>
                    </div>
                    <div id="results-count" class="font-semibold">
                        <?= count($semua_menu) ?> menu tersedia
                    </div>
                </div>

                <!-- DAFTAR MENU GRID (1 Kolom Mobile, 2 Kolom Tablet, 2-3 Kolom Desktop) -->
                <div id="menu-grid" class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-4 sm:gap-5">
                    <?php if (empty($semua_menu)): ?>
                        <div class="col-span-full bg-white rounded-3xl p-12 text-center border border-slate-200">
                            <div class="w-16 h-16 bg-slate-100 text-slate-400 rounded-full flex items-center justify-center mx-auto mb-3 text-2xl">
                                <i class="fa-solid fa-plate-wheat"></i>
                            </div>
                            <h3 class="font-bold text-base text-slate-800">Belum Ada Menu</h3>
                            <p class="text-xs text-slate-500 mt-1">Daftar menu sedang diperbarui oleh pihak restoran.</p>
                        </div>
                    <?php else: ?>
                        <?php foreach ($semua_menu as $m): ?>
                            <?php 
                                $is_habis = ($m['status'] === 'habis');
                                // Penyesuaian helper gambar (uploads lokal vs URL)
                                $img_url = get_menu_image($m['gambar'], '..');
                            ?>
                            <div class="menu-item group bg-white rounded-3xl border border-slate-200/80 shadow-sm hover:shadow-lg hover:-translate-y-0.5 transition-all duration-200 flex flex-col overflow-hidden relative"
                                 data-id="<?= $m['id'] ?>"
                                 data-name="<?= htmlspecialchars(strtolower($m['nama_menu'])) ?>"
                                 data-category="<?= htmlspecialchars($m['kategori']) ?>"
                                 data-price="<?= $m['harga'] ?>">
                                
                                <!-- Foto Menu Makanan/Minuman -->
                                <div class="relative w-full aspect-[16/10] bg-slate-100 overflow-hidden">
                                    <img src="<?= htmlspecialchars($img_url) ?>" 
                                         alt="<?= htmlspecialchars($m['nama_menu']) ?>" 
                                         loading="lazy"
                                         onerror="this.onerror=null; this.src='https://images.unsplash.com/photo-1546069901-ba9599a7e63c?w=500&q=80';"
                                         class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300 <?= $is_habis ? 'grayscale opacity-60' : '' ?>">
                                    
                                    <!-- Kategori Badge -->
                                    <span class="absolute top-3 left-3 bg-black/60 backdrop-blur-sm text-white text-[10px] font-bold px-2.5 py-0.5 rounded-full capitalize">
                                        <?= htmlspecialchars($m['kategori']) ?>
                                    </span>

                                    <!-- Badge Stok Habis Jika Ada -->
                                    <?php if ($is_habis): ?>
                                        <div class="absolute inset-0 bg-[#1a0f0a]/70 backdrop-blur-sm flex items-center justify-center">
                                            <span class="bg-red-500 text-white text-xs font-black px-3.5 py-1 rounded-full uppercase tracking-wider shadow-md">
                                                Stok Habis
                                            </span>
                                        </div>
                                    <?php endif; ?>
                                </div>

                                <!-- Rincian Menu & Tombol Action -->
                                <div class="p-4 sm:p-5 flex-1 flex flex-col justify-between space-y-3">
                                    <div>
                                        <h3 class="font-extrabold text-sm sm:text-base text-slate-900 leading-snug group-hover:text-brand-600 transition-colors">
                                            <?= htmlspecialchars($m['nama_menu']) ?>
                                        </h3>
                                        <p class="text-xs text-slate-500 mt-1 line-clamp-2 leading-relaxed">
                                            <?= htmlspecialchars($m['deskripsi'] ?: 'Menu lezat pilihan resep istimewa KanDar.') ?>
                                        </p>
                                    </div>

                                    <div class="pt-2 border-t border-slate-100 flex items-center justify-between gap-2">
                                        <!-- Harga Menu -->
                                        <div>
                                            <span class="text-[10px] text-slate-400 block font-medium">Harga</span>
                                            <span class="font-extrabold text-brand-600 text-sm sm:text-base">
                                                <?= format_rupiah($m['harga']) ?>
                                            </span>
                                        </div>

                                        <!-- Tombol Tambah / Stepper Real-time -->
                                        <?php if ($is_habis): ?>
                                            <button disabled class="px-3.5 py-1.5 rounded-xl bg-slate-100 text-slate-400 text-xs font-bold cursor-not-allowed">
                                                Habis
                                            </button>
                                        <?php else: ?>
                                            <div id="btn-container-<?= $m['id'] ?>">
                                                <!-- Dikontrol oleh JavaScript untuk menampilkan Stepper [-] [qty] [+] jika sudah di cart -->
                                                <button onclick="addToCart(<?= $m['id'] ?>, '<?= addslashes(htmlspecialchars($m['nama_menu'])) ?>', <?= $m['harga'] ?>, '<?= addslashes(htmlspecialchars($img_url)) ?>')" 
                                                        class="px-3.5 py-1.5 rounded-xl bg-brand-500 hover:bg-brand-600 text-white text-xs font-bold flex items-center space-x-1.5 shadow-md shadow-brand-500/25 active:scale-95 transition">
                                                    <i class="fa-solid fa-plus text-[10px]"></i>
                                                    <span>Tambah</span>
                                                </button>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>

                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>

                <!-- Empty State jika Pencarian Tidak Ketemu -->
                <div id="search-empty-state" class="hidden bg-white rounded-3xl p-10 text-center border border-slate-200">
                    <div class="w-14 h-14 bg-brand-50 text-brand-500 rounded-full flex items-center justify-center mx-auto mb-3 text-xl">
                        <i class="fa-solid fa-magnifying-glass"></i>
                    </div>
                    <h3 class="font-bold text-base text-slate-900">Menu Tidak Ditemukan</h3>
                    <p class="text-xs text-slate-500 mt-1 max-w-sm mx-auto">Tidak ada menu yang cocok dengan kata kunci Anda. Silakan coba kata kunci lain.</p>
                    <button onclick="clearSearch()" class="mt-4 px-4 py-2 bg-brand-500 text-white rounded-xl text-xs font-bold hover:bg-brand-600 transition">
                        Reset Pencarian
                    </button>
                </div>

            </div>

            <!-- KOLOM KANAN (lg:col-span-4): Desktop Sticky Live Cart Panel (Hanya Tampil di Layar Desktop lg:) -->
            <div class="hidden lg:block lg:col-span-4 sticky top-20">
                <div class="bg-white rounded-3xl p-5 border border-slate-200/80 shadow-lg shadow-slate-200/50 flex flex-col max-h-[calc(100vh-6rem)]">
                    
                    <!-- Header Drawer -->
                    <div class="flex items-center justify-between pb-3.5 border-b border-slate-100">
                        <div class="flex items-center space-x-2">
                            <div class="w-8 h-8 rounded-xl bg-brand-100 text-brand-600 flex items-center justify-center font-bold text-sm">
                                <i class="fa-solid fa-bag-shopping"></i>
                            </div>
                            <div>
                                <h2 class="font-extrabold text-sm text-slate-900">Keranjang Pesanan</h2>
                                <span class="text-[11px] text-slate-400">Meja <span class="desktop-table-label"><?= htmlspecialchars($meja) ?></span></span>
                            </div>
                        </div>
                        <button onclick="clearCartConfirm()" class="text-xs text-red-500 hover:text-red-700 font-semibold" title="Kosongkan Keranjang">
                            <i class="fa-regular fa-trash-can mr-1"></i>Hapus
                        </button>
                    </div>

                    <!-- List Item Keranjang Desktop (Scrollable) -->
                    <div id="desktop-cart-list" class="flex-1 overflow-y-auto no-scrollbar py-3 space-y-3">
                        <!-- Rendered by JavaScript -->
                    </div>

                    <!-- State Keranjang Kosong di Desktop -->
                    <div id="desktop-cart-empty" class="py-12 text-center">
                        <div class="w-14 h-14 bg-slate-100 text-slate-300 rounded-full flex items-center justify-center mx-auto mb-2 text-xl">
                            <i class="fa-solid fa-basket-shopping"></i>
                        </div>
                        <p class="text-xs font-bold text-slate-600">Keranjang Masih Kosong</p>
                        <p class="text-[11px] text-slate-400 mt-0.5">Pilih menu favorit di samping untuk mulai memesan.</p>
                    </div>

                    <!-- Ringkasan Biaya & Tombol Checkout Desktop -->
                    <div id="desktop-cart-footer" class="hidden pt-3 border-t border-slate-100 space-y-3">
                        <div class="space-y-1.5 text-xs">
                            <div class="flex justify-between text-slate-500">
                                <span>Subtotal Pesanan</span>
                                <span id="desktop-subtotal-val" class="font-bold text-slate-800">Rp 0</span>
                            </div>
                            <div class="flex justify-between text-slate-500">
                                <span>Layanan Meja</span>
                                <span class="font-bold text-emerald-600">GRATIS</span>
                            </div>
                            <div class="flex justify-between text-sm font-black text-slate-900 pt-1 border-t border-slate-100">
                                <span>Total Tagihan</span>
                                <span id="desktop-total-val" class="text-brand-600">Rp 0</span>
                            </div>
                        </div>

                        <a href="cart.php" 
                           class="w-full py-3.5 bg-brand-500 hover:bg-brand-600 text-white font-extrabold rounded-2xl text-xs sm:text-sm shadow-md shadow-brand-500/25 flex items-center justify-center space-x-2 active:scale-95 transition">
                            <span>Review & Kirim Pesanan</span>
                            <i class="fa-solid fa-arrow-right text-xs"></i>
                        </a>
                    </div>

                </div>
            </div>

        </div>

    </main>

    <!-- FLOATING BOTTOM BAR (Cerah, Bersih & Modern) -->
    <div id="floating-cart" class="lg:hidden fixed bottom-4 left-4 right-4 z-40 max-w-lg mx-auto hidden">
        <a href="cart.php" 
           class="bg-white/95 backdrop-blur-md rounded-2xl p-3.5 flex items-center justify-between shadow-2xl border-2 border-orange-500 hover:border-orange-600 transition-all active:scale-95">
            <div class="flex items-center space-x-3">
                <div class="w-10 h-10 rounded-xl bg-gradient-to-tr from-orange-500 to-amber-500 text-white flex items-center justify-center font-black text-sm shadow-md shadow-orange-500/30">
                    <span id="floating-count">0</span>
                </div>
                <div>
                    <div class="text-[11px] text-slate-500 font-bold">Total Pesanan Meja <?= htmlspecialchars($meja) ?>:</div>
                    <div id="floating-total" class="font-black text-base text-orange-600">Rp 0</div>
                </div>
            </div>
            <div class="flex items-center space-x-1.5 text-xs font-black text-white bg-orange-500 hover:bg-orange-600 px-4 py-2.5 rounded-xl shadow-md shadow-orange-500/25 transition">
                <span>Lihat Pesanan</span>
                <i class="fa-solid fa-arrow-right text-[10px]"></i>
            </div>
        </a>
    </div>

    <!-- MODAL GANTI NOMOR MEJA -->
    <div id="table-modal" class="fixed inset-0 z-50 bg-black/40 backdrop-blur-sm hidden flex items-center justify-center p-4">
        <div class="bg-white rounded-3xl max-w-sm w-full p-6 shadow-2xl border border-slate-100 transform transition-all">
            <div class="flex justify-between items-center mb-4">
                <div class="flex items-center space-x-2">
                    <div class="w-8 h-8 rounded-xl bg-brand-100 text-brand-600 flex items-center justify-center font-bold text-sm">
                        <i class="fa-solid fa-chair"></i>
                    </div>
                    <h3 class="font-black text-base text-slate-900">Ubah Nomor Meja</h3>
                </div>
                <button onclick="closeTableModal()" class="text-slate-400 hover:text-slate-600 p-1">
                    <i class="fa-solid fa-xmark text-lg"></i>
                </button>
            </div>
            
            <p class="text-xs text-slate-500 mb-4 leading-relaxed">
                Nomor meja digunakan untuk pelayan mengantarkan pesanan dan pencatatan struk pesanan.
            </p>

            <form onsubmit="handleTableChange(event)" class="space-y-4">
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Nomor Meja Baru:</label>
                    <input type="text" 
                           id="modal-table-input" 
                           value="<?= htmlspecialchars($meja) ?>" 
                           placeholder="Contoh: 03" 
                           class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-base font-black text-slate-800 text-center focus:outline-none focus:border-brand-500 focus:bg-white focus:ring-2 focus:ring-brand-500/20 uppercase" 
                           required>
                </div>

                <div class="flex items-center space-x-2 pt-2">
                    <button type="button" onclick="closeTableModal()" class="flex-1 py-2.5 rounded-xl border border-slate-200 text-xs font-bold text-slate-600 hover:bg-slate-50 transition">
                        Batal
                    </button>
                    <button type="submit" class="flex-1 py-2.5 rounded-xl bg-brand-500 hover:bg-brand-600 text-white text-xs font-bold shadow-md shadow-brand-500/25 transition">
                        Simpan Meja
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- TOAST NOTIFIKASI FEEDBACK (Cerah) -->
    <div id="toast" class="fixed top-20 right-4 z-50 pointer-events-none transform -translate-y-4 opacity-0 transition-all duration-300">
        <div class="bg-white text-slate-800 px-4 py-3 rounded-2xl shadow-xl flex items-center space-x-2.5 border border-orange-200">
            <i class="fa-solid fa-circle-check text-emerald-500 text-sm"></i>
            <span id="toast-msg" class="text-xs font-bold">Menu ditambahkan ke keranjang!</span>
        </div>
    </div>

    <!-- JAVASCRIPT: Cart Management, Stepper Interaktif, Filter & Pencarian -->
    <script>
        const CURRENT_TABLE = "<?= htmlspecialchars($meja) ?>";
        let activeCategory = "<?= htmlspecialchars($kategori_aktif) ?>";

        localStorage.setItem('nomor_meja', CURRENT_TABLE);

        function getCart() {
            try {
                return JSON.parse(localStorage.getItem('cart_resto') || '[]');
            } catch (e) {
                return [];
            }
        }

        function saveCart(cart) {
            localStorage.setItem('cart_resto', JSON.stringify(cart));
            updateUI();
        }

        function addToCart(id, name, price, img) {
            let cart = getCart();
            let existing = cart.find(item => item.id === id);

            if (existing) {
                existing.jumlah += 1;
            } else {
                cart.push({
                    id: id,
                    nama: name,
                    harga: price,
                    gambar: img,
                    jumlah: 1,
                    catatan: ''
                });
            }
            saveCart(cart);
            showToast(`+1 ${name} dimasukkan ke keranjang`);
        }

        function updateItemQty(id, delta) {
            let cart = getCart();
            let index = cart.findIndex(item => item.id === id);

            if (index !== -1) {
                cart[index].jumlah += delta;
                if (cart[index].jumlah <= 0) {
                    cart.splice(index, 1);
                }
                saveCart(cart);
            }
        }

        function clearCartConfirm() {
            if (confirm('Yakin ingin mengosongkan seluruh pesanan di keranjang?')) {
                localStorage.removeItem('cart_resto');
                updateUI();
                showToast('Keranjang pesanan telah dikosongkan');
            }
        }

        function updateUI() {
            const cart = getCart();
            const totalCount = cart.reduce((acc, item) => acc + item.jumlah, 0);
            const totalPrice = cart.reduce((acc, item) => acc + (item.harga * item.jumlah), 0);

            // 1. Badge Header
            const badge = document.getElementById('badge-cart');
            if (totalCount > 0) {
                badge.classList.remove('hidden');
                badge.innerText = totalCount;
            } else {
                badge.classList.add('hidden');
            }

            // 2. Mobile Floating Bar
            const floating = document.getElementById('floating-cart');
            const floatingCount = document.getElementById('floating-count');
            const floatingTotal = document.getElementById('floating-total');

            if (totalCount > 0) {
                floating.classList.remove('hidden');
                floatingCount.innerText = totalCount;
                floatingTotal.innerText = 'Rp ' + totalPrice.toLocaleString('id-ID');
            } else {
                floating.classList.add('hidden');
            }

            // 3. Desktop Live Cart Drawer
            const desktopList = document.getElementById('desktop-cart-list');
            const desktopEmpty = document.getElementById('desktop-cart-empty');
            const desktopFooter = document.getElementById('desktop-cart-footer');
            const desktopSubtotal = document.getElementById('desktop-subtotal-val');
            const desktopTotal = document.getElementById('desktop-total-val');

            if (desktopList) {
                if (cart.length === 0) {
                    desktopList.innerHTML = '';
                    desktopEmpty.classList.remove('hidden');
                    desktopFooter.classList.add('hidden');
                } else {
                    desktopEmpty.classList.add('hidden');
                    desktopFooter.classList.remove('hidden');

                    let drawerHtml = '';
                    cart.forEach((item) => {
                        const itemSub = item.harga * item.jumlah;
                        drawerHtml += `
                            <div class="flex items-center justify-between p-2.5 rounded-2xl bg-slate-50 border border-slate-100 gap-2">
                                <div class="flex-1 min-w-0">
                                    <h4 class="font-bold text-xs text-slate-800 truncate">${item.nama}</h4>
                                    <div class="text-[11px] text-brand-600 font-semibold">Rp ${itemSub.toLocaleString('id-ID')}</div>
                                </div>
                                <div class="flex items-center space-x-1.5 bg-white px-2 py-1 rounded-xl border border-slate-200">
                                    <button onclick="updateItemQty(${item.id}, -1)" class="w-5 h-5 text-slate-600 hover:text-brand-600 font-bold text-xs flex items-center justify-center">
                                        <i class="fa-solid fa-minus text-[9px]"></i>
                                    </button>
                                    <span class="w-5 text-center text-xs font-black text-slate-800">${item.jumlah}</span>
                                    <button onclick="updateItemQty(${item.id}, 1)" class="w-5 h-5 text-slate-600 hover:text-brand-600 font-bold text-xs flex items-center justify-center">
                                        <i class="fa-solid fa-plus text-[9px]"></i>
                                    </button>
                                </div>
                            </div>
                        `;
                    });
                    desktopList.innerHTML = drawerHtml;
                    desktopSubtotal.innerText = 'Rp ' + totalPrice.toLocaleString('id-ID');
                    desktopTotal.innerText = 'Rp ' + totalPrice.toLocaleString('id-ID');
                }
            }

            // 4. Update Stepper Buttons on Menu Cards
            document.querySelectorAll('.menu-item').forEach(itemEl => {
                const id = parseInt(itemEl.dataset.id);
                const name = itemEl.querySelector('h3').innerText;
                const price = parseInt(itemEl.dataset.price);
                const img = itemEl.querySelector('img').src;
                const container = document.getElementById(`btn-container-${id}`);

                if (!container) return;

                const cartItem = cart.find(ci => ci.id === id);

                if (cartItem && cartItem.jumlah > 0) {
                    container.innerHTML = `
                        <div class="flex items-center space-x-1.5 bg-brand-50 border border-brand-200 rounded-xl p-1 shadow-sm">
                            <button onclick="updateItemQty(${id}, -1)" 
                                    class="w-7 h-7 rounded-lg bg-white text-brand-600 hover:bg-brand-500 hover:text-white transition flex items-center justify-center font-bold text-xs shadow-sm active:scale-95">
                                <i class="fa-solid fa-minus text-[10px]"></i>
                            </button>
                            <span class="w-6 text-center font-black text-xs text-brand-700">${cartItem.jumlah}</span>
                            <button onclick="updateItemQty(${id}, 1)" 
                                    class="w-7 h-7 rounded-lg bg-brand-500 text-white hover:bg-brand-600 transition flex items-center justify-center font-bold text-xs shadow-sm active:scale-95">
                                <i class="fa-solid fa-plus text-[10px]"></i>
                            </button>
                        </div>
                    `;
                } else {
                    container.innerHTML = `
                        <button onclick="addToCart(${id}, '${name.replace(/'/g, "\\'")}', ${price}, '${img.replace(/'/g, "\\'")}')" 
                                class="px-3.5 py-1.5 rounded-xl bg-brand-500 hover:bg-brand-600 text-white text-xs font-bold flex items-center space-x-1.5 shadow-md shadow-brand-500/25 active:scale-95 transition">
                            <i class="fa-solid fa-plus text-[10px]"></i>
                            <span>Tambah</span>
                        </button>
                    `;
                }
            });
        }

        function setCategoryFilter(kategori) {
            activeCategory = kategori;
            document.getElementById('label-active-category').innerText = kategori;

            document.querySelectorAll('.category-tab').forEach(tab => {
                tab.className = "category-tab px-4 py-2 rounded-xl text-xs sm:text-sm font-bold whitespace-nowrap transition-all flex items-center gap-1.5 bg-slate-100 text-slate-600 hover:bg-slate-200";
                const badge = tab.querySelector('span:last-child');
                if (badge) badge.className = "text-[10px] py-0.5 px-1.5 rounded-full bg-slate-200 text-slate-600";
            });

            const activeTab = document.getElementById(`tab-${kategori}`);
            if (activeTab) {
                activeTab.className = "category-tab px-4 py-2 rounded-xl text-xs sm:text-sm font-bold whitespace-nowrap transition-all flex items-center gap-1.5 bg-brand-500 text-white shadow-md shadow-brand-500/25 ring-2 ring-brand-500/20";
                const badge = activeTab.querySelector('span:last-child');
                if (badge) badge.className = "text-[10px] py-0.5 px-1.5 rounded-full bg-white/30 text-white";
            }

            filterMenuClient();
        }

        function filterMenuClient() {
            const query = document.getElementById('search-input').value.trim().toLowerCase();
            const clearBtn = document.getElementById('search-clear');
            if (query.length > 0) {
                clearBtn.classList.remove('hidden');
            } else {
                clearBtn.classList.add('hidden');
            }

            const items = document.querySelectorAll('.menu-item');
            let visibleCount = 0;

            items.forEach(item => {
                const name = item.dataset.name || '';
                const category = item.dataset.category || '';

                const matchesQuery = !query || name.includes(query);
                const matchesCategory = (activeCategory === 'semua' || category === activeCategory);

                if (matchesQuery && matchesCategory) {
                    item.classList.remove('hidden');
                    visibleCount++;
                } else {
                    item.classList.add('hidden');
                }
            });

            document.getElementById('results-count').innerText = `${visibleCount} menu tersedia`;

            const emptyState = document.getElementById('search-empty-state');
            if (visibleCount === 0 && items.length > 0) {
                emptyState.classList.remove('hidden');
            } else {
                emptyState.classList.add('hidden');
            }
        }

        function clearSearch() {
            document.getElementById('search-input').value = '';
            setCategoryFilter('semua');
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
            }, 2200);
        }

        function openTableModal() {
            document.getElementById('table-modal').classList.remove('hidden');
            document.getElementById('modal-table-input').focus();
        }

        function closeTableModal() {
            document.getElementById('table-modal').classList.add('hidden');
        }

        function handleTableChange(e) {
            e.preventDefault();
            const newTable = document.getElementById('modal-table-input').value.trim();
            if (newTable) {
                localStorage.setItem('nomor_meja', newTable);
                window.location.href = `index.php?meja=${encodeURIComponent(newTable)}`;
            }
        }

        document.addEventListener('DOMContentLoaded', () => {
            updateUI();
            filterMenuClient();
        });
    </script>

</body>
</html>
