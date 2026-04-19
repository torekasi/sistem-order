<?php
/**
 * =========================================================
 * PaymentModel - Model Bayaran
 * =========================================================
 */

class PaymentModel {

    private PDO $db;

    public function __construct() {
        $this->db = getDB();
    }

    /**
     * Buat rekod bayaran
     */
    public function createPayment(int $orderId, float $jumlah, string $kaedah = 'tunai'): int|false {
        try {
            $rujukan = 'PAY' . date('ymd') . strtoupper(substr(bin2hex(random_bytes(3)), 0, 6));
            $stmt = $this->db->prepare("
                INSERT INTO payments (order_id, jumlah, kaedah, rujukan) VALUES (?, ?, ?, ?)
            ");
            $stmt->execute([$orderId, $jumlah, $kaedah, $rujukan]);
            return (int) $this->db->lastInsertId();
        } catch (PDOException $e) {
            Logger::error("Buat bayaran gagal: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Dapatkan bayaran berdasarkan pesanan
     */
    public function getPaymentByOrder(int $orderId): array|false {
        $stmt = $this->db->prepare("SELECT * FROM payments WHERE order_id = ?");
        $stmt->execute([$orderId]);
        return $stmt->fetch();
    }

    /**
     * Kemaskini status bayaran
     */
    public function updatePaymentStatus(int $paymentId, string $status): bool {
        $stmt = $this->db->prepare("UPDATE payments SET status = ? WHERE id = ?");
        return $stmt->execute([$status, $paymentId]);
    }

    /**
     * Senarai bayaran hari ini
     */
    public function getTodayPayments(): array {
        $stmt = $this->db->query("
            SELECT p.*, o.no_pesanan, o.nama_pelanggan 
            FROM payments p 
            JOIN orders o ON p.order_id = o.id 
            WHERE DATE(p.created_at) = CURDATE() 
            ORDER BY p.created_at DESC
        ");
        return $stmt->fetchAll();
    }
}
