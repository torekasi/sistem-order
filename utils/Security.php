<?php
/**
 * =========================================================
 * Security - Utiliti Keselamatan
 * =========================================================
 */

class Security {

    /**
     * Jana CSRF token dan simpan dalam session
     */
    public static function generateCSRFToken(): string {
        if (empty($_SESSION[CSRF_TOKEN_NAME])) {
            $_SESSION[CSRF_TOKEN_NAME] = bin2hex(random_bytes(32));
        }
        return $_SESSION[CSRF_TOKEN_NAME];
    }

    /**
     * Sahkan CSRF token
     */
    public static function validateCSRFToken(string $token): bool {
        if (empty($_SESSION[CSRF_TOKEN_NAME])) {
            return false;
        }
        $valid = hash_equals($_SESSION[CSRF_TOKEN_NAME], $token);
        // Regenerate selepas guna
        unset($_SESSION[CSRF_TOKEN_NAME]);
        return $valid;
    }

    /**
     * Render hidden input field untuk CSRF
     */
    public static function csrfField(): string {
        $token = self::generateCSRFToken();
        return '<input type="hidden" name="' . CSRF_TOKEN_NAME . '" value="' . htmlspecialchars($token) . '">';
    }

    /**
     * Sanitize input string
     */
    public static function sanitize(string $input): string {
        return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
    }

    /**
     * Sanitize integer
     */
    public static function sanitizeInt($input): int {
        return (int) filter_var($input, FILTER_SANITIZE_NUMBER_INT);
    }

    /**
     * Sanitize decimal/float
     */
    public static function sanitizeFloat($input): float {
        return (float) filter_var($input, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
    }

    /**
     * Sanitize email
     */
    public static function sanitizeEmail(string $input): string {
        return filter_var(trim($input), FILTER_SANITIZE_EMAIL);
    }

    /**
     * Hash kata laluan
     */
    public static function hashPassword(string $password): string {
        return password_hash($password, PASSWORD_BCRYPT, ['cost' => PASSWORD_COST]);
    }

    /**
     * Sahkan kata laluan
     */
    public static function verifyPassword(string $password, string $hash): bool {
        return password_verify($password, $hash);
    }

    /**
     * Sahkan pengguna sudah login
     */
    public static function isLoggedIn(): bool {
        return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
    }

    /**
     * Dapatkan ID pengguna semasa
     */
    public static function currentUserId(): ?int {
        return $_SESSION['user_id'] ?? null;
    }

    /**
     * Dapatkan peranan pengguna semasa
     */
    public static function currentUserRole(): ?string {
        return $_SESSION['user_role'] ?? null;
    }

    /**
     * Semak kebenaran peranan
     * superadmin sentiasa mendapat akses ke semua halaman
     */
    public static function hasRole(string ...$roles): bool {
        $currentRole = self::currentUserRole();
        if ($currentRole === 'superadmin') return true;
        return $currentRole !== null && in_array($currentRole, $roles);
    }

    /**
     * Redirect jika tiada kebenaran
     */
    public static function requireRole(string ...$roles): void {
        if (!self::isLoggedIn()) {
            header('Location: ' . APP_URL . '/index.php?page=login');
            exit;
        }
        if (!self::hasRole(...$roles)) {
            http_response_code(403);
            die('Akses ditolak. Anda tiada kebenaran untuk halaman ini.');
        }
    }

    /**
     * Redirect jika belum login
     */
    public static function requireLogin(): void {
        if (!self::isLoggedIn()) {
            header('Location: ' . APP_URL . '/index.php?page=login');
            exit;
        }
    }

    /**
     * Jana nombor pesanan unik
     */
    public static function generateOrderNumber(): string {
        $date = date('dmy'); // Format: 220326 (DDMMYY)
        $number = sprintf('%04d', random_int(1000, 9999));
        return "{$date}-{$number}";
    }
}
