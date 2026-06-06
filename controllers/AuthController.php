<?php
/**
 * =========================================================
 * AuthController - Pengurusan Autentikasi
 * =========================================================
 */

require_once BASE_PATH . 'models/UserModel.php';

class AuthController {

    private UserModel $userModel;

    public function __construct() {
        $this->userModel = new UserModel();
    }

    /**
     * Papar halaman login
     */
    public function showLogin(): void {
        if (Security::isLoggedIn()) {
            $this->redirectByRole();
            return;
        }
        require_once BASE_PATH . 'views/login.php';
    }

    /**
     * Papar halaman daftar
     */
    public function showRegister(): void {
        if (Security::isLoggedIn()) {
            $this->redirectByRole();
            return;
        }
        require_once BASE_PATH . 'views/register.php';
    }

    /**
     * Proses login
     */
    public function processLogin(): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . url('login'));
            exit;
        }

        // CSRF validation
        if (!Security::validateCSRFToken($_POST[CSRF_TOKEN_NAME] ?? '')) {
            $_SESSION['error'] = 'Token keselamatan tidak sah. Sila cuba lagi.';
            header('Location: ' . url('login'));
            exit;
        }

        $loginId = Security::sanitize($_POST['login_id'] ?? '');
        $kata_laluan = $_POST['kata_laluan'] ?? '';

        if (empty($loginId) || empty($kata_laluan)) {
            $_SESSION['error'] = 'Sila isi semua maklumat.';
            header('Location: ' . url('login'));
            exit;
        }

        $user = $this->userModel->login($loginId, $kata_laluan);

        if ($user) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_nama'] = $user['nama'];
            $_SESSION['user_email'] = $user['email'];
            $_SESSION['user_role'] = $user['role'];

            Logger::info("Login berjaya", ['user_id' => $user['id'], 'login_id' => $loginId]);
            $this->redirectByRole();
        } else {
            $_SESSION['error'] = 'Email atau kata laluan salah.';
            header('Location: ' . url('login'));
        }
        exit;
    }

    /**
     * Proses pendaftaran
     */
    public function processRegister(): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . url('register'));
            exit;
        }

        if (!Security::validateCSRFToken($_POST[CSRF_TOKEN_NAME] ?? '')) {
            $_SESSION['error'] = 'Token keselamatan tidak sah.';
            header('Location: ' . url('register'));
            exit;
        }

        $nama = Security::sanitize($_POST['nama'] ?? '');
        $email = Security::sanitizeEmail($_POST['email'] ?? '');
        $telefon = Security::sanitize($_POST['telefon'] ?? '');
        $kata_laluan = $_POST['kata_laluan'] ?? '';
        $kata_laluan2 = $_POST['kata_laluan2'] ?? '';

        // Validasi
        $errors = [];
        if (empty($nama)) $errors[] = 'Nama diperlukan.';
        if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Email tidak sah.';
        if (strlen($kata_laluan) < 6) $errors[] = 'Kata laluan mestilah sekurang-kurangnya 6 aksara.';
        if ($kata_laluan !== $kata_laluan2) $errors[] = 'Kata laluan tidak sepadan.';
        if ($this->userModel->emailExists($email)) $errors[] = 'Email sudah didaftarkan.';

        if (!empty($errors)) {
            $_SESSION['error'] = implode('<br>', $errors);
            $_SESSION['old'] = ['nama' => $nama, 'email' => $email, 'telefon' => $telefon];
            header('Location: ' . url('register'));
            exit;
        }

        $userId = $this->userModel->register($nama, $email, $telefon, $kata_laluan);

        if ($userId) {
            $_SESSION['success'] = 'Pendaftaran berjaya! Sila log masuk.';
            Logger::info("Pendaftaran berjaya", ['user_id' => $userId, 'email' => $email]);
            header('Location: ' . url('login'));
        } else {
            $_SESSION['error'] = 'Pendaftaran gagal. Sila cuba lagi.';
            header('Location: ' . url('register'));
        }
        exit;
    }

    /**
     * Return fresh CSRF token as JSON (for AJAX requests)
     */
    public function getCsrfToken(): void {
        Security::requireLogin();
        header('Content-Type: application/json');
        echo json_encode(['token' => Security::generateCSRFToken()]);
        exit;
    }

    /**
     * Entry point untuk /admin - redirect ke login atau dashboard admin
     */
    public function adminEntry(): void {
        if (Security::isLoggedIn()) {
            $role = Security::currentUserRole();
            if ($role === 'admin') {
                header('Location: ' . url('sales'));
                exit;
            }
            // Bukan admin, log keluar dulu
            session_destroy();
        }
        header('Location: ' . url('login'));
        exit;
    }

    /**
     * Log keluar
     */
    public function logout(): void {
        $userId = Security::currentUserId();
        Logger::info("Logout", ['user_id' => $userId]);
        session_destroy();
        header('Location: ' . url('menu'));
        exit;
    }

    /**
     * Papar halaman tukar kata laluan
     */
    public function showChangePassword(): void {
        Security::requireLogin();
        $pageTitle = 'Tukar Kata Laluan - Sistem Order';
        require_once BASE_PATH . 'views/change-password.php';
    }

    /**
     * Proses tukar kata laluan
     */
    public function processChangePassword(): void {
        Security::requireLogin();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . url('change-password'));
            exit;
        }

        if (!Security::validateCSRFToken($_POST[CSRF_TOKEN_NAME] ?? '')) {
            $_SESSION['error'] = 'Token keselamatan tidak sah.';
            header('Location: ' . url('change-password'));
            exit;
        }

        $currentPassword = $_POST['kata_laluan_semasa'] ?? '';
        $newPassword = $_POST['kata_laluan_baru'] ?? '';
        $confirmPassword = $_POST['kata_laluan_ulang'] ?? '';

        $errors = [];
        if (empty($currentPassword)) $errors[] = 'Kata laluan semasa diperlukan.';
        if (strlen($newPassword) < 6) $errors[] = 'Kata laluan baru mestilah sekurang-kurangnya 6 aksara.';
        if ($newPassword !== $confirmPassword) $errors[] = 'Kata laluan baru tidak sepadan.';

        // Verify current password
        if (!$this->userModel->verifyPassword(Security::currentUserId(), $currentPassword)) {
            $errors[] = 'Kata laluan semasa tidak betul.';
        }

        if (!empty($errors)) {
            $_SESSION['error'] = implode('<br>', $errors);
            header('Location: ' . url('change-password'));
            exit;
        }

        // Update password
        $hash = Security::hashPassword($newPassword);
        $this->userModel->updateUser(Security::currentUserId(), ['kata_laluan' => $hash]);

        $_SESSION['success'] = 'Kata laluan berjaya ditukar.';
        Logger::admin("Tukar kata laluan", ['user_id' => Security::currentUserId()]);
        header('Location: ' . url('change-password'));
        exit;
    }

    /**
     * Redirect mengikut peranan
     */
    private function redirectByRole(): void {
        $role = Security::currentUserRole();
        $url = match ($role) {
            'superadmin' => url('sales'),
            'admin'      => url('sales'),
            'staff'      => url('kitchen'),
            'buyer'      => url('grocery'),
            default      => url('menu'),
        };
        header('Location: ' . $url);
        exit;
    }
}
