<?php
/**
 * =========================================================
 * PaymentController - Pengurusan Bayaran
 * =========================================================
 */

require_once BASE_PATH . 'models/PaymentModel.php';
require_once BASE_PATH . 'models/OrderModel.php';

class PaymentController {

    private PaymentModel $paymentModel;
    private OrderModel $orderModel;

    public function __construct() {
        $this->paymentModel = new PaymentModel();
        $this->orderModel = new OrderModel();
    }

    /**
     * Papar halaman bayaran
     */
    public function showPaymentPage(): void {
        $noPesanan = Security::sanitize($_GET['no'] ?? '');
        $order = null;
        $items = [];
        $payment = null;

        if ($noPesanan) {
            $order = $this->orderModel->getOrderByNumber($noPesanan);
            if ($order) {
                $items = $this->orderModel->getOrderItems($order['id']);
                $payment = $this->paymentModel->getPaymentByOrder($order['id']);
            }
        }

        if (!$order || !$payment) {
            $_SESSION['error'] = 'Pesanan atau bayaran tidak ditemui.';
            header('Location: ' . APP_URL . '/index.php?page=menu');
            exit;
        }

        require_once BASE_PATH . 'views/payment.php';
    }

    /**
     * Proses bayaran
     */
    public function processPayment(): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . APP_URL . '/index.php?page=menu');
            exit;
        }

        if (!Security::validateCSRFToken($_POST[CSRF_TOKEN_NAME] ?? '')) {
            $_SESSION['error'] = 'Token keselamatan tidak sah.';
            header('Location: ' . APP_URL . '/index.php?page=menu');
            exit;
        }

        $orderId = Security::sanitizeInt($_POST['order_id'] ?? 0);
        $kaedah = Security::sanitize($_POST['kaedah'] ?? 'tunai');

        $order = $this->orderModel->getOrderById($orderId);
        if (!$order) {
            $_SESSION['error'] = 'Pesanan tidak ditemui.';
            header('Location: ' . APP_URL . '/index.php?page=menu');
            exit;
        }

        $paymentId = $this->paymentModel->createPayment($orderId, $order['jumlah_harga'], $kaedah);

        if ($paymentId) {
            // Bayaran over-the-counter/QR (Tunai) tidak auto-berjaya. Berstatus 'pending' supaya Cashier boleh verify.
            
            $_SESSION['success'] = 'Permohonan bayaran dihantar! Sila tunggu pengesahan daripada pihak kami.';
            header('Location: ' . APP_URL . '/index.php?page=track-order&no=' . urlencode($order['no_pesanan']));
        } else {
            $_SESSION['error'] = 'Bayaran gagal. Sila cuba lagi.';
            header('Location: ' . APP_URL . '/index.php?page=payment&no=' . urlencode($order['no_pesanan']));
        }
        exit;
    }
}
