<?php
// pembeli/cart.php
// [BAGIAN 2: PROGRAMMER 2] Halaman Keranjang & Konfirmasi Checkout

require_once __DIR__ . '/../config/koneksi.php';
require_once __DIR__ . '/../config/helper.php';

$meja = isset($_SESSION['nomor_meja']) ? $_SESSION['nomor_meja'] : '01';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Keranjang Pesanan - Meja <?= htmlspecialchars($meja) ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-slate-50 min-h-screen pb-32 text-slate-800">

    <!-- Top Bar -->
    <header class="sticky top-0 z-30 bg-white border-b border-slate-200 px-4 py-3 shadow-sm">
        <div class="max-w-md mx-auto flex items-center justify-between">
            <a href="index.php" class="p-2 text-slate-600 hover:text-slate-900 transition">
                <i class="fa-solid fa-arrow-left text-lg"></i>
            </a>
            <h1 class="font-bold text-base text-slate-900">Review Pesanan</h1>
            <span class="text-xs font-semibold text-orange-600 bg-orange-50 px-2.5 py-1 rounded-full border border-orange-200">
                Meja #<?= htmlspecialchars($meja) ?>
            </span>
        </div>
    </header>

    <main class="max-w-md mx-auto px-4 mt-4">
        <!-- Form Nama Pemesan -->
        <div class="bg-white rounded-2xl p-4 border border-slate-200 shadow-sm mb-4">
            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Nama Pemesan (Wajib Diisi):</label>
            <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                    <i class="fa-solid fa-user text-sm"></i>
                </div>
                <input type="text" id="nama_pelanggan" placeholder="Contoh: Kak Budi / Meja 3" 
                       class="w-full pl-9 pr-3 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:border-orange-500 focus:bg-white transition" required>
            </div>
        </div>

        <!-- List Keranjang -->
        <div class="bg-white rounded-2xl p-4 border border-slate-200 shadow-sm mb-4">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3 mb-3">
                <h2 class="font-bold text-sm text-slate-900">Daftar Item</h2>
                <button onclick="clearCart()" class="text-xs text-red-500 hover:text-red-700 font-semibold">
                    <i class="fa-regular fa-trash-can mr-1"></i>Kosongkan
                </button>
            </div>

            <div id="cart-list" class="space-y-4">
                <!-- Rendered by JavaScript -->
            </div>

            <!-- Keranjang Kosong State -->
            <div id="cart-empty" class="hidden text-center py-8">
                <div class="w-16 h-16 bg-slate-100 text-slate-400 rounded-full flex items-center justify-center mx-auto mb-3 text-2xl">
                    <i class="fa-solid fa-bag-shopping"></i>
                </div>
                <p class="text-sm font-semibold text-slate-600">Keranjang Masih Kosong</p>
                <p class="text-xs text-slate-400 mt-1 mb-4">Yuk pilih makanan dan minuman favoritmu dulu!</p>
                <a href="index.php" class="inline-flex items-center space-x-2 px-4 py-2 bg-orange-500 text-white rounded-xl text-xs font-bold shadow-md hover:bg-orange-600 transition">
                    <i class="fa-solid fa-plus text-[10px]"></i>
                    <span>Pilih Menu Sekarang</span>
                </a>
            </div>
        </div>

        <!-- Ringkasan Biaya -->
        <div id="cost-summary" class="bg-white rounded-2xl p-4 border border-slate-200 shadow-sm">
            <h3 class="font-bold text-xs uppercase text-slate-400 tracking-wider mb-3">Ringkasan Pembayaran</h3>
            <div class="space-y-2 text-xs">
                <div class="flex justify-between text-slate-600">
                    <span>Subtotal Menu</span>
                    <span id="subtotal-val" class="font-semibold text-slate-900">Rp 0</span>
                </div>
                <div class="flex justify-between text-slate-600">
                    <span>Biaya Layanan & Meja</span>
                    <span class="font-semibold text-emerald-600">GRATIS</span>
                </div>
                <div class="border-t border-slate-100 pt-2 flex justify-between text-sm font-bold text-slate-900">
                    <span>Total Pembayaran</span>
                    <span id="total-val" class="text-orange-600">Rp 0</span>
                </div>
            </div>
        </div>

        <!-- Pilihan Metode Pembayaran -->
        <div id="payment-method-box" class="bg-white rounded-2xl p-4 border border-slate-200 shadow-sm mt-4">
            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">
                <i class="fa-solid fa-wallet text-orange-500 mr-1"></i> Metode Pembayaran:
            </label>
            <div class="grid grid-cols-2 gap-2 text-xs">
                <label class="relative flex flex-col p-3 border-2 rounded-xl cursor-pointer transition border-orange-500 bg-orange-50/50" id="label-qris">
                    <input type="radio" name="metode_pembayaran" value="QRIS" class="sr-only" checked onchange="selectPayment('QRIS')">
                    <span class="font-bold text-slate-900 flex items-center gap-1.5">
                        <i class="fa-solid fa-qrcode text-orange-600"></i> QRIS
                    </span>
                    <span class="text-[10px] text-slate-500 mt-0.5">Scan instan lewat HP</span>
                </label>
                <label class="relative flex flex-col p-3 border-2 rounded-xl cursor-pointer transition border-slate-200 bg-white hover:border-slate-300" id="label-cash">
                    <input type="radio" name="metode_pembayaran" value="Tunai / Kasir" class="sr-only" onchange="selectPayment('Tunai / Kasir')">
                    <span class="font-bold text-slate-900 flex items-center gap-1.5">
                        <i class="fa-solid fa-money-bill-wave text-slate-600"></i> Tunai (Cash)
                    </span>
                    <span class="text-[10px] text-slate-500 mt-0.5">Bayar di meja kasir</span>
                </label>
            </div>
            <div class="mt-2.5 p-2 rounded-lg bg-orange-50/60 border border-orange-100 text-[10px] text-slate-600 flex items-center gap-1.5">
                <i class="fa-solid fa-shield-halved text-orange-500"></i>
                <span>Anti-Fake Order: QR validasi akan tampil di layar status Anda.</span>
            </div>
        </div>
    </main>

    <!-- Bottom Checkout Action Bar -->
    <div id="checkout-bar" class="fixed bottom-0 left-0 right-0 bg-white border-t border-slate-200 p-4 shadow-lg z-40">
        <div class="max-w-md mx-auto flex items-center space-x-3">
            <div class="flex-1">
                <span class="text-[11px] text-slate-400 block">Total Tagihan:</span>
                <span id="checkout-total-val" class="text-lg font-black text-orange-600">Rp 0</span>
            </div>
            <button id="btn-submit-order" onclick="submitOrder()" 
                    class="flex-1 py-3 px-4 bg-orange-500 hover:bg-orange-600 text-white font-bold rounded-xl text-sm shadow-lg shadow-orange-500/30 flex items-center justify-center space-x-2 active:scale-95 transition">
                <span>Kirim Pesanan</span>
                <i class="fa-solid fa-paper-plane text-xs"></i>
            </button>
        </div>
    </div>

    <script>
        const NOMOR_MEJA = "<?= htmlspecialchars($meja) ?>";

        function getCart() {
            return JSON.parse(localStorage.getItem('cart_resto') || '[]');
        }

        function saveCart(cart) {
            localStorage.setItem('cart_resto', JSON.stringify(cart));
            renderCart();
        }

        function clearCart() {
            if (confirm('Yakin ingin mengosongkan keranjang?')) {
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
            const summaryEl = document.getElementById('cost-summary');
            const checkoutBar = document.getElementById('checkout-bar');

            if (cart.length === 0) {
                listEl.innerHTML = '';
                emptyEl.classList.remove('hidden');
                summaryEl.classList.add('hidden');
                checkoutBar.classList.add('hidden');
                return;
            }

            emptyEl.classList.add('hidden');
            summaryEl.classList.remove('hidden');
            checkoutBar.classList.remove('hidden');

            let total = 0;
            let html = '';

            cart.forEach((item, idx) => {
                const subtotal = item.harga * item.jumlah;
                total += subtotal;

                html += `
                    <div class="border-b border-slate-100 pb-3 last:border-b-0 last:pb-0">
                        <div class="flex justify-between items-start">
                            <div>
                                <h4 class="font-bold text-sm text-slate-800">${item.nama}</h4>
                                <span class="text-xs text-orange-600 font-semibold">Rp ${item.harga.toLocaleString('id-ID')}</span>
                            </div>
                            <div class="flex items-center space-x-2 bg-slate-100 rounded-lg p-1">
                                <button onclick="changeQty(${idx}, -1)" class="w-6 h-6 rounded bg-white text-slate-700 flex items-center justify-center font-bold text-xs shadow-sm hover:bg-orange-500 hover:text-white transition">
                                    <i class="fa-solid fa-minus text-[9px]"></i>
                                </button>
                                <span class="w-6 text-center font-bold text-xs text-slate-800">${item.jumlah}</span>
                                <button onclick="changeQty(${idx}, 1)" class="w-6 h-6 rounded bg-white text-slate-700 flex items-center justify-center font-bold text-xs shadow-sm hover:bg-orange-500 hover:text-white transition">
                                    <i class="fa-solid fa-plus text-[9px]"></i>
                                </button>
                            </div>
                        </div>
                        <div class="mt-2">
                            <input type="text" placeholder="Catatan (misal: Jangan pedas / es sedikit)" 
                                   value="${item.catatan || ''}" 
                                   onchange="updateNote(${idx}, this.value)"
                                   class="w-full text-xs px-2.5 py-1.5 bg-slate-50 border border-slate-200 rounded-lg focus:outline-none focus:border-orange-400">
                        </div>
                    </div>
                `;
            });

            listEl.innerHTML = html;
            document.getElementById('subtotal-val').innerText = 'Rp ' + total.toLocaleString('id-ID');
            document.getElementById('total-val').innerText = 'Rp ' + total.toLocaleString('id-ID');
            document.getElementById('checkout-total-val').innerText = 'Rp ' + total.toLocaleString('id-ID');
        }

        function selectPayment(metode) {
            const qrisLabel = document.getElementById('label-qris');
            const cashLabel = document.getElementById('label-cash');
            if (metode === 'QRIS') {
                qrisLabel.className = 'relative flex flex-col p-3 border-2 rounded-xl cursor-pointer transition border-orange-500 bg-orange-50/50';
                cashLabel.className = 'relative flex flex-col p-3 border-2 rounded-xl cursor-pointer transition border-slate-200 bg-white hover:border-slate-300';
            } else {
                cashLabel.className = 'relative flex flex-col p-3 border-2 rounded-xl cursor-pointer transition border-orange-500 bg-orange-50/50';
                qrisLabel.className = 'relative flex flex-col p-3 border-2 rounded-xl cursor-pointer transition border-slate-200 bg-white hover:border-slate-300';
            }
        }

        async function submitOrder() {
            const cart = getCart();
            const nama = document.getElementById('nama_pelanggan').value.trim();
            const btnSubmit = document.getElementById('btn-submit-order');
            const metodeChecked = document.querySelector('input[name="metode_pembayaran"]:checked');
            const metode = metodeChecked ? metodeChecked.value : 'QRIS';

            if (!nama) {
                alert('Silakan masukkan Nama Pemesan terlebih dahulu!');
                document.getElementById('nama_pelanggan').focus();
                return;
            }

            if (cart.length === 0) {
                alert('Keranjang masih kosong!');
                return;
            }

            btnSubmit.disabled = true;
            btnSubmit.innerHTML = `<i class="fa-solid fa-circle-notch fa-spin"></i><span>Mengirim...</span>`;

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
                    // Kosongkan keranjang setelah berhasil
                    localStorage.removeItem('cart_resto');
                    // Arahkan ke halaman status pesanan
                    window.location.href = `status.php?order_id=${result.order_id}`;
                } else {
                    alert('Gagal: ' + result.message);
                    btnSubmit.disabled = false;
                    btnSubmit.innerHTML = `<span>Kirim Pesanan</span><i class="fa-solid fa-paper-plane text-xs"></i>`;
                }
            } catch (err) {
                alert('Terjadi kesalahan jaringan! Pastikan server lokal aktif.');
                btnSubmit.disabled = false;
                btnSubmit.innerHTML = `<span>Kirim Pesanan</span><i class="fa-solid fa-paper-plane text-xs"></i>`;
            }
        }

        document.addEventListener('DOMContentLoaded', renderCart);
    </script>
</body>
</html>

