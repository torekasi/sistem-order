<?php
/**
 * =========================================================
 * OrderModel - Model Pesanan
 * =========================================================
 */

class OrderModel {

    private $db;

    public function __construct() {
        $this->db = getDB();
    }

    /**
     * Buat pesanan baru
     */
    public function createOrder(array $orderData, array $items) {
        try {
            $this->db->beginTransaction();

            $stmt = $this->db->prepare("
                INSERT INTO orders (no_pesanan, customer_id, staff_id, nama_pelanggan, no_meja, jumlah_harga, nota) 
                VALUES (?, ?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([
                $orderData['no_pesanan'],
                $orderData['customer_id'] ?? null,
                $orderData['staff_id'] ?? null,
                $orderData['nama_pelanggan'],
                $orderData['no_meja'] ?? null,
                $orderData['jumlah_harga'],
                $orderData['nota'] ?? null,
            ]);

            $orderId = (int) $this->db->lastInsertId();

            $stmtItem = $this->db->prepare("
                INSERT INTO order_items (order_id, menu_item_id, nama_item, kuantiti, harga_seunit, jumlah, nota) 
                VALUES (?, ?, ?, ?, ?, ?, ?)
            ");

            foreach ($items as $item) {
                $stmtItem->execute([
                    $orderId,
                    $item['menu_item_id'],
                    $item['nama_item'],
                    $item['kuantiti'],
                    $item['harga_seunit'],
                    $item['kuantiti'] * $item['harga_seunit'],
                    $item['nota'] ?? null,
                ]);
            }

            $this->db->commit();
            return $orderId;
        } catch (PDOException $e) {
            $this->db->rollBack();
            Logger::error("Buat pesanan gagal: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Dapatkan pesanan berdasarkan ID
     */
    public function getOrderById(int $id) {
        $stmt = $this->db->prepare("SELECT * FROM orders WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    /**
     * Dapatkan pesanan berdasarkan nombor pesanan
     */
    public function getOrderByNumber(string $noPesanan) {
        $stmt = $this->db->prepare("SELECT * FROM orders WHERE no_pesanan = ?");
        $stmt->execute([$noPesanan]);
        return $stmt->fetch();
    }

    /**
     * Dapatkan item dalam pesanan
     */
    public function getOrderItems(int $orderId): array {
        $stmt = $this->db->prepare("SELECT * FROM order_items WHERE order_id = ?");
        $stmt->execute([$orderId]);
        return $stmt->fetchAll();
    }

    /**
     * Senarai pesanan berdasarkan pengguna
     */
    public function getOrdersByUser(int $userId): array {
        $stmt = $this->db->prepare("
            SELECT * FROM orders WHERE customer_id = ? ORDER BY created_at DESC
        ");
        $stmt->execute([$userId]);
        return $stmt->fetchAll();
    }

    /**
     * Senarai pesanan berdasarkan status
     */
    public function getOrdersByStatus(string $status): array {
        $stmt = $this->db->prepare("
            SELECT * FROM orders WHERE status = ? ORDER BY created_at ASC
        ");
        $stmt->execute([$status]);
        return $stmt->fetchAll();
    }

    /**
     * Senarai pesanan aktif (bukan completed/cancelled)
     */
    public function getActiveOrders(): array {
        $stmt = $this->db->query("
            SELECT * FROM orders 
            WHERE status NOT IN ('completed', 'cancelled') 
            ORDER BY created_at ASC
        ");
        return $stmt->fetchAll();
    }

    /**
     * Kemaskini status pesanan
     */
    public function updateStatus(int $orderId, string $status): bool {
        $stmt = $this->db->prepare("UPDATE orders SET status = ? WHERE id = ?");
        return $stmt->execute([$status, $orderId]);
    }

    /**
     * Pesanan hari ini
     */
    public function getTodayOrders(): array {
        $stmt = $this->db->query("
            SELECT * FROM orders 
            WHERE DATE(created_at) = CURDATE() 
            ORDER BY created_at DESC
        ");
        return $stmt->fetchAll();
    }

    /**
     * Kiraan pesanan berdasarkan status (hari ini)
     */
    public function getOrderCountByStatus(): array {
        $stmt = $this->db->query("
            SELECT status, COUNT(*) as jumlah 
            FROM orders 
            WHERE DATE(created_at) = CURDATE() 
            GROUP BY status
        ");
        return $stmt->fetchAll();
    }
}
