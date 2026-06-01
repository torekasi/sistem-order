-- =========================================================
-- SISTEM ORDER - Database Schema
-- Tarikh: 2026-03-22
-- =========================================================

CREATE DATABASE IF NOT EXISTS sistem_order 
    CHARACTER SET utf8mb4 
    COLLATE utf8mb4_unicode_ci;

USE sistem_order;

-- =========================================================
-- JADUAL: users
-- Semua pengguna (admin, staff, customer, buyer)
-- =========================================================
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nama VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    telefon VARCHAR(20) DEFAULT NULL,
    kata_laluan VARCHAR(255) NOT NULL,
    role ENUM('superadmin', 'admin', 'staff', 'customer', 'buyer') NOT NULL DEFAULT 'customer',
    status ENUM('aktif', 'tidak_aktif') NOT NULL DEFAULT 'aktif',
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_role (role),
    INDEX idx_email (email)
) ENGINE=InnoDB;

-- =========================================================
-- JADUAL: categories
-- Kategori menu (Makanan, Minuman, dll.)
-- =========================================================
CREATE TABLE categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nama VARCHAR(100) NOT NULL,
    penerangan TEXT DEFAULT NULL,
    gambar VARCHAR(255) DEFAULT NULL,
    status ENUM('aktif', 'tidak_aktif') NOT NULL DEFAULT 'aktif',
    urutan INT NOT NULL DEFAULT 0,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- =========================================================
-- JADUAL: menu_items
-- Item menu dengan harga, gambar, status
-- =========================================================
CREATE TABLE menu_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    category_id INT NOT NULL,
    nama VARCHAR(150) NOT NULL,
    penerangan TEXT DEFAULT NULL,
    harga DECIMAL(10,2) NOT NULL,
    gambar VARCHAR(255) DEFAULT NULL,
    status ENUM('tersedia', 'habis', 'tidak_aktif') NOT NULL DEFAULT 'tersedia',
    popular TINYINT(1) NOT NULL DEFAULT 0,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE CASCADE,
    INDEX idx_category (category_id),
    INDEX idx_status (status)
) ENGINE=InnoDB;

-- =========================================================
-- JADUAL: orders
-- Pesanan pelanggan
-- =========================================================
CREATE TABLE orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    no_pesanan VARCHAR(20) NOT NULL UNIQUE,
    customer_id INT DEFAULT NULL,
    staff_id INT DEFAULT NULL,
    nama_pelanggan VARCHAR(100) NOT NULL,
    no_meja VARCHAR(20) DEFAULT NULL,
    jumlah_harga DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    status ENUM('pending', 'confirmed', 'preparing', 'ready', 'completed', 'cancelled') NOT NULL DEFAULT 'pending',
    nota TEXT DEFAULT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (customer_id) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (staff_id) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_status (status),
    INDEX idx_no_pesanan (no_pesanan),
    INDEX idx_created (created_at)
) ENGINE=InnoDB;

-- =========================================================
-- JADUAL: order_items
-- Item dalam setiap pesanan
-- =========================================================
CREATE TABLE order_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    menu_item_id INT DEFAULT NULL,
    nama_item VARCHAR(150) NOT NULL,
    kuantiti INT NOT NULL DEFAULT 1,
    harga_seunit DECIMAL(10,2) NOT NULL,
    jumlah DECIMAL(10,2) NOT NULL,
    nota VARCHAR(255) DEFAULT NULL,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    INDEX idx_order (order_id),
    INDEX idx_menu_item (menu_item_id)
) ENGINE=InnoDB;

-- =========================================================
-- JADUAL: payments
-- Rekod bayaran
-- =========================================================
CREATE TABLE payments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    jumlah DECIMAL(10,2) NOT NULL,
    kaedah ENUM('tunai', 'online', 'qr') NOT NULL DEFAULT 'tunai',
    status ENUM('pending', 'berjaya', 'gagal', 'dibatalkan') NOT NULL DEFAULT 'pending',
    rujukan VARCHAR(100) DEFAULT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    INDEX idx_order (order_id),
    INDEX idx_status (status)
) ENGINE=InnoDB;

