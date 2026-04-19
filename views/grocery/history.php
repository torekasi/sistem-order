<?php
$pageTitle = 'Sejarah Belanja - Sistem Order';
require_once BASE_PATH . 'views/includes/header.php';
?>
<div class="container">
    <div class="page-header d-flex justify-between align-center" style="flex-wrap:wrap;gap:12px;">
        <div>
            <h1><i class="bi bi-clock-history"></i> Sejarah Belanja</h1>
            <p>Rekod semua senarai belanja yang lepas</p>
        </div>
        <a href="<?= APP_URL ?>/index.php?page=grocery" class="btn btn-secondary"><i class="bi bi-arrow-left"></i> Kembali</a>
    </div>
    <?php if (empty($history)): ?>
        <div class="empty-state"><div class="icon">📋</div><h3>Tiada sejarah</h3></div>
    <?php else: ?>
        <div class="table-wrap">
            <table>
                <thead><tr><th>Tajuk</th><th>Tarikh</th><th>Dibuat Oleh</th><th style="text-align:center">Item</th><th style="text-align:right">Anggaran</th><th style="text-align:right">Sebenar</th><th>Status</th></tr></thead>
                <tbody>
                    <?php foreach ($history as $h): ?>
                    <tr>
                        <td class="fw-bold"><?= htmlspecialchars($h['tajuk']) ?></td>
                        <td><?= date('d/m/Y', strtotime($h['tarikh_belanja'])) ?></td>
                        <td><?= htmlspecialchars($h['dibuat_oleh']) ?></td>
                        <td style="text-align:center"><?= ($h['item_selesai'] ?? 0) . '/' . ($h['jumlah_item'] ?? 0) ?></td>
                        <td style="text-align:right">RM <?= number_format($h['jumlah_anggaran'], 2) ?></td>
                        <td style="text-align:right" class="text-primary fw-bold">RM <?= number_format($h['jumlah_sebenar'], 2) ?></td>
                        <td><span class="badge badge-<?= $h['status'] === 'selesai' ? 'completed' : ($h['status'] === 'dibatalkan' ? 'cancelled' : 'pending') ?>"><?= ucfirst($h['status']) ?></span></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>
<?php require_once BASE_PATH . 'views/includes/footer.php'; ?>
