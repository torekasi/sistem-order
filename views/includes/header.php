<?php
/**
 * =========================================================
 * Header Template
 * =========================================================
 */
$cartCount = isset($_SESSION['cart']) ? array_sum(array_column($_SESSION['cart'], 'kuantiti')) : 0;
$isLoggedIn = Security::isLoggedIn();
$userRole = Security::currentUserRole();
$userName = $_SESSION['user_nama'] ?? '';

require_once BASE_PATH . 'models/SettingsModel.php';
$_sModel = new SettingsModel();
$_storeLogo = $_sModel->get('store_logo', '');
$_storeName = $_sModel->get('app_name', 'Sistem Order');
?>
<!DOCTYPE html>
<html lang="ms">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Sistem Order - Pesanan makanan dalam talian">
    <title><?= htmlspecialchars($pageTitle ?? $_storeName) ?></title>
    <link rel="stylesheet" href="<?= APP_URL ?>/assets/css/style.css?v=<?= filemtime(BASE_PATH . 'public/assets/css/style.css') ?>">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
</head>
<body>

<!-- Loading Overlay -->
<div class="loading-overlay" id="loadingOverlay">
    <div class="spinner"></div>
    <div class="loading-text">Memproses...</div>
</div>

<!-- Navbar -->
<nav class="navbar" style="padding-bottom: 0; border-bottom: none;">
    <div class="container" style="justify-content: flex-start; padding-bottom: 10px;">
        <a href="<?= APP_URL ?>/index.php?page=menu" class="navbar-brand">
            <?php if (!empty($_storeLogo)): ?>
                <img src="<?= APP_URL ?>/assets/uploads/<?= htmlspecialchars($_storeLogo) ?>" alt="Logo" style="height: 32px; border-radius: 4px; object-fit: contain;">
            <?php else: ?>
                <span class="brand-icon">🍽️</span>
            <?php endif; ?>
            <?= htmlspecialchars($_storeName) ?>
        </a>
    </div>
</nav>



<?php if ($isLoggedIn): ?>
<!-- Sub Navbar / User Role Menu -->
<div class="user-sub-navbar">
    <div class="container" style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 10px;">
        <div class="user-welcome" style="font-size: 0.85rem; color: var(--text-secondary); white-space: nowrap;">
            <i class="bi bi-person-circle"></i> Hai, <strong><?= htmlspecialchars($userName) ?></strong>
            <span class="badge" style="background:var(--primary-glow); color:var(--primary); margin-left:5px; font-size:0.65rem; padding: 2px 6px;"><?= strtoupper($userRole) ?></span>
        </div>
        
        <ul class="sub-navbar-nav">
            <?php if ($userRole === 'superadmin'): ?>
                <li><a href="<?= APP_URL ?>/index.php?page=config"><i class="bi bi-gear-wide-connected"></i> Konfigurasi</a></li>
            <?php endif; ?>

            <?php if (in_array($userRole, ['superadmin', 'admin'])): ?>
                <li><a href="<?= APP_URL ?>/index.php?page=manage-users"><i class="bi bi-people"></i> Pengguna</a></li>
            <?php endif; ?>

            <?php if (in_array($userRole, ['superadmin', 'admin', 'staff', 'cashier'])): ?>
                <li><a href="<?= APP_URL ?>/index.php?page=staff-order"><i class="bi bi-calculator"></i> Cashier</a></li>
            <?php endif; ?>

            <?php if (in_array($userRole, ['superadmin', 'admin'])): ?>
                <li><a href="<?= APP_URL ?>/index.php?page=sales"><i class="bi bi-graph-up"></i> Jualan</a></li>
                <li><a href="<?= APP_URL ?>/index.php?page=admin-menu"><i class="bi bi-pencil-square"></i> Urus Menu</a></li>
            <?php endif; ?>
            
            <?php if (in_array($userRole, ['superadmin', 'admin', 'staff', 'cashier'])): ?>
                <li><a href="<?= APP_URL ?>/index.php?page=kitchen"><i class="bi bi-fire"></i> Dapur</a></li>
            <?php endif; ?>
            
            <?php if (in_array($userRole, ['superadmin', 'admin', 'buyer'])): ?>
                <li><a href="<?= APP_URL ?>/index.php?page=grocery"><i class="bi bi-basket3"></i> Pergi Pasar</a></li>
            <?php endif; ?>

            <?php if ($userRole === 'customer'): ?>
                <li><a href="<?= APP_URL ?>/index.php?page=order-history"><i class="bi bi-clock-history"></i> Sejarah</a></li>
            <?php endif; ?>
        </ul>
    </div>
</div>
<?php endif; ?>

<!-- Flash Messages -->
<div class="container">
    <?php if (!empty($_SESSION['success'])): ?>
        <div class="alert alert-success mt-2"><i class="bi bi-check-circle"></i> <?= $_SESSION['success'] ?></div>
        <?php unset($_SESSION['success']); ?>
    <?php endif; ?>

    <?php if (!empty($_SESSION['error'])): ?>
        <div class="alert alert-error mt-2"><i class="bi bi-exclamation-circle"></i> <?= $_SESSION['error'] ?></div>
        <?php unset($_SESSION['error']); ?>
    <?php endif; ?>
</div>
