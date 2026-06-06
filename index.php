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
        echo '<pre>Fatal Error: ' . htmlspecialchars($e->getMessage()) . "\n\n";
        echo 'File: ' . htmlspecialchars($e->getFile()) . ':' . $e->getLine() . '</pre>';
    }
    error_log('Fatal Error: ' . $e->getMessage() . ' in ' . $e->getFile() . ':' . $e->getLine());
    http_response_code(500);
    echo '<h1>500 - Internal Server Error</h1><p>Please check the error log or contact administrator.</p>';
}
