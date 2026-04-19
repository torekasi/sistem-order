<?php
/**
 * =========================================================
 * KitchenController - Pengurusan Dapur / Staff
 * =========================================================
 */

require_once BASE_PATH . 'models/OrderModel.php';
require_once BASE_PATH . 'models/MenuModel.php';
require_once BASE_PATH . 'models/PaymentModel.php';

class KitchenController {

    private OrderModel $orderModel;

    public function __construct() {
        $this->orderModel = new OrderModel();
    }

    /**
     * Dashboard dapur - senarai pesanan aktif
     */
    public function dashboard(): void {
        Security::requireRole('admin', 'staff', 'cashier');
        $activeOrders = $this->orderModel->getActiveOrders();
        
        // Dapatkan item untuk setiap pesanan aktif
        $ordersWithItems = [];
        foreach ($activeOrders as $order) {
            $order['items'] = $this->orderModel->getOrderItems($order['id']);
            $ordersWithItems[] = $order;
        }

        // Pesanan yang telah diselesaikan (untuk tab "complete")
        $completedOrdersRaw = $this->orderModel->getOrdersByStatus('completed');
        // Filter pesanan hari ini sahaja supaya tidak penuhkkan server (pilihan terbaik)
        $completedOrders = array_filter($completedOrdersRaw, function($o) {
            return date('Y-m-d', strtotime($o['created_at'])) === date('Y-m-d');
        });
        
        $completedOrdersWithItems = [];
        foreach ($completedOrders as $order) {
            $order['items'] = $this->orderModel->getOrderItems($order['id']);
            $completedOrdersWithItems[] = $order;
        }

        require_once BASE_PATH . 'views/kitchen/dashboard.php';
    }

