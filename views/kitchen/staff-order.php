<?php
$pageTitle = 'Cashier / POS - Sistem Order';
$extraJS = ['kitchen.js'];
require_once BASE_PATH . 'views/includes/header.php';
?>

<div class="container">
    <div class="page-header d-flex justify-between align-center" style="flex-wrap:wrap;gap:12px;">
        <div>
            <h1><i class="bi bi-calculator"></i> Cashier Dashboard</h1>
            <p>Urus pesanan dan ambil order pelanggan</p>
        </div>
        <div class="d-flex gap-1" id="cashierTopButtons">
            <button class="btn btn-primary" onclick="toggleOrderForm()"><i class="bi bi-plus-circle"></i> Buat Order Baru</button>
            <button class="btn btn-secondary" onclick="refreshOrders()"><i class="bi bi-arrow-clockwise"></i> Refresh</button>
        </div>
    </div>

    <!-- The exact Dashboard Orders Lists from dashboard.php -->
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

    <div class="kitchen-tabs" id="posTabs">
        <button id="tabActiveBtn" class="btn btn-primary" onclick="switchKitchenTab('active')">Pesanan Aktif</button>
        <button id="tabCompletedBtn" class="btn btn-secondary" onclick="switchKitchenTab('completed')">Selesai (Hari Ini)</button>
    </div>

    <!-- Active Orders -->
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

    <!-- Creation Form Section -->
    <div id="createOrderWrapper" style="display:none; margin-top:10px; border-top:2px dashed var(--border-color); padding-top:20px;">
        <div class="page-header d-flex justify-between align-center" style="flex-wrap:wrap; gap:10px; margin-bottom: 20px;">
            <div>
                <h2 style="font-size:1.5rem;"><i class="bi bi-cart-plus"></i> Form Buat Order Baru</h2>
            </div>
            <button type="button" class="btn btn-secondary btn-sm" onclick="toggleOrderForm()"><i class="bi bi-x"></i> Tutup Form</button>
        </div>

        <form method="POST" action="<?= APP_URL ?>/index.php?page=staff-order" id="staffOrderForm">
            <?= Security::csrfField() ?>
            
            <div style="display:grid;grid-template-columns:1fr 380px;gap:24px;align-items:start;">
                <!-- Menu Selection -->
                <div>
                    <h3 class="mb-2">Pilih Menu</h3>
                    <?php foreach ($categories as $cat): ?>
                        <?php $catItems = $menuByCategory[$cat['id']] ?? []; ?>
                        <?php if (!empty($catItems)): ?>
                            <h4 style="color:var(--text-secondary);margin:16px 0 8px;font-size:0.95rem;"><?= htmlspecialchars($cat['nama']) ?></h4>
                            <?php foreach ($catItems as $item): ?>
                                <div class="card" style="margin-bottom:8px;padding:12px;" id="staff-item-<?= $item['id'] ?>">
                                    <div style="display:flex;align-items:center;gap:12px;">
                                        <div style="flex:1;">
                                            <div class="fw-bold"><?= htmlspecialchars($item['nama']) ?></div>
                                            <div class="text-primary fs-sm">RM <?= number_format($item['harga'], 2) ?></div>
                                        </div>
                                        <div style="display:flex;align-items:center;gap:6px;">
                                            <button type="button" class="btn btn-secondary btn-icon btn-sm" onclick="staffQty(<?= $item['id'] ?>, -1, <?= $item['harga'] ?>)">
                                                <i class="bi bi-dash"></i>
                                            </button>
                                            <span id="sQty-<?= $item['id'] ?>" style="min-width:28px;text-align:center;font-weight:600;">0</span>
                                            <button type="button" class="btn btn-primary btn-icon btn-sm" onclick="staffQty(<?= $item['id'] ?>, 1, <?= $item['harga'] ?>)">
                                                <i class="bi bi-plus"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </div>

                <!-- Order Summary -->
                <div class="card" style="position:sticky;top:80px;">
                    <div class="card-body">
                        <h3 style="margin-bottom:16px;">Maklumat Pesanan</h3>
                        <div class="form-group">
                            <label class="form-label">Nama Pelanggan</label>
                            <input type="text" name="nama_pelanggan" class="form-control" value="Walk-in" required>
                        </div>
                        <?php if ($_sModel->get('order_allow_table', '1') === '1'): ?>
                        <div class="form-group">
                            <label class="form-label">No. Meja</label>
                            <input type="text" name="no_meja" class="form-control" placeholder="A1, B2...">
                        </div>
                        <?php endif; ?>
                        <div class="form-group">
                            <label class="form-label">Nota</label>
                            <textarea name="nota" class="form-control" rows="2" placeholder="Nota tambahan"></textarea>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Kaedah Bayaran</label>
                            <select name="kaedah_bayaran" class="form-control">
                                <option value="tunai">💵 Tunai</option>
                                <option value="qr">📱 QR Pay</option>
                            </select>
                        </div>
                        <div class="form-group mt-1">
                            <label class="form-label">Status Bayaran</label>
                            <select name="status_bayaran" class="form-control">
                                <option value="pending">Belum Dibayar (Pending)</option>
                                <option value="berjaya" selected>Telah Dibayar (Berjaya)</option>
                            </select>
                        </div>

                        <div id="staffOrderSummary" style="border-top:1px solid var(--border-color);padding-top:16px;margin-top:16px;">
                            <div class="text-muted fs-sm text-center">Belum pilih item</div>
                        </div>

                        <!-- Hidden inputs for items -->
                        <div id="staffItemInputs"></div>

                        <div style="border-top:1px solid var(--border-color);padding-top:16px;margin-top:16px;">
                            <div class="d-flex justify-between fs-lg fw-bold">
                                <span>Jumlah</span>
                                <span class="text-primary" id="staffTotal">RM 0.00</span>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary btn-block btn-lg mt-2" id="btnStaffSubmit" disabled>
                            <i class="bi bi-check-circle"></i> Hantar Pesanan
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

