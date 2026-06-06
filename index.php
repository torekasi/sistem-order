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
require_once BASE_PATH . 'utils/Helpers.php';
require_once BASE_PATH . 'utils/Logger.php';
require_once BASE_PATH . 'utils/Security.php';

try {
    initSession();
    setSecurityHeaders();
    Logger::init();
    define('BOOTED_FROM_ROOT', true);
    require_once BASE_PATH . 'public/index.php';
} catch (\Throwable $e) {
    // Papar ralat secara paksa untuk tujuan troubleshooting di cPanel
    echo '<div style="background:#1a1a1a;color:#ff4d4d;padding:20px;font-family:sans-serif;border:1px solid #333;margin:20px;border-radius:8px;position:relative;z-index:9999;">';
    echo '<h3 style="margin-top:0;">Root Diagnostic Error</h3>';
    echo '<pre style="background:#000;padding:10px;border-radius:4px;overflow:auto;white-space:pre-wrap;word-break:break-all;">' . htmlspecialchars($e->getMessage()) . '</pre>';
    echo '<small>File: ' . htmlspecialchars($e->getFile()) . ':' . $e->getLine() . '</small>';
    echo '</div>';

    error_log('Fatal Error: ' . $e->getMessage() . ' in ' . $e->getFile() . ':' . $e->getLine());
    http_response_code(500);
}
