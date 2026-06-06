<?php
$pageTitle = 'Menu - Sistem Order';
require_once BASE_PATH . 'views/includes/header.php';
?>

<div class="container" id="menuListingContainer">
    <!-- Hero Section -->
    <div class="page-header text-center" style="padding: 40px 0 20px;">
        <h1>🍽️ Pilih Menu Anda</h1>
        <p>Pilih makanan kegemaran anda dan buat pesanan dengan mudah</p>
    </div>

    <!-- Search Bar & Layout Toggle -->
    <div class="d-flex justify-between align-center" style="max-width:500px; margin:0 auto 24px; gap:10px;">
        <div style="position:relative; flex:1;">
            <i class="bi bi-search" style="position:absolute; left:14px; top:50%; transform:translateY(-50%); color:var(--text-muted);"></i>
            <input type="text" class="form-control" id="searchMenu" placeholder="Cari menu..." style="padding-left:40px; padding-right:40px;">
            <i class="bi bi-x-circle-fill" id="clearSearch" style="position:absolute; right:14px; top:50%; transform:translateY(-50%); color:var(--text-muted); cursor:pointer; display:none;"></i>
        </div>
        <div class="view-toggle d-flex" style="background: var(--bg-input); border-radius: var(--radius-sm); padding: 4px;">
            <button class="btn btn-sm btn-view-toggle layout-btn-grid" data-view="grid" style="padding: 6px 12px; border-radius: 4px; background: transparent; border: none; color: var(--text-muted);"><i class="bi bi-grid"></i></button>
            <button class="btn btn-sm btn-view-toggle layout-btn-list" data-view="list" style="padding: 6px 12px; border-radius: 4px; background: transparent; border: none; color: var(--text-muted);"><i class="bi bi-list-ul"></i></button>
        </div>
    </div>

    <!-- Category Tabs -->
    <style>
        .category-tabs-sticky {
            position: sticky;
            top: 0;
            z-index: 900;
            background: var(--bg-dark);
            padding: 12px 20px 8px;
            margin-left: -20px;
            margin-right: -20px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.05);
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.2);
            transition: all 0.3s ease;
        }
        @supports (backdrop-filter: blur(16px)) {
            .category-tabs-sticky {
                background: rgba(15, 15, 20, 0.7);
                backdrop-filter: blur(16px);
                -webkit-backdrop-filter: blur(16px);
            }
        }
    </style>
    <div class="category-tabs category-tabs-sticky">
        <button class="category-tab active" data-category="all">Semua</button>
        <?php foreach ($categories as $cat): ?>
            <button class="category-tab" data-category="<?= $cat['id'] ?>"><?= htmlspecialchars($cat['nama']) ?></button>
        <?php endforeach; ?>
    </div>

    <!-- Popular Section -->
    <?php if (!empty($popularItems)): ?>
    <div class="section">
        <h2 class="section-title"><span class="icon">🔥</span> Popular</h2>
        <div class="grid grid-4">
            <?php foreach ($popularItems as $item): ?>
                <div class="card menu-item" data-category="<?= $item['category_id'] ?>" data-name="<?= htmlspecialchars(strtolower($item['nama'])) ?>">
                    <?php if ($item['gambar']): ?>
                        <img src="<?= APP_URL ?>/<?= htmlspecialchars($item['gambar']) ?>" alt="<?= htmlspecialchars($item['nama']) ?>" class="card-img">
                    <?php else: ?>
                        <div class="card-img" style="display:flex;align-items:center;justify-content:center;font-size:2.5rem;background:linear-gradient(135deg,var(--bg-input),var(--bg-card-hover));">🍽️</div>
                    <?php endif; ?>
                    <div class="card-body">
                        <div class="card-title"><?= htmlspecialchars($item['nama']) ?></div>
                        <div class="card-text"><?= nl2br(htmlspecialchars($item['penerangan'] ?? '', ENT_QUOTES, 'UTF-8', false)) ?></div>
                    </div>
                    <div class="card-footer">
                        <span class="card-price">RM <?= number_format($item['harga'], 2) ?></span>
                        <button class="btn btn-primary btn-sm btn-add-cart" data-id="<?= $item['id'] ?>" data-nama="<?= htmlspecialchars($item['nama']) ?>">
                            <i class="bi bi-plus"></i> Tambah
                        </button>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>

    <!-- All Menu by Category -->
    <?php foreach ($categories as $cat): ?>
        <?php $catItems = $menuByCategory[$cat['id']] ?? []; ?>
        <?php if (!empty($catItems)): ?>
        <div class="section category-section" data-cat-id="<?= $cat['id'] ?>">
            <h2 class="section-title"><?= htmlspecialchars($cat['nama']) ?></h2>
            <div class="grid grid-4">
                <?php foreach ($catItems as $item): ?>
                    <div class="card menu-item" data-category="<?= $item['category_id'] ?>" data-name="<?= htmlspecialchars(strtolower($item['nama'])) ?>">
                        <?php if ($item['gambar']): ?>
                            <img src="<?= APP_URL ?>/<?= htmlspecialchars($item['gambar']) ?>" alt="<?= htmlspecialchars($item['nama']) ?>" class="card-img">
                        <?php else: ?>
                            <div class="card-img" style="display:flex;align-items:center;justify-content:center;font-size:2.5rem;background:linear-gradient(135deg,var(--bg-input),var(--bg-card-hover));">🍽️</div>
                        <?php endif; ?>
                        <div class="card-body">
                            <div class="card-title"><?= htmlspecialchars($item['nama']) ?></div>
                            <div class="card-text"><?= nl2br(htmlspecialchars($item['penerangan'] ?? '', ENT_QUOTES, 'UTF-8', false)) ?></div>
                        </div>
                        <div class="card-footer">
                            <span class="card-price">RM <?= number_format($item['harga'], 2) ?></span>
                            <button class="btn btn-primary btn-sm btn-add-cart" data-id="<?= $item['id'] ?>" data-nama="<?= htmlspecialchars($item['nama']) ?>">
                                <i class="bi bi-plus"></i> Tambah
                            </button>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>
    <?php endforeach; ?>