-- =========================================================
-- JADUAL: ingredients
-- Senarai bahan mentah untuk setiap menu item
-- =========================================================
CREATE TABLE ingredients (
    id INT AUTO_INCREMENT PRIMARY KEY,
    menu_item_id INT NOT NULL,
    nama VARCHAR(100) NOT NULL,
    kuantiti DECIMAL(10,2) NOT NULL DEFAULT 1,
    unit VARCHAR(20) NOT NULL DEFAULT 'unit',
    FOREIGN KEY (menu_item_id) REFERENCES menu_items(id) ON DELETE CASCADE,
    INDEX idx_menu_item (menu_item_id)
) ENGINE=InnoDB;

-- =========================================================
-- JADUAL: grocery_lists
-- Senarai belanja pasar (auto-generate / manual)
-- =========================================================
CREATE TABLE grocery_lists (
    id INT AUTO_INCREMENT PRIMARY KEY,
    tajuk VARCHAR(150) NOT NULL,
    created_by INT NOT NULL,
    tarikh_belanja DATE NOT NULL,
    status ENUM('aktif', 'selesai', 'dibatalkan') NOT NULL DEFAULT 'aktif',
    jumlah_anggaran DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    jumlah_sebenar DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    nota TEXT DEFAULT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    completed_at DATETIME DEFAULT NULL,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_status (status),
    INDEX idx_tarikh (tarikh_belanja)
) ENGINE=InnoDB;

-- =========================================================
-- JADUAL: grocery_items
-- Item dalam senarai belanja (checklist)
-- =========================================================
CREATE TABLE grocery_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    list_id INT NOT NULL,
    nama VARCHAR(100) NOT NULL,
    kuantiti DECIMAL(10,2) NOT NULL DEFAULT 1,
    unit VARCHAR(20) NOT NULL DEFAULT 'unit',
    harga_anggaran DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    harga_sebenar DECIMAL(10,2) DEFAULT NULL,
    checked TINYINT(1) NOT NULL DEFAULT 0,
    nota VARCHAR(255) DEFAULT NULL,
    FOREIGN KEY (list_id) REFERENCES grocery_lists(id) ON DELETE CASCADE,
    INDEX idx_list (list_id)
) ENGINE=InnoDB;

-- =========================================================
-- JADUAL: activity_logs
-- Log aktiviti admin/staff
-- =========================================================
CREATE TABLE activity_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    aksi VARCHAR(50) NOT NULL,
    penerangan TEXT NOT NULL,
    ip_address VARCHAR(45) DEFAULT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user (user_id),
    INDEX idx_created (created_at)
) ENGINE=InnoDB;

-- =========================================================
-- JADUAL: system_settings
-- Tetapan sistem yang boleh dikonfigurasi oleh super admin
-- =========================================================
CREATE TABLE system_settings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    setting_key VARCHAR(100) NOT NULL UNIQUE,
    setting_value TEXT DEFAULT NULL,
    setting_group VARCHAR(50) NOT NULL DEFAULT 'general',
    setting_type ENUM('text', 'number', 'boolean', 'select', 'textarea', 'password', 'image') NOT NULL DEFAULT 'text',
    setting_label VARCHAR(150) NOT NULL,
    setting_description VARCHAR(255) DEFAULT NULL,
    setting_options TEXT DEFAULT NULL,
    urutan INT NOT NULL DEFAULT 0,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_group (setting_group),
    INDEX idx_key (setting_key)
) ENGINE=InnoDB;