    /**
     * Kemaskini status pesanan
     */
    public function updateOrderStatus(): void {
        Security::requireRole('admin', 'staff', 'cashier');

        header('Content-Type: application/json');

        $orderId = Security::sanitizeInt($_POST['order_id'] ?? 0);
        $status = Security::sanitize($_POST['status'] ?? '');

        $validStatuses = ['confirmed', 'preparing', 'ready', 'completed', 'cancelled'];
        if (!in_array($status, $validStatuses)) {
            echo json_encode(['success' => false, 'message' => 'Status tidak sah.']);
            return;
        }

        if ($this->orderModel->updateStatus($orderId, $status)) {
            Logger::admin("Kemaskini status pesanan", [
                'order_id' => $orderId,
                'status' => $status,
                'user_id' => Security::currentUserId()
            ]);
            echo json_encode(['success' => true, 'message' => 'Status berjaya dikemaskini.']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Gagal mengemaskini status.']);
        }
    }

    /**
     * API: Dapatkan pesanan baru (AJAX polling)
     */
    public function apiGetOrders(): void {
        Security::requireRole('admin', 'staff', 'cashier');
        header('Content-Type: application/json');

        $activeOrders = $this->orderModel->getActiveOrders();
        $ordersWithItems = [];
        foreach ($activeOrders as $order) {
            $order['items'] = $this->orderModel->getOrderItems($order['id']);
            $ordersWithItems[] = $order;
        }

        echo json_encode([
            'success' => true,
            'orders' => $ordersWithItems,
            'count' => count($ordersWithItems),
        ]);
    }

    /**
     * Pesanan baru (AJAX)
     */
    public function getNewOrders(): void {
        $this->apiGetOrders();
    }

    /**
     * Staff buat pesanan untuk customer
     */
    public function staffCreateOrder(): void {
        Security::requireRole('admin', 'staff', 'cashier');

        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            // Dapatkan senarai pesanan untuk dipapar (Dashboard)
            $activeOrders = $this->orderModel->getActiveOrders();
            $ordersWithItems = [];
            foreach ($activeOrders as $order) {
                $order['items'] = $this->orderModel->getOrderItems($order['id']);
                $ordersWithItems[] = $order;
            }

            // Pesanan hari ini yang selesai
            $completedOrdersRaw = $this->orderModel->getOrdersByStatus('completed');
            $completedOrders = array_filter($completedOrdersRaw, function($o) {
                return date('Y-m-d', strtotime($o['created_at'])) === date('Y-m-d');
            });
            $completedOrdersWithItems = [];
            foreach ($completedOrders as $order) {
                $order['items'] = $this->orderModel->getOrderItems($order['id']);
                $completedOrdersWithItems[] = $order;
            }

            // Papar form POS
            $menuModel = new MenuModel();
            $categories = $menuModel->getAllCategories();
            $allItems = $menuModel->getAllItems();
            $menuByCategory = [];
            foreach ($allItems as $item) {
                $menuByCategory[$item['category_id']][] = $item;
            }
            require_once BASE_PATH . 'views/kitchen/staff-order.php';
            return;
        }

        // POST - proses pesanan
        if (!Security::validateCSRFToken($_POST[CSRF_TOKEN_NAME] ?? '')) {
            $_SESSION['error'] = 'Token keselamatan tidak sah.';
            header('Location: ' . APP_URL . '/index.php?page=staff-order');
            exit;
        }

        $namaPelanggan = Security::sanitize($_POST['nama_pelanggan'] ?? 'Walk-in');
        $noMeja = Security::sanitize($_POST['no_meja'] ?? '');
        $nota = Security::sanitize($_POST['nota'] ?? '');
        $kaedahBayaran = Security::sanitize($_POST['kaedah_bayaran'] ?? 'tunai');
        $statusBayaran = Security::sanitize($_POST['status_bayaran'] ?? 'pending');
        $itemIds = $_POST['item_ids'] ?? [];
        $itemQtys = $_POST['item_qtys'] ?? [];

        if (empty($itemIds)) {
            $_SESSION['error'] = 'Sila pilih sekurang-kurangnya satu item.';
            header('Location: ' . APP_URL . '/index.php?page=staff-order');
            exit;
        }

        $menuModel = new MenuModel();
        $items = [];
        $jumlah = 0;

        foreach ($itemIds as $index => $itemId) {
            $qty = (int)($itemQtys[$index] ?? 1);
            if ($qty < 1) continue;

            $menuItem = $menuModel->getItemById((int)$itemId);
            if ($menuItem) {
                $items[] = [
                    'menu_item_id' => $menuItem['id'],
                    'nama_item' => $menuItem['nama'],
                    'kuantiti' => $qty,
                    'harga_seunit' => $menuItem['harga'],
                ];
                $jumlah += $menuItem['harga'] * $qty;
            }
        }

        $orderData = [
            'no_pesanan' => Security::generateOrderNumber(),
            'staff_id' => Security::currentUserId(),
            'nama_pelanggan' => $namaPelanggan,
            'no_meja' => $noMeja,
            'jumlah_harga' => $jumlah,
            'nota' => $nota,
        ];

        $orderId = $this->orderModel->createOrder($orderData, $items);

        if ($orderId) {
            $paymentModel = new PaymentModel();
            $paymentId = $paymentModel->createPayment($orderId, $jumlah, $kaedahBayaran);
            if ($paymentId && $statusBayaran === 'berjaya') {
                $paymentModel->updatePaymentStatus($paymentId, 'berjaya');
                $this->orderModel->updateStatus($orderId, 'confirmed');
            }

            $_SESSION['success'] = 'Pesanan ' . $orderData['no_pesanan'] . ' berjaya dibuat!';
            Logger::admin("Staff buat pesanan", ['order' => $orderData['no_pesanan'], 'user_id' => Security::currentUserId()]);
        } else {
            $_SESSION['error'] = 'Gagal membuat pesanan.';
        }

        header('Location: ' . APP_URL . '/index.php?page=kitchen');
        exit;
    }
}
