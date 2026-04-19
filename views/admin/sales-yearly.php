<?php $pageTitle='Laporan Tahunan'; require_once BASE_PATH.'views/includes/header.php'; $nb=['','Jan','Feb','Mac','Apr','Mei','Jun','Jul','Ogo','Sep','Okt','Nov','Dis']; ?>
<div class="container">
    <div class="page-header d-flex justify-between align-center" style="flex-wrap:wrap;gap:12px;">
        <div><h1><i class="bi bi-calendar"></i> Laporan Tahunan <?= $year ?></h1></div>
        <a href="<?= APP_URL ?>/index.php?page=sales" class="btn btn-secondary"><i class="bi bi-arrow-left"></i></a>
    </div>
    <div class="stats-grid">
        <div class="stat-card"><div class="stat-icon" style="background:var(--primary-glow);color:var(--primary);">💰</div><div class="stat-value">RM <?= number_format($sales['jumlah_jualan']??0,2) ?></div><div class="stat-label">Jumlah Tahunan</div></div>
        <div class="stat-card"><div class="stat-icon" style="background:var(--info-bg);color:var(--info);">🧾</div><div class="stat-value"><?= $sales['jumlah_pesanan']??0 ?></div><div class="stat-label">Pesanan</div></div>
    </div>
    <?php if (!empty($monthlyTrend)): ?>
    <div class="card"><div class="card-body"><h3 class="mb-2">Trend Bulanan</h3><canvas id="yearChart" height="280"></canvas></div></div>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4/dist/chart.umd.min.js"></script>
    <script>
    const md=<?=json_encode($monthlyTrend)?>;const lb=<?=json_encode($nb)?>;
    new Chart(document.getElementById('yearChart'),{type:'bar',data:{labels:md.map(d=>lb[d.bulan]),datasets:[{label:'Jualan (RM)',data:md.map(d=>parseFloat(d.jumlah_jualan)),backgroundColor:'rgba(255,107,53,0.6)',borderColor:'#FF6B35',borderWidth:1,borderRadius:6}]},options:{responsive:true,maintainAspectRatio:false,plugins:{legend:{display:false}},scales:{x:{grid:{color:'rgba(255,255,255,0.05)'},ticks:{color:'#6B6B80'}},y:{grid:{color:'rgba(255,255,255,0.05)'},ticks:{color:'#6B6B80',callback:v=>'RM '+v}}}}});
    </script>
    <?php endif; ?>
</div>
<?php require_once BASE_PATH.'views/includes/footer.php'; ?>
