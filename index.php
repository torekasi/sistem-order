<?php
/**
 * =========================================================
 * SISTEM ORDER - Root Entry Point
 * =========================================================
 * When document root points here (not to public/), this file
 * boots the application directly — no .htaccess dependency.
 */

if (PHP_VERSION_ID < 70200) {
    die('PHP 7.2 or higher is required. Your server is running PHP ' . phpversion());
}

define('BASE_PATH', __DIR__ . DIRECTORY_SEPARATOR);

require_once BASE_PATH . '.config.php';
require_once BASE_PATH . 'utils/Logger.php';
require_once BASE_PATH . 'utils/Security.php';

try {
    initSession();
    setSecurityHeaders();
    Logger::init();
    define('BOOTED_FROM_ROOT', true);
    require_once BASE_PATH . 'public/index.php';
} catch (\Throwable $e) {
    if (ini_get('display_errors')) {
        echo '<div style="background:#1a1a1a;color:#ff4d4d;padding:20px;font-family:sans-serif;border:1px solid #333;margin:20px;border-radius:8px;">';
        echo '<h3 style="margin-top:0;">Root Entry Error</h3>';
        echo '<pre style="background:#000;padding:10px;border-radius:4px;overflow:auto;">' . htmlspecialchars($e->getMessage()) . '</pre>';
        echo '<small>' . htmlspecialchars($e->getFile()) . ':' . $e->getLine() . '</small>';
        echo '</div>';
    }
    error_log('Fatal Error: ' . $e->getMessage() . ' in ' . $e->getFile() . ':' . $e->getLine());
    http_response_code(500);
    echo '<div style="background:#0f0f14;color:#fff;height:100vh;display:flex;align-items:center;justify-content:center;font-family:sans-serif;text-align:center;">';
    echo '<div><h1 style="color:#ff4d4d;">500</h1><p>Internal Server Error</p><small style="color:#666;">Sila hubungi pentadbir sistem.</small></div>';
    echo '</div>';
}
