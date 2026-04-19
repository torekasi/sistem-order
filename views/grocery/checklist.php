<?php
$pageTitle = 'Checklist Belanja - Sistem Order';
require_once BASE_PATH . 'views/includes/header.php';
$totalItems = count($items);
$checkedItems = count(array_filter($items, fn($i) => $i['checked']));
$progress = $totalItems > 0 ? round(($checkedItems / $totalItems) * 100) : 0;
?>

<div class="container">
    <div class="page-header d-flex justify-between align-center" style="flex-wrap:wrap;gap:12px;">
        <div>
            <h1><i class="bi bi-card-checklist"></i> <?= htmlspecialchars($list['tajuk']) ?></h1>
            <p>Tarikh: <?= date('d/m/Y', strtotime($list['tarikh_belanja'])) ?> • Dibuat oleh: <?= htmlspecialchars($list['dibuat_oleh']) ?></p>
        </div>
        <div class="d-flex gap-1">
            <a href="<?= APP_URL ?>/index.php?page=grocery-done&id=<?= $list['id'] ?>" class="btn btn-success" onclick="return confirm('Tandak senarai selesai?')">
                <i class="bi bi-check-all"></i> Selesai Belanja
            </a>
            <a href="<?= APP_URL ?>/index.php?page=grocery" class="btn btn-secondary"><i class="bi bi-arrow-left"></i> Kembali</a>
        </div>
    </div>

    <!-- Progress -->
    <div class="card" style="margin-bottom:20px;">
        <div class="card-body">
            <div class="d-flex justify-between mb-1">
                <span class="fw-bold">Progress Belanja</span>
                <span class="text-primary fw-bold"><?= $progress ?>%</span>
            </div>
            <div class="progress" style="height:12px;">
                <div class="progress-bar" id="progressBar" style="width:<?= $progress ?>%"></div>
            </div>
            <div class="d-flex justify-between mt-1 fs-sm text-muted">
                <span><?= $checkedItems ?> / <?= $totalItems ?> item</span>
                <div class="d-flex gap-2">
                    <span>Anggaran: RM <?= number_format($list['jumlah_anggaran'], 2) ?></span>
                    <span class="text-primary fw-bold">Sebenar: RM <span id="totalSebenar"><?= number_format($list['jumlah_sebenar'], 2) ?></span></span>
                </div>
            </div>
        </div>
    </div>

    <!-- Add Item Form -->
    <div class="card" style="margin-bottom:20px;">
        <div class="card-body">
            <form method="POST" action="<?= APP_URL ?>/index.php?page=grocery-edit&id=<?= $list['id'] ?>" class="d-flex gap-1 align-center" style="flex-wrap:wrap;">
                <?= Security::csrfField() ?>
                <input type="hidden" name="add_item" value="1">
                <input type="text" name="nama" class="form-control" placeholder="Nama barang..." style="flex:2;min-width:150px;" required>
                <input type="number" name="kuantiti" class="form-control" placeholder="Qty" value="1" step="0.1" min="0.1" style="width:80px;">
                <select name="unit" class="form-control" style="width:100px;">
                    <option value="unit">Unit</option>
                    <option value="kg">Kg</option>
                    <option value="gram">Gram</option>
                    <option value="liter">Liter</option>
                    <option value="ml">ml</option>
                    <option value="bungkus">Bungkus</option>
                    <option value="pek">Pek</option>
                    <option value="biji">Biji</option>
                    <option value="kotak">Kotak</option>
                    <option value="botol">Botol</option>
                </select>
                <input type="number" name="harga_anggaran" class="form-control" placeholder="Harga (RM)" step="0.10" min="0" style="width:110px;">
                <button type="submit" class="btn btn-primary"><i class="bi bi-plus"></i> Tambah</button>
            </form>
        </div>
    </div>

    <!-- Checklist Items -->
    <div id="checklistItems">
        <?php if (empty($items)): ?>
            <div class="empty-state">
                <div class="icon">📝</div>
                <h3>Senarai kosong</h3>
                <p class="text-muted">Tambah item menggunakan form di atas</p>
            </div>
        <?php else: ?>
            <?php foreach ($items as $item): ?>
                <div class="checklist-item <?= $item['checked'] ? 'checked' : '' ?>" id="ci-<?= $item['id'] ?>">
                    <input type="checkbox" class="checklist-checkbox" 
                        <?= $item['checked'] ? 'checked' : '' ?>
                        onchange="toggleGroceryItem(<?= $item['id'] ?>, this)">
                    <div class="item-details">
                        <div class="item-name"><?= htmlspecialchars($item['nama']) ?></div>
                        <div class="item-qty"><?= $item['kuantiti'] ?> <?= htmlspecialchars($item['unit']) ?>
                            <?php if ($item['harga_anggaran'] > 0): ?>
                                • Anggaran: RM <?= number_format($item['harga_anggaran'], 2) ?>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="item-price">
                        <input type="number" placeholder="RM sebenar" step="0.10" min="0"
                            value="<?= $item['harga_sebenar'] ? number_format($item['harga_sebenar'], 2) : '' ?>"
                            onchange="updateGroceryPrice(<?= $item['id'] ?>, this.value)"
                            style="<?= $item['checked'] ? '' : '' ?>">
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<script>
function toggleGroceryItem(itemId, checkbox) {
    const formData = new FormData();
    formData.append('item_id', itemId);
    fetch('<?= APP_URL ?>/index.php?page=grocery-toggle', { method: 'POST', body: formData })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                const ci = document.getElementById('ci-' + itemId);
                ci.classList.toggle('checked', checkbox.checked);
                if (data.list_totals) {
                    document.getElementById('totalSebenar').textContent = parseFloat(data.list_totals.sebenar).toFixed(2);
                }
                updateProgress();
            }
        });
}

function updateGroceryPrice(itemId, price) {
    if (!price || price <= 0) return;
    const formData = new FormData();
    formData.append('item_id', itemId);
    formData.append('harga_sebenar', price);
    fetch('<?= APP_URL ?>/index.php?page=grocery-toggle', { method: 'POST', body: formData })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                const ci = document.getElementById('ci-' + itemId);
                const cb = ci.querySelector('.checklist-checkbox');
                cb.checked = true;
                ci.classList.add('checked');
                if (data.list_totals) {
                    document.getElementById('totalSebenar').textContent = parseFloat(data.list_totals.sebenar).toFixed(2);
                }
                updateProgress();
            }
        });
}

function updateProgress() {
    const all = document.querySelectorAll('.checklist-checkbox');
    const checked = document.querySelectorAll('.checklist-checkbox:checked');
    const pct = all.length > 0 ? Math.round((checked.length / all.length) * 100) : 0;
    document.getElementById('progressBar').style.width = pct + '%';
    document.querySelector('.text-primary.fw-bold[style]').textContent = pct + '%';
}
</script>

<?php require_once BASE_PATH . 'views/includes/footer.php'; ?>
