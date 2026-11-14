-- Database: resto_db
-- Dibuat untuk sistem pemesanan makanan self-service & kasir (PHP Native)

CREATE DATABASE IF NOT EXISTS `resto_db` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `resto_db`;

-- 1. Tabel Users (Untuk login Kasir dan Admin)
DROP TABLE IF EXISTS `order_items`;
DROP TABLE IF EXISTS `orders`;
DROP TABLE IF EXISTS `menus`;
DROP TABLE IF EXISTS `users`;

CREATE TABLE `users` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `username` VARCHAR(50) NOT NULL UNIQUE,
    `password` VARCHAR(255) NOT NULL,
    `nama_lengkap` VARCHAR(100) NOT NULL,
    `role` ENUM('admin', 'kasir') NOT NULL DEFAULT 'kasir',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- 2. Tabel Menus (Katalog Makanan, Minuman, & Cemilan)
CREATE TABLE `menus` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `nama_menu` VARCHAR(100) NOT NULL,
    `kategori` ENUM('makanan', 'minuman', 'snack') NOT NULL DEFAULT 'makanan',
    `harga` INT NOT NULL,
    `deskripsi` TEXT NULL,
    `gambar` VARCHAR(255) NULL,
    `status` ENUM('tersedia', 'habis') NOT NULL DEFAULT 'tersedia',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- 3. Tabel Orders (Data Pesanan Pelanggan)
CREATE TABLE `orders` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `kode_pesanan` VARCHAR(30) NOT NULL UNIQUE,
    `nomor_meja` VARCHAR(10) NOT NULL,
    `nama_pelanggan` VARCHAR(100) NOT NULL DEFAULT 'Pelanggan Meja',
    `total_harga` INT NOT NULL DEFAULT 0,
    `status_pembayaran` ENUM('belum_bayar', 'lunas') NOT NULL DEFAULT 'belum_bayar',
    `status_pesanan` ENUM('menunggu', 'dimasak', 'selesai', 'dibatalkan') NOT NULL DEFAULT 'menunggu',
    `metode_pembayaran` VARCHAR(50) NULL DEFAULT 'Tunai / Kasir',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- 4. Tabel Order Items (Rincian Menu yang Dipesan)
CREATE TABLE `order_items` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `order_id` INT NOT NULL,
    `menu_id` INT NOT NULL,
    `jumlah` INT NOT NULL DEFAULT 1,
    `harga_satuan` INT NOT NULL,
    `subtotal` INT NOT NULL,
    `catatan` VARCHAR(255) NULL,
    CONSTRAINT `fk_order_items_order` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_order_items_menu` FOREIGN KEY (`menu_id`) REFERENCES `menus` (`id`) ON DELETE RESTRICT
) ENGINE=InnoDB;

-- ========================================================
-- DATA AWAL (SEED DUMMY DATA BIAR LANGSUNG BISA DITEST)
-- ========================================================

-- Akun Default (Password: admin123 & kasir123 menggunakan MD5 untuk kesederhanaan hackathon)
INSERT INTO `users` (`username`, `password`, `nama_lengkap`, `role`) VALUES
('admin', MD5('admin123'), 'Administrator Utama', 'admin'),
('kasir', MD5('kasir123'), 'Kasir Shift 1', 'kasir');

-- Data Menu Contoh
INSERT INTO `menus` (`nama_menu`, `kategori`, `harga`, `deskripsi`, `gambar`, `status`) VALUES
('Nasi Goreng Spesial Telur', 'makanan', 25000, 'Nasi goreng dengan bumbu rempah pilihan, suwiran ayam, dan telur ceplok.', 'https://images.unsplash.com/photo-1603133872878-684f208fb84b?w=400&q=80', 'tersedia'),
('Ayam Bakar Madu + Nasi', 'makanan', 32000, 'Ayam bakar legit manis gurih disajikan lengkap dengan sambal terasi dan lalapan.', 'https://images.unsplash.com/photo-1598515214211-89d3c73ae83b?w=400&q=80', 'tersedia'),
('Mie Goreng Seafood', 'makanan', 28000, 'Mie kenyal ditumis dengan udang, cumi segar, dan sayuran renyah.', 'https://images.unsplash.com/photo-1585032226651-759b368d7246?w=400&q=80', 'tersedia'),
('Kentang Goreng Keju', 'snack', 16000, 'French fries renyah bertabur bumbu keju gurih spesial.', 'https://images.unsplash.com/photo-1576107232684-1279f3908594?w=400&q=80', 'tersedia'),
('Pisang Bakar Coklat Keju', 'snack', 18000, 'Pisang bakar manis dengan taburan cokelat leleh dan keju parut melimpah.', 'https://images.unsplash.com/photo-1528735602780-2552fd46c7af?w=400&q=80', 'tersedia'),
('Es Teh Manis Segar', 'minuman', 5000, 'Teh melati wangi dengan es batu menyegarkan dahaga.', 'https://images.unsplash.com/photo-1556679343-c7306c1976bc?w=400&q=80', 'tersedia'),
('Kopi Susu Gula Aren', 'minuman', 18000, 'Espresso arabika dipadu susu creamy dan manis legit gula aren asli.', 'https://images.unsplash.com/photo-1541167760496-1628856ab772?w=400&q=80', 'tersedia'),
('Lemon Tea Dingin', 'minuman', 12000, 'Perpaduan segar sari lemon asli dengan seduhan teh premium.', 'https://images.unsplash.com/photo-1513558161293-cdaf765ed2fd?w=400&q=80', 'tersedia');

-- Contoh Pesanan Dummy (Biar Dashboard Kasir langsung ada isi saat pertama kali dibuka)
INSERT INTO `orders` (`id`, `kode_pesanan`, `nomor_meja`, `nama_pelanggan`, `total_harga`, `status_pembayaran`, `status_pesanan`, `metode_pembayaran`) VALUES
(1, 'ORD-20261113-001', '03', 'Budi Santoso', 55000, 'belum_bayar', 'menunggu', 'Tunai / Kasir');

INSERT INTO `order_items` (`order_id`, `menu_id`, `jumlah`, `harga_satuan`, `subtotal`, `catatan`) VALUES
(1, 1, 1, 25000, 25000, 'Pedas sedang, telur setengah matang'),
(1, 2, 1, 30000, 30000, 'Nasi dipisah');

