<?php
/**
 * =========================================================
 * SISTEM ORDER - Route Dispatcher (Entry Point)
 * =========================================================
 * Semua request masuk melalui fail ini.
 */

if (PHP_VERSION_ID < 70200) {
    die('PHP 7.2 or higher is required. Your server is running PHP ' . phpversion());
}

// Define base path (skip if already defined by root index.php)
if (!defined('BASE_PATH')) {
    define('BASE_PATH', dirname(__DIR__) . DIRECTORY_SEPARATOR);
}

// Skip bootstrap if root index.php already handled it
if (!defined('BOOTED_FROM_ROOT')) {
    require_once BASE_PATH . '.config.php';
    require_once BASE_PATH . 'utils/Logger.php';
    require_once BASE_PATH . 'utils/Security.php';

    try {
        initSession();
        setSecurityHeaders();
        Logger::init();
    } catch (\Throwable $e) {
        if (ini_get('display_errors')) {
            echo '<div style="background:#1a1a1a;color:#ff4d4d;padding:20px;font-family:sans-serif;border:1px solid #333;margin:20px;border-radius:8px;">';
            echo '<h3 style="margin-top:0;">Bootstrap Error</h3>';
            echo '<pre style="background:#000;padding:10px;border-radius:4px;overflow:auto;">' . htmlspecialchars($e->getMessage()) . '</pre>';
            echo '<small>' . htmlspecialchars($e->getFile()) . ':' . $e->getLine() . '</small>';
            echo '</div>';
        }
        error_log('Bootstrap Error: ' . $e->getMessage() . ' in ' . $e->getFile() . ':' . $e->getLine());
        http_response_code(500);
        echo '<div style="background:#0f0f14;color:#fff;height:100vh;display:flex;align-items:center;justify-content:center;font-family:sans-serif;text-align:center;">';
        echo '<div><h1 style="color:#ff4d4d;">500</h1><p>Internal Server Error</p><small style="color:#666;">Sila hubungi pentadbir sistem.</small></div>';
        echo '</div>';
        exit;
    }
}

// Dapatkan halaman yang diminta
// Sokong dua format: ?page=xxx ATAU URL pretty /xxx (via .htaccess rewrite)
if (isset($_GET['page'])) {
    $page = Security::sanitize($_GET['page']);
} else {
    // Extract path dari REQUEST_URI dan bersihkan sub-direktori
    $scriptDir = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME']));
    $requestUri = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?? '/';
    
    // Buang scriptDir dari requestUri jika ada (untuk sokongan sub-direktori cPanel)
    if ($scriptDir !== '/' && strpos($requestUri, $scriptDir) === 0) {
        $uri = substr($requestUri, strlen($scriptDir));
    } else {
        $uri = $requestUri;
    }

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
    'add-user'           => ['controller' => 'ConfigController',  'action' => 'addUser'],
    'csrf-token'         => ['controller' => 'AuthController',    'action' => 'getCsrfToken'],
    'change-password'    => ['controller' => 'AuthController',    'action' => 'showChangePassword'],
    'change-password-save' => ['controller' => 'AuthController',  'action' => 'processChangePassword'],
];

// =========================================================
// DISPATCH
// =========================================================
try {
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
                echo '<div style="background:#0f0f14;color:#fff;height:100vh;display:flex;align-items:center;justify-content:center;font-family:sans-serif;text-align:center;">';
                echo '<div><h1 style="color:#ffcc00;">404</h1><p>Kaedah tidak ditemui</p><a href="' . url('menu') . '" style="color:#0af;text-decoration:none;">Kembali ke Menu</a></div>';
                echo '</div>';
                Logger::error("Method not found: {$route['controller']}::{$method}");
            }
        } else {
            http_response_code(404);
            echo '<div style="background:#0f0f14;color:#fff;height:100vh;display:flex;align-items:center;justify-content:center;font-family:sans-serif;text-align:center;">';
            echo '<div><h1 style="color:#ffcc00;">404</h1><p>Controller tidak ditemui</p><a href="' . url('menu') . '" style="color:#0af;text-decoration:none;">Kembali ke Menu</a></div>';
            echo '</div>';
            Logger::error("Controller not found: {$controllerFile}");
        }
    } else {
        http_response_code(404);
        echo '<div style="background:#0f0f14;color:#fff;height:100vh;display:flex;align-items:center;justify-content:center;font-family:sans-serif;text-align:center;">';
        echo '<div><h1 style="color:#ffcc00;">404</h1><p>Halaman tidak ditemui</p><a href="' . url('menu') . '" style="color:#0af;text-decoration:none;">Kembali ke Menu</a></div>';
        echo '</div>';
    }
} catch (\Throwable $e) {
    // Papar ralat secara paksa untuk tujuan troubleshooting di cPanel
    echo '<div style="background:#1a1a1a;color:#ff4d4d;padding:20px;font-family:sans-serif;border:1px solid #333;margin:20px;border-radius:8px;position:relative;z-index:9999;">';
    echo '<h3 style="margin-top:0;">Diagnostic Error Details</h3>';
    echo '<pre style="background:#000;padding:10px;border-radius:4px;overflow:auto;white-space:pre-wrap;word-break:break-all;">' . htmlspecialchars($e->getMessage()) . '</pre>';
    echo '<small>File: ' . htmlspecialchars($e->getFile()) . ':' . $e->getLine() . '</small>';
    echo '</div>';
    
    error_log('Runtime Error: ' . $e->getMessage() . ' in ' . $e->getFile() . ':' . $e->getLine());
    http_response_code(500);
}
