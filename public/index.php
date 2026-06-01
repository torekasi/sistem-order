<?php
/**
 * =========================================================
 * SISTEM ORDER - Route Dispatcher (Entry Point)
 * =========================================================
 * Semua request masuk melalui fail ini.
 */

// Define base path
define('BASE_PATH', dirname(__DIR__) . DIRECTORY_SEPARATOR);

// Load konfigurasi
require_once BASE_PATH . '.config.php';

// Load utiliti
require_once BASE_PATH . 'utils/Logger.php';
require_once BASE_PATH . 'utils/Security.php';

// Inisialisasi
initSession();
setSecurityHeaders();
Logger::init();

// Dapatkan halaman yang diminta
// Sokong dua format: ?page=xxx ATAU URL pretty /xxx (via .htaccess rewrite)
if (isset($_GET['page'])) {
    $page = Security::sanitize($_GET['page']);
} else {
    // Extract path dari REQUEST_URI
    $uri = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?? '/';
    $uri = trim($uri, '/');
    $page = $uri !== '' && $uri !== 'index.php' ? Security::sanitize($uri) : 'menu';
}
$action = isset($_GET['action']) ? Security::sanitize($_GET['action']) : 'index';

// =========================================================
// ROUTING
// =========================================================
$routes = [
    // Autentikasi
    'login'         => ['controller' => 'AuthController',    'action' => 'showLogin'],
    'register'      => ['controller' => 'AuthController',    'action' => 'showRegister'],
    'auth-login'    => ['controller' => 'AuthController',    'action' => 'processLogin'],
    'auth-register' => ['controller' => 'AuthController',    'action' => 'processRegister'],
    'logout'        => ['controller' => 'AuthController',    'action' => 'logout'],
    'admin'         => ['controller' => 'AuthController',    'action' => 'adminEntry'],

    // Menu (Pelanggan)
    'menu'          => ['controller' => 'MenuController',    'action' => 'showMenu'],

    // Pesanan
    'cart'          => ['controller' => 'OrderController',   'action' => 'viewCart'],
    'checkout'      => ['controller' => 'OrderController',   'action' => 'checkout'],
    'receipt'       => ['controller' => 'OrderController',   'action' => 'showReceipt'],
    'add-to-cart'   => ['controller' => 'OrderController',   'action' => 'addToCart'],
    'remove-cart'   => ['controller' => 'OrderController',   'action' => 'removeFromCart'],
    'update-cart'   => ['controller' => 'OrderController',   'action' => 'updateCart'],
    'order-status'  => ['controller' => 'OrderController',   'action' => 'getOrderStatus'],
    'order-history' => ['controller' => 'OrderController',   'action' => 'getOrderHistory'],
    'track-order'   => ['controller' => 'OrderController',   'action' => 'trackOrder'],

    // Bayaran
    'payment'       => ['controller' => 'PaymentController', 'action' => 'showPaymentPage'],
    'process-pay'   => ['controller' => 'PaymentController', 'action' => 'processPayment'],

    // Dapur / Staff
    'kitchen'       => ['controller' => 'KitchenController', 'action' => 'dashboard'],
    'kitchen-update'=> ['controller' => 'KitchenController', 'action' => 'updateOrderStatus'],
    'kitchen-orders'=> ['controller' => 'KitchenController', 'action' => 'getNewOrders'],
    'staff-order'   => ['controller' => 'KitchenController', 'action' => 'staffCreateOrder'],

    // Laporan Jualan
    'sales'         => ['controller' => 'SalesController',   'action' => 'dashboardSales'],
    'sales-daily'   => ['controller' => 'SalesController',   'action' => 'dailyReport'],
    'sales-monthly' => ['controller' => 'SalesController',   'action' => 'monthlyReport'],
    'sales-yearly'  => ['controller' => 'SalesController',   'action' => 'yearlyReport'],
    'sales-top'     => ['controller' => 'SalesController',   'action' => 'topItems'],
    'sales-export'  => ['controller' => 'SalesController',   'action' => 'exportCSV'],

    // Pergi Pasar
    'grocery'       => ['controller' => 'GroceryController', 'action' => 'showGroceryDashboard'],
    'grocery-create'=> ['controller' => 'GroceryController', 'action' => 'createList'],
    'grocery-auto'  => ['controller' => 'GroceryController', 'action' => 'autoGenerate'],
    'grocery-edit'  => ['controller' => 'GroceryController', 'action' => 'editList'],
    'grocery-toggle'=> ['controller' => 'GroceryController', 'action' => 'toggleItem'],
    'grocery-done'  => ['controller' => 'GroceryController', 'action' => 'completeList'],
    'grocery-history'=> ['controller' => 'GroceryController','action' => 'listHistory'],

    // Admin - Pengurusan Menu
    'admin-menu'    => ['controller' => 'MenuController',    'action' => 'showMenuAdmin'],
    'admin-menu-add'=> ['controller' => 'MenuController',    'action' => 'addItem'],
    'admin-menu-edit'=> ['controller' => 'MenuController',   'action' => 'editItem'],
    'admin-menu-delete'=> ['controller' => 'MenuController', 'action' => 'deleteItem'],

    // API endpoints (AJAX)
    'api-order-status'   => ['controller' => 'OrderController',   'action' => 'apiOrderStatus'],
    'api-kitchen'        => ['controller' => 'KitchenController', 'action' => 'apiGetOrders'],
    'api-cart-add'       => ['controller' => 'OrderController',   'action' => 'addToCart'],
    'api-cart-update'    => ['controller' => 'OrderController',   'action' => 'updateCart'],
    'api-cart-remove'    => ['controller' => 'OrderController',   'action' => 'removeFromCart'],
    'api-kitchen-update' => ['controller' => 'KitchenController', 'action' => 'updateOrderStatus'],

    // Bayaran (alias)
    'payment-process'    => ['controller' => 'PaymentController', 'action' => 'processPayment'],

    // Super Admin - Konfigurasi
    'config'             => ['controller' => 'ConfigController',  'action' => 'showConfig'],
    'config-save'        => ['controller' => 'ConfigController',  'action' => 'saveConfig'],
    'config-test-db'     => ['controller' => 'ConfigController',  'action' => 'testConnection'],
    'manage-users'       => ['controller' => 'ConfigController',  'action' => 'manageUsers'],
    'update-user-role'   => ['controller' => 'ConfigController',  'action' => 'updateUserRole'],
    'delete-user'        => ['controller' => 'ConfigController',  'action' => 'deleteUser'],
    'csrf-token'         => ['controller' => 'AuthController',    'action' => 'getCsrfToken'],
];

// =========================================================
// DISPATCH
// =========================================================
if (isset($routes[$page])) {
    $route = $routes[$page];
    $controllerFile = BASE_PATH . 'controllers/' . $route['controller'] . '.php';

    if (file_exists($controllerFile)) {
        require_once $controllerFile;
        $controller = new $route['controller']();
        $method = $route['action'];

        if (method_exists($controller, $method)) {
            $controller->$method();
        } else {
            http_response_code(404);
            echo '<h1>404 - Kaedah tidak ditemui</h1>';
            Logger::error("Method not found: {$route['controller']}::{$method}");
        }
    } else {
        http_response_code(404);
        echo '<h1>404 - Controller tidak ditemui</h1>';
        Logger::error("Controller not found: {$controllerFile}");
    }
} else {
    http_response_code(404);
    echo '<h1>404 - Halaman tidak ditemui</h1>';
}
