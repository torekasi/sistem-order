<?php
/**
 * =========================================================
 * GroceryController - Modul Pergi Pasar
 * =========================================================
 */

require_once BASE_PATH . 'models/GroceryModel.php';

class GroceryController {

    private $groceryModel;

    public function __construct() {
        $this->groceryModel = new GroceryModel();
    }

    /**
     * Dashboard Pergi Pasar
     */
    public function showGroceryDashboard(): void {
        Security::requireRole('admin', 'buyer');

        $activeLists = $this->groceryModel->getAllActiveLists();
        $recentHistory = $this->groceryModel->getListHistory(5);

        require_once BASE_PATH . 'views/grocery/dashboard.php';
    }

    /**
     * Buat senarai belanja baru (manual)
     */
    public function createList(): void {
        Security::requireRole('admin', 'buyer');

        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            require_once BASE_PATH . 'views/grocery/create-list.php';
            return;
        }

        if (!Security::validateCSRFToken($_POST[CSRF_TOKEN_NAME] ?? '')) {
            $_SESSION['error'] = 'Token keselamatan tidak sah.';
            header('Location: ' . APP_URL . '/index.php?page=grocery');
            exit;
        }

        $tajuk = Security::sanitize($_POST['tajuk'] ?? '');
        $tarikh = Security::sanitize($_POST['tarikh'] ?? date('Y-m-d'));
        $nota = Security::sanitize($_POST['nota'] ?? '');

        $listId = $this->groceryModel->createList($tajuk, $tarikh, Security::currentUserId(), $nota);

        if ($listId) {
            // Tambah item jika ada
            $itemNames = $_POST['item_nama'] ?? [];
            $itemQtys = $_POST['item_kuantiti'] ?? [];
            $itemUnits = $_POST['item_unit'] ?? [];
            $itemPrices = $_POST['item_harga'] ?? [];

            foreach ($itemNames as $i => $nama) {
                if (!empty($nama)) {
                    $this->groceryModel->addItem(
                        $listId,
                        Security::sanitize($nama),
                        Security::sanitizeFloat($itemQtys[$i] ?? 1),
                        Security::sanitize($itemUnits[$i] ?? 'unit'),
                        Security::sanitizeFloat($itemPrices[$i] ?? 0)
                    );
                }
            }

            $_SESSION['success'] = 'Senarai belanja berjaya dibuat.';
            Logger::admin("Buat senarai belanja", ['list_id' => $listId, 'user_id' => Security::currentUserId()]);
            header('Location: ' . APP_URL . '/index.php?page=grocery-edit&id=' . $listId);
        } else {
            $_SESSION['error'] = 'Gagal membuat senarai.';
            header('Location: ' . APP_URL . '/index.php?page=grocery');
        }
        exit;
    }

    /**
     * Auto-generate senarai dari jualan
     */
    public function autoGenerate(): void {
        Security::requireRole('admin', 'buyer');

        $days = Security::sanitizeInt($_GET['days'] ?? 7);
        $listId = $this->groceryModel->autoGenerateFromSales(Security::currentUserId(), $days);

        if ($listId) {
            $_SESSION['success'] = 'Senarai belanja auto-generated berjaya!';
            Logger::admin("Auto-generate senarai belanja", ['list_id' => $listId, 'days' => $days]);
            header('Location: ' . APP_URL . '/index.php?page=grocery-edit&id=' . $listId);
        } else {
            $_SESSION['error'] = 'Gagal menjana senarai. Mungkin tiada data jualan.';
            header('Location: ' . APP_URL . '/index.php?page=grocery');
        }
        exit;
    }

    /**
     * Edit / lihat senarai belanja (checklist)
     */
    public function editList(): void {
        Security::requireRole('admin', 'buyer');

        $listId = Security::sanitizeInt($_GET['id'] ?? 0);
        $list = $this->groceryModel->getListById($listId);

        if (!$list) {
            $_SESSION['error'] = 'Senarai tidak ditemui.';
            header('Location: ' . APP_URL . '/index.php?page=grocery');
            exit;
        }

        $items = $this->groceryModel->getListItems($listId);

        // Handle tambah item baru via POST
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_item'])) {
            if (Security::validateCSRFToken($_POST[CSRF_TOKEN_NAME] ?? '')) {
                $nama = Security::sanitize($_POST['nama'] ?? '');
                $kuantiti = Security::sanitizeFloat($_POST['kuantiti'] ?? 1);
                $unit = Security::sanitize($_POST['unit'] ?? 'unit');
                $harga = Security::sanitizeFloat($_POST['harga_anggaran'] ?? 0);

                if (!empty($nama)) {
                    $this->groceryModel->addItem($listId, $nama, $kuantiti, $unit, $harga);
                    $_SESSION['success'] = 'Item berjaya ditambah.';
                }
            }
            header('Location: ' . APP_URL . '/index.php?page=grocery-edit&id=' . $listId);
            exit;
        }

        require_once BASE_PATH . 'views/grocery/checklist.php';
    }

    /**
     * Toggle item checked (AJAX)
     */
    public function toggleItem(): void {
        Security::requireRole('admin', 'buyer');
        header('Content-Type: application/json');

        $itemId = Security::sanitizeInt($_POST['item_id'] ?? 0);
        $hargaSebenar = isset($_POST['harga_sebenar']) ? Security::sanitizeFloat($_POST['harga_sebenar']) : null;

        if ($hargaSebenar !== null && $hargaSebenar > 0) {
            $result = $this->groceryModel->updateItemPrice($itemId, $hargaSebenar);
        } else {
            $result = $this->groceryModel->toggleItemChecked($itemId);
        }

        $item = $this->groceryModel->getItemById($itemId);
        $list = $item ? $this->groceryModel->getListById($item['list_id']) : null;

        echo json_encode([
            'success' => $result,
            'item' => $item,
            'list_totals' => $list ? [
                'anggaran' => $list['jumlah_anggaran'],
                'sebenar' => $list['jumlah_sebenar'],
            ] : null,
        ]);
    }

    /**
     * Tandak senarai selesai
     */
    public function completeList(): void {
        Security::requireRole('admin', 'buyer');

        $listId = Security::sanitizeInt($_GET['id'] ?? 0);

        if ($this->groceryModel->completeList($listId)) {
            $_SESSION['success'] = 'Senarai belanja ditandakan selesai.';
            Logger::admin("Selesai senarai belanja", ['list_id' => $listId, 'user_id' => Security::currentUserId()]);
        } else {
            $_SESSION['error'] = 'Gagal mengemaskini senarai.';
        }

        header('Location: ' . APP_URL . '/index.php?page=grocery');
        exit;
    }

    /**
     * Sejarah senarai belanja
     */
    public function listHistory(): void {
        Security::requireRole('admin', 'buyer');
        $history = $this->groceryModel->getListHistory(50);
        require_once BASE_PATH . 'views/grocery/history.php';
    }
}
