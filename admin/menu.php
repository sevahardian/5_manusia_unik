<?php
// admin/menu.php
// Halaman Kelola Data Menu Makanan & Minuman

require_once __DIR__ . '/../config/koneksi.php';
require_once __DIR__ . '/../config/helper.php';

// Pastikan hanya admin yang bisa akses
check_auth('admin');

$pesan = '';

// Handle Tambah Menu
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'tambah') {
    $nama_menu = sanitize($_POST['nama_menu'] ?? '');
    $kategori  = sanitize($_POST['kategori'] ?? 'makanan');
    $harga     = (int)($_POST['harga'] ?? 0);
    $deskripsi = sanitize($_POST['deskripsi'] ?? '');
    $gambar    = trim($_POST['gambar'] ?? '');
    $status    = sanitize($_POST['status'] ?? 'tersedia');

    if (!empty($nama_menu) && $harga > 0) {
        $stmt = $pdo->prepare("
            INSERT INTO menus (nama_menu, kategori, harga, deskripsi, gambar, status)
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([$nama_menu, $kategori, $harga, $deskripsi, $gambar, $status]);
        $pesan = "Menu \"$nama_menu\" berhasil ditambahkan!";
    } else {
        $pesan = 'Nama menu dan harga valid wajib diisi!';
    }
}

// Handle Hapus Menu
if (isset($_GET['hapus'])) {
    $id_hapus = (int)$_GET['hapus'];
    try {
        $stmt = $pdo->prepare("DELETE FROM menus WHERE id = ?");
        $stmt->execute([$id_hapus]);
        $pesan = 'Menu berhasil dihapus dari katalog!';
    } catch (Exception $e) {
        $pesan = 'Menu tidak dapat dihapus karena sudah memiliki riwayat pemesanan.';
    }
}

// Handle Toggle Status (Tersedia / Habis)
if (isset($_GET['toggle_status']) && isset($_GET['current'])) {
    $id_toggle = (int)$_GET['toggle_status'];
    $new_status = $_GET['current'] === 'tersedia' ? 'habis' : 'tersedia';
    $stmt = $pdo->prepare("UPDATE menus SET status = ? WHERE id = ?");
    $stmt->execute([$new_status, $id_toggle]);
    header('Location: menu.php');
    exit;
}

// Ambil semua menu
$menus = $pdo->query("SELECT * FROM menus ORDER BY id DESC")->fetchAll();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Menu - Resto Enak Rasa</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
    </style>
</head>
<body class="bg-slate-50 text-slate-800 flex flex-col min-h-screen">

    <!-- Navbar Pelanggan & Petugas Persis Landing Page -->
    <header class="sticky top-0 z-40 bg-white/90 backdrop-blur-md border-b border-slate-200">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 py-3.5 flex items-center justify-between">
            <a href="index.php" class="flex items-center space-x-2.5">
                <div class="w-10 h-10 rounded-2xl bg-gradient-to-tr from-orange-600 to-amber-500 text-white flex items-center justify-center font-bold text-lg shadow-md shadow-orange-500/20">
                    <i class="fa-solid fa-utensils"></i>
                </div>
                <div>
                    <span class="font-extrabold text-slate-900 text-lg tracking-tight block leading-tight">Resto Enak Rasa</span>
                    <span class="text-[10px] text-orange-600 font-semibold tracking-wider uppercase">Admin Backoffice</span>
                </div>
            </a>

            <!-- Nav Links -->
            <div class="flex items-center space-x-2 text-xs">
                <a href="index.php" class="px-3.5 py-2 bg-slate-100 hover:bg-orange-50 hover:text-orange-600 text-slate-700 font-bold rounded-xl border border-slate-200 transition flex items-center space-x-1.5">
                    <i class="fa-solid fa-chart-pie text-xs"></i>
                    <span>Overview</span>
                </a>
                <a href="menu.php" class="px-4 py-2 bg-orange-500 hover:bg-orange-600 text-white font-bold rounded-xl shadow-md shadow-orange-500/20 active:scale-95 transition flex items-center space-x-1.5">
                    <i class="fa-solid fa-bowl-food text-xs"></i>
                    <span>Kelola Menu</span>
                </a>
                <a href="../kasir/index.php" class="px-3.5 py-2 bg-slate-100 hover:bg-orange-50 hover:text-orange-600 text-slate-700 font-bold rounded-xl border border-slate-200 transition flex items-center space-x-1.5">
                    <i class="fa-solid fa-cash-register text-xs"></i>
                    <span class="hidden sm:inline">Layar Kasir</span>
                </a>
                <a href="logout.php" onclick="return confirm('Keluar dari sesi Admin?')" class="p-2 text-slate-400 hover:text-rose-600 transition" title="Logout">
                    <i class="fa-solid fa-right-from-bracket text-base"></i>
                </a>
            </div>
        </div>
    </header>

    <main class="max-w-6xl mx-auto px-4 sm:px-6 py-8 flex-1 w-full space-y-6">

        <!-- Notifikasi Pesan -->
        <?php if (!empty($pesan)): ?>
            <div class="bg-orange-50 text-orange-800 border border-orange-200 px-4 py-3 rounded-2xl text-xs font-semibold flex items-center justify-between shadow-sm">
                <div class="flex items-center space-x-2">
                    <i class="fa-solid fa-circle-info text-orange-600 text-sm"></i>
                    <span><?= htmlspecialchars($pesan) ?></span>
                </div>
                <a href="menu.php" class="text-xs font-bold text-orange-700 underline">Tutup</a>
            </div>
        <?php endif; ?>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 items-start">
            
            <!-- Form Tambah Menu (Kolom Kiri) -->
            <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm sticky top-20">
                <div class="pb-4 mb-4 border-b border-slate-100">
                    <span class="text-xs font-bold text-orange-600 uppercase tracking-widest block">Form Input</span>
                    <h2 class="font-black text-base text-slate-900 mt-0.5">Tambah Menu Baru</h2>
                </div>

                <form action="menu.php" method="POST" class="space-y-4 text-xs">
                    <input type="hidden" name="action" value="tambah">

                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Nama Menu:</label>
                        <input type="text" name="nama_menu" placeholder="Contoh: Bebek Goreng Kremes" 
                               class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl font-bold text-slate-800 focus:outline-none focus:border-orange-500 focus:bg-white transition" required>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block font-bold text-slate-700 mb-1">Kategori:</label>
                            <select name="kategori" class="w-full px-3 py-2.5 bg-slate-50 border border-slate-200 rounded-xl font-bold text-slate-800 focus:outline-none focus:border-orange-500 focus:bg-white transition">
                                <option value="makanan">Makanan</option>
                                <option value="minuman">Minuman</option>
                                <option value="snack">Cemilan / Snack</option>
                            </select>
                        </div>
                        <div>
                            <label class="block font-bold text-slate-700 mb-1">Harga (Rp):</label>
                            <input type="number" name="harga" placeholder="25000" min="500"
                                   class="w-full px-3 py-2.5 bg-slate-50 border border-slate-200 rounded-xl font-bold text-slate-800 focus:outline-none focus:border-orange-500 focus:bg-white transition" required>
                        </div>
                    </div>

                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Deskripsi:</label>
                        <textarea name="deskripsi" rows="2" placeholder="Keterangan bumbu, rasa, atau porsi..." 
                                  class="w-full px-4 py-2 bg-slate-50 border border-slate-200 rounded-xl font-semibold text-slate-800 focus:outline-none focus:border-orange-500 focus:bg-white transition"></textarea>
                    </div>

                    <div>
                        <label class="block font-bold text-slate-700 mb-1">URL Gambar (Unsplash / Online):</label>
                        <input type="url" name="gambar" placeholder="https://images.unsplash.com/..." 
                               class="w-full px-4 py-2 bg-slate-50 border border-slate-200 rounded-xl text-slate-800 focus:outline-none focus:border-orange-500 focus:bg-white transition">
                    </div>

                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Status Ketersediaan Awal:</label>
                        <select name="status" class="w-full px-3 py-2.5 bg-slate-50 border border-slate-200 rounded-xl font-bold text-slate-800 focus:outline-none focus:border-orange-500 focus:bg-white transition">
                            <option value="tersedia">Tersedia</option>
                            <option value="habis">Habis</option>
                        </select>
                    </div>

                    <button type="submit" class="w-full py-3 bg-orange-500 hover:bg-orange-600 text-white font-bold rounded-xl shadow-md shadow-orange-500/20 active:scale-95 transition flex items-center justify-center space-x-1.5 mt-2">
                        <i class="fa-solid fa-plus text-xs"></i>
                        <span>Simpan Menu</span>
                    </button>
                </form>
            </div>

            <!-- Tabel Daftar Menu (Kolom Kanan) -->
            <div class="lg:col-span-2 bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
                <div class="p-5 border-b border-slate-200 flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                    <div>
                        <span class="text-xs font-bold text-orange-600 uppercase tracking-widest">Katalog Makanan & Minuman</span>
                        <h2 class="font-black text-base text-slate-900 mt-0.5">Daftar Menu Tersedia (<?= count($menus) ?>)</h2>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs">
                        <thead class="bg-slate-50 text-slate-500 uppercase border-b border-slate-200 font-bold text-[11px]">
                            <tr>
                                <th class="py-3 px-4">Menu</th>
                                <th class="py-3 px-3">Kategori</th>
                                <th class="py-3 px-3">Harga</th>
                                <th class="py-3 px-3 text-center">Status</th>
                                <th class="py-3 px-4 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <?php if (empty($menus)): ?>
                                <tr>
                                    <td colspan="5" class="py-10 text-center text-slate-400">Belum ada menu di katalog.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($menus as $m): ?>
                                    <tr class="hover:bg-orange-50/50 transition">
                                        <td class="py-3.5 px-4">
                                            <div class="flex items-center space-x-3">
                                                <img src="<?= htmlspecialchars($m['gambar'] ?: 'https://images.unsplash.com/photo-1546069901-ba9599a7e63c?w=100') ?>" 
                                                     alt="<?= htmlspecialchars($m['nama_menu']) ?>"
                                                     class="w-12 h-12 rounded-xl object-cover bg-slate-100 border border-slate-200 flex-shrink-0"
                                                     onerror="this.src='https://images.unsplash.com/photo-1546069901-ba9599a7e63c?w=100'">
                                                <div class="min-w-0">
                                                    <p class="font-bold text-slate-900 truncate"><?= htmlspecialchars($m['nama_menu']) ?></p>
                                                    <p class="text-[11px] text-slate-400 truncate max-w-xs mt-0.5"><?= htmlspecialchars($m['deskripsi'] ?: '-') ?></p>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="py-3.5 px-3">
                                            <span class="inline-block px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-orange-50 text-orange-600 border border-orange-200 uppercase">
                                                <?= htmlspecialchars($m['kategori']) ?>
                                            </span>
                                        </td>
                                        <td class="py-3.5 px-3 font-black text-slate-900">
                                            <?= format_rupiah($m['harga']) ?>
                                        </td>
                                        <td class="py-3.5 px-3 text-center">
                                            <a href="menu.php?toggle_status=<?= $m['id'] ?>&current=<?= $m['status'] ?>" 
                                               title="Klik untuk ubah status ketersediaan"
                                               class="inline-block px-3 py-1 rounded-lg text-[10px] font-bold transition shadow-sm <?= $m['status'] === 'tersedia' ? 'bg-orange-500 hover:bg-orange-600 text-white' : 'bg-slate-100 hover:bg-slate-200 text-slate-500 border border-slate-200' ?>">
                                                <?= strtoupper($m['status']) ?>
                                            </a>
                                        </td>
                                        <td class="py-3.5 px-4 text-right">
                                            <a href="menu.php?hapus=<?= $m['id'] ?>" 
                                               onclick="return confirm('Hapus menu <?= addslashes($m['nama_menu']) ?>?')"
                                               class="p-2 text-slate-400 hover:text-rose-600 rounded-lg hover:bg-rose-50 transition inline-block"
                                               title="Hapus Menu">
                                                <i class="fa-regular fa-trash-can text-sm"></i>
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

        </div>

    </main>

    <!-- Footer Persis Landing Page -->
    <footer class="bg-slate-900 text-slate-400 mt-auto pt-8 pb-6 border-t border-slate-800">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 flex flex-col sm:flex-row items-center justify-between text-[11px] text-slate-500 gap-2">
            <span>&copy; <?= date('Y') ?> Resto Enak Rasa. All rights reserved.</span>
            <span>Panel Khusus Administrator Resto</span>
        </div>
    </footer>

</body>
</html>
