<?php
// index.php
// Landing Page Utama Resto & Cafe untuk Pembeli

// Jika scan QR membawa parameter meja (contoh: index.php?meja=05), langsung alihkan ke katalog pembeli
if (isset($_GET['meja']) && !empty($_GET['meja'])) {
    header('Location: pembeli/index.php?meja=' . urlencode($_GET['meja']));
    exit;
}
?>
<!DOCTYPE html>
<html lang="id" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Resto Enak Rasa - Pesan Makanan Langsung Dari Meja</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
    </style>
</head>
<body class="bg-slate-50 text-slate-800 flex flex-col min-h-screen">

    <!-- Navbar Pelanggan -->
    <header class="sticky top-0 z-40 bg-white/90 backdrop-blur-md border-b border-slate-200">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 py-3.5 flex items-center justify-between">
            <a href="index.php" class="flex items-center space-x-2.5">
                <div class="w-10 h-10 rounded-2xl bg-gradient-to-tr from-orange-600 to-amber-500 text-white flex items-center justify-center font-bold text-lg shadow-md shadow-orange-500/20">
                    <i class="fa-solid fa-utensils"></i>
                </div>
                <div>
                    <span class="font-extrabold text-slate-900 text-lg tracking-tight block leading-tight">Resto Enak Rasa</span>
                    <span class="text-[10px] text-orange-600 font-semibold tracking-wider uppercase">Self-Service Dining</span>
                </div>
            </a>

            <!-- Nav Links -->
            <nav class="hidden md:flex items-center space-x-6 text-xs font-semibold text-slate-600">
                <a href="#cara-pesan" class="hover:text-orange-600 transition">Cara Pesan</a>
                <a href="#menu-populer" class="hover:text-orange-600 transition">Menu Populer</a>
                <a href="#tentang" class="hover:text-orange-600 transition">Tentang Kami</a>
            </nav>

            <div>
                <a href="#pilih-meja" class="px-4 py-2 bg-orange-500 hover:bg-orange-600 text-white text-xs font-bold rounded-xl shadow-md shadow-orange-500/20 active:scale-95 transition flex items-center space-x-1.5">
                    <i class="fa-solid fa-chair text-[11px]"></i>
                    <span>Pesan di Meja</span>
                </a>
            </div>
        </div>
    </header>

    <!-- Hero Section -->
    <section class="relative overflow-hidden bg-gradient-to-b from-orange-50/60 to-slate-50 py-16 md:py-24">
        <div class="max-w-6xl mx-auto px-4 sm:px-6">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 items-center">
                
                <!-- Hero Text -->
                <div class="space-y-6 text-center lg:text-left">
                    <div class="inline-flex items-center space-x-2 px-3 py-1.5 rounded-full bg-orange-100/80 text-orange-700 text-xs font-bold border border-orange-200">
                        <span class="w-2 h-2 rounded-full bg-orange-500 animate-pulse"></span>
                        <span>Pesan Tanpa Perlu Antre di Kasir</span>
                    </div>

                    <h1 class="text-3xl sm:text-4xl md:text-5xl font-black text-slate-900 leading-tight">
                        Nikmati Kuliner Lezat, <br class="hidden sm:inline">
                        <span class="text-transparent bg-clip-text bg-gradient-to-r from-orange-600 to-amber-500">Pesan Langsung</span> Dari Meja Anda.
                    </h1>

                    <p class="text-sm sm:text-base text-slate-600 max-w-lg mx-auto lg:mx-0 leading-relaxed">
                        Cukup duduk santai di mejamu, pilih makanan dan minuman favorit lewat HP, dan biarkan koki kami memasaknya untuk Anda.
                    </p>

                    <!-- Form Pilih Meja Pelanggan -->
                    <div id="pilih-meja" class="bg-white p-4 sm:p-5 rounded-2xl shadow-xl border border-slate-200 max-w-md mx-auto lg:mx-0">
                        <span class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2 text-left">
                            <i class="fa-solid fa-qrcode text-orange-500 mr-1.5"></i> Masukkan / Pilih Nomor Meja Anda:
                        </span>
                        
                        <form action="pembeli/index.php" method="GET" class="flex gap-2">
                            <input type="text" name="meja" placeholder="Contoh: 03" value="03" 
                                   class="flex-1 px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-sm font-bold text-slate-800 focus:outline-none focus:border-orange-500 focus:bg-white transition" required>
                            <button type="submit" class="px-6 py-3 bg-orange-500 hover:bg-orange-600 text-white font-bold rounded-xl text-xs sm:text-sm shadow-md shadow-orange-500/30 flex items-center space-x-2 active:scale-95 transition flex-shrink-0">
                                <span>Buka Menu</span>
                                <i class="fa-solid fa-arrow-right text-xs"></i>
                            </button>
                        </form>

                        <div class="flex items-center gap-2 mt-3 pt-3 border-t border-slate-100 text-[11px] font-semibold text-slate-500 overflow-x-auto">
                            <span class="text-slate-400 flex-shrink-0">Pilihan cepat:</span>
                            <a href="pembeli/index.php?meja=01" class="px-2.5 py-1 bg-slate-100 hover:bg-orange-50 hover:text-orange-600 rounded-lg transition border border-slate-200">Meja 01</a>
                            <a href="pembeli/index.php?meja=02" class="px-2.5 py-1 bg-slate-100 hover:bg-orange-50 hover:text-orange-600 rounded-lg transition border border-slate-200">Meja 02</a>
                            <a href="pembeli/index.php?meja=03" class="px-2.5 py-1 bg-slate-100 hover:bg-orange-50 hover:text-orange-600 rounded-lg transition border border-slate-200">Meja 03</a>
                            <a href="pembeli/index.php?meja=04" class="px-2.5 py-1 bg-slate-100 hover:bg-orange-50 hover:text-orange-600 rounded-lg transition border border-slate-200">Meja 04</a>
                            <a href="pembeli/index.php?meja=05" class="px-2.5 py-1 bg-slate-100 hover:bg-orange-50 hover:text-orange-600 rounded-lg transition border border-slate-200">Meja 05</a>
                        </div>
                    </div>
                </div>

                <!-- Hero Image Showcase -->
                <div class="relative flex justify-center">
                    <div class="relative w-full max-w-sm sm:max-w-md aspect-square rounded-3xl overflow-hidden shadow-2xl border-4 border-white">
                        <img src="https://images.unsplash.com/photo-1555396273-367ea4eb4db5?w=800&q=80" 
                             alt="Restoran Suasana" 
                             class="w-full h-full object-cover">
                        <div class="absolute inset-0 bg-gradient-to-t from-black/60 via-transparent to-transparent flex items-end p-6">
                            <div class="text-white">
                                <span class="bg-orange-500 text-[10px] font-bold px-2.5 py-1 rounded-full uppercase tracking-wider">Kualitas Terbaik</span>
                                <h3 class="font-bold text-lg mt-1">Bahan Pilihan, Rasa Otentik</h3>
                                <p class="text-xs text-slate-200 mt-0.5">Disajikan hangat dan higienis langsung ke meja Anda.</p>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </section>

    <!-- Section Cara Pesan (3 Langkah Mudah) -->
    <section id="cara-pesan" class="py-16 bg-white border-y border-slate-200">
        <div class="max-w-6xl mx-auto px-4 sm:px-6">
            <div class="text-center max-w-md mx-auto mb-12">
                <span class="text-xs font-bold text-orange-600 uppercase tracking-widest">Sederhana & Cepat</span>
                <h2 class="text-2xl sm:text-3xl font-black text-slate-900 mt-1">Hanya 3 Langkah Mudah</h2>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <!-- Step 1 -->
                <div class="bg-slate-50 rounded-2xl p-6 border border-slate-200 text-center relative group hover:border-orange-300 transition">
                    <div class="w-14 h-14 rounded-2xl bg-orange-100 text-orange-600 font-black text-xl flex items-center justify-center mx-auto mb-4 group-hover:scale-110 transition">
                        1
                    </div>
                    <h3 class="font-bold text-base text-slate-900 mb-2">Scan QR / Pilih Meja</h3>
                    <p class="text-xs text-slate-500 leading-relaxed">
                        Arahkan kamera smartphone ke stiker barcode QR di mejamu atau masukkan nomor meja langsung.
                    </p>
                </div>

                <!-- Step 2 -->
                <div class="bg-slate-50 rounded-2xl p-6 border border-slate-200 text-center relative group hover:border-orange-300 transition">
                    <div class="w-14 h-14 rounded-2xl bg-amber-100 text-amber-600 font-black text-xl flex items-center justify-center mx-auto mb-4 group-hover:scale-110 transition">
                        2
                    </div>
                    <h3 class="font-bold text-base text-slate-900 mb-2">Pilih Menu & Catatan</h3>
                    <p class="text-xs text-slate-500 leading-relaxed">
                        Pilih makanan & minuman favorit, tambahkan catatan khusus (seperti pedas/manis), lalu kirim pesanan.
                    </p>
                </div>

                <!-- Step 3 -->
                <div class="bg-slate-50 rounded-2xl p-6 border border-slate-200 text-center relative group hover:border-orange-300 transition">
                    <div class="w-14 h-14 rounded-2xl bg-emerald-100 text-emerald-600 font-black text-xl flex items-center justify-center mx-auto mb-4 group-hover:scale-110 transition">
                        3
                    </div>
                    <h3 class="font-bold text-base text-slate-900 mb-2">Makanan Siap Disajikan</h3>
                    <p class="text-xs text-slate-500 leading-relaxed">
                        Dapur langsung memasak hidangan Anda dan pelayan akan mengantarkannya tepat ke mejamu.
                    </p>
                </div>
            </div>
        </div>
    </section>

    <!-- Section Menu Populer -->
    <section id="menu-populer" class="py-16 bg-slate-50">
        <div class="max-w-6xl mx-auto px-4 sm:px-6">
            <div class="flex flex-col sm:flex-row sm:items-end justify-between mb-8">
                <div>
                    <span class="text-xs font-bold text-orange-600 uppercase tracking-widest">Favorit Pelanggan</span>
                    <h2 class="text-2xl sm:text-3xl font-black text-slate-900 mt-1">Menu Rekomendasi Hari Ini</h2>
                </div>
                <a href="#pilih-meja" class="text-xs font-bold text-orange-600 hover:text-orange-700 mt-2 sm:mt-0 inline-flex items-center space-x-1">
                    <span>Lihat Semua Menu</span>
                    <i class="fa-solid fa-arrow-right text-[10px]"></i>
                </a>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                <!-- Card 1 -->
                <div class="bg-white rounded-2xl overflow-hidden border border-slate-200 shadow-sm hover:shadow-md transition">
                    <img src="https://images.unsplash.com/photo-1603133872878-684f208fb84b?w=600&q=80" alt="Nasi Goreng" class="w-full h-44 object-cover">
                    <div class="p-4">
                        <span class="text-[10px] font-bold text-orange-600 bg-orange-50 px-2 py-0.5 rounded-full uppercase">Makanan</span>
                        <h3 class="font-bold text-base text-slate-900 mt-1.5">Nasi Goreng Spesial Telur</h3>
                        <p class="text-xs text-slate-500 mt-1">Bumbu rempah pilihan dengan suwiran ayam gurih dan telur mata sapi.</p>
                        <div class="mt-4 flex items-center justify-between pt-3 border-t border-slate-100">
                            <span class="font-black text-base text-slate-900">Rp 25.000</span>
                            <a href="pembeli/index.php?meja=03" class="px-3 py-1.5 bg-orange-500 hover:bg-orange-600 text-white text-xs font-bold rounded-lg transition">Pesan</a>
                        </div>
                    </div>
                </div>

                <!-- Card 2 -->
                <div class="bg-white rounded-2xl overflow-hidden border border-slate-200 shadow-sm hover:shadow-md transition">
                    <img src="https://images.unsplash.com/photo-1598515214211-89d3c73ae83b?w=600&q=80" alt="Ayam Bakar" class="w-full h-44 object-cover">
                    <div class="p-4">
                        <span class="text-[10px] font-bold text-orange-600 bg-orange-50 px-2 py-0.5 rounded-full uppercase">Makanan</span>
                        <h3 class="font-bold text-base text-slate-900 mt-1.5">Ayam Bakar Madu + Nasi</h3>
                        <p class="text-xs text-slate-500 mt-1">Ayam bakar legit manis gurih disajikan dengan sambal terasi dan lalapan.</p>
                        <div class="mt-4 flex items-center justify-between pt-3 border-t border-slate-100">
                            <span class="font-black text-base text-slate-900">Rp 32.000</span>
                            <a href="pembeli/index.php?meja=03" class="px-3 py-1.5 bg-orange-500 hover:bg-orange-600 text-white text-xs font-bold rounded-lg transition">Pesan</a>
                        </div>
                    </div>
                </div>

                <!-- Card 3 -->
                <div class="bg-white rounded-2xl overflow-hidden border border-slate-200 shadow-sm hover:shadow-md transition">
                    <img src="https://images.unsplash.com/photo-1541167760496-1628856ab772?w=600&q=80" alt="Kopi Susu" class="w-full h-44 object-cover">
                    <div class="p-4">
                        <span class="text-[10px] font-bold text-blue-600 bg-blue-50 px-2 py-0.5 rounded-full uppercase">Minuman</span>
                        <h3 class="font-bold text-base text-slate-900 mt-1.5">Kopi Susu Gula Aren</h3>
                        <p class="text-xs text-slate-500 mt-1">Espresso arabika dipadu susu creamy dan manis legit gula aren asli.</p>
                        <div class="mt-4 flex items-center justify-between pt-3 border-t border-slate-100">
                            <span class="font-black text-base text-slate-900">Rp 18.000</span>
                            <a href="pembeli/index.php?meja=03" class="px-3 py-1.5 bg-orange-500 hover:bg-orange-600 text-white text-xs font-bold rounded-lg transition">Pesan</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer dengan Pintu Masuk Petugas yang Disembunyikan / Diskret -->
    <footer id="tentang" class="bg-slate-900 text-slate-400 mt-auto pt-12 pb-6 border-t border-slate-800">
        <div class="max-w-6xl mx-auto px-4 sm:px-6">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8 pb-8 border-b border-slate-800">
                <div>
                    <div class="flex items-center space-x-2.5 text-white mb-3">
                        <div class="w-8 h-8 rounded-xl bg-orange-500 flex items-center justify-center font-bold text-sm">
                            <i class="fa-solid fa-utensils"></i>
                        </div>
                        <span class="font-extrabold text-base tracking-tight">Resto Enak Rasa</span>
                    </div>
                    <p class="text-xs text-slate-400 leading-relaxed">
                        Menyajikan aneka masakan nusantara dengan cita rasa otentik dan kemudahan pemesanan mandiri melalui meja Anda.
                    </p>
                </div>

                <div>
                    <h4 class="text-xs font-bold text-white uppercase tracking-wider mb-3">Jam Operasional</h4>
                    <p class="text-xs text-slate-400">Senin - Minggu: 10:00 - 22:00 WIB</p>
                    <p class="text-xs text-slate-400 mt-1">Pelayanan Dapur Terakhir: 21:30 WIB</p>
                </div>

                <div>
                    <h4 class="text-xs font-bold text-white uppercase tracking-wider mb-3">Lokasi Resto</h4>
                    <p class="text-xs text-slate-400">Jl. Hackathon Inovasi No. 123, Lantai 1</p>
                    <p class="text-xs text-slate-400 mt-1">WhatsApp Reservasi: 0812-3456-7890</p>
                </div>
            </div>

            <!-- Bagian Hak Cipta & Pintu Masuk Rahasia/Tersembunyi Petugas -->
            <div class="pt-6 flex flex-col sm:flex-row items-center justify-between text-[11px] text-slate-500 gap-2">
                <span>&copy; <?= date('Y') ?> Resto Enak Rasa. All rights reserved.</span>

                <!-- Section Akses Login Petugas Disembunyikan (Discreet / Subtle) -->
                <div class="opacity-40 hover:opacity-100 transition-opacity">
                    <a href="admin/login.php" class="text-slate-500 hover:text-slate-300 flex items-center space-x-1" title="Akses Khusus Kasir & Admin">
                        <i class="fa-solid fa-lock text-[9px]"></i>
                        <span>Akses Petugas</span>
                    </a>
                </div>
            </div>
        </div>
    </footer>

</body>
</html>
