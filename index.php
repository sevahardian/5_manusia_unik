<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistem Resto & Cafe - Portal Utama</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-slate-100 min-h-screen flex items-center justify-center p-4">
    <div class="max-w-md w-full bg-white rounded-2xl shadow-xl overflow-hidden border border-slate-200">
        <!-- Header -->
        <div class="bg-gradient-to-r from-amber-500 to-orange-600 p-6 text-white text-center">
            <div class="w-16 h-16 bg-white/20 rounded-2xl flex items-center justify-center mx-auto mb-3 backdrop-blur-sm">
                <i class="fa-solid fa-utensils text-2xl"></i>
            </div>
            <h1 class="text-2xl font-bold">Resto & Cafe App</h1>
            <p class="text-amber-100 text-sm mt-1">Sistem Pemesanan Self-Service & Kasir</p>
        </div>

        <!-- Body Menu -->
        <div class="p-6 space-y-4">
            <div class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-2">Pilih Mode Tampilan Demo:</div>

            <!-- Option 1: Pembeli -->
            <a href="pembeli/index.php?meja=03" class="block p-4 rounded-xl border border-slate-200 hover:border-orange-500 hover:shadow-md transition bg-slate-50 hover:bg-orange-50 group">
                <div class="flex items-center space-x-4">
                    <div class="w-12 h-12 rounded-xl bg-orange-100 text-orange-600 flex items-center justify-center text-xl group-hover:scale-110 transition">
                        <i class="fa-solid fa-qrcode"></i>
                    </div>
                    <div class="flex-1">
                        <h3 class="font-bold text-slate-800 group-hover:text-orange-600">Simulasi Pembeli (Scan QR Meja)</h3>
                        <p class="text-xs text-slate-500 mt-0.5">Buka menu meja #03, tambah ke keranjang & pesan</p>
                    </div>
                    <i class="fa-solid fa-chevron-right text-slate-400 group-hover:text-orange-500"></i>
                </div>
            </a>

            <!-- Option 2: Kasir -->
            <a href="kasir/index.php" class="block p-4 rounded-xl border border-slate-200 hover:border-blue-500 hover:shadow-md transition bg-slate-50 hover:bg-blue-50 group">
                <div class="flex items-center space-x-4">
                    <div class="w-12 h-12 rounded-xl bg-blue-100 text-blue-600 flex items-center justify-center text-xl group-hover:scale-110 transition">
                        <i class="fa-solid fa-cash-register"></i>
                    </div>
                    <div class="flex-1">
                        <h3 class="font-bold text-slate-800 group-hover:text-blue-600">Dashboard Kasir / Dapur</h3>
                        <p class="text-xs text-slate-500 mt-0.5">Monitoring pesanan real-time & cetak struk</p>
                    </div>
                    <i class="fa-solid fa-chevron-right text-slate-400 group-hover:text-blue-500"></i>
                </div>
            </a>

            <!-- Option 3: Admin -->
            <a href="admin/menu.php" class="block p-4 rounded-xl border border-slate-200 hover:border-emerald-500 hover:shadow-md transition bg-slate-50 hover:bg-emerald-50 group">
                <div class="flex items-center space-x-4">
                    <div class="w-12 h-12 rounded-xl bg-emerald-100 text-emerald-600 flex items-center justify-center text-xl group-hover:scale-110 transition">
                        <i class="fa-solid fa-bowl-food"></i>
                    </div>
                    <div class="flex-1">
                        <h3 class="font-bold text-slate-800 group-hover:text-emerald-600">Kelola Menu (Admin)</h3>
                        <p class="text-xs text-slate-500 mt-0.5">Tambah makanan/minuman, harga & status stok</p>
                    </div>
                    <i class="fa-solid fa-chevron-right text-slate-400 group-hover:text-emerald-500"></i>
                </div>
            </a>
        </div>

        <!-- Footer -->
        <div class="px-6 py-4 bg-slate-50 border-t border-slate-100 text-center text-xs text-slate-400">
            Tech Stack: <b>PHP Native + MySQL + Tailwind CSS</b>
        </div>
    </div>
</body>
</html>

