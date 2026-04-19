<?php $pageTitle='Laporan Harian - Sistem Order'; require_once BASE_PATH.'views/includes/header.php'; ?>
<div class="container">
    <div class="page-header d-flex justify-between align-center" style="flex-wrap:wrap;gap:12px;">
        <div><h1><i class="bi bi-calendar-day"></i> Laporan Harian</h1><p>Laporan jualan untuk <?= date('d/m/Y', strtotime($date)) ?></p></div>
        <div class="d-flex gap-1">
            <form class="d-flex gap-1"><input type="date" name="date" class="form-control" value="<?= htmlspecialchars($date) ?>" onchange="this.form.submit()" style="width:auto;"></form>
            <a href="<?= APP_URL ?>/index.php?page=sales-export&date=<?= htmlspecialchars($date) ?>" class="btn btn-secondary"><i class="bi bi-download"></i> CSV</a>
            <a href="<?= APP_URL ?>/index.php?page=sales" class="btn btn-secondary"><i class="bi bi-arrow-left"></i></a>
        </div>
    </div>
    <div class="stats-grid">
        <div class="stat-card"><div class="stat-icon" style="background:var(--primary-glow);color:var(--primary);">💰</div><div class="stat-value">RM <?= number_format($sales['jumlah_jualan'] ?? 0, 2) ?></div><div class="stat-label">Jumlah Jualan</div></div>
        <div class="stat-card"><div class="stat-icon" style="background:var(--info-bg);color:var(--info);">🧾</div><div class="stat-value"><?= $sales['jumlah_pesanan'] ?? 0 ?></div><div class="stat-label">Jumlah Pesanan</div></div>
        <div class="stat-card"><div class="stat-icon" style="background:var(--success-bg);color:var(--success);">📊</div><div class="stat-value">RM <?= number_format($sales['purata_pesanan'] ?? 0, 2) ?></div><div class="stat-label">Purata/Pesanan</div></div>
    </div>
    <?php if (!empty($transactions)): ?>
    <div class="table-wrap"><table><thead><tr><th>No Pesanan</th><th>Pelanggan</th><th>Bayaran</th><th style="text-align:right">Jumlah</th><th>Masa</th></tr></thead><tbody>
    <?php foreach ($transactions as $t): ?>
    <tr><td class="fw-bold text-primary"><?= htmlspecialchars($t['no_pesanan']) ?></td><td><?= htmlspecialchars($t['nama_pelanggan']) ?></td><td><?= ucfirst($t['kaedah_bayaran'] ?? '-') ?></td><td style="text-align:right">RM <?= number_format($t['jumlah_harga'], 2) ?></td><td><?= date('H:i', strtotime($t['created_at'])) ?></td></tr>
    <?php endforeach; ?></tbody></table></div>
    <?php else: ?><div class="empty-state"><div class="icon">📭</div><h3>Tiada transaksi</h3></div><?php endif; ?>
</div>
<?php require_once BASE_PATH.'views/includes/footer.php'; ?>
