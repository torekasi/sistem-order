-- =========================================================
-- CLEAR数据库 - Reset Menu dan Order Data
-- =========================================================
-- Run this in phpMyAdmin SQL tab

-- 1. Clear all data related to orders and menu
SET FOREIGN_KEY_CHECKS = 0;

-- Clear order_items first (has FK to menu_items)
DELETE FROM order_items;

-- Clear ingredients (has FK to menu_items)
DELETE FROM ingredients;

-- Clear menu items
DELETE FROM menu_items;

-- Clear categories
DELETE FROM categories;

-- Reset auto-increment
ALTER TABLE categories AUTO_INCREMENT = 1;
ALTER TABLE menu_items AUTO_INCREMENT = 1;
ALTER TABLE order_items AUTO_INCREMENT = 1;
ALTER TABLE ingredients AUTO_INCREMENT = 1;

-- Re-enable foreign key checks
SET FOREIGN_KEY_CHECKS = 1;

-- 2. Insert default categories
INSERT INTO categories (nama, status, created_at) VALUES
('Char Kuey Teow', 'aktif', NOW()),
('Kuey Teow Goreng', 'aktif', NOW()),
('Maggi Goreng', 'aktif', NOW()),
('Set Combo', 'aktif', NOW());

-- 3. Insert menu items
-- Char Kuey Teow (category_id = 1)
INSERT INTO menu_items (category_id, nama, penerangan, harga, gambar, status, popular, created_at) VALUES
(1, 'Char Kuey Teow Biasa', 'Menu Char Kuey Teow', 7.00, '', 'tersedia', 0, NOW()),
(1, 'Char Kuey Teow Besar', 'Menu Char Kuey Teow', 8.00, '', 'tersedia', 0, NOW()),
(1, 'Char Kuey Teow Special', 'Menu Char Kuey Teow', 10.00, '', 'tersedia', 0, NOW());

-- Kuey Teow Goreng (category_id = 2)
INSERT INTO menu_items (category_id, nama, penerangan, harga, gambar, status, popular, created_at) VALUES
(2, 'Kuey Teow Goreng Kerang/Ayam', 'Menu Kuey Teow Goreng', 6.00, '', 'tersedia', 0, NOW()),
(2, 'Kuey Teow Goreng Udang', 'Menu Kuey Teow Goreng', 7.00, '', 'tersedia', 0, NOW()),
(2, 'Kuey Teow Goreng Special', 'Menu Kuey Teow Goreng', 9.00, '', 'tersedia', 0, NOW());

-- Maggi Goreng (category_id = 3)
INSERT INTO menu_items (category_id, nama, penerangan, harga, gambar, status, popular, created_at) VALUES
(3, 'Maggi Goreng Kerang/Ayam', 'Menu Maggi Goreng', 6.00, '', 'tersedia', 0, NOW()),
(3, 'Maggi Goreng Udang', 'Menu Maggi Goreng', 7.00, '', 'tersedia', 0, NOW()),
(3, 'Maggi Goreng Special', 'Menu Maggi Goreng', 9.00, '', 'tersedia', 0, NOW());

-- Set Combo (category_id = 4)
INSERT INTO menu_items (category_id, nama, penerangan, harga, gambar, status, popular, created_at) VALUES
(4, 'Set Combo Nuggets + Sosej Cheezy', 'Menu Set Combo', 6.00, '', 'tersedia', 0, NOW());

-- =========================================================
-- Summary
-- Categories: 4
-- Products: 13
-- Orders/Items: Cleared
-- =========================================================
