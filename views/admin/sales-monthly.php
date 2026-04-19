<?php $pageTitle='Laporan Bulanan'; $nb=['','Januari','Februari','Mac','April','Mei','Jun','Julai','Ogos','September','Oktober','November','Disember']; require_once BASE_PATH.'views/includes/header.php'; ?>
<div class="container">
    <div class="page-header d-flex justify-between align-center" style="flex-wrap:wrap;gap:12px;">
        <div><h1><i class="bi bi-calendar-month"></i> Laporan Bulanan</h1><p><?= $nb[$month] ?? '' ?> <?= $year ?></p></div>
        <a href="<?= APP_URL ?>/index.php?page=sales" class="btn btn-secondary"><i class="bi bi-arrow-left"></i> Kembali</a>
    </div>
    <div class="stats-grid">
        <div class="stat-card"><div class="stat-icon" style="background:var(--primary-glow);color:var(--primary);">💰</div><div class="stat-value">RM <?= number_format($sales['jumlah_jualan']??0,2) ?></div><div class="stat-label">Jumlah Jualan</div></div>
        <div class="stat-card"><div class="stat-icon" style="background:var(--info-bg);color:var(--info);">🧾</div><div class="stat-value"><?= $sales['jumlah_pesanan']??0 ?></div><div class="stat-label">Pesanan</div></div>
        <div class="stat-card"><div class="stat-icon" style="background:var(--success-bg);color:var(--success);">📊</div><div class="stat-value">RM <?= number_format($sales['purata_pesanan']??0,2) ?></div><div class="stat-label">Purata</div></div>
    </div>
    <?php if (!empty($topItems)): ?><div class="card"><div class="card-body"><h3 class="mb-2">Item Paling Laris</h3><div class="table-wrap"><table><thead><tr><th>#</th><th>Item</th><th style="text-align:center">Terjual</th><th style="text-align:right">Hasil</th></tr></thead><tbody>
    <?php foreach ($topItems as $i=>$t): ?><tr><td><?= $i+1 ?></td><td class="fw-bold"><?= htmlspecialchars($t['nama_item']) ?></td><td style="text-align:center"><?= $t['jumlah_terjual'] ?></td><td style="text-align:right" class="text-primary">RM <?= number_format($t['jumlah_hasil'],2) ?></td></tr><?php endforeach; ?>
    </tbody></table></div></div></div><?php endif; ?>
</div>
<?php require_once BASE_PATH.'views/includes/footer.php'; ?>
