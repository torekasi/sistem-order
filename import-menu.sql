-- =========================================================
-- Import Menu Data - Reset dan Import
-- =========================================================
-- executes this SQL on your CPanel phpMyAdmin or MySQL client

-- 1. Clear existing data
SET FOREIGN_KEY_CHECKS = 0;

DELETE FROM ingredients;
DELETE FROM order_items;
DELETE FROM menu_items;
DELETE FROM categories;

ALTER TABLE categories AUTO_INCREMENT = 1;
ALTER TABLE menu_items AUTO_INCREMENT = 1;

SET FOREIGN_KEY_CHECKS = 1;

-- 2. Insert categories (explicitly set status to aktif)
INSERT INTO categories (nama, status, created_at) VALUES
('Char Kuey Teow', 'aktif', NOW()),
('Kuey Teow Goreng', 'aktif', NOW()),
('Maggi Goreng', 'aktif', NOW()),
('Set Combo', 'aktif', NOW());

-- 3. Insert menu items

-- Char Kuey Teow items (category_id = 1)
INSERT INTO menu_items (category_id, nama, penerangan, harga, gambar, status, popular, created_at) VALUES
(1, 'Char Kuey Teow Biasa', 'Menu Char Kuey Teow', 7.00, '', 'tersedia', 0, NOW()),
(1, 'Char Kuey Teow Besar', 'Menu Char Kuey Teow', 8.00, '', 'tersedia', 0, NOW()),
(1, 'Char Kuey Teow Special', 'Menu Char Kuey Teow', 10.00, '', 'tersedia', 0, NOW());

-- Kuey Teow Goreng items (category_id = 2)
INSERT INTO menu_items (category_id, nama, penerangan, harga, gambar, status, popular, created_at) VALUES
(2, 'Kuey Teow Goreng Kerang/Ayam', 'Menu Kuey Teow Goreng', 6.00, '', 'tersedia', 0, NOW()),
(2, 'Kuey Teow Goreng Udang', 'Menu Kuey Teow Goreng', 7.00, '', 'tersedia', 0, NOW()),
(2, 'Kuey Teow Goreng Special', 'Menu Kuey Teow Goreng', 9.00, '', 'tersedia', 0, NOW());

-- Maggi Goreng items (category_id = 3)
INSERT INTO menu_items (category_id, nama, penerangan, harga, gambar, status, popular, created_at) VALUES
(3, 'Maggi Goreng Kerang/Ayam', 'Menu Maggi Goreng', 6.00, '', 'tersedia', 0, NOW()),
(3, 'Maggi Goreng Udang', 'Menu Maggi Goreng', 7.00, '', 'tersedia', 0, NOW()),
(3, 'Maggi Goreng Special', 'Menu Maggi Goreng', 9.00, '', 'tersedia', 0, NOW());

-- Set Combo items (category_id = 4)
INSERT INTO menu_items (category_id, nama, penerangan, harga, gambar, status, popular, created_at) VALUES
(4, 'Set Combo Nuggets + Sosej Cheezy', 'Menu Set Combo', 6.00, '', 'tersedia', 0, NOW());

-- =========================================================
-- Summary
-- Categories: 4
-- Products: 13
-- =========================================================
