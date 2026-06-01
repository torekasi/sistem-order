<?php
/**
 * =========================================================
 * MenuController - Pengurusan Menu
 * =========================================================
 */

require_once BASE_PATH . 'models/MenuModel.php';

class MenuController {

    private MenuModel $menuModel;

    public function __construct() {
        $this->menuModel = new MenuModel();
    }

    /**
     * Papar menu kepada pelanggan
     */
    public function showMenu(): void {
        $categories = $this->menuModel->getAllCategories();
        $allItems = $this->menuModel->getAllItems();
        $popularItems = $this->menuModel->getPopularItems();

        // Susun item mengikut kategori
        $menuByCategory = [];
        foreach ($allItems as $item) {
            $menuByCategory[$item['category_id']][] = $item;
        }

        require_once BASE_PATH . 'views/menu.php';
    }

    /**
     * Papar menu admin (CRUD)
     */
    public function showMenuAdmin(): void {
        Security::requireRole('admin');
        $categories = $this->menuModel->getAllCategories();
        $items = $this->menuModel->getAllItemsAdmin();
        require_once BASE_PATH . 'views/admin/menu-management.php';
    }

    /**
     * Tambah item menu (admin)
     */
    public function addItem(): void {
        Security::requireRole('admin');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . APP_URL . '/index.php?page=admin-menu');
            exit;
        }

        if (!Security::validateCSRFToken($_POST[CSRF_TOKEN_NAME] ?? '')) {
            $_SESSION['error'] = 'Token keselamatan tidak sah.';
            header('Location: ' . APP_URL . '/index.php?page=admin-menu');
            exit;
        }

        $categoryId = Security::sanitizeInt($_POST['category_id'] ?? 0);
        $nama = Security::sanitize($_POST['nama'] ?? '');
        $penerangan = Security::sanitize($_POST['penerangan'] ?? '');
        $harga = Security::sanitizeFloat($_POST['harga'] ?? 0);
        $popular = isset($_POST['popular']) ? 1 : 0;
        $gambar = '';

        // Handle upload gambar
        if (isset($_FILES['gambar']) && $_FILES['gambar']['error'] === UPLOAD_ERR_OK) {
            $gambar = $this->handleUpload($_FILES['gambar']);
            if (empty($gambar)) {
                // Return if upload failed, error is already set in handleUpload
                header('Location: ' . APP_URL . '/index.php?page=admin-menu');
                exit;
            }
        }

        if (empty($nama) || $harga <= 0 || $categoryId <= 0) {
            $_SESSION['error'] = 'Sila isi semua maklumat yang diperlukan.';
            header('Location: ' . APP_URL . '/index.php?page=admin-menu');
            exit;
        }

        $id = $this->menuModel->addItem($categoryId, $nama, $penerangan, $harga, $gambar, $popular);

        if ($id) {
            $_SESSION['success'] = 'Item menu berjaya ditambah.';
            Logger::admin("Tambah menu item", ['id' => $id, 'nama' => $nama, 'user_id' => Security::currentUserId()]);
        } else {
            $_SESSION['error'] = 'Gagal menambah item menu.';
        }

        header('Location: ' . APP_URL . '/index.php?page=admin-menu');
        exit;
    }

    /**
     * Kemaskini item menu (admin)
     */
    public function editItem(): void {
        Security::requireRole('admin');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . APP_URL . '/index.php?page=admin-menu');
            exit;
        }

        if (!Security::validateCSRFToken($_POST[CSRF_TOKEN_NAME] ?? '')) {
            $_SESSION['error'] = 'Token keselamatan tidak sah.';
            header('Location: ' . APP_URL . '/index.php?page=admin-menu');
            exit;
        }

        $id = Security::sanitizeInt($_POST['id'] ?? 0);
        $data = [
            'category_id' => Security::sanitizeInt($_POST['category_id'] ?? 0),
            'nama' => Security::sanitize($_POST['nama'] ?? ''),
            'penerangan' => Security::sanitize($_POST['penerangan'] ?? ''),
            'harga' => Security::sanitizeFloat($_POST['harga'] ?? 0),
            'status' => Security::sanitize($_POST['status'] ?? 'tersedia'),
            'popular' => isset($_POST['popular']) ? 1 : 0,
        ];

        if (isset($_FILES['gambar']) && $_FILES['gambar']['error'] === UPLOAD_ERR_OK) {
            $upload_path = $this->handleUpload($_FILES['gambar']);
            if (empty($upload_path)) {
                header('Location: ' . APP_URL . '/index.php?page=admin-menu');
                exit;
            }
            $data['gambar'] = $upload_path;
        }

        if ($this->menuModel->updateItem($id, $data)) {
            $_SESSION['success'] = 'Item menu berjaya dikemaskini.';
            Logger::admin("Edit menu item", ['id' => $id, 'user_id' => Security::currentUserId()]);
        } else {
            if (!isset($_SESSION['error'])) {
                $_SESSION['error'] = 'Gagal mengemaskini item menu.';
            }
        }

        header('Location: ' . APP_URL . '/index.php?page=admin-menu');
        exit;
    }

    /**
     * Padam item menu (admin - hard delete via AJAX POST)
     */
    public function deleteItem(): void {
        Security::requireRole('admin');
        header('Content-Type: application/json');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Kaedah tidak dibenarkan.']);
            exit;
        }

        if (!Security::validateCSRFToken($_POST[CSRF_TOKEN_NAME] ?? '')) {
            echo json_encode(['success' => false, 'message' => 'Token keselamatan tidak sah.']);
            exit;
        }

        $id = Security::sanitizeInt($_POST['id'] ?? 0);
        if ($id <= 0) {
            echo json_encode(['success' => false, 'message' => 'ID tidak sah.']);
            exit;
        }

        if ($this->menuModel->deleteItem($id)) {
            Logger::admin("Padam menu item", ['id' => $id, 'user_id' => Security::currentUserId()]);
            echo json_encode(['success' => true, 'message' => 'Item menu berjaya dipadam.']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Gagal memadam item menu.']);
        }
        exit;
    }

    /**
     * Handle upload gambar
     */
    private function handleUpload(array $file): string {
        if ($file['size'] > UPLOAD_MAX_SIZE) {
            $_SESSION['error'] = 'Saiz fail melebihi had (2MB).';
            return '';
        }

        if (!in_array($file['type'], UPLOAD_ALLOWED_TYPES)) {
            $_SESSION['error'] = 'Jenis fail tidak dibenarkan.';
            return '';
        }

        if (!is_dir(UPLOAD_DIR)) {
            mkdir(UPLOAD_DIR, 0755, true);
        }

        $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
        $filename = 'menu_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
        $dest = UPLOAD_DIR . $filename;

        if (move_uploaded_file($file['tmp_name'], $dest)) {
            return 'assets/uploads/' . $filename;
        }

        return '';
    }
}
