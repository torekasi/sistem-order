<?php
$pageTitle = 'Pergi Pasar - Sistem Order';
require_once BASE_PATH . 'views/includes/header.php';
?>

<div class="container">
    <div class="page-header d-flex justify-between align-center" style="flex-wrap:wrap;gap:12px;">
        <div>
            <h1><i class="bi bi-basket3"></i> Pergi Pasar</h1>
            <p>Senarai belanja bahan mentah untuk kedai</p>
        </div>
        <div class="d-flex gap-1">
            <a href="<?= url('grocery-auto?days=7') ?>" class="btn btn-primary" onclick="showLoading()"><i class="bi bi-magic"></i> Auto-Generate</a>
            <a href="<?= url('grocery-create') ?>" class="btn btn-secondary"><i class="bi bi-plus"></i> Senarai Manual</a>
            <a href="<?= url('grocery-history') ?>" class="btn btn-secondary"><i class="bi bi-clock-history"></i> Sejarah</a>
        </div>
    </div>

    <!-- Active Lists -->
    <h3 class="section-title mb-2">📝 Senarai Aktif</h3>
    <?php if (empty($activeLists)): ?>
        <div class="empty-state">
            <div class="icon">🛒</div>
            <h3>Tiada senarai aktif</h3>
            <p class="text-muted">Klik "Auto-Generate" untuk menjana senarai berdasarkan jualan, atau buat senarai manual</p>
        </div>
    <?php else: ?>
        <div class="grid grid-3">
            <?php foreach ($activeLists as $list): ?>
                <div class="card">
                    <div class="card-body">
                        <h4 style="margin-bottom:8px;"><?= htmlspecialchars($list['tajuk']) ?></h4>
                        <div class="text-muted fs-sm mb-1">
                            <i class="bi bi-calendar"></i> <?= date('d/m/Y', strtotime($list['tarikh_belanja'])) ?>
                        </div>
                        <div class="text-muted fs-sm mb-2">
                            <i class="bi bi-person"></i> <?= htmlspecialchars($list['dibuat_oleh']) ?>
                        </div>
                        <div class="d-flex justify-between mb-1 fs-sm">
                            <span class="text-muted">Anggaran:</span>
                            <span>RM <?= number_format($list['jumlah_anggaran'], 2) ?></span>
                        </div>
                        <div class="d-flex justify-between mb-2 fs-sm">
                            <span class="text-muted">Sebenar:</span>
                            <span class="text-primary fw-bold">RM <?= number_format($list['jumlah_sebenar'], 2) ?></span>
                        </div>
                    </div>
                    <div class="card-footer">
                        <a href="<?= url('grocery-edit?id=' . $list['id']) ?>" class="btn btn-primary btn-sm"><i class="bi bi-pencil"></i> Buka</a>
                        <a href="<?= url('grocery-done?id=' . $list['id']) ?>" class="btn btn-success btn-sm" onclick="return confirm('Tandak senarai ini sebagai selesai?')"><i class="bi bi-check-all"></i> Selesai</a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <!-- Recent History -->
    <?php if (!empty($recentHistory)): ?>
    <h3 class="section-title mt-3 mb-2">📋 Terkini</h3>
    <div class="table-wrap">
        <table>
            <thead><tr><th>Tajuk</th><th>Tarikh</th><th style="text-align:center">Item</th><th style="text-align:right">Kos (RM)</th><th>Status</th></tr></thead>
            <tbody>
                <?php foreach ($recentHistory as $h): ?>
                <tr>
                    <td class="fw-bold"><?= htmlspecialchars($h['tajuk']) ?></td>
                    <td><?= date('d/m/Y', strtotime($h['tarikh_belanja'])) ?></td>
                    <td style="text-align:center"><?= $h['item_selesai'] ?? 0 ?>/<?= $h['jumlah_item'] ?? 0 ?></td>
                    <td style="text-align:right">RM <?= number_format($h['jumlah_sebenar'], 2) ?></td>
                    <td><span class="badge badge-<?= $h['status'] === 'selesai' ? 'completed' : 'pending' ?>"><?= ucfirst($h['status']) ?></span></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>
</div>

<?php require_once BASE_PATH . 'views/includes/footer.php'; ?>
