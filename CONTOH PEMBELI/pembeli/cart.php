<?php
// pembeli/cart.php
// [BAGIAN 2: PROGRAMMER 2] Halaman Keranjang, Review Pesanan & Konfirmasi Checkout
// Didesain responsif di semua perangkat (Mobile, Tablet, Laptop, Desktop)

require_once __DIR__ . '/../config/koneksi.php';
require_once __DIR__ . '/../config/helper.php';

// Ambil nomor meja dari session atau fallback
if (isset($_GET['meja']) && !empty($_GET['meja'])) {
    $meja_input = sanitize($_GET['meja']);
    $_SESSION['meja'] = $meja_input;
    $_SESSION['nomor_meja'] = $meja_input;
}

$meja = $_SESSION['meja'] ?? $_SESSION['nomor_meja'] ?? '01';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Keranjang Pesanan - Meja #<?= htmlspecialchars($meja) ?> | KanDar</title>
    
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

    <!-- TOP NAVBAR -->
    <header class="sticky top-0 z-40 bg-white/95 backdrop-blur-md border-b border-slate-200/80 shadow-xs">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-16">
                
                <!-- Back Button & Page Title -->
                <div class="flex items-center space-x-3">
                    <a href="index.php?meja=<?= urlencode($meja) ?>" 
                       class="w-10 h-10 rounded-xl bg-slate-100 hover:bg-brand-50 text-slate-700 hover:text-brand-600 transition flex items-center justify-center active:scale-95" 
                       title="Kembali ke Menu">
                        <i class="fa-solid fa-arrow-left text-sm"></i>
                    </a>
                    <div>
                        <h1 class="font-extrabold text-slate-900 text-base sm:text-lg tracking-tight leading-tight">Review Keranjang</h1>
                        <span class="text-[11px] text-slate-400 font-medium">Periksa kembali pesanan Anda</span>
                    </div>
                </div>

                <!-- Table Badge Indicator -->
                <div class="flex items-center space-x-2">
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-brand-50 text-brand-700 text-xs font-bold border border-brand-200 shadow-xs">
                        <i class="fa-solid fa-chair text-[11px] text-brand-600"></i>
                        <span>Meja #<?= htmlspecialchars($meja) ?></span>
                    </span>
                </div>

            </div>
        </div>
    </header>

    <!-- MAIN CONTAINER -->
    <main class="flex-1 max-w-7xl w-full mx-auto px-4 sm:px-6 lg:px-8 py-6">
        
        <!-- RESPONSIVE 2-COLUMN LAYOUT (lg:grid-cols-12) -->
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
            
            <!-- LEFT COLUMN: Identitas Pemesan & List Item (lg:col-span-7 or 8) -->
            <div class="lg:col-span-7 xl:col-span-8 space-y-5">
                
                <!-- Card 1: Nama Pemesan -->
                <div class="bg-white rounded-3xl p-5 sm:p-6 border border-slate-200/80 shadow-xs">
                    <div class="flex items-center space-x-2.5 mb-3">
                        <div class="w-8 h-8 rounded-xl bg-brand-100 text-brand-600 flex items-center justify-center font-bold text-sm">
                            <i class="fa-solid fa-user"></i>
                        </div>
                        <div>
                            <h2 class="font-extrabold text-sm sm:text-base text-slate-900 leading-tight">Identitas Pemesan</h2>
                            <span class="text-[11px] text-slate-400">Agar pelayan dapat memanggil atau mengantar pesanan Anda dengan tepat</span>
                        </div>
                    </div>

                    <div class="relative mt-2">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                            <i class="fa-regular fa-id-badge text-base"></i>
                        </div>
                        <input type="text" 
                               id="nama_pelanggan" 
                               placeholder="Masukkan Nama Anda (contoh: Mas Budi / Mbak Siti)" 
                               class="w-full pl-11 pr-4 py-3 bg-slate-50 border border-slate-200 rounded-2xl text-sm font-semibold text-slate-800 placeholder-slate-400 focus:outline-none focus:border-brand-500 focus:bg-white focus:ring-2 focus:ring-brand-500/20 transition"
                               required>
                    </div>
                </div>

                <!-- Card 2: Daftar Item Pesanan -->
                <div class="bg-white rounded-3xl p-5 sm:p-6 border border-slate-200/80 shadow-xs">
                    
                    <div class="flex items-center justify-between pb-4 border-b border-slate-100">
                        <div class="flex items-center space-x-2.5">
                            <div class="w-8 h-8 rounded-xl bg-brand-100 text-brand-600 flex items-center justify-center font-bold text-sm">
                                <i class="fa-solid fa-utensils"></i>
                            </div>
                            <h2 class="font-extrabold text-sm sm:text-base text-slate-900">
                                Menu yang Dipesan (<span id="cart-item-count">0</span>)
                            </h2>
                        </div>
                        <button onclick="clearCart()" 
                                id="btn-clear-cart" 
                                class="text-xs font-bold text-red-500 hover:text-red-700 hover:bg-red-50 px-2.5 py-1.5 rounded-xl transition flex items-center space-x-1">
                            <i class="fa-regular fa-trash-can text-xs"></i>
                            <span>Kosongkan</span>
                        </button>
                    </div>

                    <!-- List Container (Rendered by JS) -->
                    <div id="cart-list" class="divide-y divide-slate-100 space-y-1">
                        <!-- Populated by JavaScript -->
                    </div>

                    <!-- Keranjang Kosong State -->
                    <div id="cart-empty" class="hidden py-14 text-center">
                        <div class="w-20 h-20 bg-brand-50 text-brand-400 rounded-full flex items-center justify-center mx-auto mb-4 text-3xl">
                            <i class="fa-solid fa-bag-shopping"></i>
                        </div>
                        <h3 class="font-extrabold text-base text-slate-800">Keranjang Masih Kosong</h3>
                        <p class="text-xs sm:text-sm text-slate-400 mt-1 max-w-sm mx-auto">
                            Anda belum memilih makanan atau minuman apapun. Yuk lihat daftar menu lezat kami sekarang!
                        </p>
                        <div class="mt-5">
                            <a href="index.php?meja=<?= urlencode($meja) ?>" 
                               class="inline-flex items-center space-x-2 px-5 py-2.5 bg-brand-500 hover:bg-brand-600 text-white font-bold rounded-2xl text-xs sm:text-sm shadow-md shadow-brand-500/25 transition active:scale-95">
                                <i class="fa-solid fa-plus text-xs"></i>
                                <span>Pilih Menu Sekarang</span>
                            </a>
                        </div>
                    </div>

                    <!-- Tombol Tambah Menu Lain -->
                    <div id="add-more-box" class="pt-4 border-t border-slate-100 flex justify-center">
                        <a href="index.php?meja=<?= urlencode($meja) ?>" 
                           class="inline-flex items-center space-x-2 text-xs font-bold text-brand-600 hover:text-brand-700 bg-brand-50 hover:bg-brand-100 px-4 py-2 rounded-xl transition">
                            <i class="fa-solid fa-plus text-[10px]"></i>
                            <span>+ Tambah Menu Lainnya</span>
                        </a>
                    </div>

                </div>

            </div>

            <!-- RIGHT COLUMN: Metode Pembayaran & Ringkasan Checkout (lg:col-span-5 or 4) -->
            <div id="checkout-sidebar" class="lg:col-span-5 xl:col-span-4 space-y-5 lg:sticky lg:top-24">
                
                <!-- Card 3: Pilihan Metode Pembayaran -->
                <div class="bg-white rounded-3xl p-5 sm:p-6 border border-slate-200/80 shadow-xs space-y-4">
                    <div class="flex items-center space-x-2.5">
                        <div class="w-8 h-8 rounded-xl bg-brand-100 text-brand-600 flex items-center justify-center font-bold text-sm">
                            <i class="fa-solid fa-wallet"></i>
                        </div>
                        <div>
                            <h2 class="font-extrabold text-sm sm:text-base text-slate-900 leading-tight">Metode Pembayaran</h2>
                            <span class="text-[11px] text-slate-400">Pilih kemudahan pembayaran Anda</span>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-3 text-xs">
                        
                        <!-- Option 1: QRIS -->
                        <label id="label-qris" 
                               class="relative flex flex-col p-4 border-2 rounded-2xl cursor-pointer transition-all border-brand-500 bg-brand-50/60 shadow-xs">
                            <input type="radio" name="metode_pembayaran" value="QRIS" class="sr-only" checked onchange="selectPayment('QRIS')">
                            <div class="flex items-center justify-between mb-1.5">
                                <span class="font-black text-slate-900 text-sm flex items-center gap-1.5">
                                    <i class="fa-solid fa-qrcode text-brand-600"></i> QRIS
                                </span>
                                <span class="w-4 h-4 rounded-full border-2 border-brand-600 flex items-center justify-center">
                                    <span class="w-2 h-2 rounded-full bg-brand-600"></span>
                                </span>
                            </div>
                            <span class="text-[10px] text-slate-500 leading-tight">BCA, Gopay, OVO, Dana, ShopeePay</span>
                        </label>

                        <!-- Option 2: Tunai / Kasir -->
                        <label id="label-cash" 
                               class="relative flex flex-col p-4 border-2 rounded-2xl cursor-pointer transition-all border-slate-200 bg-white hover:border-slate-300">
                            <input type="radio" name="metode_pembayaran" value="Tunai / Kasir" class="sr-only" onchange="selectPayment('Tunai / Kasir')">
                            <div class="flex items-center justify-between mb-1.5">
                                <span class="font-black text-slate-900 text-sm flex items-center gap-1.5">
                                    <i class="fa-solid fa-money-bill-wave text-slate-600"></i> Tunai
                                </span>
                                <span class="w-4 h-4 rounded-full border-2 border-slate-300 flex items-center justify-center"></span>
                            </div>
                            <span class="text-[10px] text-slate-500 leading-tight">Bayar langsung di meja kasir</span>
                        </label>

                    </div>

                    <div class="p-3 bg-amber-50/80 border border-amber-200/80 rounded-2xl text-[11px] text-amber-900 flex items-start space-x-2.5">
                        <i class="fa-solid fa-shield-halved text-amber-600 text-sm mt-0.5"></i>
                        <p class="leading-relaxed">
                            <b>Sistem Anti-Fake Order:</b> Dapur akan langsung memproses dan memasak pesanan Anda setelah pembayaran tervalidasi.
                        </p>
                    </div>
                </div>

                <!-- Card 4: Ringkasan Biaya -->
                <div class="bg-white rounded-3xl p-5 sm:p-6 border border-slate-200/80 shadow-xs space-y-4">
                    <h2 class="font-extrabold text-sm sm:text-base text-slate-900">Ringkasan Pembayaran</h2>
                    
                    <div class="space-y-2.5 text-xs sm:text-sm">
                        <div class="flex justify-between text-slate-600">
                            <span>Subtotal Menu</span>
                            <span id="subtotal-val" class="font-bold text-slate-800">Rp 0</span>
                        </div>
                        <div class="flex justify-between text-slate-600">
                            <span>Biaya Layanan & Meja</span>
                            <span class="font-bold text-emerald-600">GRATIS</span>
                        </div>
                        <div class="flex justify-between text-slate-600">
                            <span>Pajak Restoran</span>
                            <span class="font-bold text-slate-500">Termasuk</span>
                        </div>
                        <div class="pt-3 border-t border-slate-100 flex justify-between items-center text-sm sm:text-base font-black text-slate-900">
                            <span>Total Pembayaran</span>
                            <span id="total-val" class="text-brand-600 text-lg sm:text-xl font-black">Rp 0</span>
                        </div>
                    </div>

                    <!-- Submit Button for Desktop (Visible on lg: screen) -->
                    <div class="hidden lg:block pt-3">
                        <button id="btn-submit-order-desktop" 
                                onclick="submitOrder()" 
                                class="w-full py-4 bg-brand-500 hover:bg-brand-600 text-white font-extrabold rounded-2xl text-sm shadow-lg shadow-brand-500/30 flex items-center justify-center space-x-2 active:scale-95 transition">
                            <span>Kirim Pesanan Sekarang</span>
                            <i class="fa-solid fa-paper-plane text-xs"></i>
                        </button>
                    </div>

                </div>

            </div>

        </div>

    </main>

    <!-- MOBILE STICKY CHECKOUT BAR (Visible on Mobile & Tablet, Hidden on Desktop lg:) -->
    <div id="checkout-bar" class="lg:hidden fixed bottom-0 left-0 right-0 bg-white/95 backdrop-blur-md border-t border-slate-200/90 p-4 shadow-2xl z-40">
        <div class="max-w-2xl mx-auto flex items-center space-x-3">
            <div class="flex-1 min-w-0">
                <span class="text-[10px] uppercase font-bold tracking-wider text-slate-400 block">Total Tagihan:</span>
                <span id="checkout-total-val" class="text-base sm:text-lg font-black text-brand-600 truncate block">Rp 0</span>
            </div>
            <button id="btn-submit-order" 
                    onclick="submitOrder()" 
                    class="py-3 px-5 bg-brand-500 hover:bg-brand-600 text-white font-extrabold rounded-2xl text-xs sm:text-sm shadow-lg shadow-brand-500/25 flex items-center justify-center space-x-2 active:scale-95 transition flex-shrink-0">
                <span>Kirim Pesanan</span>
                <i class="fa-solid fa-paper-plane text-xs"></i>
            </button>
        </div>
    </div>

    <!-- JAVASCRIPT: Cart Handling, Dynamic Calculations & API Integration -->
    <script>
        const NOMOR_MEJA = "<?= htmlspecialchars($meja) ?>";

        function getCart() {
            try {
                return JSON.parse(localStorage.getItem('cart_resto') || '[]');
            } catch (e) {
                return [];
            }
        }

        function saveCart(cart) {
            localStorage.setItem('cart_resto', JSON.stringify(cart));
            renderCart();
        }

        function clearCart() {
            if (confirm('Yakin ingin mengosongkan seluruh pesanan di keranjang?')) {
                localStorage.removeItem('cart_resto');
                renderCart();
            }
        }

        function changeQty(index, delta) {
            let cart = getCart();
            if (cart[index]) {
                cart[index].jumlah += delta;
                if (cart[index].jumlah <= 0) {
                    cart.splice(index, 1);
                }
                saveCart(cart);
            }
        }

        function removeItem(index) {
            let cart = getCart();
            if (cart[index]) {
                cart.splice(index, 1);
                saveCart(cart);
            }
        }

        function updateNote(index, note) {
            let cart = getCart();
            if (cart[index]) {
                cart[index].catatan = note;
                localStorage.setItem('cart_resto', JSON.stringify(cart));
            }
        }

        function renderCart() {
            const cart = getCart();
            const listEl = document.getElementById('cart-list');
            const emptyEl = document.getElementById('cart-empty');
            const addMoreBox = document.getElementById('add-more-box');
            const clearBtn = document.getElementById('btn-clear-cart');
            const checkoutSidebar = document.getElementById('checkout-sidebar');
            const checkoutBar = document.getElementById('checkout-bar');
            const itemCountEl = document.getElementById('cart-item-count');

            const totalItemCount = cart.reduce((acc, item) => acc + item.jumlah, 0);
            itemCountEl.innerText = totalItemCount;

            if (cart.length === 0) {
                listEl.innerHTML = '';
                emptyEl.classList.remove('hidden');
                addMoreBox.classList.add('hidden');
                clearBtn.classList.add('hidden');
                checkoutSidebar.classList.add('hidden');
                checkoutBar.classList.add('hidden');
                return;
            }

            emptyEl.classList.add('hidden');
            addMoreBox.classList.remove('hidden');
            clearBtn.classList.remove('hidden');
            checkoutSidebar.classList.remove('hidden');
            checkoutBar.classList.remove('hidden');

            let total = 0;
            let html = '';

            cart.forEach((item, idx) => {
                const subtotal = item.harga * item.jumlah;
                total += subtotal;
                const img = item.gambar || 'https://images.unsplash.com/photo-1546069901-ba9599a7e63c?w=300&q=80';

                html += `
                    <div class="py-4 first:pt-2 last:pb-2">
                        <div class="flex items-start gap-3 sm:gap-4">
                            <!-- Image Thumbnail -->
                            <div class="w-16 h-16 sm:w-20 sm:h-20 rounded-2xl bg-slate-100 overflow-hidden flex-shrink-0 border border-slate-200">
                                <img src="${img}" alt="${item.nama}" class="w-full h-full object-cover">
                            </div>

                            <!-- Item Info & Stepper -->
                            <div class="flex-1 min-w-0">
                                <div class="flex justify-between items-start gap-2">
                                    <div>
                                        <h4 class="font-extrabold text-sm sm:text-base text-slate-800 leading-tight">${item.nama}</h4>
                                        <div class="text-xs text-brand-600 font-bold mt-0.5">Rp ${item.harga.toLocaleString('id-ID')} / porsi</div>
                                    </div>
                                    <button onclick="removeItem(${idx})" class="text-slate-400 hover:text-red-500 p-1 transition" title="Hapus menu">
                                        <i class="fa-solid fa-xmark text-sm"></i>
                                    </button>
                                </div>

                                <div class="flex items-center justify-between mt-3 gap-2">
                                    <!-- Stepper Quantity -->
                                    <div class="flex items-center space-x-1.5 bg-slate-100 border border-slate-200 rounded-xl p-1">
                                        <button onclick="changeQty(${idx}, -1)" 
                                                class="w-7 h-7 rounded-lg bg-white text-slate-700 hover:bg-brand-500 hover:text-white flex items-center justify-center font-bold text-xs shadow-xs transition active:scale-95">
                                            <i class="fa-solid fa-minus text-[10px]"></i>
                                        </button>
                                        <span class="w-7 text-center font-black text-xs text-slate-800">${item.jumlah}</span>
                                        <button onclick="changeQty(${idx}, 1)" 
                                                class="w-7 h-7 rounded-lg bg-white text-slate-700 hover:bg-brand-500 hover:text-white flex items-center justify-center font-bold text-xs shadow-xs transition active:scale-95">
                                            <i class="fa-solid fa-plus text-[10px]"></i>
                                        </button>
                                    </div>

                                    <!-- Subtotal -->
                                    <div class="text-right">
                                        <span class="text-[10px] text-slate-400 block">Subtotal</span>
                                        <span class="font-extrabold text-slate-900 text-sm sm:text-base">Rp ${subtotal.toLocaleString('id-ID')}</span>
                                    </div>
                                </div>

                                <!-- Note Input -->
                                <div class="mt-2.5">
                                    <div class="relative">
                                        <div class="absolute inset-y-0 left-0 pl-2.5 flex items-center pointer-events-none text-slate-400">
                                            <i class="fa-regular fa-comment-dots text-[11px]"></i>
                                        </div>
                                        <input type="text" 
                                               placeholder="Catatan khusus (misal: Jangan pedas / es batu sedikit)" 
                                               value="${item.catatan || ''}" 
                                               oninput="updateNote(${idx}, this.value)"
                                               class="w-full text-xs pl-7 pr-3 py-2 bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:border-brand-500 focus:bg-white transition">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                `;
            });

            listEl.innerHTML = html;
            const formattedTotal = 'Rp ' + total.toLocaleString('id-ID');
            document.getElementById('subtotal-val').innerText = formattedTotal;
            document.getElementById('total-val').innerText = formattedTotal;
            document.getElementById('checkout-total-val').innerText = formattedTotal;
        }

        function selectPayment(metode) {
            const qrisLabel = document.getElementById('label-qris');
            const cashLabel = document.getElementById('label-cash');

            if (metode === 'QRIS') {
                qrisLabel.className = 'relative flex flex-col p-4 border-2 rounded-2xl cursor-pointer transition-all border-brand-500 bg-brand-50/60 shadow-xs';
                cashLabel.className = 'relative flex flex-col p-4 border-2 rounded-2xl cursor-pointer transition-all border-slate-200 bg-white hover:border-slate-300';
                qrisLabel.querySelector('span.w-4').innerHTML = '<span class="w-2 h-2 rounded-full bg-brand-600"></span>';
                qrisLabel.querySelector('span.w-4').className = 'w-4 h-4 rounded-full border-2 border-brand-600 flex items-center justify-center';
                cashLabel.querySelector('span.w-4').innerHTML = '';
                cashLabel.querySelector('span.w-4').className = 'w-4 h-4 rounded-full border-2 border-slate-300 flex items-center justify-center';
            } else {
                cashLabel.className = 'relative flex flex-col p-4 border-2 rounded-2xl cursor-pointer transition-all border-brand-500 bg-brand-50/60 shadow-xs';
                qrisLabel.className = 'relative flex flex-col p-4 border-2 rounded-2xl cursor-pointer transition-all border-slate-200 bg-white hover:border-slate-300';
                cashLabel.querySelector('span.w-4').innerHTML = '<span class="w-2 h-2 rounded-full bg-brand-600"></span>';
                cashLabel.querySelector('span.w-4').className = 'w-4 h-4 rounded-full border-2 border-brand-600 flex items-center justify-center';
                qrisLabel.querySelector('span.w-4').innerHTML = '';
                qrisLabel.querySelector('span.w-4').className = 'w-4 h-4 rounded-full border-2 border-slate-300 flex items-center justify-center';
            }
        }

        async function submitOrder() {
            const cart = getCart();
            const namaInput = document.getElementById('nama_pelanggan');
            const nama = namaInput.value.trim();
            const btnSubmitMobile = document.getElementById('btn-submit-order');
            const btnSubmitDesktop = document.getElementById('btn-submit-order-desktop');
            const metodeChecked = document.querySelector('input[name="metode_pembayaran"]:checked');
            const metode = metodeChecked ? metodeChecked.value : 'QRIS';

            if (!nama) {
                alert('Silakan masukkan Nama Pemesan terlebih dahulu!');
                namaInput.focus();
                namaInput.scrollIntoView({ behavior: 'smooth', block: 'center' });
                return;
            }

            if (cart.length === 0) {
                alert('Keranjang belanja Anda masih kosong!');
                return;
            }

            // Set loading state
            const loadingHtml = `<i class="fa-solid fa-circle-notch fa-spin text-sm"></i><span>Mengirim Pesanan...</span>`;
            if (btnSubmitMobile) {
                btnSubmitMobile.disabled = true;
                btnSubmitMobile.innerHTML = loadingHtml;
            }
            if (btnSubmitDesktop) {
                btnSubmitDesktop.disabled = true;
                btnSubmitDesktop.innerHTML = loadingHtml;
            }

            try {
                const response = await fetch('../api/submit_order.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        nomor_meja: NOMOR_MEJA,
                        nama_pelanggan: nama,
                        metode_pembayaran: metode,
                        items: cart
                    })
                });

                const result = await response.json();

                if (result.status === 'success') {
                    // Simpan nama pelanggan di local storage untuk auto-fill pesanan selanjutnya
                    localStorage.setItem('nama_pelanggan', nama);
                    // Kosongkan keranjang setelah berhasil
                    localStorage.removeItem('cart_resto');
                    // Arahkan ke halaman status pesanan & validasi pembayaran
                    window.location.href = `status.php?order_id=${result.order_id}`;
                } else {
                    alert('Gagal Mengirim: ' + result.message);
                    resetButtons();
                }
            } catch (err) {
                alert('Terjadi kesalahan jaringan! Pastikan koneksi atau server Apache & MySQL aktif.');
                resetButtons();
            }
        }

        function resetButtons() {
            const defaultMobile = `<span>Kirim Pesanan</span><i class="fa-solid fa-paper-plane text-xs"></i>`;
            const defaultDesktop = `<span>Kirim Pesanan Sekarang</span><i class="fa-solid fa-paper-plane text-xs"></i>`;
            const btnSubmitMobile = document.getElementById('btn-submit-order');
            const btnSubmitDesktop = document.getElementById('btn-submit-order-desktop');

            if (btnSubmitMobile) {
                btnSubmitMobile.disabled = false;
                btnSubmitMobile.innerHTML = defaultMobile;
            }
            if (btnSubmitDesktop) {
                btnSubmitDesktop.disabled = false;
                btnSubmitDesktop.innerHTML = defaultDesktop;
            }
        }

        document.addEventListener('DOMContentLoaded', () => {
            // Isi nama pelanggan jika tersimpan sebelumnya
            const savedName = localStorage.getItem('nama_pelanggan');
            if (savedName) {
                document.getElementById('nama_pelanggan').value = savedName;
            }
            renderCart();
        });
    </script>
</body>
</html>
