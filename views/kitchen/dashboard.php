<?php
$pageTitle = 'Dashboard Dapur - Sistem Order';
$extraJS = ['kitchen.js'];
require_once BASE_PATH . 'views/includes/header.php';
?>

<div class="container">
    <div class="page-header d-flex justify-between align-center" style="flex-wrap:wrap;gap:12px;">
        <div>
            <h1><i class="bi bi-fire"></i> Dashboard Dapur</h1>
            <p>Pesanan aktif yang perlu disediakan</p>
        </div>
        <div class="d-flex gap-1">
            <a href="<?= APP_URL ?>/index.php?page=staff-order" class="btn btn-primary"><i class="bi bi-plus-circle"></i> Buat Order Baru</a>
            <button class="btn btn-secondary" onclick="refreshOrders()"><i class="bi bi-arrow-clockwise"></i> Refresh</button>
        </div>
    </div>

    <!-- Stats -->
    <div class="stats-grid" id="kitchenStats">
        <?php
        $counts = ['pending' => 0, 'confirmed' => 0, 'preparing' => 0, 'ready' => 0];
        foreach ($ordersWithItems as $o) {
            if (isset($counts[$o['status']])) $counts[$o['status']]++;
        }
        ?>
        <div class="stat-card">
            <div class="stat-icon" style="background:var(--warning-bg);color:var(--warning);">⏳</div>
            <div class="stat-value"><?= $counts['pending'] ?></div>
            <div class="stat-label">Menunggu</div>
        </div>
        <div class="stat-card">
            <div class="stat-icon" style="background:var(--info-bg);color:var(--info);">✅</div>
            <div class="stat-value"><?= $counts['confirmed'] ?></div>
            <div class="stat-label">Disahkan</div>
        </div>
        <div class="stat-card">
            <div class="stat-icon" style="background:rgba(168,85,247,0.15);color:#A855F7;">👨‍🍳</div>
            <div class="stat-value"><?= $counts['preparing'] ?></div>
            <div class="stat-label">Sedang Disediakan</div>
        </div>
        <div class="stat-card">
            <div class="stat-icon" style="background:var(--success-bg);color:var(--success);">🔔</div>
            <div class="stat-value"><?= $counts['ready'] ?></div>
            <div class="stat-label">Siap</div>
        </div>
    </div>

    <style>
        .kitchen-tabs {
            display: flex;
            gap: 10px;
            margin-bottom: 20px;
        }
        .orders-grid {
            display: grid;
            grid-template-columns: 1fr;
            gap: 16px;
        }
        @media (min-width: 992px) {
            .orders-grid {
                grid-template-columns: 1fr 1fr;
            }
        }
    </style>

    <div class="kitchen-tabs">
        <button id="tabActiveBtn" class="btn btn-primary" onclick="switchKitchenTab('active')">Pesanan Aktif</button>
        <button id="tabCompletedBtn" class="btn btn-secondary" onclick="switchKitchenTab('completed')">Selesai (Hari Ini)</button>
    </div>

    <!-- Order Cards (Active) -->
    <div id="ordersList" class="orders-grid">
        <?php if (empty($ordersWithItems)): ?>
            <div class="empty-state" style="grid-column: 1 / -1;">
                <div class="icon">✨</div>
                <h3>Tiada pesanan aktif</h3>
                <p class="text-muted">Pesanan baru akan muncul di sini secara automatik</p>
            </div>
        <?php else: ?>
            <?php foreach ($ordersWithItems as $order): ?>
                <div class="order-card" id="order-<?= $order['id'] ?>">
                    <div class="order-header">
                        <div>
                            <span class="order-number"><?= htmlspecialchars($order['no_pesanan']) ?></span>
                            <?php if ($order['no_meja']): ?>
                                <span class="badge badge-confirmed" style="margin-left:8px;">Meja <?= htmlspecialchars($order['no_meja']) ?></span>
                            <?php endif; ?>
                        </div>
                        <div class="d-flex align-center gap-1">
                            <span class="badge badge-<?= $order['status'] ?>">
                                <?= ucfirst($order['status'] === 'preparing' ? 'Sedang Disediakan' : ($order['status'] === 'confirmed' ? 'Disahkan' : ($order['status'] === 'ready' ? 'Siap' : $order['status']))) ?>
                            </span>
                            <span class="text-muted fs-sm"><?= date('H:i', strtotime($order['created_at'])) ?></span>
                        </div>
                    </div>
                    
                    <div style="margin-bottom:8px;">
                        <span class="text-muted fs-sm">Pelanggan:</span>
                        <span class="fw-bold"><?= htmlspecialchars($order['nama_pelanggan']) ?></span>
                    </div>

                    <ul class="order-items-list">
                        <?php foreach ($order['items'] as $item): ?>
                            <li>
                                <span><?= htmlspecialchars($item['nama_item']) ?></span>
                                <span class="fw-bold">x<?= $item['kuantiti'] ?></span>
                            </li>
                        <?php endforeach; ?>
                    </ul>

                    <?php if ($order['nota']): ?>
                        <div style="background:var(--warning-bg);padding:8px 12px;border-radius:var(--radius-sm);margin-bottom:12px;font-size:0.85rem;color:var(--warning);">
                            <i class="bi bi-sticky"></i> <?= htmlspecialchars($order['nota']) ?>
                        </div>
                    <?php endif; ?>

                    <div class="d-flex justify-between align-center" style="flex-wrap:wrap;gap:8px;">
                        <span class="fw-bold text-primary">RM <?= number_format($order['jumlah_harga'], 2) ?></span>
                        <div class="order-actions">
                            <?php if ($order['status'] === 'pending'): ?>
                                <button class="btn btn-success btn-sm btn-update-status" data-id="<?= $order['id'] ?>" data-status="confirmed"><i class="bi bi-check"></i> Terima</button>
                                <button class="btn btn-danger btn-sm btn-update-status" data-id="<?= $order['id'] ?>" data-status="cancelled"><i class="bi bi-x"></i> Tolak</button>
                            <?php elseif ($order['status'] === 'confirmed'): ?>
                                <button class="btn btn-primary btn-sm btn-update-status" data-id="<?= $order['id'] ?>" data-status="preparing"><i class="bi bi-fire"></i> Mula Sediakan</button>
                            <?php elseif ($order['status'] === 'preparing'): ?>
                                <button class="btn btn-success btn-sm btn-update-status" data-id="<?= $order['id'] ?>" data-status="ready"><i class="bi bi-bell"></i> Siap!</button>
                            <?php elseif ($order['status'] === 'ready'): ?>
                                <button class="btn btn-secondary btn-sm btn-update-status" data-id="<?= $order['id'] ?>" data-status="completed"><i class="bi bi-check-all"></i> Selesai</button>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <!-- Completed Orders Cards -->
    <div id="completedOrdersList" class="orders-grid" style="display:none;">
        <?php if (empty($completedOrdersWithItems)): ?>
            <div class="empty-state" style="grid-column: 1 / -1;">
                <div class="icon">✅</div>
                <h3>Tiada pesanan selesai hari ini</h3>
            </div>
        <?php else: ?>
            <?php foreach ($completedOrdersWithItems as $order): ?>
                <div class="order-card" id="order-<?= $order['id'] ?>" style="opacity: 0.8;">
                    <div class="order-header">
                        <div>
                            <span class="order-number"><?= htmlspecialchars($order['no_pesanan']) ?></span>
                            <?php if ($order['no_meja']): ?>
                                <span class="badge badge-confirmed" style="margin-left:8px;">Meja <?= htmlspecialchars($order['no_meja']) ?></span>
                            <?php endif; ?>
                        </div>
                        <div class="d-flex align-center gap-1">
                            <span class="badge badge-completed">Selesai</span>
                            <span class="text-muted fs-sm"><?= date('H:i', strtotime($order['created_at'])) ?></span>
                        </div>
                    </div>
                    
                    <div style="margin-bottom:8px;">
                        <span class="text-muted fs-sm">Pelanggan:</span>
                        <span class="fw-bold"><?= htmlspecialchars($order['nama_pelanggan']) ?></span>
                    </div>

                    <ul class="order-items-list">
                        <?php foreach ($order['items'] as $item): ?>
                            <li>
                                <span><?= htmlspecialchars($item['nama_item']) ?></span>
                                <span class="fw-bold text-muted">x<?= $item['kuantiti'] ?></span>
                            </li>
                        <?php endforeach; ?>
                    </ul>

                    <div class="d-flex justify-between align-center" style="flex-wrap:wrap;gap:8px;">
                        <span class="fw-bold text-primary">RM <?= number_format($order['jumlah_harga'], 2) ?></span>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<script>
function switchKitchenTab(tab) {
    const btnActive = document.getElementById('tabActiveBtn');
    const btnCompleted = document.getElementById('tabCompletedBtn');
    const listActive = document.getElementById('ordersList');
    const listCompleted = document.getElementById('completedOrdersList');

    if (tab === 'active') {
        btnActive.className = 'btn btn-primary';
        btnCompleted.className = 'btn btn-secondary';
        listActive.style.display = 'grid';
        listCompleted.style.display = 'none';
        document.getElementById('kitchenStats').style.display = 'grid'; // show stats only for active
    } else {
        btnCompleted.className = 'btn btn-primary';
        btnActive.className = 'btn btn-secondary';
        listCompleted.style.display = 'grid';
        listActive.style.display = 'none';
        document.getElementById('kitchenStats').style.display = 'none'; // hide stats
    }
}
</script>

<?php require_once BASE_PATH . 'views/includes/footer.php'; ?>
