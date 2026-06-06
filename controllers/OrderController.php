<?php
/**
 * =========================================================
 * OrderController - Pengurusan Pesanan
 * =========================================================
 */

require_once BASE_PATH . 'models/OrderModel.php';
require_once BASE_PATH . 'models/MenuModel.php';
require_once BASE_PATH . 'models/PaymentModel.php';

class OrderController {

    private $orderModel;
    private $menuModel;

    public function __construct() {
        $this->orderModel = new OrderModel();
        $this->menuModel = new MenuModel();
    }

    /**
     * Tambah ke cart (AJAX)
     */
    public function addToCart(): void {
        header('Content-Type: application/json');

        $itemId = Security::sanitizeInt($_POST['item_id'] ?? 0);
        $kuantiti = Security::sanitizeInt($_POST['kuantiti'] ?? 1);

        $item = $this->menuModel->getItemById($itemId);
        if (!$item) {
            echo json_encode(['success' => false, 'message' => 'Item tidak ditemui.']);
            return;
        }

        // Cart disimpan di session
        if (!isset($_SESSION['cart'])) {
            $_SESSION['cart'] = [];
        }

        $found = false;
        foreach ($_SESSION['cart'] as &$cartItem) {
            if ($cartItem['id'] == $itemId) {
                $cartItem['kuantiti'] += $kuantiti;
                $found = true;
                break;
            }
        }
        unset($cartItem);

        if (!$found) {
            $_SESSION['cart'][] = [
                'id' => $item['id'],
                'nama' => $item['nama'],
                'harga' => $item['harga'],
                'kuantiti' => $kuantiti,
                'gambar' => $item['gambar'] ?? '',
            ];
        }

        $totalItems = array_sum(array_column($_SESSION['cart'], 'kuantiti'));

        echo json_encode([
            'success' => true,
            'message' => $item['nama'] . ' ditambah ke cart.',
            'cartCount' => $totalItems,
        ]);
    }

    /**
     * Papar cart
     */
    public function viewCart(): void {
        $cart = $_SESSION['cart'] ?? [];
        $jumlah = 0;
        foreach ($cart as $item) {
            $jumlah += $item['harga'] * $item['kuantiti'];
        }
        require_once BASE_PATH . 'views/cart.php';
    }

    /**
     * Buang item dari cart (AJAX)
     */
    public function removeFromCart(): void {
        header('Content-Type: application/json');
        $itemId = Security::sanitizeInt($_POST['item_id'] ?? 0);

        if (isset($_SESSION['cart'])) {
            $_SESSION['cart'] = array_values(array_filter($_SESSION['cart'], function ($item) use ($itemId) {
                return $item['id'] != $itemId;
            }));
        }

        $totalItems = isset($_SESSION['cart']) ? array_sum(array_column($_SESSION['cart'], 'kuantiti')) : 0;

        echo json_encode(['success' => true, 'cartCount' => $totalItems]);
    }

    /**
     * Kemaskini kuantiti cart (AJAX)
     */
    public function updateCart(): void {
        header('Content-Type: application/json');
        $itemId = Security::sanitizeInt($_POST['item_id'] ?? 0);
        $action = Security::sanitize($_POST['action'] ?? '');
        $kuantiti = Security::sanitizeInt($_POST['kuantiti'] ?? 0);

        if (isset($_SESSION['cart'])) {
            foreach ($_SESSION['cart'] as $key => &$cartItem) {
                if ($cartItem['id'] == $itemId) {
                    if ($action === 'plus') {
                        $cartItem['kuantiti']++;
                    } elseif ($action === 'minus') {
                        $cartItem['kuantiti']--;
                        if ($cartItem['kuantiti'] < 1) {
                            unset($_SESSION['cart'][$key]);
                            $_SESSION['cart'] = array_values($_SESSION['cart']);
                        }
                    } elseif ($kuantiti > 0) {
                        $cartItem['kuantiti'] = $kuantiti;
                    }
                    break;
                }
            }
            unset($cartItem);
        }

        echo json_encode(['success' => true]);
    }

