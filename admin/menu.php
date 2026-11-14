<?php
// admin/menu.php
// Halaman Kelola Data Menu Makanan & Minuman

require_once __DIR__ . '/../config/koneksi.php';
require_once __DIR__ . '/../config/helper.php';

// Pastikan hanya admin yang bisa akses
check_auth('admin');

$pesan = '';
$pesan_tipe = 'sukses';

// Handle Tambah Menu
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'tambah') {
    $nama_menu = sanitize($_POST['nama_menu'] ?? '');
    $kategori  = sanitize($_POST['kategori'] ?? 'makanan');
    $harga     = (int)($_POST['harga'] ?? 0);
    $deskripsi = sanitize($_POST['deskripsi'] ?? '');
    $status    = sanitize($_POST['status'] ?? 'tersedia');
    $gambar    = '';

    // Handle Upload File Gambar
    if (isset($_FILES['gambar_file']) && $_FILES['gambar_file']['error'] === UPLOAD_ERR_OK) {
        $file_tmp  = $_FILES['gambar_file']['tmp_name'];
        $file_name = $_FILES['gambar_file']['name'];
        $file_size = $_FILES['gambar_file']['size'];
        $file_ext  = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));

        $allowed_ext = ['jpg', 'jpeg', 'png', 'webp'];
        if (in_array($file_ext, $allowed_ext)) {
            // Maksimal ukuran 5 MB
            if ($file_size <= 5 * 1024 * 1024) {
                $new_filename = 'menu_' . time() . '_' . substr(md5(uniqid()), 0, 8) . '.' . $file_ext;
                $upload_dir   = __DIR__ . '/../uploads/';
                if (!is_dir($upload_dir)) {
                    mkdir($upload_dir, 0777, true);
                }
                $destination = $upload_dir . $new_filename;

                if (move_uploaded_file($file_tmp, $destination)) {
                    $gambar = 'uploads/' . $new_filename;
                } else {
                    $pesan = 'Gagal menyimpan file gambar ke folder uploads!';
                    $pesan_tipe = 'gagal';
                }
            } else {
                $pesan = 'Ukuran file gambar maksimal 5MB!';
                $pesan_tipe = 'gagal';
            }
        } else {
            $pesan = 'Format file gambar tidak didukung! Gunakan format JPG, PNG, atau WEBP.';
            $pesan_tipe = 'gagal';
        }
    }

    // Jika tidak ada upload gambar atau upload gagal tapi form tetap diisi, gunakan placeholder default
    if (empty($gambar) && $pesan_tipe !== 'gagal') {
        $gambar = 'https://images.unsplash.com/photo-1546069901-ba9599a7e63c?w=400';
    }

    if (empty($pesan) && !empty($nama_menu) && $harga > 0) {
        $stmt = $pdo->prepare("
            INSERT INTO menus (nama_menu, kategori, harga, deskripsi, gambar, status)
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([$nama_menu, $kategori, $harga, $deskripsi, $gambar, $status]);
        $pesan = "Menu \"$nama_menu\" berhasil ditambahkan!";
        $pesan_tipe = 'sukses';
    } elseif (empty($pesan)) {
        $pesan = 'Nama menu dan harga valid wajib diisi!';
        $pesan_tipe = 'gagal';
    }
}

