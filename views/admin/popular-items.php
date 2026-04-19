<?php $pageTitle='Item Popular'; require_once BASE_PATH.'views/includes/header.php'; $p=$_GET['period']??'month'; ?>
<div class="container">
    <div class="page-header d-flex justify-between align-center" style="flex-wrap:wrap;gap:12px;">
        <div><h1><i class="bi bi-star"></i> Item Paling Laris</h1><p>Barang yang paling banyak dipesan</p></div>
        <div class="d-flex gap-1">
            <a href="?page=sales-top&period=today" class="btn <?=$p==='today'?'btn-primary':'btn-secondary'?> btn-sm">Hari Ini</a>
            <a href="?page=sales-top&period=week" class="btn <?=$p==='week'?'btn-primary':'btn-secondary'?> btn-sm">Minggu</a>
            <a href="?page=sales-top&period=month" class="btn <?=$p==='month'?'btn-primary':'btn-secondary'?> btn-sm">Bulan</a>
            <a href="?page=sales-top&period=year" class="btn <?=$p==='year'?'btn-primary':'btn-secondary'?> btn-sm">Tahun</a>
            <a href="<?=APP_URL?>/index.php?page=sales" class="btn btn-secondary btn-sm"><i class="bi bi-arrow-left"></i></a>
        </div>
    </div>
    <?php if (empty($topItems)): ?><div class="empty-state"><div class="icon">📊</div><h3>Tiada data</h3></div>
    <?php else: ?>
    <div class="card mb-3"><div class="card-body"><h3 class="mb-2">🛒 Cadangan Barang Modal untuk Beli</h3><p class="text-muted fs-sm mb-2">Berdasarkan jumlah pesanan, ini adalah bahan/barang yang anda perlu pastikan stok mencukupi:</p>
    <div class="grid grid-4">
        <?php foreach (array_slice($topItems, 0, 8) as $i=>$t): ?>
        <div class="card" style="border-color:<?=$i<3?'var(--primary)':'var(--border-color)'?>;">
            <div class="card-body text-center">
                <div style="font-size:1.5rem;margin-bottom:4px;"><?=$i===0?'🥇':($i===1?'🥈':($i===2?'🥉':'📦'))?></div>
                <div class="fw-bold"><?=htmlspecialchars($t['nama_item'])?></div>
                <div class="text-primary fs-sm"><?=$t['jumlah_terjual']?> unit terjual</div>
            </div>
        </div>
        <?php endforeach; ?>
    </div></div></div>
    <div class="table-wrap"><table><thead><tr><th>#</th><th>Item</th><th style="text-align:center">Terjual</th><th style="text-align:center">Pesanan</th><th style="text-align:right">Hasil (RM)</th></tr></thead><tbody>
    <?php foreach ($topItems as $i=>$t): ?><tr><td><span class="badge badge-confirmed"><?=$i+1?></span></td><td class="fw-bold"><?=htmlspecialchars($t['nama_item'])?></td><td style="text-align:center"><?=$t['jumlah_terjual']?></td><td style="text-align:center"><?=$t['jumlah_pesanan']?></td><td style="text-align:right" class="text-primary fw-bold">RM <?=number_format($t['jumlah_hasil'],2)?></td></tr><?php endforeach; ?>
    </tbody></table></div>
    <?php endif; ?>
</div>
<?php require_once BASE_PATH.'views/includes/footer.php'; ?>