    /**
     * Checkout - proses pesanan
     */
    public function checkout(): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . APP_URL . '/index.php?page=cart');
            exit;
        }

        if (!Security::validateCSRFToken($_POST[CSRF_TOKEN_NAME] ?? '')) {
            $_SESSION['error'] = 'Token keselamatan tidak sah.';
            header('Location: ' . APP_URL . '/index.php?page=cart');
            exit;
        }

        $cart = $_SESSION['cart'] ?? [];
        if (empty($cart)) {
            $_SESSION['error'] = 'Cart kosong.';
            header('Location: ' . APP_URL . '/index.php?page=menu');
            exit;
        }

        $namaPelanggan = Security::sanitize($_POST['nama_pelanggan'] ?? 'Pelanggan');
        $noMeja = Security::sanitize($_POST['no_meja'] ?? '');
        $nota = Security::sanitize($_POST['nota'] ?? '');
        $kaedahBayaran = Security::sanitize($_POST['kaedah_bayaran'] ?? 'tunai');

        // Kira jumlah
        $jumlah = 0;
        $items = [];
        foreach ($cart as $ci) {
            $jumlah += $ci['harga'] * $ci['kuantiti'];
            $items[] = [
                'menu_item_id' => $ci['id'],
                'nama_item' => $ci['nama'],
                'kuantiti' => $ci['kuantiti'],
                'harga_seunit' => $ci['harga'],
            ];
        }

        $orderData = [
            'no_pesanan' => Security::generateOrderNumber(),
            'customer_id' => Security::currentUserId(),
            'nama_pelanggan' => $namaPelanggan,
            'no_meja' => $noMeja,
            'jumlah_harga' => $jumlah,
            'nota' => $nota,
        ];

        $orderId = $this->orderModel->createOrder($orderData, $items);

        if ($orderId) {
            // Buat bayaran
            $paymentModel = new PaymentModel();
            $paymentId = $paymentModel->createPayment($orderId, $jumlah, $kaedahBayaran);

            if ($paymentId) {
                // Tunai over-the-counter tidak lagi auto-berjaya. Biarkan status 'pending'.
                // Cashier akan sahkan secara manual kemudian.
            }

            // Kosongkan cart
            unset($_SESSION['cart']);

            $_SESSION['success'] = 'Pesanan berjaya dibuat!';
            $_SESSION['last_order'] = $orderData['no_pesanan'];
            setcookie('last_order_no', $orderData['no_pesanan'], time() + (86400 * 30), "/");
            header('Location: ' . APP_URL . '/index.php?page=track-order&no=' . urlencode($orderData['no_pesanan']));
        } else {
            $_SESSION['error'] = 'Gagal membuat pesanan. Sila cuba lagi.';
            header('Location: ' . APP_URL . '/index.php?page=cart');
        }
        exit;
    }

    /**
     * Papar halaman jejak pesanan
     */
    public function trackOrder(): void {
        $noPesanan = Security::sanitize($_GET['no'] ?? '');
        
        if (empty($noPesanan) && !empty($_COOKIE['last_order_no'])) {
            header('Location: ' . APP_URL . '/index.php?page=track-order&no=' . urlencode($_COOKIE['last_order_no']));
            exit;
        }

        $order = null;
        $orderItems = [];

        if ($noPesanan) {
            $order = $this->orderModel->getOrderByNumber($noPesanan);
            if ($order) {
                $orderItems = $this->orderModel->getOrderItems($order['id']);
            }
        }

        require_once BASE_PATH . 'views/order-status.php';
    }

    /**
     * API: Status pesanan (AJAX polling)
     */
    public function apiOrderStatus(): void {
        header('Content-Type: application/json');
        $noPesanan = Security::sanitize($_GET['no'] ?? '');

        if (!$noPesanan) {
            echo json_encode(['success' => false, 'message' => 'Nombor pesanan diperlukan.']);
            return;
        }

        $order = $this->orderModel->getOrderByNumber($noPesanan);
        if (!$order) {
            echo json_encode(['success' => false, 'message' => 'Pesanan tidak ditemui.']);
            return;
        }

        $statusLabels = [
            'pending' => 'Menunggu',
            'confirmed' => 'Disahkan',
            'preparing' => 'Sedang Disediakan',
            'ready' => 'Siap',
            'completed' => 'Selesai',
            'cancelled' => 'Dibatalkan',
        ];

        echo json_encode([
            'success' => true,
            'status' => $order['status'],
            'status_label' => $statusLabels[$order['status']] ?? $order['status'],
            'updated_at' => $order['updated_at'],
        ]);
    }

    /**
     * Sejarah pesanan pelanggan
     */
    public function getOrderHistory(): void {
        Security::requireLogin();
        $orders = $this->orderModel->getOrdersByUser(Security::currentUserId());
        require_once BASE_PATH . 'views/order-history.php';
    }

    /**
     * Dapatkan status pesanan (halaman)
     */
    public function getOrderStatus(): void {
        $this->trackOrder();
    }

    /**
     * Tunjuk dan Muat Turun Resit (Print View)
     */
    public function showReceipt(): void {
        $orderId = Security::sanitizeInt($_GET['id'] ?? 0);
        if (!$orderId) {
            die('Nombor resit tidak sah.');
        }
        
        $order = $this->orderModel->getOrderById($orderId);
        if (!$order) {
            die('Pesanan tidak ditemui.');
        }
        
        $orderItems = $this->orderModel->getOrderItems($orderId);
        require_once BASE_PATH . 'models/SettingsModel.php';
        $_sModel = new SettingsModel();
        
        require_once BASE_PATH . 'views/receipt.php';
    }
}