// Handle Hapus Menu
if (isset($_GET['hapus'])) {
    $id_hapus = (int)$_GET['hapus'];
    try {
        // Ambil info gambar lokal jika ada untuk dihapus fisiknya
        $stmt_img = $pdo->prepare("SELECT gambar FROM menus WHERE id = ?");
        $stmt_img->execute([$id_hapus]);
        $old_img = $stmt_img->fetchColumn();

        $stmt = $pdo->prepare("DELETE FROM menus WHERE id = ?");
        $stmt->execute([$id_hapus]);

        // Hapus file fisik jika tersimpan di folder uploads
        if (!empty($old_img) && strpos($old_img, 'uploads/') === 0) {
            $full_path = __DIR__ . '/../' . $old_img;
            if (file_exists($full_path)) {
                @unlink($full_path);
            }
        }

        $pesan = 'Menu berhasil dihapus dari katalog!';
        $pesan_tipe = 'sukses';
    } catch (Exception $e) {
        $pesan = 'Menu tidak dapat dihapus karena sudah memiliki riwayat pemesanan pelanggan.';
        $pesan_tipe = 'gagal';
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
    <title>Kelola Menu - KanDar</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
    </style>
</head>
<body class="bg-slate-50 text-slate-800 flex flex-col min-h-screen">

    <!-- Navbar Admin Responsive -->
    <header class="sticky top-0 z-40 bg-white/95 backdrop-blur-md border-b border-slate-200">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 py-3 flex items-center justify-between">
            
            <!-- Brand / Logo -->
            <a href="index.php" class="flex items-center space-x-2 sm:space-x-2.5">
                <div class="w-9 h-9 sm:w-10 sm:h-10 rounded-2xl bg-gradient-to-tr from-orange-600 to-amber-500 text-white flex items-center justify-center font-bold text-base sm:text-lg shadow-md shadow-orange-500/20 flex-shrink-0">
                    <i class="fa-solid fa-utensils"></i>
                </div>
                <div>
                    <span class="font-extrabold text-slate-900 text-base sm:text-lg tracking-tight block leading-tight">KanDar</span>
                    <span class="text-[9px] sm:text-[10px] text-orange-600 font-semibold tracking-wider uppercase block">Admin Backoffice</span>
                </div>
            </a>

            <!-- Nav Links Desktop -->
            <div class="hidden md:flex items-center space-x-2 text-xs">
                <!-- Tombol Scan QR Pesanan -->
                <button type="button" onclick="openScannerModal()" class="px-3.5 py-2 bg-orange-500 hover:bg-orange-600 text-white font-bold rounded-xl shadow-md shadow-orange-500/20 active:scale-95 transition flex items-center space-x-1.5">
                    <i class="fa-solid fa-qrcode text-xs"></i>
                    <span>Scan QR</span>
                </button>
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
                    <span>Layar Kasir</span>
                </a>
                <a href="logout.php" onclick="return confirm('Keluar dari sesi Admin?')" class="p-2 text-slate-400 hover:text-rose-600 transition" title="Logout">
                    <i class="fa-solid fa-right-from-bracket text-base"></i>
                </a>
            </div>

            <!-- Tombol Aksi Mobile (Scan QR Cepat + Hamburger Toggle) -->
            <div class="flex items-center space-x-2 md:hidden">
                <button type="button" onclick="openScannerModal()" class="px-3 py-1.5 bg-orange-500 hover:bg-orange-600 text-white font-bold rounded-xl shadow-md shadow-orange-500/20 active:scale-95 transition flex items-center space-x-1 text-xs">
                    <i class="fa-solid fa-qrcode"></i>
                    <span>Scan</span>
                </button>
                <button type="button" onclick="toggleAdminMenu()" class="w-9 h-9 flex items-center justify-center rounded-xl bg-slate-100 text-slate-700 hover:bg-orange-50 hover:text-orange-600 transition text-sm">
                    <i class="fa-solid fa-bars" id="admin-hamburger-icon"></i>
                </button>
            </div>
        </div>

        <!-- Menu Dropdown Mobile Admin -->
        <div id="admin-mobile-menu" class="hidden md:hidden border-t border-slate-100 bg-white/95 px-4 py-3 space-y-2 text-xs font-bold shadow-lg">
            <a href="index.php" class="block py-2.5 px-3 rounded-xl text-slate-700 hover:bg-orange-50 hover:text-orange-600 transition flex items-center space-x-2.5">
                <i class="fa-solid fa-chart-pie text-sm w-4 text-orange-500"></i>
                <span>Overview Dashboard</span>
            </a>
            <a href="menu.php" class="block py-2.5 px-3 rounded-xl bg-orange-50 text-orange-600 flex items-center space-x-2.5">
                <i class="fa-solid fa-bowl-food text-sm w-4"></i>
                <span>Kelola Katalog Menu</span>
            </a>
            <a href="../kasir/index.php" class="block py-2.5 px-3 rounded-xl text-slate-700 hover:bg-orange-50 hover:text-orange-600 transition flex items-center space-x-2.5">
                <i class="fa-solid fa-cash-register text-sm w-4 text-orange-500"></i>
                <span>Layar Kasir & Dapur</span>
            </a>
            <a href="../pembeli/index.php?meja=01" target="_blank" class="block py-2.5 px-3 rounded-xl text-slate-700 hover:bg-orange-50 hover:text-orange-600 transition flex items-center space-x-2.5">
                <i class="fa-solid fa-arrow-up-right-from-square text-sm w-4 text-orange-500"></i>
                <span>Preview Tampilan Pembeli</span>
            </a>
            <div class="pt-2 border-t border-slate-100">
                <a href="logout.php" onclick="return confirm('Keluar dari sesi Admin?')" class="block py-2 px-3 rounded-xl text-rose-600 hover:bg-rose-50 transition flex items-center space-x-2">
                    <i class="fa-solid fa-right-from-bracket w-4"></i>
                    <span>Logout Admin</span>
                </a>
            </div>
        </div>
    </header>

    <main class="max-w-6xl mx-auto px-4 sm:px-6 py-6 sm:py-8 flex-1 w-full space-y-6">

        <!-- Notifikasi Pesan -->
        <?php if (!empty($pesan)): ?>
            <div class="<?= $pesan_tipe === 'sukses' ? 'bg-emerald-50 text-emerald-800 border-emerald-200' : 'bg-rose-50 text-rose-800 border-rose-200' ?> border px-4 py-3 rounded-2xl text-xs font-semibold flex items-center justify-between shadow-sm">
                <div class="flex items-center space-x-2">
                    <i class="fa-solid <?= $pesan_tipe === 'sukses' ? 'fa-circle-check text-emerald-600' : 'fa-circle-exclamation text-rose-600' ?> text-sm"></i>
                    <span><?= htmlspecialchars($pesan) ?></span>
                </div>
                <a href="menu.php" class="text-xs font-bold underline opacity-70 hover:opacity-100">Tutup</a>
            </div>
        <?php endif; ?>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 items-start">
            
            <!-- Form Tambah Menu (Kolom Kiri di Desktop, Atas di Mobile) -->
            <div class="bg-white p-5 sm:p-6 rounded-3xl border border-slate-200 shadow-sm static lg:sticky lg:top-20">
                <div class="pb-4 mb-4 border-b border-slate-100">
                    <span class="text-[10px] sm:text-xs font-bold text-orange-600 uppercase tracking-widest block">Form Input</span>
                    <h2 class="font-black text-base text-slate-900 mt-0.5">Tambah Menu Baru</h2>
                </div>

                <form action="menu.php" method="POST" enctype="multipart/form-data" class="space-y-4 text-xs">
                    <input type="hidden" name="action" value="tambah">

                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Nama Menu:</label>
                        <input type="text" name="nama_menu" placeholder="Contoh: Bebek Goreng Kremes" 
                               class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl font-bold text-slate-800 focus:outline-none focus:border-orange-500 focus:bg-white transition" required>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
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
                        <label class="block font-bold text-slate-700 mb-1">Deskripsi Singkat:</label>
                        <textarea name="deskripsi" rows="2" placeholder="Keterangan bumbu, rasa, atau porsi..." 
                                  class="w-full px-4 py-2 bg-slate-50 border border-slate-200 rounded-xl font-semibold text-slate-800 focus:outline-none focus:border-orange-500 focus:bg-white transition"></textarea>
                    </div>

                    <!-- Input Upload File Gambar dari Komputer -->
                    <div>
                        <label class="block font-bold text-slate-700 mb-1.5">
                            <i class="fa-solid fa-image text-orange-500 mr-1"></i> Foto / Gambar Menu:
                        </label>
                        <div class="relative border-2 border-dashed border-slate-300 hover:border-orange-500 rounded-2xl p-4 text-center cursor-pointer bg-slate-50 hover:bg-orange-50/30 transition group">
                            <input type="file" name="gambar_file" id="gambar_file" accept="image/jpeg,image/png,image/webp,image/jpg" 
                                   class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-10" onchange="previewUploadImage(this)">
                            
                            <!-- Placeholder Awal -->
                            <div id="upload-placeholder" class="space-y-1.5 py-1">
                                <div class="w-10 h-10 rounded-xl bg-orange-100 text-orange-600 flex items-center justify-center mx-auto text-lg group-hover:scale-110 transition shadow-sm">
                                    <i class="fa-solid fa-cloud-arrow-up"></i>
                                </div>
                                <p class="font-bold text-slate-700 text-xs">Pilih foto dari HP / Komputer</p>
                                <p class="text-[10px] text-slate-400">JPG, PNG, atau WEBP (Maks. 5MB)</p>
                            </div>

                            <!-- Preview Setelah Dipilih -->
                            <div id="upload-preview-box" class="hidden flex items-center space-x-3 text-left">
                                <img id="upload-preview-img" src="#" alt="Preview" class="w-14 h-14 rounded-xl object-cover border border-slate-200 bg-white flex-shrink-0 shadow-sm">
                                <div class="min-w-0 flex-1">
                                    <p id="upload-preview-name" class="font-bold text-slate-800 text-xs truncate">nama_file.jpg</p>
                                    <p id="upload-preview-size" class="text-[10px] text-slate-400">0 KB</p>
                                    <span class="inline-block text-[10px] text-orange-600 font-bold mt-0.5">Klik untuk ganti file</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Status Ketersediaan Awal:</label>
                        <select name="status" class="w-full px-3 py-2.5 bg-slate-50 border border-slate-200 rounded-xl font-bold text-slate-800 focus:outline-none focus:border-orange-500 focus:bg-white transition">
                            <option value="tersedia">Tersedia (Ready)</option>
                            <option value="habis">Habis (Kosong)</option>
                        </select>
                    </div>

                    <button type="submit" class="w-full py-3 bg-orange-500 hover:bg-orange-600 text-white font-bold rounded-xl shadow-md shadow-orange-500/20 active:scale-95 transition flex items-center justify-center space-x-1.5 mt-2">
                        <i class="fa-solid fa-plus text-xs"></i>
                        <span>Simpan Menu ke Katalog</span>
                    </button>
                </form>
            </div>

            <!-- Tabel Daftar Menu (Kolom Kanan di Desktop) -->
            <div class="lg:col-span-2 bg-white rounded-3xl border border-slate-200 shadow-sm overflow-hidden">
                <div class="p-4 sm:p-5 border-b border-slate-200 flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                    <div>
                        <span class="text-[10px] sm:text-xs font-bold text-orange-600 uppercase tracking-widest">Katalog Resto</span>
                        <h2 class="font-black text-sm sm:text-base text-slate-900 mt-0.5">Daftar Menu Tersedia (<?= count($menus) ?>)</h2>
                    </div>
                    <span class="text-[11px] text-slate-400 font-semibold">Klik status untuk mengubah stok</span>
                </div>

                <div class="overflow-x-auto" style="-webkit-overflow-scrolling: touch;">
                    <table class="w-full text-left text-xs min-w-[560px]">
                        <thead class="bg-slate-50 text-slate-500 uppercase border-b border-slate-200 font-bold text-[11px]">
                            <tr>
                                <th class="py-3 px-4">Menu & Foto</th>
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
                                        <td class="py-3 px-4">
                                            <div class="flex items-center space-x-3">
                                                <img src="<?= htmlspecialchars(get_menu_image($m['gambar'])) ?>" 
                                                     alt="<?= htmlspecialchars($m['nama_menu']) ?>"
                                                     class="w-12 h-12 rounded-xl object-cover bg-slate-100 border border-slate-200 flex-shrink-0 shadow-sm"
                                                     onerror="this.src='https://images.unsplash.com/photo-1546069901-ba9599a7e63c?w=100'">
                                                <div class="min-w-0">
                                                    <p class="font-bold text-slate-900 truncate"><?= htmlspecialchars($m['nama_menu']) ?></p>
                                                    <p class="text-[11px] text-slate-400 truncate max-w-xs mt-0.5"><?= htmlspecialchars($m['deskripsi'] ?: '-') ?></p>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="py-3 px-3">
                                            <span class="inline-block px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-orange-50 text-orange-600 border border-orange-200 uppercase">
                                                <?= htmlspecialchars($m['kategori']) ?>
                                            </span>
                                        </td>
                                        <td class="py-3 px-3 font-black text-slate-900">
                                            <?= format_rupiah($m['harga']) ?>
                                        </td>
                                        <td class="py-3 px-3 text-center">
                                            <a href="menu.php?toggle_status=<?= $m['id'] ?>&current=<?= $m['status'] ?>" 
                                               title="Klik untuk ubah status ketersediaan"
                                               class="inline-block px-3 py-1 rounded-lg text-[10px] font-bold transition shadow-sm <?= $m['status'] === 'tersedia' ? 'bg-orange-500 hover:bg-orange-600 text-white' : 'bg-slate-100 hover:bg-slate-200 text-slate-500 border border-slate-200' ?>">
                                                <?= strtoupper($m['status']) ?>
                                            </a>
                                        </td>
                                        <td class="py-3 px-4 text-right">
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

    <!-- Footer Cerah & Bersih (Bukan Gelap) -->
    <footer class="bg-white text-slate-500 mt-auto pt-6 pb-6 border-t border-slate-200">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 flex flex-col sm:flex-row items-center justify-between text-[11px] text-slate-500 gap-2 text-center sm:text-left">
            <span>&copy; <?= date('Y') ?> KanDar. All rights reserved.</span>
            <span class="font-semibold text-orange-600">Panel Khusus Administrator Resto</span>
        </div>
    </footer>

    <!-- Script Toggle Navigasi Mobile Admin & Preview Upload -->
    <script>
        function toggleAdminMenu() {
            const menu = document.getElementById('admin-mobile-menu');
            const icon = document.getElementById('admin-hamburger-icon');
            if (menu.classList.contains('hidden')) {
                menu.classList.remove('hidden');
                icon.className = 'fa-solid fa-xmark text-orange-600';
            } else {
                menu.classList.add('hidden');
                icon.className = 'fa-solid fa-bars';
            }
        }

        function previewUploadImage(input) {
            const placeholder = document.getElementById('upload-placeholder');
            const previewBox = document.getElementById('upload-preview-box');
            const previewImg = document.getElementById('upload-preview-img');
            const previewName = document.getElementById('upload-preview-name');
            const previewSize = document.getElementById('upload-preview-size');

            if (input.files && input.files[0]) {
                const file = input.files[0];
                previewName.innerText = file.name;
                previewSize.innerText = (file.size / 1024).toFixed(1) + ' KB';

                const reader = new FileReader();
                reader.onload = function(e) {
                    previewImg.src = e.target.result;
                    placeholder.classList.add('hidden');
                    previewBox.classList.remove('hidden');
                }
                reader.readAsDataURL(file);
            }
        }
    </script>

    <!-- Modal Scanner QR Code -->
    <?php include_once __DIR__ . '/../components/scanner_modal.php'; ?>
</body>
</html>