<style>
    @media (max-width: 768px) {
        div[style*="grid-template-columns:1fr 380px"] { grid-template-columns: 1fr !important; }
    }
</style>

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
    } else {
        btnCompleted.className = 'btn btn-primary';
        btnActive.className = 'btn btn-secondary';
        listCompleted.style.display = 'grid';
        listActive.style.display = 'none';
    }
}

function toggleOrderForm() {
    const wrapper = document.getElementById('createOrderWrapper');
    const tabs = document.getElementById('posTabs');
    const activeList = document.getElementById('ordersList');
    const completedList = document.getElementById('completedOrdersList');
    const topButtons = document.getElementById('cashierTopButtons');
    
    if (wrapper.style.display === 'none') {
        wrapper.style.display = 'block';
        tabs.style.display = 'none';
        activeList.style.display = 'none';
        completedList.style.display = 'none';
        topButtons.style.display = 'none'; // hide the top "Buat Order" button itself
        
        if (typeof kitchenRefreshTimer !== 'undefined') {
            clearInterval(kitchenRefreshTimer); // Stop auto refresh while creating order
        }
    } else {
        wrapper.style.display = 'none';
        tabs.style.display = 'flex';
        topButtons.style.display = 'flex';
        if(document.getElementById('tabActiveBtn').className.includes('btn-primary')) {
            activeList.style.display = 'grid';
        } else {
            completedList.style.display = 'grid';
        }

        if (typeof startAutoRefresh === 'function') {
            startAutoRefresh(); // Resume auto refresh
        }
    }
}

const staffItems = {};
function staffQty(id, delta, price) {
    if (!staffItems[id]) staffItems[id] = { qty: 0, price: price, name: document.querySelector('#staff-item-' + id + ' .fw-bold').textContent };
    staffItems[id].qty = Math.max(0, staffItems[id].qty + delta);
    if (staffItems[id].qty === 0) delete staffItems[id];
    document.getElementById('sQty-' + id).textContent = staffItems[id]?.qty || 0;
    updateStaffSummary();
}
function updateStaffSummary() {
    const keys = Object.keys(staffItems);
    let html = '', total = 0, inputsHtml = '';
    if (keys.length === 0) {
        html = '<div class="text-muted fs-sm text-center">Belum pilih item</div>';
        document.getElementById('btnStaffSubmit').disabled = true;
    } else {
        keys.forEach((id, i) => {
            const item = staffItems[id];
            const sub = item.qty * item.price;
            total += sub;
            html += '<div class="d-flex justify-between fs-sm mb-1"><span>' + item.name + ' x' + item.qty + '</span><span>RM ' + sub.toFixed(2) + '</span></div>';
            inputsHtml += '<input type="hidden" name="item_ids[]" value="' + id + '"><input type="hidden" name="item_qtys[]" value="' + item.qty + '">';
        });
        document.getElementById('btnStaffSubmit').disabled = false;
    }
    document.getElementById('staffOrderSummary').innerHTML = html;
    document.getElementById('staffItemInputs').innerHTML = inputsHtml;
    document.getElementById('staffTotal').textContent = 'RM ' + total.toFixed(2);
}
</script>

<?php require_once BASE_PATH . 'views/includes/footer.php'; ?>
