<?php
// admin/menu.php
// [BAGIAN 1: PROGRAMMER 1] Halaman Kelola Data Menu Makanan & Minuman

require_once __DIR__ . '/../config/koneksi.php';
require_once __DIR__ . '/../config/helper.php';

$pesan = '';

// Handle Tambah Menu
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'tambah') {
    $nama_menu = sanitize($_POST['nama_menu']);
    $kategori  = sanitize($_POST['kategori']);
    $harga     = (int)$_POST['harga'];
    $deskripsi = sanitize($_POST['deskripsi']);
    $gambar    = trim($_POST['gambar']);
    $status    = sanitize($_POST['status']);

    if (!empty($nama_menu) && $harga > 0) {
        $stmt = $pdo->prepare("
            INSERT INTO menus (nama_menu, kategori, harga, deskripsi, gambar, status)
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([$nama_menu, $kategori, $harga, $deskripsi, $gambar, $status]);
        $pesan = 'Menu berhasil ditambahkan!';
    }
}

// Handle Hapus Menu
if (isset($_GET['hapus'])) {
    $id_hapus = (int)$_GET['hapus'];
    try {
        $stmt = $pdo->prepare("DELETE FROM menus WHERE id = ?");
        $stmt->execute([$id_hapus]);
        $pesan = 'Menu berhasil dihapus!';
    } catch (Exception $e) {
        $pesan = 'Menu tidak dapat dihapus karena sudah pernah dipesan.';
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
    <title>Kelola Menu - Admin Resto</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-slate-100 min-h-screen text-slate-800">

    <!-- Header -->
    <header class="bg-white border-b border-slate-200 sticky top-0 z-30 px-4 py-3 shadow-sm">
        <div class="max-w-6xl mx-auto flex items-center justify-between">
            <div class="flex items-center space-x-3">
                <a href="../index.php" class="p-2 text-slate-500 hover:text-slate-800 transition">
                    <i class="fa-solid fa-arrow-left"></i>
                </a>
                <div class="w-9 h-9 rounded-lg bg-emerald-600 text-white flex items-center justify-center font-bold">
                    <i class="fa-solid fa-bowl-food"></i>
                </div>
                <h1 class="font-bold text-base text-slate-900">Kelola Katalog Menu</h1>
            </div>
            <a href="../kasir/index.php" class="px-3 py-1.5 bg-blue-600 hover:bg-blue-700 text-white text-xs font-semibold rounded-lg transition flex items-center space-x-1.5">
                <i class="fa-solid fa-cash-register"></i>
                <span>Ke Kasir</span>
            </a>
        </div>
    </header>

    <main class="max-w-6xl mx-auto px-4 py-6">
        <?php if (!empty($pesan)): ?>
            <div class="bg-emerald-50 text-emerald-800 border border-emerald-200 px-4 py-3 rounded-xl text-xs font-semibold mb-6 flex items-center justify-between">
                <span><?= htmlspecialchars($pesan) ?></span>
                <a href="menu.php" class="text-emerald-900 underline">Tutup</a>
            </div>
        <?php endif; ?>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Form Tambah Menu -->
            <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm h-fit">
                <h2 class="font-bold text-sm text-slate-900 mb-4 flex items-center space-x-2">
                    <i class="fa-solid fa-plus-circle text-emerald-600"></i>
                    <span>Tambah Menu Baru</span>
                </h2>

                <form action="menu.php" method="POST" class="space-y-3 text-xs">
                    <input type="hidden" name="action" value="tambah">

                    <div>
                        <label class="block font-semibold text-slate-700 mb-1">Nama Menu:</label>
                        <input type="text" name="nama_menu" placeholder="Contoh: Bebek Goreng Kremes" 
                               class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-lg focus:outline-none focus:border-emerald-500" required>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block font-semibold text-slate-700 mb-1">Kategori:</label>
                            <select name="kategori" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-lg focus:outline-none focus:border-emerald-500">
                                <option value="makanan">Makanan</option>
                                <option value="minuman">Minuman</option>
                                <option value="snack">Cemilan / Snack</option>
                            </select>
                        </div>
                        <div>
                            <label class="block font-semibold text-slate-700 mb-1">Harga (Rp):</label>
                            <input type="number" name="harga" placeholder="25000" 
                                   class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-lg focus:outline-none focus:border-emerald-500" required>
                        </div>
                    </div>

                    <div>
                        <label class="block font-semibold text-slate-700 mb-1">Deskripsi Singkat:</label>
                        <textarea name="deskripsi" rows="2" placeholder="Keterangan rasa atau porsi..." 
                                  class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-lg focus:outline-none focus:border-emerald-500"></textarea>
                    </div>

                    <div>
                        <label class="block font-semibold text-slate-700 mb-1">URL Gambar (Unsplash / Online):</label>
                        <input type="url" name="gambar" placeholder="https://..." 
                               class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-lg focus:outline-none focus:border-emerald-500">
                    </div>

                    <div>
                        <label class="block font-semibold text-slate-700 mb-1">Status Stok Awal:</label>
                        <select name="status" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-lg focus:outline-none focus:border-emerald-500">
                            <option value="tersedia">Tersedia (Ready)</option>
                            <option value="habis">Habis (Kosong)</option>
                        </select>
                    </div>

                    <button type="submit" class="w-full py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded-lg shadow-md transition">
                        Simpan Menu
                    </button>
                </form>
            </div>

            <!-- Tabel Daftar Menu -->
            <div class="lg:col-span-2 bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
                <div class="p-4 border-b border-slate-100 flex justify-between items-center">
                    <h2 class="font-bold text-sm text-slate-900">Daftar Menu Tersedia (<?= count($menus) ?>)</h2>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs">
                        <thead class="bg-slate-50 text-slate-500 uppercase border-b border-slate-100 font-semibold">
                            <tr>
                                <th class="p-3">Menu</th>
                                <th class="p-3">Kategori</th>
                                <th class="p-3">Harga</th>
                                <th class="p-3 text-center">Status Stok</th>
                                <th class="p-3 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <?php foreach ($menus as $m): ?>
                                <tr class="hover:bg-slate-50 transition">
                                    <td class="p-3 flex items-center space-x-3">
                                        <img src="<?= htmlspecialchars($m['gambar'] ?: 'https://images.unsplash.com/photo-1546069901-ba9599a7e63c?w=100') ?>" 
                                             class="w-10 h-10 rounded-lg object-cover bg-slate-100 flex-shrink-0">
                                        <div>
                                            <p class="font-bold text-slate-900"><?= htmlspecialchars($m['nama_menu']) ?></p>
                                            <p class="text-[10px] text-slate-400 truncate max-w-xs"><?= htmlspecialchars($m['deskripsi'] ?: '-') ?></p>
                                        </div>
                                    </td>
                                    <td class="p-3 capitalize text-slate-600"><?= $m['kategori'] ?></td>
                                    <td class="p-3 font-bold text-slate-900"><?= format_rupiah($m['harga']) ?></td>
                                    <td class="p-3 text-center">
                                        <a href="menu.php?toggle_status=<?= $m['id'] ?>&current=<?= $m['status'] ?>" 
                                           class="inline-block px-2.5 py-1 rounded-full text-[10px] font-bold transition <?= $m['status'] === 'tersedia' ? 'bg-emerald-100 text-emerald-800 hover:bg-emerald-200' : 'bg-red-100 text-red-800 hover:bg-red-200' ?>">
                                            <?= strtoupper($m['status']) ?>
                                        </a>
                                    </td>
                                    <td class="p-3 text-right space-x-1">
                                        <a href="menu.php?hapus=<?= $m['id'] ?>" 
                                           onclick="return confirm('Hapus menu <?= addslashes($m['nama_menu']) ?>?')"
                                           class="p-1.5 text-red-500 hover:text-red-700 hover:bg-red-50 rounded transition">
                                            <i class="fa-regular fa-trash-can"></i>
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </main>
</body>
</html>

