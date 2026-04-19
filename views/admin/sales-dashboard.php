<?php
$pageTitle = 'Dashboard Jualan - Sistem Order';
require_once BASE_PATH . 'views/includes/header.php';
$namaBulan = ['', 'Jan', 'Feb', 'Mac', 'Apr', 'Mei', 'Jun', 'Jul', 'Ogo', 'Sep', 'Okt', 'Nov', 'Dis'];
?>

<div class="container">
    <div class="page-header d-flex justify-between align-center" style="flex-wrap:wrap;gap:12px;">
        <div>
            <h1><i class="bi bi-graph-up"></i> Dashboard Jualan</h1>
            <p>Ringkasan jualan dan aliran tunai</p>
        </div>
        <div class="d-flex gap-1">
            <a href="<?= APP_URL ?>/index.php?page=sales-export&date=<?= date('Y-m-d') ?>" class="btn btn-secondary"><i class="bi bi-download"></i> Eksport CSV</a>
            <a href="<?= APP_URL ?>/index.php?page=sales-top" class="btn btn-primary"><i class="bi bi-star"></i> Item Popular</a>
        </div>
    </div>

    <!-- Summary Stats -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-icon" style="background:var(--primary-glow);color:var(--primary);">📊</div>
            <div class="stat-value">RM <?= number_format($todaySales['jumlah_jualan'] ?? 0, 2) ?></div>
            <div class="stat-label">Jualan Hari Ini</div>
        </div>
        <div class="stat-card">
            <div class="stat-icon" style="background:var(--info-bg);color:var(--info);">📅</div>
            <div class="stat-value">RM <?= number_format($monthlySales['jumlah_jualan'] ?? 0, 2) ?></div>
            <div class="stat-label">Jualan Bulan Ini</div>
        </div>
        <div class="stat-card">
            <div class="stat-icon" style="background:var(--success-bg);color:var(--success);">📆</div>
            <div class="stat-value">RM <?= number_format($yearlySales['jumlah_jualan'] ?? 0, 2) ?></div>
            <div class="stat-label">Jualan Tahun Ini</div>
        </div>
        <div class="stat-card">
            <div class="stat-icon" style="background:var(--warning-bg);color:var(--warning);">🧾</div>
            <div class="stat-value"><?= $todaySales['jumlah_pesanan'] ?? 0 ?></div>
            <div class="stat-label">Pesanan Hari Ini</div>
        </div>
    </div>

    <!-- Charts Row -->
    <div style="display:grid;grid-template-columns:2fr 1fr;gap:20px;margin-bottom:24px;">
        <!-- Sales Trend Chart -->
        <div class="card">
            <div class="card-body">
                <h3 style="margin-bottom:16px;">📈 Trend Jualan (30 Hari)</h3>
                <?php if (empty($salesTrend)): ?>
                    <div class="text-muted text-center" style="padding:40px 0;">Tiada data jualan</div>
                <?php else: ?>
                    <div style="position: relative; height: 250px; width: 100%;">
                        <canvas id="salesTrendChart"></canvas>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Cash Flow -->
        <div class="card">
            <div class="card-body">
                <h3 style="margin-bottom:16px;">💰 Aliran Tunai</h3>
                <?php if (empty($cashFlow)): ?>
                    <div class="text-muted text-center" style="padding:40px 0;">Tiada data</div>
                <?php else: ?>
                    <?php foreach ($cashFlow as $cf): ?>
                        <div style="display:flex;justify-content:space-between;align-items:center;padding:12px 0;border-bottom:1px solid var(--border-color);">
                            <div>
                                <span class="fw-bold"><?= ucfirst($cf['kaedah']) ?></span>
                                <div class="text-muted fs-sm"><?= $cf['jumlah_transaksi'] ?> transaksi</div>
                            </div>
                            <span class="fw-bold text-primary">RM <?= number_format($cf['jumlah_bayaran'], 2) ?></span>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <style>
        .stats-grid {
            display: flex !important;
            flex-wrap: nowrap !important;
            overflow-x: auto !important;
            scroll-snap-type: x mandatory !important;
            -webkit-overflow-scrolling: touch !important;
            scrollbar-width: none !important; /* Firefox */
            gap: 16px !important;
            margin-bottom: 24px !important;
            padding-bottom: 8px !important;
        }
        .stats-grid::-webkit-scrollbar {
            display: none !important; /* Chrome/Safari */
        }
        .stats-grid .stat-card {
            flex: 0 0 auto !important;
            width: 250px !important; /* Fixed width on desktop/tablet */
            scroll-snap-align: center !important;
            margin-bottom: 0 !important;
        }

        @media (max-width: 768px) {
            div[style*="grid-template-columns:2fr 1fr"] { grid-template-columns: 1fr !important; }
            .stats-grid .stat-card {
                width: 85% !important; /* Responsive width on mobile */
            }
        }
    </style>

    <!-- Top Selling Items -->
    <div class="card">
        <div class="card-body">
            <h3 style="margin-bottom:16px;">🔥 Top 10 Item Paling Laris (Bulan Ini)</h3>
            <?php if (empty($topItems)): ?>
                <div class="text-muted text-center" style="padding:20px;">Tiada data</div>
            <?php else: ?>
                <div class="table-wrap">
                    <table>
                        <thead><tr><th>#</th><th>Item</th><th style="text-align:center">Terjual</th><th style="text-align:center">Pesanan</th><th style="text-align:right">Hasil (RM)</th></tr></thead>
                        <tbody>
                            <?php foreach ($topItems as $i => $ti): ?>
                            <tr>
                                <td><span class="badge badge-confirmed"><?= $i + 1 ?></span></td>
                                <td class="fw-bold"><?= htmlspecialchars($ti['nama_item']) ?></td>
                                <td style="text-align:center"><?= $ti['jumlah_terjual'] ?></td>
                                <td style="text-align:center"><?= $ti['jumlah_pesanan'] ?></td>
                                <td style="text-align:right" class="text-primary fw-bold">RM <?= number_format($ti['jumlah_hasil'], 2) ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Quick Links -->
    <div class="d-flex gap-1 mt-3" style="flex-wrap:wrap;">
        <a href="<?= APP_URL ?>/index.php?page=sales-daily" class="btn btn-secondary"><i class="bi bi-calendar-day"></i> Laporan Harian</a>
        <a href="<?= APP_URL ?>/index.php?page=sales-monthly" class="btn btn-secondary"><i class="bi bi-calendar-month"></i> Laporan Bulanan</a>
        <a href="<?= APP_URL ?>/index.php?page=sales-yearly" class="btn btn-secondary"><i class="bi bi-calendar"></i> Laporan Tahunan</a>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4/dist/chart.umd.min.js"></script>
<script>
// Sales Trend Chart
const trendData = <?= json_encode($salesTrend) ?>;
if (trendData.length > 0) {
    new Chart(document.getElementById('salesTrendChart'), {
        type: 'line',
        data: {
            labels: trendData.map(d => { const dt = new Date(d.tarikh); return dt.getDate() + '/' + (dt.getMonth()+1); }),
            datasets: [{
                label: 'Jualan (RM)',
                data: trendData.map(d => parseFloat(d.jumlah_jualan)),
                borderColor: '#FF6B35',
                backgroundColor: 'rgba(255,107,53,0.1)',
                fill: true,
                tension: 0.4,
                pointRadius: 3,
                pointBackgroundColor: '#FF6B35',
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: {
                x: { grid: { color: 'rgba(255,255,255,0.05)' }, ticks: { color: '#6B6B80', font: { size: 11 } } },
                y: { grid: { color: 'rgba(255,255,255,0.05)' }, ticks: { color: '#6B6B80', font: { size: 11 }, callback: v => 'RM ' + v } }
            }
        }
    });
}
</script>

<?php require_once BASE_PATH . 'views/includes/footer.php'; ?>
