<?php $pageTitle = 'Sejarah Pesanan - Sistem Order'; require_once BASE_PATH . 'views/includes/header.php'; ?>
<div class="container">
    <div class="page-header">
        <h1><i class="bi bi-clock-history"></i> Sejarah Pesanan</h1>
        <p>Senarai semua pesanan anda yang lepas</p>
    </div>
    <?php if (empty($orders)): ?>
        <div class="empty-state">
            <div class="icon">📋</div>
            <h3>Tiada pesanan lagi</h3>
            <p class="text-muted">Anda belum membuat sebarang pesanan</p>
            <a href="<?= APP_URL ?>/index.php?page=menu" class="btn btn-primary mt-2"><i class="bi bi-grid"></i> Lihat Menu</a>
        </div>
    <?php else: ?>
        <div class="table-wrap">
            <table>
                <thead><tr><th>No. Pesanan</th><th>Tarikh</th><th>Jumlah</th><th>Status</th><th>Tindakan</th></tr></thead>
                <tbody>
                    <?php foreach ($orders as $o): ?>
                    <tr>
                        <td class="fw-bold text-primary"><?= htmlspecialchars($o['no_pesanan']) ?></td>
                        <td><?= date('d/m/Y H:i', strtotime($o['created_at'])) ?></td>
                        <td>RM <?= number_format($o['jumlah_harga'], 2) ?></td>
                        <td><span class="badge badge-<?= $o['status'] ?>"><?= ucfirst($o['status']) ?></span></td>
                        <td><a href="<?= APP_URL ?>/index.php?page=track-order&no=<?= urlencode($o['no_pesanan']) ?>" class="btn btn-secondary btn-sm"><i class="bi bi-eye"></i> Jejak</a></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>
<?php require_once BASE_PATH . 'views/includes/footer.php'; ?>
