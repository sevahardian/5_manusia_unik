# 📋 Panduan Tim & Pembagian Tugas (PHP Native)
**Sistem Self-Service Pemesanan Meja (QR Code) & Dashboard Kasir**

> 💡 **Prinsip Utama:** Menggunakan **PHP Native (Murni)** dengan basis modular (folder terpisah) agar 3 programmer bisa koding paralel tanpa sering *conflict* di Git, serta mudah dijalankan di XAMPP/Laragon.

---

## 🗂️ 1. Struktur Folder Project (PHP Native)

Rekomendasi struktur direktori yang modular dan terisolasi per peran:

```text
5_manusia_unik/
│
├── config/
│   ├── koneksi.php          # Koneksi database (PDO / mysqli_connect)
│   └── helper.php           # Fungsi bantu (format rupiah, sanitize input, redirect)
│
├── database/
│   └── database.sql         # Script tabel tinggal import di phpMyAdmin
│
├── assets/
│   ├── css/
│   └── js/                  # (Bisa pakai CDN Tailwind CSS untuk styling cepat)
│
├── pembeli/                 # 👤 PIC: PROGRAMMER 2 (Frontend Pembeli)
│   ├── index.php            # Tampilan menu per meja (cth: ?meja=05)
│   ├── cart.php             # Review keranjang & submit pesanan
│   └── status.php           # Halaman nota & status pesanan (Menunggu/Diproses/Selesai)
│
├── kasir/                   # 👤 PIC: PROGRAMMER 3 (Dashboard Kasir & Cetak)
│   ├── index.php            # Dashboard monitor pesanan masuk (Auto-refresh/Polling)
│   ├── update_status.php    # Aksi ganti status (Lunas/Dapur/Selesai)
│   └── cetak_struk.php      # Halaman cetak struk mini/tiket dapur (window.print)
│
├── admin/                   # 👤 PIC: PROGRAMMER 1 (Admin & Master Data)
│   ├── login.php            # Login kasir/admin sederhana (session)
│   ├── logout.php           # Hapus session
│   └── menu.php             # Kelola data menu (Tambah, Edit, Hapus)
│
├── api/                     # 👤 PIC: PROGRAMMER 1 & 2 (Komunikasi Data JSON/Form)
│   ├── submit_order.php     # Simpan pesanan & detail pesanan ke DB
│   └── get_orders.php       # Mengambil data pesanan terbaru untuk polling kasir
│
└── index.php                # Redirect otomatis ke pembeli/ atau kasir/
```

---

## 👥 2. Pembagian Tugas 3 Programmer

### 👤 Bagian 1: Programmer 1 (Database, Core Config & Data Handler)
**Fokus:** Otak sistem, alur data pesanan, dan manajemen menu.

