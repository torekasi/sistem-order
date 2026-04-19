<?php
/**
 * =========================================================
 * Logger - Pengendalian Log
 * =========================================================
 */

class Logger {
    
    private static string $logDir;

    /**
     * Inisialisasi direktori log
     */
    public static function init(): void {
        self::$logDir = BASE_PATH . 'logs' . DIRECTORY_SEPARATOR;
        if (!is_dir(self::$logDir)) {
            mkdir(self::$logDir, 0755, true);
        }
        if (!is_dir(self::$logDir . 'admin')) {
            mkdir(self::$logDir . 'admin', 0755, true);
        }
    }

    /**
     * Log ralat umum
     */
    public static function error(string $message, array $context = []): void {
        self::write('error.log', 'ERROR', $message, $context);
    }

    /**
     * Log maklumat
     */
    public static function info(string $message, array $context = []): void {
        self::write('error.log', 'INFO', $message, $context);
    }

    /**
     * Log aktiviti admin
     */
    public static function admin(string $message, array $context = []): void {
        self::write('admin/error.log', 'ADMIN', $message, $context);
    }

    /**
     * Tulis ke fail log
     */
    private static function write(string $file, string $level, string $message, array $context): void {
        self::init();
        $timestamp = date('Y-m-d H:i:s');
        $contextStr = !empty($context) ? ' | ' . json_encode($context, JSON_UNESCAPED_UNICODE) : '';
        $logLine = "[{$timestamp}] [{$level}] {$message}{$contextStr}" . PHP_EOL;
        
        $logFile = self::$logDir . $file;
        file_put_contents($logFile, $logLine, FILE_APPEND | LOCK_EX);
    }
}