-- =========================================================
-- DATA AWAL: Super Admin default
-- =========================================================
INSERT INTO users (nama, email, kata_laluan, role, status) VALUES
('Super Admin', 'superadmin@sistemorder.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'superadmin', 'aktif'),
('Admin', 'admin@sistemorder.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', 'aktif');
-- Kata laluan default: password

-- =========================================================
-- DATA AWAL: Tetapan sistem
-- =========================================================
INSERT INTO system_settings (setting_key, setting_value, setting_group, setting_type, setting_label, setting_description, setting_options, urutan) VALUES
-- Pangkalan Data
('db_host', 'localhost', 'database', 'text', 'DB Host', 'Alamat server pangkalan data', NULL, 1),
('db_name', 'sistem_order', 'database', 'text', 'DB Name', 'Nama pangkalan data', NULL, 2),
('db_user', 'root', 'database', 'text', 'DB User', 'Nama pengguna pangkalan data', NULL, 3),
('db_pass', '', 'database', 'password', 'DB Password', 'Kata laluan pangkalan data', NULL, 4),
-- Aplikasi
('app_name', 'Sistem Order', 'application', 'text', 'Nama Aplikasi', 'Nama yang dipaparkan di header', NULL, 1),
('app_timezone', 'Asia/Kuala_Lumpur', 'application', 'select', 'Zon Masa', 'Zon masa server', 'Asia/Kuala_Lumpur,Asia/Singapore,Asia/Jakarta,UTC', 3),
('app_currency', 'RM', 'application', 'text', 'Simbol Mata Wang', 'Simbol wang yang dipaparkan', NULL, 4),
('app_tax_rate', '0', 'application', 'number', 'Kadar Cukai (%)', 'Kadar cukai jualan (0 untuk tiada cukai)', NULL, 5),
-- Pesanan
('order_auto_confirm', '0', 'order', 'boolean', 'Auto Sahkan Pesanan', 'Sahkan pesanan secara automatik tanpa perlu staff', NULL, 1),
('order_prefix', 'ORD', 'order', 'text', 'Prefix Pesanan', 'Awalan untuk nombor pesanan', NULL, 2),
('kitchen_auto_refresh', '15', 'order', 'number', 'Auto Refresh Dapur (saat)', 'Selang masa auto refresh dashboard dapur', NULL, 3),
('order_allow_table', '1', 'order', 'boolean', 'Benarkan Pengisian No Meja', 'Sama ada pelanggan dan staf boleh memasukkan No Meja.', NULL, 4),
-- Bayaran
('payment_cash_enabled', '1', 'payment', 'boolean', 'Bayaran Tunai', 'Benarkan bayaran tunai', NULL, 1),
('payment_qr_enabled', '1', 'payment', 'boolean', 'Bayaran QR', 'Benarkan bayaran QR', NULL, 2),
('payment_qr_image', '', 'payment', 'image', 'Gambar QR Code', 'Muat naik fail gambar (PNG/JPG/WEBP). Saiz bawah 2MB.', NULL, 3),
-- Kedai
('store_name', 'Kedai Makan', 'store', 'text', 'Nama Kedai', 'Nama kedai yang dipaparkan', NULL, 1),
('store_address', '', 'store', 'textarea', 'Alamat Kedai', 'Alamat penuh kedai', NULL, 2),
('store_phone', '', 'store', 'text', 'No. Telefon', 'Nombor telefon kedai', NULL, 3),
('store_logo', '', 'store', 'text', 'Logo URL', 'URL atau path ke logo kedai', NULL, 4);

-- =========================================================
-- DATA AWAL: Kategori (Burger Stall)
-- =========================================================
INSERT INTO categories (nama, penerangan, urutan) VALUES
('Burger', 'Pelbagai jenis burger segar', 1),
('Set Kombo', 'Kombo burger + fries + minuman', 2),
('Sampingan', 'Fries, nugget, dan lain-lain', 3),
('Minuman', 'Minuman panas dan sejuk', 4);

-- =========================================================
-- DATA AWAL: Menu Burger Stall
-- =========================================================
INSERT INTO menu_items (category_id, nama, penerangan, harga, status, popular) VALUES
-- Burger
(1, 'Burger Daging Classic', 'Patty daging lembu 100%, salad, tomato, mayo & sos istimewa', 8.00, 'tersedia', 1),
(1, 'Burger Daging Double', 'Double patty daging lembu, keju cheddar, salad & sos', 12.00, 'tersedia', 1),
(1, 'Burger Ayam Crispy', 'Fillet ayam goreng crispy dengan coleslaw & mayo', 7.50, 'tersedia', 1),
(1, 'Burger Ayam Spicy', 'Fillet ayam berempah pedas dengan jalapeno & sos cili', 8.50, 'tersedia', 0),
(1, 'Burger Ikan', 'Fillet ikan goreng crispy dengan tartar sauce', 7.00, 'tersedia', 0),
(1, 'Burger Telur Special', 'Patty daging dengan telur mata, keju & sos BBQ', 9.50, 'tersedia', 1),
(1, 'Burger Kambing', 'Patty kambing bakar dengan bawang karamel & sos lada hitam', 13.00, 'tersedia', 0),
-- Set Kombo
(2, 'Kombo Classic', 'Burger Daging Classic + Fries + Air Sirap', 13.00, 'tersedia', 1),
(2, 'Kombo Double', 'Burger Daging Double + Fries + Milo Ais', 17.00, 'tersedia', 1),
(2, 'Kombo Ayam', 'Burger Ayam Crispy + Fries + Teh Ais', 12.50, 'tersedia', 0),
(2, 'Kombo Spicy', 'Burger Ayam Spicy + Wedges + Air Sirap', 14.00, 'tersedia', 0),
-- Sampingan
(3, 'French Fries', 'Kentang goreng rangup (biasa)', 4.00, 'tersedia', 1),
(3, 'Cheese Fries', 'Kentang goreng dengan sos keju cair', 6.00, 'tersedia', 1),
(3, 'Potato Wedges', 'Kentang wedges berempah', 5.00, 'tersedia', 0),
(3, 'Chicken Nugget (6 pcs)', 'Nugget ayam rangup 6 keping', 5.50, 'tersedia', 0),
(3, 'Onion Rings', 'Bawang goreng rangup', 4.50, 'tersedia', 0),
(3, 'Coleslaw', 'Salad kubis segar dengan mayo', 3.00, 'tersedia', 0),
-- Minuman
(4, 'Teh Ais', 'Teh ais limau segar', 2.50, 'tersedia', 1),
(4, 'Milo Ais', 'Milo sejuk pekat', 3.50, 'tersedia', 1),
(4, 'Air Sirap Limau', 'Air sirap dengan limau', 3.00, 'tersedia', 0),
(4, 'Kopi O Ais', 'Kopi tradisional sejuk', 3.00, 'tersedia', 0),
(4, 'Air Kosong', 'Air mineral', 1.00, 'tersedia', 0),
(4, 'Bandung', 'Air bandung susu pekat', 3.50, 'tersedia', 0);

-- =========================================================
-- DATA AWAL: Pengguna contoh
-- =========================================================
INSERT INTO users (nama, email, telefon, kata_laluan, role, status) VALUES
('Ahmad (Staff)', 'staff@sistemorder.com', '012-3456789', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'staff', 'aktif'),
('Siti (Customer)', 'siti@gmail.com', '013-1112222', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'customer', 'aktif'),
('Ali (Customer)', 'ali@gmail.com', '014-3334444', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'customer', 'aktif'),
('Ros (Buyer)', 'ros@gmail.com', '015-5556666', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'buyer', 'aktif');
-- Semua kata laluan default: password

-- =========================================================
-- DATA AWAL: Pesanan contoh (Meja 1-5 & Direct Order)
-- =========================================================

-- Pesanan 1: Meja 1 (Customer Siti) - Selesai
INSERT INTO orders (no_pesanan, customer_id, nama_pelanggan, no_meja, jumlah_harga, status, nota, created_at) VALUES
('ORD260322A1B2', 4, 'Siti', 'Meja 1', 25.50, 'completed', 'Kurang pedas', '2026-03-22 08:15:00');
INSERT INTO order_items (order_id, menu_item_id, nama_item, kuantiti, harga_seunit, jumlah) VALUES
(1, 1, 'Burger Daging Classic', 2, 8.00, 16.00),
(1, 12, 'French Fries', 1, 4.00, 4.00),
(1, 19, 'Teh Ais', 2, 2.50, 5.00),
(1, 18, 'Coleslaw', 1, 3.00, 3.00);
INSERT INTO payments (order_id, jumlah, kaedah, status, created_at) VALUES
(1, 25.50, 'tunai', 'berjaya', '2026-03-22 08:16:00');

-- Pesanan 2: Meja 2 (Customer Ali) - Sedang disediakan
INSERT INTO orders (no_pesanan, customer_id, nama_pelanggan, no_meja, jumlah_harga, status, nota, created_at) VALUES
('ORD260322C3D4', 5, 'Ali', 'Meja 2', 17.00, 'preparing', NULL, '2026-03-22 09:30:00');
INSERT INTO order_items (order_id, menu_item_id, nama_item, kuantiti, harga_seunit, jumlah) VALUES
(2, 9, 'Kombo Double', 1, 17.00, 17.00);
INSERT INTO payments (order_id, jumlah, kaedah, status, created_at) VALUES
(2, 17.00, 'tunai', 'berjaya', '2026-03-22 09:31:00');

-- Pesanan 3: Meja 3 (Walk-in / Staff order) - Disahkan
INSERT INTO orders (no_pesanan, staff_id, nama_pelanggan, no_meja, jumlah_harga, status, nota, created_at) VALUES
('ORD260322E5F6', 3, 'Pelanggan Walk-in', 'Meja 3', 21.00, 'confirmed', 'Tambah keju', '2026-03-22 10:00:00');
INSERT INTO order_items (order_id, menu_item_id, nama_item, kuantiti, harga_seunit, jumlah) VALUES
(3, 6, 'Burger Telur Special', 1, 9.50, 9.50),
(3, 13, 'Cheese Fries', 1, 6.00, 6.00),
(3, 20, 'Milo Ais', 1, 3.50, 3.50),
(3, 16, 'Chicken Nugget (6 pcs)', 1, 5.50, 5.50);
INSERT INTO payments (order_id, jumlah, kaedah, status, created_at) VALUES
(3, 21.00, 'tunai', 'berjaya', '2026-03-22 10:01:00');

-- Pesanan 4: Meja 4 (Walk-in / Staff order) - Pending
INSERT INTO orders (no_pesanan, staff_id, nama_pelanggan, no_meja, jumlah_harga, status, nota, created_at) VALUES
('ORD260322G7H8', 3, 'Keluarga Razak', 'Meja 4', 40.00, 'pending', 'Bungkus sebahagian', '2026-03-22 10:30:00');
INSERT INTO order_items (order_id, menu_item_id, nama_item, kuantiti, harga_seunit, jumlah) VALUES
(4, 8, 'Kombo Classic', 2, 13.00, 26.00),
(4, 15, 'Potato Wedges', 1, 5.00, 5.00),
(4, 3, 'Burger Ayam Crispy', 1, 7.50, 7.50),
(4, 19, 'Teh Ais', 1, 2.50, 2.50);
INSERT INTO payments (order_id, jumlah, kaedah, status, created_at) VALUES
(4, 40.00, 'tunai', 'pending', '2026-03-22 10:30:00');

-- Pesanan 5: Meja 5 (Customer Direct) - Siap
INSERT INTO orders (no_pesanan, customer_id, nama_pelanggan, no_meja, jumlah_harga, status, nota, created_at) VALUES
('ORD260322I9J0', 4, 'Siti', 'Meja 5', 14.00, 'ready', NULL, '2026-03-22 10:15:00');
INSERT INTO order_items (order_id, menu_item_id, nama_item, kuantiti, harga_seunit, jumlah) VALUES
(5, 4, 'Burger Ayam Spicy', 1, 8.50, 8.50),
(5, 12, 'French Fries', 1, 4.00, 4.00),
(5, 21, 'Air Sirap Limau', 1, 3.00, 3.00);
INSERT INTO payments (order_id, jumlah, kaedah, status, created_at) VALUES
(5, 14.00, 'qr', 'berjaya', '2026-03-22 10:16:00');

-- Pesanan 6: Direct Order / Bungkus (Tiada meja) - Selesai
INSERT INTO orders (no_pesanan, customer_id, nama_pelanggan, no_meja, jumlah_harga, status, nota, created_at) VALUES
('ORD260322K1L2', 5, 'Ali', '', 12.50, 'completed', 'Bungkus semua', '2026-03-22 09:00:00');
INSERT INTO order_items (order_id, menu_item_id, nama_item, kuantiti, harga_seunit, jumlah) VALUES
(6, 10, 'Kombo Ayam', 1, 12.50, 12.50);
INSERT INTO payments (order_id, jumlah, kaedah, status, created_at) VALUES
(6, 12.50, 'tunai', 'berjaya', '2026-03-22 09:01:00');

-- Pesanan 7: Direct Order / Walk-in Bungkus - Selesai
INSERT INTO orders (no_pesanan, staff_id, nama_pelanggan, no_meja, jumlah_harga, status, nota, created_at) VALUES
('ORD260322M3N4', 3, 'Customer Bungkus', '', 19.50, 'completed', 'Direct order - bungkus', '2026-03-22 08:45:00');
INSERT INTO order_items (order_id, menu_item_id, nama_item, kuantiti, harga_seunit, jumlah) VALUES
(7, 2, 'Burger Daging Double', 1, 12.00, 12.00),
(7, 12, 'French Fries', 1, 4.00, 4.00),
(7, 20, 'Milo Ais', 1, 3.50, 3.50);
INSERT INTO payments (order_id, jumlah, kaedah, status, created_at) VALUES
(7, 19.50, 'tunai', 'berjaya', '2026-03-22 08:46:00');