* **Tugas Spesifik:**
  1. **Setup Database (`database/database.sql`):**
     * Buat tabel: `users`, `menus`, `orders`, `order_items`.
  2. **File Koneksi & Helper (`config/koneksi.php` & `config/helper.php`):**
     * Koneksi database tunggal yang di-`include` ke semua file.
     * Fungsi `session_start()` aman, format mata uang `rupiah()`, dan pembersih input anti-SQL injection (`mysqli_real_escape_string` / PDO prepared statement).
  3. **Backend API / Action Handler (`api/`):**
     * File `api/submit_order.php`: Menangkap data pesanan dari form pembeli (no meja, daftar menu, jumlah, catatan), lalu masukkan ke tabel `orders` dan `order_items` dalam satu transaksi.
     * File `api/get_orders.php`: Mengembalikan data pesanan terbaru dalam format JSON untuk kebutuhan dashboard kasir.
  4. **CRUD Menu (`admin/menu.php`):**
     * Halaman sederhana untuk tambah menu makanan/minuman, foto/gambar, harga, dan ketersediaan stok (*ready*/*habis*).

---

### 👤 Bagian 2: Programmer 2 (Tampilan Pembeli / Self-Service QR)
**Fokus:** Tampilan antarmuka pembeli yang responsif (*mobile-first*), pemilihan menu, dan pemesanan.

* **Tugas Spesifik:**
  1. **Deteksi Meja (`pembeli/index.php`):**
     * Tangkap nomor meja dari parameter URL (contoh: `pembeli/index.php?meja=03`).
     * Simpan nomor meja ke `$_SESSION['meja']` atau `localStorage` agar tidak hilang saat berpindah halaman.
  2. **Katalog & Filter Menu:**
     * Menampilkan daftar menu dalam bentuk kartu (*card*) modern (manfaatkan Tailwind CSS CDN).
     * Filter kategori (Makanan, Minuman, Cemilan).
  3. **Keranjang Belanja (*Cart*):**
     * Tombol tambah/kurang jumlah pesanan (bisa pakai JavaScript sederhana / LocalStorage).
     * Modal atau halaman `pembeli/cart.php` untuk melihat ringkasan item yang dipilih dan total tagihan.
  4. **Submit Pesanan & Notifikasi:**
     * Kirim data order ke `api/submit_order.php` menggunakan `fetch()` JS atau form POST biasa.
     * Redirect pembeli ke `pembeli/status.php` dengan parameter ID pesanan untuk melihat status pesanan mereka.

---

### 👤 Bagian 3: Programmer 3 (Dashboard Kasir & Cetak Struk)
**Fokus:** Monitoring pesanan masuk secara langsung, perubahan status pembayaran/dapur, dan cetak fisik.

* **Tugas Spesifik:**
  1. **Dashboard Monitor Pesanan (`kasir/index.php`):**
     * Tampilan daftar pesanan masuk dengan informasi: **Nomor Meja**, Jam Order, Daftar Menu, Total Harga, dan Status (Menunggu Pembayaran / Diproses / Selesai).
     * Berikan fitur **Auto-Refresh** (Polling tiap 5–10 detik dengan `setInterval(fetch, 5000)` atau meta refresh) agar kasir langsung tahu saat ada pembeli memesan.
  2. **Kontrol Status (`kasir/update_status.php`):**
     * Tombol cepat untuk mengubah status: *Sudah Bayar*, *Sedang Dimasak*, atau *Selesai*.
  3. **Fitur Cetak Struk & Tiket Dapur (`kasir/cetak_struk.php`):**
     * Layout kertas struk ukuran thermal 58mm / 80mm.
     * Gunakan CSS `@media print` untuk menyembunyikan navigasi dan tombol.
     * Pemicu cetak otomatis dengan JavaScript: `<script>window.print();</script>`.

---

## 🗄️ 3. Skema Database Cepat (`database.sql`)

Bisa langsung di-copy dan di-import di phpMyAdmin untuk mempercepat kerja:

```sql
CREATE DATABASE IF NOT EXISTS resto_db;
USE resto_db;

-- Tabel Pengguna (Kasir & Admin)
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin', 'kasir') DEFAULT 'kasir'
);

-- Tabel Menu Makanan / Minuman
CREATE TABLE menus (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nama_menu VARCHAR(100) NOT NULL,
    kategori ENUM('makanan', 'minuman', 'snack') NOT NULL,
    harga INT NOT NULL,
    gambar VARCHAR(255) NULL,
    status ENUM('tersedia', 'habis') DEFAULT 'tersedia'
);

-- Tabel Pesanan Utama
CREATE TABLE orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    kode_pesanan VARCHAR(20) NOT NULL,
    nomor_meja VARCHAR(10) NOT NULL,
    nama_pelanggan VARCHAR(50) DEFAULT 'Tamu',
    total_harga INT NOT NULL,
    status_pembayaran ENUM('belum_bayar', 'lunas') DEFAULT 'belum_bayar',
    status_pesanan ENUM('menunggu', 'dimasak', 'selesai') DEFAULT 'menunggu',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tabel Rincian Menu yang Dipesan
CREATE TABLE order_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    menu_id INT NOT NULL,
    jumlah INT NOT NULL,
    harga_satuan INT NOT NULL,
    subtotal INT NOT NULL,
    catatan VARCHAR(255) NULL,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    FOREIGN KEY (menu_id) REFERENCES menus(id)
);

-- Data Dummy Awal
INSERT INTO users (username, password, role) VALUES ('kasir', MD5('kasir123'), 'kasir');
INSERT INTO menus (nama_menu, kategori, harga, status) VALUES 
('Nasi Goreng Spesial', 'makanan', 25000, 'tersedia'),
('Ayam Bakar Madu', 'makanan', 30000, 'tersedia'),
('Es Teh Manis', 'minuman', 5000, 'tersedia'),
('Kopi Susu Gula Aren', 'minuman', 18000, 'tersedia');
```

---

## ⚡ 4. Tips Hackathon Biar Gak Tabrakan Koding
1. **Gunakan CDN untuk UI:** Gunakan `<script src="https://cdn.tailwindcss.com"></script>` di tag `<head>`, tidak perlu install npm/Vite.
2. **Koneksi Tunggal:** Selalu panggil `require_once '../config/koneksi.php';` di awal file masing-masing.
3. **Pemisahan Folder Bersih:** 
   - Programmer 1 fokus di folder `admin/`, `api/`, dan `config/`.
   - Programmer 2 fokus di folder `pembeli/`.
   - Programmer 3 fokus di folder `kasir/`.
   *(Dengan begini, saat commit/push git tidak akan terjadi merge conflict).*


### 👤 Bagian 3: Programmer 3 (Dashboard Kasir & Cetak Struk)
**Fokus:** Monitoring pesanan masuk secara langsung, perubahan status pembayaran/dapur, dan cetak fisik.

* **Tugas Spesifik:**
  1. **Dashboard Monitor Pesanan (`kasir/index.php`):**
     * Tampilan daftar pesanan masuk dengan informasi: **Nomor Meja**, Jam Order, Daftar Menu, Total Harga, dan Status (Menunggu Pembayaran / Diproses / Selesai).
     * Berikan fitur **Auto-Refresh** (Polling tiap 5–10 detik dengan `setInterval(fetch, 5000)` atau meta refresh) agar kasir langsung tahu saat ada pembeli memesan.
  2. **Kontrol Status (`kasir/update_status.php`):**
     * Tombol cepat untuk mengubah status: *Sudah Bayar*, *Sedang Dimasak*, atau *Selesai*.
  3. **Fitur Cetak Struk & Tiket Dapur (`kasir/cetak_struk.php`):**
     * Layout kertas struk ukuran thermal 58mm / 80mm.
     * Gunakan CSS `@media print` untuk menyembunyikan navigasi dan tombol.
     * Pemicu cetak otomatis dengan JavaScript: `<script>window.print();</script>`.

---

## 🗄️ 3. Skema Database Cepat (`database.sql`)

Bisa langsung di-copy dan di-import di phpMyAdmin untuk mempercepat kerja:

```sql
CREATE DATABASE IF NOT EXISTS resto_db;
USE resto_db;

-- Tabel Pengguna (Kasir & Admin)
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin', 'kasir') DEFAULT 'kasir'
);

-- Tabel Menu Makanan / Minuman
CREATE TABLE menus (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nama_menu VARCHAR(100) NOT NULL,
    kategori ENUM('makanan', 'minuman', 'snack') NOT NULL,
    harga INT NOT NULL,
    gambar VARCHAR(255) NULL,
    status ENUM('tersedia', 'habis') DEFAULT 'tersedia'
);

-- Tabel Pesanan Utama
CREATE TABLE orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    kode_pesanan VARCHAR(20) NOT NULL,
    nomor_meja VARCHAR(10) NOT NULL,
    nama_pelanggan VARCHAR(50) DEFAULT 'Tamu',
    total_harga INT NOT NULL,
    status_pembayaran ENUM('belum_bayar', 'lunas') DEFAULT 'belum_bayar',
    status_pesanan ENUM('menunggu', 'dimasak', 'selesai') DEFAULT 'menunggu',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tabel Rincian Menu yang Dipesan
CREATE TABLE order_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    menu_id INT NOT NULL,
    jumlah INT NOT NULL,
    harga_satuan INT NOT NULL,
    subtotal INT NOT NULL,
    catatan VARCHAR(255) NULL,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    FOREIGN KEY (menu_id) REFERENCES menus(id)
);

-- Data Dummy Awal
INSERT INTO users (username, password, role) VALUES ('kasir', MD5('kasir123'), 'kasir');
INSERT INTO menus (nama_menu, kategori, harga, status) VALUES 
('Nasi Goreng Spesial', 'makanan', 25000, 'tersedia'),
('Ayam Bakar Madu', 'makanan', 30000, 'tersedia'),
('Es Teh Manis', 'minuman', 5000, 'tersedia'),
('Kopi Susu Gula Aren', 'minuman', 18000, 'tersedia');
```

---

## ⚡ 4. Tips Hackathon Biar Gak Tabrakan Koding
1. **Gunakan CDN untuk UI:** Gunakan `<script src="https://cdn.tailwindcss.com"></script>` di tag `<head>`, tidak perlu install npm/Vite.
2. **Koneksi Tunggal:** Selalu panggil `require_once '../config/koneksi.php';` di awal file masing-masing.
3. **Pemisahan Folder Bersih:** 
   - Programmer 1 fokus di folder `admin/`, `api/`, dan `config/`.
   - Programmer 2 fokus di folder `pembeli/`.
   - Programmer 3 fokus di folder `kasir/`.
   *(Dengan begini, saat commit/push git tidak akan terjadi merge conflict).*