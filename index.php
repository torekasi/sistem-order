<?php
/**
 * =========================================================
 * SISTEM ORDER - Root Entry Point
 * =========================================================
 * When document root points here (not to public/), this file
 * boots the application directly — no .htaccess dependency.
 */
define('BASE_PATH', __DIR__ . DIRECTORY_SEPARATOR);
require_once BASE_PATH . '.config.php';
require_once BASE_PATH . 'utils/Logger.php';
require_once BASE_PATH . 'utils/Security.php';
initSession();
setSecurityHeaders();
Logger::init();
define('BOOTED_FROM_ROOT', true);
require_once BASE_PATH . 'public/index.php';