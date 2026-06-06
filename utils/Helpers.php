<?php
/**
 * =========================================================
 * SISTEM ORDER - Helper Functions
 * =========================================================
 * Tracked by git for consistency across environments.
 */

/**
 * Generate clean application URLs
 */
if (!function_exists('url')) {
    function url(string $path = ''): string {
        $base = defined('APP_URL') ? APP_URL : '';
        
        // Handle cases where APP_URL is not yet defined
        if (!$base) {
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
            $base = $protocol . $domainName . $pathDir;
        }

        if ($path === '' || $path === '/') {
            return $base;
        }
        return $base . '/' . ltrim($path, '/');
    }
}
