<?php
/**
 * =========================================================
 * SISTEM ORDER - Konfigurasi Utama
 * =========================================================
 * AMARAN: Jangan ubah fail ini tanpa kebenaran.
 */

// Elak akses langsung
if (!defined('BASE_PATH')) {
    define('BASE_PATH', dirname(__DIR__) . DIRECTORY_SEPARATOR);
}

// =========================================================
// DATABASE
// =========================================================
// Auto-detect Docker environment
$isDocker = (getenv('APACHE_DOCUMENT_ROOT') !== false) || file_exists('/.dockerenv');

define('DB_HOST', $isDocker ? 'db' : 'localhost');
define('DB_NAME', 'sistem_order');
define('DB_USER', $isDocker ? 'sistem_user' : 'root');
define('DB_PASS', $isDocker ? 'sistem_pass123' : '');
define('DB_CHARSET', 'utf8mb4');

// =========================================================
// APLIKASI
// =========================================================
define('APP_NAME', 'Sistem Order');
$isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
    || (isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == 443)
    || (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && strtolower($_SERVER['HTTP_X_FORWARDED_PROTO']) === 'https');
$protocol = $isHttps ? "https://" : "http://";
$domainName = $_SERVER['HTTP_HOST'] ?? 'localhost';
$pathDir = isset($_SERVER['SCRIPT_NAME']) ? dirname($_SERVER['SCRIPT_NAME']) : '';
$pathDir = str_replace('\\', '/', $pathDir);
if ($pathDir === '/') {
    $pathDir = '';
}
define('APP_URL', $protocol . $domainName . $pathDir);
define('APP_TIMEZONE', 'Asia/Kuala_Lumpur');

// =========================================================
// SESSION
// =========================================================
define('SESSION_LIFETIME', 3600); // 1 jam
define('SESSION_NAME', 'sistem_order_sess');

// =========================================================
// UPLOAD
// =========================================================
define('UPLOAD_DIR', BASE_PATH . 'public/assets/uploads/');
define('UPLOAD_MAX_SIZE', 5 * 1024 * 1024); // 5MB
define('UPLOAD_ALLOWED_TYPES', ['image/jpeg', 'image/png', 'image/webp', 'image/gif']);

// =========================================================
// SECURITY
// =========================================================
define('CSRF_TOKEN_NAME', 'csrf_token');
define('PASSWORD_COST', 12);

// =========================================================
// TIMEZONE
// =========================================================
date_default_timezone_set(APP_TIMEZONE);

// =========================================================
// ERROR HANDLING (Production)
// =========================================================
ini_set('display_errors', 0);
ini_set('display_startup_errors', 0);
error_reporting(E_ALL);
ini_set('log_errors', 1);
ini_set('error_log', BASE_PATH . 'logs/error.log');

// =========================================================
// DATABASE CONNECTION (PDO)
// =========================================================
function getDB(): PDO {
    static $pdo = null;
    if ($pdo === null) {
        try {
            $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
            $pdo = new PDO($dsn, DB_USER, DB_PASS, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ]);
        } catch (PDOException $e) {
            error_log("Database Connection Error: " . $e->getMessage());
            die("Ralat sambungan pangkalan data. Sila hubungi pentadbir.");
        }
    }
    return $pdo;
}

// =========================================================
// SESSION START
// =========================================================
function initSession(): void {
    if (session_status() === PHP_SESSION_NONE) {
        session_name(SESSION_NAME);
        session_set_cookie_params([
            'lifetime' => SESSION_LIFETIME,
            'path' => '/',
            'httponly' => true,
            'samesite' => 'Strict',
        ]);
        session_start();
    }
}

// =========================================================
// SECURITY HEADERS
// =========================================================
function setSecurityHeaders(): void {
    header('X-Frame-Options: DENY');
    header('X-Content-Type-Options: nosniff');
    header('X-XSS-Protection: 1; mode=block');
    header('Referrer-Policy: strict-origin-when-cross-origin');
    header("Content-Security-Policy: default-src 'self'; script-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net https://fonts.googleapis.com; style-src 'self' 'unsafe-inline' https://fonts.googleapis.com https://cdn.jsdelivr.net; font-src 'self' https://fonts.gstatic.com https://cdn.jsdelivr.net; img-src 'self' data:;");
}
