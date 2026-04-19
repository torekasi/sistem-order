<?php
/**
 * =========================================================
 * SalesModel - Model Laporan Jualan & Cash Flow
 * =========================================================
 */

class SalesModel {

    private PDO $db;

    public function __construct() {
        $this->db = getDB();
    }

    /**
     * Jualan harian
     */
    public function getDailySales(string $date = ''): array {
        if (!$date) $date = date('Y-m-d');
        $stmt = $this->db->prepare("
            SELECT 
                COUNT(*) as jumlah_pesanan,
                COALESCE(SUM(jumlah_harga), 0) as jumlah_jualan,
                COALESCE(AVG(jumlah_harga), 0) as purata_pesanan
            FROM orders 
            WHERE DATE(created_at) = ? AND status = 'completed'
        ");
        $stmt->execute([$date]);
        return $stmt->fetch();
    }

    /**
     * Jualan bulanan
     */
    public function getMonthlySales(int $month = 0, int $year = 0): array {
        if (!$month) $month = (int) date('m');
        if (!$year) $year = (int) date('Y');
        $stmt = $this->db->prepare("
            SELECT 
                COUNT(*) as jumlah_pesanan,
                COALESCE(SUM(jumlah_harga), 0) as jumlah_jualan,
                COALESCE(AVG(jumlah_harga), 0) as purata_pesanan
            FROM orders 
            WHERE MONTH(created_at) = ? AND YEAR(created_at) = ? AND status = 'completed'
        ");
        $stmt->execute([$month, $year]);
        return $stmt->fetch();
    }

    /**
     * Jualan tahunan
     */
    public function getYearlySales(int $year = 0): array {
        if (!$year) $year = (int) date('Y');
        $stmt = $this->db->prepare("
            SELECT 
                COUNT(*) as jumlah_pesanan,
                COALESCE(SUM(jumlah_harga), 0) as jumlah_jualan,
                COALESCE(AVG(jumlah_harga), 0) as purata_pesanan
            FROM orders 
            WHERE YEAR(created_at) = ? AND status = 'completed'
        ");
        $stmt->execute([$year]);
        return $stmt->fetch();
    }

    /**
     * Item paling laris
     */
    public function getTopSellingItems(int $limit = 10, string $period = 'month'): array {
        $dateCondition = match ($period) {
            'today' => "DATE(o.created_at) = CURDATE()",
            'week' => "o.created_at >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)",
            'month' => "MONTH(o.created_at) = MONTH(CURDATE()) AND YEAR(o.created_at) = YEAR(CURDATE())",
            'year' => "YEAR(o.created_at) = YEAR(CURDATE())",
            default => "MONTH(o.created_at) = MONTH(CURDATE())",
        };

        $stmt = $this->db->prepare("
            SELECT 
                oi.nama_item,
                oi.menu_item_id,
                SUM(oi.kuantiti) as jumlah_terjual,
                SUM(oi.jumlah) as jumlah_hasil,
                COUNT(DISTINCT oi.order_id) as jumlah_pesanan
            FROM order_items oi
            JOIN orders o ON oi.order_id = o.id
            WHERE o.status = 'completed' AND {$dateCondition}
            GROUP BY oi.menu_item_id, oi.nama_item
            ORDER BY jumlah_terjual DESC
            LIMIT ?
        ");
        $stmt->execute([$limit]);
        return $stmt->fetchAll();
    }

    /**
     * Trend jualan (data untuk carta)
     */
    public function getSalesTrend(string $from, string $to): array {
        $stmt = $this->db->prepare("
            SELECT 
                DATE(created_at) as tarikh,
                COUNT(*) as jumlah_pesanan,
                SUM(jumlah_harga) as jumlah_jualan
            FROM orders 
            WHERE DATE(created_at) BETWEEN ? AND ? AND status = 'completed'
            GROUP BY DATE(created_at)
            ORDER BY tarikh ASC
        ");
        $stmt->execute([$from, $to]);
        return $stmt->fetchAll();
    }

    /**
     * Trend bulanan dalam tahun
     */
    public function getMonthlyTrend(int $year = 0): array {
        if (!$year) $year = (int) date('Y');
        $stmt = $this->db->prepare("
            SELECT 
                MONTH(created_at) as bulan,
                COUNT(*) as jumlah_pesanan,
                COALESCE(SUM(jumlah_harga), 0) as jumlah_jualan
            FROM orders 
            WHERE YEAR(created_at) = ? AND status = 'completed'
            GROUP BY MONTH(created_at)
            ORDER BY bulan ASC
        ");
        $stmt->execute([$year]);
        return $stmt->fetchAll();
    }

    /**
     * Ringkasan aliran tunai (cash flow)
     */
    public function getCashFlowSummary(string $period = 'month'): array {
        $dateCondition = match ($period) {
            'today' => "DATE(p.created_at) = CURDATE()",
            'week' => "p.created_at >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)",
            'month' => "MONTH(p.created_at) = MONTH(CURDATE()) AND YEAR(p.created_at) = YEAR(CURDATE())",
            'year' => "YEAR(p.created_at) = YEAR(CURDATE())",
            default => "MONTH(p.created_at) = MONTH(CURDATE())",
        };

        $stmt = $this->db->prepare("
            SELECT 
                p.kaedah,
                COUNT(*) as jumlah_transaksi,
                SUM(p.jumlah) as jumlah_bayaran
            FROM payments p
            WHERE p.status = 'berjaya' AND {$dateCondition}
            GROUP BY p.kaedah
        ");
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * Senarai transaksi harian terperinci
     */
    public function getDailyTransactions(string $date = ''): array {
        if (!$date) $date = date('Y-m-d');
        $stmt = $this->db->prepare("
            SELECT o.*, p.kaedah as kaedah_bayaran, p.status as status_bayaran, p.rujukan
            FROM orders o
            LEFT JOIN payments p ON o.id = p.order_id
            WHERE DATE(o.created_at) = ? AND o.status = 'completed'
            ORDER BY o.created_at DESC
        ");
        $stmt->execute([$date]);
        return $stmt->fetchAll();
    }
}
