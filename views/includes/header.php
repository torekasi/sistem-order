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

<style>
/* Floating Bottom Navigation */
.floating-bottom-nav {
    position: fixed;
    bottom: 0;
    left: 0;
    width: 100%;
    margin: 0;
    background: rgba(26, 26, 36, 0.95);
    backdrop-filter: blur(20px);
    -webkit-backdrop-filter: blur(20px);
    border-top: 1px solid rgba(255, 255, 255, 0.08);
    box-shadow: 0 -5px 20px rgba(0, 0, 0, 0.5);
    border-radius: 24px 24px 0 0;
    display: flex;
    justify-content: space-around;
    align-items: center;
    padding: 12px 15px;
    padding-bottom: calc(12px + env(safe-area-inset-bottom));
    z-index: 1000;
}

.floating-bottom-nav a {
    color: var(--text-secondary);
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 4px;
    font-size: 0.75rem;
    font-weight: 500;
    text-decoration: none;
    transition: all 0.3s ease;
    position: relative;
    padding: 8px 12px;
    border-radius: 16px;
}

.floating-bottom-nav a i {
    font-size: 1.25rem;
}

.floating-bottom-nav a.active,
.floating-bottom-nav a:hover {
    color: var(--primary);
    background: rgba(255, 107, 53, 0.1);
}

.floating-bottom-nav a.active::after {
    content: '';
    position: absolute;
    bottom: -8px;
    width: 20px;
    height: 4px;
    background: var(--primary);
    border-radius: 4px;
    box-shadow: 0 0 10px var(--primary-glow);
}

/* Adjust body padding for the bottom nav */
body {
    padding-bottom: 90px;
}

/* Adjust floating menu for desktop */
@media (min-width: 768px) {
    <?php if ($isLoggedIn && in_array($userRole, ['superadmin', 'admin', 'staff', 'cashier'])): ?>
    /* Admin side: Horizontal Top */
    .floating-bottom-nav {
        top: 12px;
        bottom: auto;
        left: auto;
        right: 20px;
        width: auto;
        background: transparent;
        box-shadow: none;
        border: none;
        padding: 0;
        border-radius: 0;
        flex-direction: row;
        gap: 12px;
    }
    .floating-bottom-nav a {
        flex-direction: row;
        background: var(--bg-card);
        border: 1px solid rgba(255,255,255,0.1);
        box-shadow: 0 4px 15px rgba(0,0,0,0.2);
        padding: 8px 16px;
        gap: 8px;
    }
    .floating-bottom-nav a.active::after {
        display: none;
    }
    body {
        padding-bottom: 0px;
    }
    <?php else: ?>
    /* Customer side: Bottom Floating */
    .floating-bottom-nav {
        padding: 6px 15px;
        padding-bottom: 6px;
    }
    .floating-bottom-nav a {
        padding: 4px 12px;
        gap: 2px;
    }
    body {
        padding-bottom: 60px;
    }
    <?php endif; ?>
}
</style>

<!-- Floating Nav Dock -->
<div class="floating-bottom-nav">
<?php
$currentPage = $page ?? '';
if ($isLoggedIn && in_array($userRole, ['superadmin', 'admin'])): ?>
    <!-- ADMIN / SUPERADMIN bottom nav -->
    <a href="<?= APP_URL ?>/index.php?page=sales" class="<?= $currentPage === 'sales' ? 'active' : '' ?>">
        <i class="bi bi-speedometer2"></i>
        <span>Dashboard</span>
    </a>
    <a href="<?= APP_URL ?>/index.php?page=kitchen" class="<?= $currentPage === 'kitchen' ? 'active' : '' ?>">
        <i class="bi bi-receipt"></i>
        <span>Order List</span>
    </a>
    <a href="<?= APP_URL ?>/index.php?page=admin-menu" class="<?= $currentPage === 'admin-menu' ? 'active' : '' ?>">
        <i class="bi bi-pencil-square"></i>
        <span>Urus Menu</span>
    </a>
    <a href="<?= APP_URL ?>/index.php?page=staff-order" class="<?= $currentPage === 'staff-order' ? 'active' : '' ?>">
        <i class="bi bi-calculator"></i>
        <span>Cashier</span>
    </a>
    <?php if ($userRole === 'superadmin'): ?>
    <a href="<?= APP_URL ?>/index.php?page=config" class="<?= $currentPage === 'config' ? 'active' : '' ?>">
        <i class="bi bi-gear-wide-connected"></i>
        <span>Config</span>
    </a>
    <?php endif; ?>
    <a href="<?= APP_URL ?>/index.php?page=logout">
        <i class="bi bi-box-arrow-right"></i>
        <span>Keluar</span>
    </a>

