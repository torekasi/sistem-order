<?php
/**
 * =========================================================
 * SalesController - Laporan Jualan & Cash Flow
 * =========================================================
 */

require_once BASE_PATH . 'models/SalesModel.php';

class SalesController {

    private $salesModel;

    public function __construct() {
        $this->salesModel = new SalesModel();
    }

    /**
     * Dashboard jualan utama
     */
    public function dashboardSales(): void {
        Security::requireRole('admin');

        $todaySales = $this->salesModel->getDailySales();
        $monthlySales = $this->salesModel->getMonthlySales();
        $yearlySales = $this->salesModel->getYearlySales();
        $topItems = $this->salesModel->getTopSellingItems(10, 'month');
        $cashFlow = $this->salesModel->getCashFlowSummary('month');
        
        // Trend 30 hari lepas untuk carta
        $from = date('Y-m-d', strtotime('-30 days'));
        $to = date('Y-m-d');
        $salesTrendRaw = $this->salesModel->getSalesTrend($from, $to);
        
        $salesTrendMap = [];
        for ($i = 30; $i >= 0; $i--) {
            $d = date('Y-m-d', strtotime("-$i days"));
            $salesTrendMap[$d] = ['tarikh' => $d, 'jumlah_pesanan' => 0, 'jumlah_jualan' => 0];
        }
        foreach ($salesTrendRaw as $row) {
            $salesTrendMap[$row['tarikh']] = $row;
        }
        $salesTrend = array_values($salesTrendMap);
        
        // Trend bulanan untuk tahun ini
        $monthlyTrend = $this->salesModel->getMonthlyTrend();

        require_once BASE_PATH . 'views/admin/sales-dashboard.php';
    }

    /**
     * Laporan harian terperinci
     */
    public function dailyReport(): void {
        Security::requireRole('admin');

        $date = Security::sanitize($_GET['date'] ?? date('Y-m-d'));
        $sales = $this->salesModel->getDailySales($date);
        $transactions = $this->salesModel->getDailyTransactions($date);

        require_once BASE_PATH . 'views/admin/sales-daily.php';
    }

    /**
     * Laporan bulanan
     */
    public function monthlyReport(): void {
        Security::requireRole('admin');

        $month = Security::sanitizeInt($_GET['month'] ?? date('m'));
        $year = Security::sanitizeInt($_GET['year'] ?? date('Y'));
        $sales = $this->salesModel->getMonthlySales($month, $year);
        $topItems = $this->salesModel->getTopSellingItems(10, 'month');

        require_once BASE_PATH . 'views/admin/sales-monthly.php';
    }

    /**
     * Laporan tahunan
     */
    public function yearlyReport(): void {
        Security::requireRole('admin');

        $year = Security::sanitizeInt($_GET['year'] ?? date('Y'));
        $sales = $this->salesModel->getYearlySales($year);
        $monthlyTrend = $this->salesModel->getMonthlyTrend($year);

        require_once BASE_PATH . 'views/admin/sales-yearly.php';
    }

    /**
     * Item popular
     */
    public function topItems(): void {
        Security::requireRole('admin');

        $period = Security::sanitize($_GET['period'] ?? 'month');
        $topItems = $this->salesModel->getTopSellingItems(20, $period);

        require_once BASE_PATH . 'views/admin/popular-items.php';
    }

    /**
     * Eksport CSV
     */
    public function exportCSV(): void {
        Security::requireRole('admin');

        $date = Security::sanitize($_GET['date'] ?? date('Y-m-d'));
        $transactions = $this->salesModel->getDailyTransactions($date);

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="jualan_' . $date . '.csv"');

        $output = fopen('php://output', 'w');
        fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF)); // UTF-8 BOM

        fputcsv($output, ['No Pesanan', 'Pelanggan', 'Jumlah (RM)', 'Kaedah Bayaran', 'Status', 'Masa']);

        foreach ($transactions as $t) {
            fputcsv($output, [
                $t['no_pesanan'],
                $t['nama_pelanggan'],
                number_format($t['jumlah_harga'], 2),
                $t['kaedah_bayaran'] ?? '-',
                $t['status'],
                $t['created_at'],
            ]);
        }

        fclose($output);
        exit;
    }
}
