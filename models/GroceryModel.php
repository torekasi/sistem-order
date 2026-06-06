<?php
/**
 * =========================================================
 * GroceryModel - Model Pergi Pasar (Pengurusan Belanja)
 * =========================================================
 */

class GroceryModel {

    private $db;

    public function __construct() {
        $this->db = getDB();
    }

    /**
     * Buat senarai belanja baru
     */
    public function createList(string $tajuk, string $tarikh, int $createdBy, string $nota = '') {
        try {
            $stmt = $this->db->prepare("
                INSERT INTO grocery_lists (tajuk, tarikh_belanja, created_by, nota) VALUES (?, ?, ?, ?)
            ");
            $stmt->execute([$tajuk, $tarikh, $createdBy, $nota]);
            return (int) $this->db->lastInsertId();
        } catch (PDOException $e) {
            Logger::error("Buat senarai belanja gagal: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Tambah item ke senarai
     */
    public function addItem(int $listId, string $nama, float $kuantiti, string $unit, float $hargaAnggaran = 0, string $nota = '') {
        try {
            $stmt = $this->db->prepare("
                INSERT INTO grocery_items (list_id, nama, kuantiti, unit, harga_anggaran, nota) VALUES (?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([$listId, $nama, $kuantiti, $unit, $hargaAnggaran, $nota]);
            
            // Kemaskini jumlah anggaran
            $this->updateListTotals($listId);
            
            return (int) $this->db->lastInsertId();
        } catch (PDOException $e) {
            Logger::error("Tambah item belanja gagal: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Toggle checkbox item (sudah beli / belum)
     */
    public function toggleItemChecked(int $itemId): bool {
        $stmt = $this->db->prepare("UPDATE grocery_items SET checked = NOT checked WHERE id = ?");
        $result = $stmt->execute([$itemId]);
        
        // Dapatkan list_id untuk kemaskini totals
        $item = $this->getItemById($itemId);
        if ($item) {
            $this->updateListTotals($item['list_id']);
        }
        
        return $result;
    }

    /**
     * Kemaskini harga sebenar item
     */
    public function updateItemPrice(int $itemId, float $hargaSebenar): bool {
        $stmt = $this->db->prepare("UPDATE grocery_items SET harga_sebenar = ?, checked = 1 WHERE id = ?");
        $result = $stmt->execute([$hargaSebenar, $itemId]);
        
        $item = $this->getItemById($itemId);
        if ($item) {
            $this->updateListTotals($item['list_id']);
        }
        
        return $result;
    }

    /**
     * Dapatkan item berdasarkan ID
     */
    public function getItemById(int $id) {
        $stmt = $this->db->prepare("SELECT * FROM grocery_items WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    /**
     * Dapatkan senarai belanja aktif
     */
    public function getActiveList() {
        $stmt = $this->db->query("
            SELECT gl.*, u.nama as dibuat_oleh 
            FROM grocery_lists gl 
            JOIN users u ON gl.created_by = u.id 
            WHERE gl.status = 'aktif' 
            ORDER BY gl.created_at DESC 
            LIMIT 1
        ");
        return $stmt->fetch();
    }

    /**
     * Dapatkan semua senarai aktif
     */
    public function getAllActiveLists(): array {
        $stmt = $this->db->query("
            SELECT gl.*, u.nama as dibuat_oleh 
            FROM grocery_lists gl 
            JOIN users u ON gl.created_by = u.id 
            WHERE gl.status = 'aktif' 
            ORDER BY gl.tarikh_belanja ASC
        ");
        return $stmt->fetchAll();
    }

    /**
     * Dapatkan item dalam senarai
     */
    public function getListItems(int $listId): array {
        $stmt = $this->db->prepare("SELECT * FROM grocery_items WHERE list_id = ? ORDER BY checked ASC, nama ASC");
        $stmt->execute([$listId]);
        return $stmt->fetchAll();
    }

    /**
     * Dapatkan senarai berdasarkan ID
     */
    public function getListById(int $id) {
        $stmt = $this->db->prepare("
            SELECT gl.*, u.nama as dibuat_oleh 
            FROM grocery_lists gl 
            JOIN users u ON gl.created_by = u.id 
            WHERE gl.id = ?
        ");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    /**
     * Sejarah senarai belanja
     */
    public function getListHistory(int $limit = 20): array {
        $stmt = $this->db->prepare("
            SELECT gl.*, u.nama as dibuat_oleh,
                (SELECT COUNT(*) FROM grocery_items gi WHERE gi.list_id = gl.id) as jumlah_item,
                (SELECT COUNT(*) FROM grocery_items gi WHERE gi.list_id = gl.id AND gi.checked = 1) as item_selesai
            FROM grocery_lists gl 
            JOIN users u ON gl.created_by = u.id 
            ORDER BY gl.created_at DESC 
            LIMIT ?
        ");
        $stmt->execute([$limit]);
        return $stmt->fetchAll();
    }

    /**
     * Tandak senarai selesai
     */
    public function completeList(int $listId): bool {
        $stmt = $this->db->prepare("UPDATE grocery_lists SET status = 'selesai', completed_at = NOW() WHERE id = ?");
        return $stmt->execute([$listId]);
    }

    /**
     * Auto-generate senarai berdasarkan item popular
     */
    public function autoGenerateFromSales(int $createdBy, int $days = 7) {
        try {
            $this->db->beginTransaction();

            // Buat senarai baru
            $tajuk = "Belanja Pasar - " . date('d/m/Y');
            $listId = $this->createList($tajuk, date('Y-m-d'), $createdBy, 'Auto-generated berdasarkan jualan ' . $days . ' hari lepas');

            if (!$listId) {
                $this->db->rollBack();
                return false;
            }

            // Dapatkan bahan mentah berdasarkan item popular
            $stmt = $this->db->prepare("
                SELECT 
                    ing.nama,
                    SUM(oi.kuantiti * ing.kuantiti) as jumlah_kuantiti,
                    ing.unit
                FROM order_items oi
                JOIN orders o ON oi.order_id = o.id
                JOIN ingredients ing ON oi.menu_item_id = ing.menu_item_id
                WHERE o.status = 'completed' 
                AND o.created_at >= DATE_SUB(CURDATE(), INTERVAL ? DAY)
                GROUP BY ing.nama, ing.unit
                ORDER BY jumlah_kuantiti DESC
            ");
            $stmt->execute([$days]);
            $items = $stmt->fetchAll();

            // Tambah ke senarai
            $stmtAdd = $this->db->prepare("
                INSERT INTO grocery_items (list_id, nama, kuantiti, unit) VALUES (?, ?, ?, ?)
            ");
            foreach ($items as $item) {
                $stmtAdd->execute([$listId, $item['nama'], $item['jumlah_kuantiti'], $item['unit']]);
            }

            $this->db->commit();
            return $listId;
        } catch (PDOException $e) {
            $this->db->rollBack();
            Logger::error("Auto-generate senarai gagal: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Kemaskini jumlah anggaran & sebenar dalam senarai
     */
    private function updateListTotals(int $listId): void {
        $stmt = $this->db->prepare("
            UPDATE grocery_lists SET 
                jumlah_anggaran = (SELECT COALESCE(SUM(harga_anggaran * kuantiti), 0) FROM grocery_items WHERE list_id = ?),
                jumlah_sebenar = (SELECT COALESCE(SUM(COALESCE(harga_sebenar, 0) * kuantiti), 0) FROM grocery_items WHERE list_id = ? AND checked = 1)
            WHERE id = ?
        ");
        $stmt->execute([$listId, $listId, $listId]);
    }

    /**
     * Salin senarai lama
     */
    public function copyList(int $sourceListId, int $createdBy) {
        try {
            $source = $this->getListById($sourceListId);
            if (!$source) return false;

            $newListId = $this->createList(
                'Salinan - ' . $source['tajuk'],
                date('Y-m-d'),
                $createdBy,
                'Disalin dari senarai #' . $sourceListId
            );

            if (!$newListId) return false;

            $items = $this->getListItems($sourceListId);
            foreach ($items as $item) {
                $this->addItem($newListId, $item['nama'], $item['kuantiti'], $item['unit'], $item['harga_anggaran']);
            }

            return $newListId;
        } catch (PDOException $e) {
            Logger::error("Salin senarai gagal: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Padam item dari senarai
     */
    public function removeItem(int $itemId): bool {
        $item = $this->getItemById($itemId);
        $stmt = $this->db->prepare("DELETE FROM grocery_items WHERE id = ?");
        $result = $stmt->execute([$itemId]);
        if ($item) {
            $this->updateListTotals($item['list_id']);
        }
        return $result;
    }
}
