<?php
/**
 * =========================================================
 * MenuModel - Model Menu & Kategori
 * =========================================================
 */

class MenuModel {

    private PDO $db;

    public function __construct() {
        $this->db = getDB();
    }

    // =====================================================
    // KATEGORI
    // =====================================================

    /**
     * Senarai semua kategori aktif
     */
    public function getAllCategories(): array {
        $stmt = $this->db->query("SELECT * FROM categories WHERE status = 'aktif' ORDER BY urutan ASC");
        return $stmt->fetchAll();
    }

    /**
     * Tambah kategori baru
     */
    public function addCategory(string $nama, string $penerangan = '', int $urutan = 0): int|false {
        try {
            $stmt = $this->db->prepare("INSERT INTO categories (nama, penerangan, urutan) VALUES (?, ?, ?)");
            $stmt->execute([$nama, $penerangan, $urutan]);
            return (int) $this->db->lastInsertId();
        } catch (PDOException $e) {
            Logger::error("Tambah kategori gagal: " . $e->getMessage());
            return false;
        }
    }

    // =====================================================
    // MENU ITEMS
    // =====================================================

    /**
     * Senarai semua item menu (tersedia sahaja)
     */
    public function getAllItems(): array {
        $stmt = $this->db->query("
            SELECT mi.*, c.nama AS kategori_nama 
            FROM menu_items mi 
            JOIN categories c ON mi.category_id = c.id 
            WHERE mi.status = 'tersedia' AND c.status = 'aktif'
            ORDER BY c.urutan ASC, mi.nama ASC
        ");
        return $stmt->fetchAll();
    }

    /**
     * Senarai semua item (termasuk tidak aktif) untuk admin
     */
    public function getAllItemsAdmin(): array {
        $stmt = $this->db->query("
            SELECT mi.*, c.nama AS kategori_nama 
            FROM menu_items mi 
            JOIN categories c ON mi.category_id = c.id 
            ORDER BY c.urutan ASC, mi.nama ASC
        ");
        return $stmt->fetchAll();
    }

    /**
     * Dapatkan item berdasarkan ID
     */
    public function getItemById(int $id): array|false {
        $stmt = $this->db->prepare("
            SELECT mi.*, c.nama AS kategori_nama 
            FROM menu_items mi 
            JOIN categories c ON mi.category_id = c.id 
            WHERE mi.id = ?
        ");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    /**
     * Senarai item berdasarkan kategori
     */
    public function getByCategory(int $categoryId): array {
        $stmt = $this->db->prepare("
            SELECT * FROM menu_items 
            WHERE category_id = ? AND status = 'tersedia' 
            ORDER BY nama ASC
        ");
        $stmt->execute([$categoryId]);
        return $stmt->fetchAll();
    }

    /**
     * Senarai item popular
     */
    public function getPopularItems(int $limit = 6): array {
        $stmt = $this->db->prepare("
            SELECT mi.*, c.nama AS kategori_nama 
            FROM menu_items mi 
            JOIN categories c ON mi.category_id = c.id 
            WHERE mi.status = 'tersedia' AND mi.popular = 1 
            ORDER BY mi.nama ASC 
            LIMIT ?
        ");
        $stmt->execute([$limit]);
        return $stmt->fetchAll();
    }

    /**
     * Tambah item menu
     */
    public function addItem(int $categoryId, string $nama, string $penerangan, float $harga, string $gambar = '', int $popular = 0): int|false {
        try {
            $stmt = $this->db->prepare("
                INSERT INTO menu_items (category_id, nama, penerangan, harga, gambar, popular) 
                VALUES (?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([$categoryId, $nama, $penerangan, $harga, $gambar, $popular]);
            return (int) $this->db->lastInsertId();
        } catch (PDOException $e) {
            Logger::error("Tambah item gagal: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Kemaskini item menu
     */
    public function updateItem(int $id, array $data): bool {
        $fields = [];
        $params = [];
        foreach (['category_id', 'nama', 'penerangan', 'harga', 'gambar', 'status', 'popular'] as $key) {
            if (isset($data[$key])) {
                $fields[] = "{$key} = ?";
                $params[] = $data[$key];
            }
        }
        if (empty($fields)) return false;
        $params[] = $id;
        $sql = "UPDATE menu_items SET " . implode(', ', $fields) . " WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute($params);
    }

    /**
     * Padam item menu (hard delete)
     * Nullify FK in order_items first to preserve order history
     */
    public function deleteItem(int $id): bool {
        try {
            $this->db->beginTransaction();

            // Remove FK link - preserve order history by storing item name only
            $this->db->prepare("UPDATE order_items SET menu_item_id = NULL WHERE menu_item_id = ?")
                     ->execute([$id]);

            // Remove ingredients linked to this item
            $this->db->prepare("DELETE FROM ingredients WHERE menu_item_id = ?")
                     ->execute([$id]);

            // Hard delete the menu item
            $stmt = $this->db->prepare("DELETE FROM menu_items WHERE id = ?");
            $stmt->execute([$id]);

            $this->db->commit();
            return true;
        } catch (PDOException $e) {
            $this->db->rollBack();
            Logger::error("Padam menu item gagal: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Cari item menu
     */
    public function searchItems(string $keyword): array {
        $stmt = $this->db->prepare("
            SELECT mi.*, c.nama AS kategori_nama 
            FROM menu_items mi 
            JOIN categories c ON mi.category_id = c.id 
            WHERE mi.status = 'tersedia' AND (mi.nama LIKE ? OR mi.penerangan LIKE ?)
            ORDER BY mi.nama ASC
        ");
        $like = "%{$keyword}%";
        $stmt->execute([$like, $like]);
        return $stmt->fetchAll();
    }
}