<?php elseif ($isLoggedIn && $userRole === 'staff'): ?>
    <!-- STAFF bottom nav -->
    <a href="<?= APP_URL ?>/index.php?page=kitchen" class="<?= $currentPage === 'kitchen' ? 'active' : '' ?>">
        <i class="bi bi-fire"></i>
        <span>Dapur</span>
    </a>
    <a href="<?= APP_URL ?>/index.php?page=staff-order" class="<?= $currentPage === 'staff-order' ? 'active' : '' ?>">
        <i class="bi bi-calculator"></i>
        <span>Cashier</span>
    </a>
    <a href="<?= APP_URL ?>/index.php?page=menu" class="<?= $currentPage === 'menu' ? 'active' : '' ?>">
        <i class="bi bi-shop"></i>
        <span>Menu</span>
    </a>
    <a href="<?= APP_URL ?>/index.php?page=logout">
        <i class="bi bi-box-arrow-right"></i>
        <span>Keluar</span>
    </a>

<?php elseif ($isLoggedIn && $userRole === 'buyer'): ?>
    <!-- BUYER bottom nav -->
    <a href="<?= APP_URL ?>/index.php?page=grocery" class="<?= $currentPage === 'grocery' ? 'active' : '' ?>">
        <i class="bi bi-basket3"></i>
        <span>Pasar</span>
    </a>
    <a href="<?= APP_URL ?>/index.php?page=menu" class="<?= $currentPage === 'menu' ? 'active' : '' ?>">
        <i class="bi bi-shop"></i>
        <span>Menu</span>
    </a>
    <a href="<?= APP_URL ?>/index.php?page=cart" class="<?= $currentPage === 'cart' ? 'active' : '' ?>">
        <i class="bi bi-cart3" style="position:relative;">
            <?php if ($cartCount > 0): ?>
                <span class="cart-badge" id="cartBadge" style="position:absolute;top:-6px;right:-10px;width:18px;height:18px;font-size:0.65rem;border:2px solid var(--bg-card);"><?= $cartCount ?></span>
            <?php endif; ?>
        </i>
        <span>Troli</span>
    </a>
    <a href="<?= APP_URL ?>/index.php?page=logout">
        <i class="bi bi-box-arrow-right"></i>
        <span>Keluar</span>
    </a>

<?php elseif ($isLoggedIn && $userRole === 'customer'): ?>
    <!-- CUSTOMER bottom nav -->
    <a href="<?= APP_URL ?>/index.php?page=menu" class="<?= $currentPage === 'menu' ? 'active' : '' ?>">
        <i class="bi bi-shop"></i>
        <span>Menu</span>
    </a>
    <a href="<?= APP_URL ?>/index.php?page=track-order" class="<?= $currentPage === 'track-order' ? 'active' : '' ?>">
        <i class="bi bi-search"></i>
        <span>Pesanan</span>
    </a>
    <a href="<?= APP_URL ?>/index.php?page=cart" class="<?= $currentPage === 'cart' ? 'active' : '' ?>">
        <i class="bi bi-cart3" style="position:relative;">
            <?php if ($cartCount > 0): ?>
                <span class="cart-badge" id="cartBadge" style="position:absolute;top:-6px;right:-10px;width:18px;height:18px;font-size:0.65rem;border:2px solid var(--bg-card);"><?= $cartCount ?></span>
            <?php endif; ?>
        </i>
        <span>Troli</span>
    </a>
    <a href="<?= APP_URL ?>/index.php?page=order-history" class="<?= $currentPage === 'order-history' ? 'active' : '' ?>">
        <i class="bi bi-clock-history"></i>
        <span>Sejarah</span>
    </a>
    <a href="<?= APP_URL ?>/index.php?page=logout">
        <i class="bi bi-box-arrow-right"></i>
        <span>Keluar</span>
    </a>

<?php else: ?>
    <!-- GUEST bottom nav -->
    <a href="<?= APP_URL ?>/index.php?page=menu" class="<?= $currentPage === 'menu' ? 'active' : '' ?>">
        <i class="bi bi-shop"></i>
        <span>Menu</span>
    </a>
    <a href="<?= APP_URL ?>/index.php?page=track-order" class="<?= $currentPage === 'track-order' ? 'active' : '' ?>">
        <i class="bi bi-search"></i>
        <span>Pesanan</span>
    </a>
    <a href="<?= APP_URL ?>/index.php?page=cart" class="<?= $currentPage === 'cart' ? 'active' : '' ?>">
        <i class="bi bi-cart3" style="position:relative;">
            <?php if ($cartCount > 0): ?>
                <span class="cart-badge" id="cartBadge" style="position:absolute;top:-6px;right:-10px;width:18px;height:18px;font-size:0.65rem;border:2px solid var(--bg-card);"><?= $cartCount ?></span>
            <?php endif; ?>
        </i>
        <span>Troli</span>
    </a>
    <a href="<?= APP_URL ?>/index.php?page=login" class="<?= $currentPage === 'login' ? 'active' : '' ?>">
        <i class="bi bi-person"></i>
        <span>Log Masuk</span>
    </a>
<?php endif; ?>
</div>

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
    </div>
</nav>

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
