<?php
$pageTitle = 'Status Pesanan - Sistem Order';
require_once BASE_PATH . 'views/includes/header.php';

$statusFlow = ['pending', 'confirmed', 'preparing', 'ready', 'completed'];
$statusLabels = [
    'pending' => 'Menunggu',
    'confirmed' => 'Disahkan',
    'preparing' => 'Sedang Disediakan',
    'ready' => 'Siap!',
    'completed' => 'Selesai',
];
$statusIcons = [
    'pending' => '⏳',
    'confirmed' => '✅',
    'preparing' => '👨‍🍳',
    'ready' => '🔔',
    'completed' => '🎉',
];
$currentStatusIndex = $order ? array_search($order['status'], $statusFlow) : -1;
?>

<div class="container order-tracking-container">
    <style>
        .order-tracking-container {
            max-width: 480px;
            margin: 0 auto;
            padding: 20px 15px 40px;
        }

        /* Glassmorphism Empty State */
        .tracking-empty-state {
            background: rgba(26, 26, 36, 0.6);
            backdrop-filter: blur(16px);
            border: 1px solid rgba(255, 255, 255, 0.08);
            border-radius: 24px;
            padding: 40px 24px;
            text-align: center;
            margin-top: 40px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
        }
        
        .tracking-empty-state .icon {
            font-size: 4rem;
            margin-bottom: 20px;
            text-shadow: 0 0 20px var(--primary-glow);
            animation: float 3s ease-in-out infinite;
        }

        .tracking-search-form {
            display: flex;
            flex-direction: column;
            gap: 12px;
            margin-top: 24px;
        }
        
        .tracking-search-form .form-control {
            border-radius: 12px;
            padding: 14px 16px;
            background: rgba(15, 15, 20, 0.5);
            text-align: center;
            font-size: 1rem;
            letter-spacing: 1px;
        }
        
        .tracking-search-form .btn {
            border-radius: 12px;
            padding: 14px;
            font-size: 1rem;
        }

        /* Order Header Card */
        .order-header-glass {
            background: linear-gradient(145deg, rgba(255, 107, 53, 0.15), rgba(26, 26, 36, 0.8));
            backdrop-filter: blur(12px);
            border: 1px solid rgba(255, 107, 53, 0.2);
            border-radius: 20px;
            padding: 24px;
            text-align: center;
            margin-bottom: 24px;
            box-shadow: 0 8px 32px rgba(0,0,0,0.2);
            position: relative;
            overflow: hidden;
        }

        .order-header-glass::before {
            content: '';
            position: absolute;
            top: -50%; left: -50%;
            width: 200%; height: 200%;
            background: radial-gradient(circle, rgba(255,107,53,0.1) 0%, transparent 60%);
            animation: pulse-glow 4s infinite alternate;
            z-index: 0;
            pointer-events: none;
        }

        .order-header-glass > * {
            position: relative;
            z-index: 1;
        }

        .hero-status-icon {
            font-size: 4.5rem;
            margin-bottom: 15px;
            filter: drop-shadow(0 0 15px var(--primary-glow));
            transform: scale(1);
            transition: all 0.5s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        }

        .order-number-badge {
            display: inline-block;
            background: rgba(0,0,0,0.3);
            border: 1px solid rgba(255,255,255,0.1);
            padding: 6px 16px;
            border-radius: 20px;
            font-family: monospace;
            font-size: 1.1rem;
            color: var(--primary-light);
            letter-spacing: 1px;
            margin-bottom: 8px;
        }

        .hero-status-title {
            font-size: 1.6rem;
            font-weight: 700;
            margin-bottom: 5px;
            background: linear-gradient(to right, #FFF, #ffcfb3);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .hero-status-desc {
            font-size: 0.9rem;
            color: var(--text-secondary);
            line-height: 1.4;
        }

        /* Vertical Modern Timeline */
        .v-timeline-container {
            background: var(--bg-card);
            border: 1px solid var(--border-color);
            border-radius: 20px;
            padding: 24px 20px;
            margin-bottom: 24px;
            box-shadow: 0 4px 16px rgba(0,0,0,0.1);
        }

        .v-timeline {
            position: relative;
            padding-left: 30px;
            list-style: none;
        }

        .v-timeline::before {
            content: '';
            position: absolute;
            top: 0;
            left: 11px;
            width: 2px;
            height: 100%;
            background: var(--border-light);
            border-radius: 2px;
        }

        .v-step {
            position: relative;
            margin-bottom: 24px;
            padding-right: 10px;
            opacity: 0.5;
            transform: translateX(0);
            transition: all 0.4s ease;
        }
        
        .v-step:last-child {
            margin-bottom: 0;
        }

        .v-step.done, .v-step.active {
            opacity: 1;
        }

        .v-step.active {
            transform: scale(1.02);
        }

        .v-step-icon {
            position: absolute;
            left: -30px;
            top: 0;
            width: 24px;
            height: 24px;
            border-radius: 50%;
            background: var(--bg-input);
            border: 2px solid var(--border-light);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.7rem;
            z-index: 2;
            transition: all 0.3s ease;
        }

        .v-step.done .v-step-icon {
            background: var(--success);
            border-color: var(--success);
            color: white;
            box-shadow: 0 0 10px rgba(34, 197, 94, 0.4);
        }

        .v-step.done .v-step-icon::after {
            content: '✓';
            font-weight: bold;
        }

        .v-step.active .v-step-icon {
            background: var(--primary);
            border-color: var(--primary);
            box-shadow: 0 0 15px var(--primary-glow);
            animation: pulse-ring 2s infinite;
        }

        .v-step.active .v-step-icon::after {
            content: '';
            width: 8px;
            height: 8px;
            background: white;
            border-radius: 50%;
        }

        .v-step-content {
            padding-left: 15px;
            padding-top: 1px;
        }

        .v-step-title {
            font-size: 1rem;
            font-weight: 600;
            margin-bottom: 2px;
            color: var(--text-primary);
        }

        .v-step-desc {
            font-size: 0.8rem;
            color: var(--text-muted);
            line-height: 1.3;
        }

        .v-step.active .v-step-title {
            color: var(--primary-light);
        }

        /* Order Details Card */
        .order-details-card {
            background: var(--bg-card);
            border: 1px solid var(--border-color);
            border-radius: 20px;
            padding: 20px;
            margin-bottom: 24px;
        }

        .detail-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 12px;
            font-size: 0.95rem;
        }
        
        .detail-label {
            color: var(--text-secondary);
        }

        .detail-value {
            font-weight: 600;
            color: var(--text-primary);
        }

        .divider {
            height: 1px;
            background: dashed 1px var(--border-light);
            margin: 16px 0;
        }

        .order-items-list {
            margin-top: 10px;
        }
        
        .order-item-row {
            display: flex;
            justify-content: space-between;
            font-size: 0.9rem;
            margin-bottom: 8px;
            align-items: flex-start;
        }

        .order-item-name {
            flex: 1;
            padding-right: 15px;
            color: var(--text-primary);
        }
        
        .order-item-qty {
            color: var(--text-muted);
            margin-right: 12px;
            font-weight: 500;
            min-width: 25px;
        }

        .order-item-price {
            font-weight: 500;
            white-space: nowrap;
        }

        .total-row {
            display: flex;
            justify-content: space-between;
            margin-top: 16px;
            padding-top: 16px;
            border-top: 1px solid var(--border-color);
            font-size: 1.2rem;
            font-weight: 700;
        }

        .total-price {
            color: var(--primary);
        }

        /* Animations */
        @keyframes float {
            0% { transform: translateY(0px); }
            50% { transform: translateY(-10px); }
            100% { transform: translateY(0px); }
        }

        @keyframes pulse-glow {
            0% { opacity: 0.5; }
            100% { opacity: 1; }
        }

        @keyframes pulse-ring {
            0% { box-shadow: 0 0 0 0 rgba(255, 107, 53, 0.4); }
            70% { box-shadow: 0 0 0 10px rgba(255, 107, 53, 0); }
            100% { box-shadow: 0 0 0 0 rgba(255, 107, 53, 0); }
        }
        
        /* Fixed Bottom Button */
        .bottom-action {
            position: sticky;
            bottom: 20px;
            z-index: 10;
        }
        .btn-glowing {
            background: rgba(26, 26, 36, 0.8);
            backdrop-filter: blur(10px);
            border: 1px solid var(--border-light);
            color: white;
            padding: 14px 24px;
            border-radius: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            width: 100%;
            font-weight: 600;
            box-shadow: 0 8px 24px rgba(0,0,0,0.3);
            text-decoration: none;
            transition: all 0.3s;
        }
        .btn-glowing:hover {
            border-color: var(--primary);
            box-shadow: 0 8px 30px var(--primary-glow);
            transform: translateY(-2px);
            color: white;
        }
        .btn-glowing i {
            font-size: 1.2rem;
        }
    </style>

    <?php if (!$order): ?>
        <div class="tracking-empty-state">
            <div class="icon">🔍</div>
            <h3>Jejak Pesanan Anda</h3>
            <p class="text-muted" style="margin-top:8px;">Masukkan nombor pesanan di bawah untuk melihat status terkini secara langsung.</p>
            <form class="tracking-search-form" action="index.php" method="GET">
                <input type="hidden" name="page" value="order-status">
                <input type="text" name="no" class="form-control" placeholder="Cth: ORD260322ABCD" required>
                <button type="submit" class="btn btn-primary">Lihat Status</button>
            </form>
        </div>
    <?php else: ?>
        <!-- Modern Mobile-First Display -->
        
        <!-- Header Glass Container -->
        <div class="order-header-glass" id="statusHeader">
            <div class="order-number-badge"><?= htmlspecialchars($order['no_pesanan']) ?></div>
            <div class="hero-status-icon" id="statusEmoji">
                <?= $statusIcons[$order['status']] ?? '📋' ?>
            </div>
            <h1 class="hero-status-title" id="statusText">
                <?= $statusLabels[$order['status']] ?? $order['status'] ?>
            </h1>
            <p class="hero-status-desc" id="statusDesc">
                <?php
                $descriptions = [
                    'pending' => 'Pesanan anda baru diterima dan sedang menunggu pengesahan admin.',
                    'confirmed' => 'Hebat! Pesanan disahkan dan akan disediakan sebentar lagi.',
                    'preparing' => 'Chef kami sedang sibuk menyediakan hidangan anda yang lazat.',
                    'ready' => 'Hooray! Pesanan sudah siap dan menanti untuk diambil.',
                    'completed' => 'Pesanan selesai. Jemput nikmati hidangan dan terima kasih!'
                ];
                echo $descriptions[$order['status']] ?? '';
                ?>
            </p>
        </div>

        <!-- Vertical Timeline Card -->
        <div class="v-timeline-container">
            <h3 style="font-size: 1.1rem; margin-bottom: 20px; color: var(--text-primary); font-weight: 600;">Perkembangan Pesanan</h3>
            <ul class="v-timeline" id="statusTimeline">
                <?php foreach ($statusFlow as $i => $status): ?>
                    <?php
                    $class = '';
                    if ($i < $currentStatusIndex) $class = 'done';
                    elseif ($i === $currentStatusIndex) $class = 'active';
                    ?>
                    <li class="v-step <?= $class ?>" data-status="<?= $status ?>" id="step-<?= $status ?>">
                        <div class="v-step-icon"></div>
                        <div class="v-step-content">
                            <div class="v-step-title"><?= $statusLabels[$status] ?></div>
                            <div class="v-step-desc">
                                <?php
                                $stepDesc = [
                                    'pending' => 'Menghantar pesanan',
                                    'confirmed' => 'Menyemak stok & maklumat',
                                    'preparing' => 'Memasak dengan penuh kasih sayang',
                                    'ready' => 'Sedia di kaunter serahan',
                                    'completed' => 'Diserahkan kepada anda'
                                ];
                                echo $stepDesc[$status];
                                ?>
                            </div>
                        </div>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>

        <!-- Order Detail Card -->
        <div class="order-details-card">
            <div class="detail-row">
                <span class="detail-label">Pelanggan</span>
                <span class="detail-value"><?= htmlspecialchars($order['nama_pelanggan']) ?></span>
            </div>
            <?php if ($order['no_meja']): ?>
            <div class="detail-row">
                <span class="detail-label">No. Meja</span>
                <span class="detail-value"><?= htmlspecialchars($order['no_meja']) ?></span>
            </div>
            <?php endif; ?>
            
            <div class="divider"></div>
            
            <div class="order-items-list">
                <?php foreach ($orderItems as $oi): ?>
                <div class="order-item-row">
                    <span class="order-item-qty"><?= $oi['kuantiti'] ?>x</span>
                    <span class="order-item-name"><?= htmlspecialchars($oi['nama_item']) ?></span>
                    <span class="order-item-price">RM <?= number_format($oi['jumlah'], 2) ?></span>
                </div>
                <?php endforeach; ?>
            </div>
            
            <div class="total-row">
                <span>Jumlah</span>
                <span class="total-price">RM <?= number_format($order['jumlah_harga'], 2) ?></span>
            </div>
        </div>

        <div class="bottom-action" style="display:flex; flex-direction:column; gap:12px;" id="orderActions">
            <?php if ($order['status'] === 'completed'): ?>
            <a href="<?= url('receipt?id=' . $order['id']) ?>" target="_blank" class="btn btn-primary" style="text-align:center; padding:14px; border-radius:12px; font-weight:bold; box-shadow:0 4px 15px rgba(255,107,53,0.3);">
                <i class="bi bi-receipt"></i> Muat Turun Resit
            </a>
            <?php endif; ?>
            <a href="<?= url('menu') ?>" class="btn-glowing" style="text-align:center;">
                <i class="bi bi-arrow-left"></i> Kembali ke Menu Utama
            </a>
        </div>

        <script>
        // Request Notification Permission on load
        if ("Notification" in window && Notification.permission !== "granted" && Notification.permission !== "denied") {
            Notification.requestPermission();
        }

        // Auto-refresh status setiap 5 saat
        (function() {
            const noPesanan = '<?= htmlspecialchars($order['no_pesanan']) ?>';
            const statusFlow = ['pending', 'confirmed', 'preparing', 'ready', 'completed'];
            const statusLabels = {pending:'Menunggu',confirmed:'Disahkan',preparing:'Sedang Disediakan',ready:'Siap!',completed:'Selesai'};
            const statusIcons = {pending:'⏳',confirmed:'✅',preparing:'👨‍🍳',ready:'🔔',completed:'🎉'};
            const statusDescs = {
                pending: 'Pesanan anda baru diterima dan sedang menunggu pengesahan admin.',
                confirmed: 'Hebat! Pesanan disahkan dan akan disediakan sebentar lagi.',
                preparing: 'Chef kami sedang sibuk menyediakan hidangan anda yang lazat.',
                ready: 'Hooray! Pesanan sudah siap dan menanti untuk diambil.',
                completed: 'Pesanan selesai. Jemput nikmati hidangan dan terima kasih!'
            };
            let lastStatus = '<?= $order['status'] ?>';

            function checkStatus() {
                fetch('<?= url('api-order-status?no=') ?>' + encodeURIComponent(noPesanan))
                    .then(r => r.json())
                    .then(data => {
                        if (data.success && data.status !== lastStatus) {
                            const changedStatus = data.status;
                            lastStatus = changedStatus;
                            const idx = statusFlow.indexOf(changedStatus);
                            
                            // 1. Notify Frontend (Toast)
                            if (typeof showToast === 'function') {
                                showToast('Status Pesanan: ' + (statusLabels[changedStatus] || changedStatus), 4000);
                            }

                            // 2. Notify OS (Push Notification)
                            if ("Notification" in window && Notification.permission === "granted") {
                                try {
                                    new Notification('Sistem Order: ' + (statusLabels[changedStatus] || changedStatus), {
                                        body: statusDescs[changedStatus] || '',
                                        icon: '<?= APP_URL ?>/public/assets/images/favicon.png' // Fallback icon
                                    });
                                } catch (e) {}
                            }

                            // Update timeline animation
                            document.querySelectorAll('.v-step').forEach((step, i) => {
                                step.classList.remove('done', 'active');
                                if (i < idx) step.classList.add('done');
                                else if (i === idx) step.classList.add('active');
                            });

                            // Animate Emoji
                            const emojiEl = document.getElementById('statusEmoji');
                            emojiEl.style.transform = 'scale(0) rotate(-180deg)';
                            emojiEl.style.opacity = '0';
                            
                            setTimeout(() => {
                                emojiEl.textContent = statusIcons[changedStatus] || '📋';
                                emojiEl.style.transform = 'scale(1.2) rotate(10deg)';
                                emojiEl.style.opacity = '1';
                                
                                setTimeout(() => {
                                    emojiEl.style.transform = 'scale(1) rotate(0deg)';
                                }, 300);
                            }, 400);

                            // Update text
                            document.getElementById('statusText').textContent = statusLabels[changedStatus] || changedStatus;
                            document.getElementById('statusDesc').textContent = statusDescs[changedStatus] || '';

                            // Play sound if ready
                            if (changedStatus === 'ready') {
                                try { new Audio('data:audio/wav;base64,UklGRl9vT19XQVZFZm10IBAAAAABAAEAQB8AAEAfAAABAAgAZGF0YU').play(); } catch(e) {}
                            }
                        }
                    })
                    .catch(() => {});
            }

            if (lastStatus !== 'completed' && lastStatus !== 'cancelled') {
                setInterval(checkStatus, 5000);
            }
        })();
        </script>
    <?php endif; ?>
</div>

<?php require_once BASE_PATH . 'views/includes/footer.php'; ?>