</div>

<!-- Toast Notification -->
<div id="toast" style="position:fixed;bottom:20px;right:20px;background:var(--bg-card);border:1px solid var(--success);color:var(--success);padding:12px 20px;border-radius:var(--radius-sm);font-size:0.9rem;display:none;z-index:1500;animation:slideUp 0.3s ease;">
    <i class="bi bi-check-circle"></i> <span id="toastText"></span>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const viewButtons = document.querySelectorAll('.btn-view-toggle');
    const menuContainer = document.getElementById('menuListingContainer');
    
    function setViewLayout(layout) {
        if (layout === 'list') {
            menuContainer.classList.remove('layout-grid');
            menuContainer.classList.add('layout-list');
            document.querySelector('.layout-btn-grid').style.background = 'transparent';
            document.querySelector('.layout-btn-grid').style.color = 'var(--text-muted)';
            document.querySelector('.layout-btn-grid').style.boxShadow = 'none';
            document.querySelector('.layout-btn-list').style.background = 'var(--bg-card)';
            document.querySelector('.layout-btn-list').style.color = 'var(--primary)';
            document.querySelector('.layout-btn-list').style.boxShadow = 'var(--shadow-sm)';
        } else {
            menuContainer.classList.remove('layout-list');
            menuContainer.classList.add('layout-grid');
            document.querySelector('.layout-btn-list').style.background = 'transparent';
            document.querySelector('.layout-btn-list').style.color = 'var(--text-muted)';
            document.querySelector('.layout-btn-list').style.boxShadow = 'none';
            document.querySelector('.layout-btn-grid').style.background = 'var(--bg-card)';
            document.querySelector('.layout-btn-grid').style.color = 'var(--primary)';
            document.querySelector('.layout-btn-grid').style.boxShadow = 'var(--shadow-sm)';
        }
        localStorage.setItem('menuLayout', layout);
    }

    viewButtons.forEach(btn => {
        btn.addEventListener('click', (e) => {
            e.preventDefault();
            setViewLayout(btn.dataset.view);
        });
    });

    const savedLayout = localStorage.getItem('menuLayout') || 'list';
    setViewLayout(savedLayout);
});
</script>

<?php require_once BASE_PATH . 'views/includes/footer.php'; ?>
