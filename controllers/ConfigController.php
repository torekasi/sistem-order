<?php
/**
 * =========================================================
 * ConfigController - Pengurusan Konfigurasi Sistem (Super Admin)
 * =========================================================
 */

require_once BASE_PATH . 'models/SettingsModel.php';
require_once BASE_PATH . 'models/UserModel.php';

class ConfigController {

    private SettingsModel $settingsModel;

    public function __construct() {
        $this->settingsModel = new SettingsModel();
    }

    /**
     * Papar halaman konfigurasi sistem
     */
    public function showConfig(): void {
        Security::requireRole('superadmin');

        $groups = $this->settingsModel->getGroups();
        $settingsByGroup = [];
        foreach ($groups as $group) {
            $settingsByGroup[$group] = $this->settingsModel->getByGroup($group);
        }

        $groupLabels = [
            'database'    => '🗄️ Pangkalan Data',
            'application' => '⚙️ Aplikasi',
            'order'       => '📋 Pesanan',
            'payment'     => '💰 Bayaran',
            'store'       => '🏪 Maklumat Kedai',
        ];

        require_once BASE_PATH . 'views/admin/config.php';
    }

    /**
     * Simpan tetapan yang dikemaskini
     */
    public function saveConfig(): void {
        Security::requireRole('superadmin');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . APP_URL . '/index.php?page=config');
            exit;
        }

        if (!Security::validateCSRFToken($_POST[CSRF_TOKEN_NAME] ?? '')) {
            $_SESSION['error'] = 'Token keselamatan tidak sah.';
            header('Location: ' . APP_URL . '/index.php?page=config');
            exit;
        }

        $settings = $_POST['settings'] ?? [];
        $sanitized = [];

        foreach ($settings as $key => $value) {
            $sanitized[Security::sanitize($key)] = Security::sanitize($value);
        }

        // Handle boolean fields (unchecked checkboxes don't send values)
        $booleanKeys = ['order_auto_confirm', 'payment_cash_enabled', 'payment_qr_enabled'];
        foreach ($booleanKeys as $bk) {
            if (!isset($sanitized[$bk])) {
                $sanitized[$bk] = '0';
            }
        }

        // Handle File uploads
        if (isset($_FILES['file_settings']['name']) && is_array($_FILES['file_settings']['name'])) {
            foreach ($_FILES['file_settings']['name'] as $key => $name) {
                if ($_FILES['file_settings']['error'][$key] === UPLOAD_ERR_OK) {
                    $tmpName = $_FILES['file_settings']['tmp_name'][$key];
                    $size = $_FILES['file_settings']['size'][$key];
                    $type = $_FILES['file_settings']['type'][$key];

                    if ($size > UPLOAD_MAX_SIZE || !in_array($type, UPLOAD_ALLOWED_TYPES)) {
                        $_SESSION['error'] = 'Gagal muat naik fail ('.htmlspecialchars($key).'): Saiz atas had atau fail tidak sah.';
                        header('Location: ' . APP_URL . '/index.php?page=config');
                        exit;
                    }

                    $ext = strtolower(pathinfo($name, PATHINFO_EXTENSION));
                    $safeKey = Security::sanitize($key);
                    $newFilename = 'logo_' . $safeKey . '_' . time() . '.' . $ext;
                    $dest = UPLOAD_DIR . $newFilename;

                    if (move_uploaded_file($tmpName, $dest)) {
                        $oldVal = $sanitized[$safeKey] ?? '';
                        if (!empty($oldVal) && file_exists(UPLOAD_DIR . $oldVal)) {
                            unlink(UPLOAD_DIR . $oldVal);
                        }
                        $sanitized[$safeKey] = $newFilename;
                    }
                }
            }
        }

        if ($this->settingsModel->updateBatch($sanitized)) {
            $_SESSION['success'] = 'Tetapan sistem berjaya dikemaskini.';
            Logger::admin("Kemaskini tetapan sistem", [
                'user_id' => Security::currentUserId(),
                'keys' => array_keys($sanitized)
            ]);
        } else {
            $_SESSION['error'] = 'Gagal mengemaskini tetapan.';
        }

        header('Location: ' . APP_URL . '/index.php?page=config');
        exit;
    }

    public function manageUsers(): void {
        Security::requireRole('superadmin', 'admin');

        $userModel = new UserModel();
        $users = $userModel->getAllUsers();
        
        // Hide superadmin from admin view
        $currentUserRole = $_SESSION['user_role'] ?? '';
        if ($currentUserRole !== 'superadmin') {
            $users = array_filter($users, function($u) {
                return $u['role'] !== 'superadmin';
            });
        }

        require_once BASE_PATH . 'views/admin/users.php';
    }

    /**
     * Kemaskini peranan pengguna
     */
    public function updateUserRole(): void {
        Security::requireRole('superadmin', 'admin');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . APP_URL . '/index.php?page=manage-users');
            exit;
        }

        if (!Security::validateCSRFToken($_POST[CSRF_TOKEN_NAME] ?? '')) {
            $_SESSION['error'] = 'Token keselamatan tidak sah.';
            header('Location: ' . APP_URL . '/index.php?page=manage-users');
            exit;
        }

        $userId = Security::sanitizeInt($_POST['user_id'] ?? 0);
        $role = Security::sanitize($_POST['role'] ?? '');
        $status = Security::sanitize($_POST['status'] ?? 'aktif');

        $validRoles = ['superadmin', 'admin', 'staff', 'cashier', 'customer', 'buyer'];
        if (!in_array($role, $validRoles)) {
            $_SESSION['error'] = 'Peranan tidak sah.';
            header('Location: ' . APP_URL . '/index.php?page=manage-users');
            exit;
        }

        // Jangan biar superadmin tukar peranan sendiri
        if ($userId === Security::currentUserId()) {
            $_SESSION['error'] = 'Anda tidak boleh menukar peranan sendiri.';
            header('Location: ' . APP_URL . '/index.php?page=manage-users');
            exit;
        }
        
        $userModel = new UserModel();
        
        // Admin tak boleh edit/assign superadmin
        $currentUserRole = $_SESSION['user_role'] ?? '';
        if ($currentUserRole !== 'superadmin') {
            $targetUser = $userModel->getUserById($userId);
            if ($role === 'superadmin' || ($targetUser && $targetUser['role'] === 'superadmin')) {
                $_SESSION['error'] = 'Akses ditolak. Anda tidak boleh mengurus akaun Super Admin.';
                header('Location: ' . APP_URL . '/index.php?page=manage-users');
                exit;
            }
        }

        $userModel = new UserModel();
        if ($userModel->updateUser($userId, ['role' => $role, 'status' => $status])) {
            $_SESSION['success'] = 'Pengguna berjaya dikemaskini.';
            Logger::admin("Kemaskini peranan pengguna", [
                'target_user_id' => $userId,
                'role' => $role,
                'user_id' => Security::currentUserId()
            ]);
        } else {
            $_SESSION['error'] = 'Gagal mengemaskini pengguna.';
        }

        header('Location: ' . APP_URL . '/index.php?page=manage-users');
        exit;
    }

    /**
     * Test sambungan DB (AJAX)
     */
    public function testConnection(): void {
        Security::requireRole('superadmin');
        header('Content-Type: application/json');

        $host = Security::sanitize($_POST['host'] ?? 'localhost');
        $name = Security::sanitize($_POST['name'] ?? '');
        $user = Security::sanitize($_POST['user'] ?? '');
        $pass = $_POST['pass'] ?? '';

        try {
            $dsn = "mysql:host={$host};dbname={$name};charset=utf8mb4";
            $testPdo = new PDO($dsn, $user, $pass, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_TIMEOUT => 5,
            ]);
            echo json_encode(['success' => true, 'message' => 'Sambungan berjaya!']);
        } catch (PDOException $e) {
            echo json_encode(['success' => false, 'message' => 'Gagal: ' . $e->getMessage()]);
        }
    }
}
