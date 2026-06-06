<?php
$pageTitle = 'Bayaran - Sistem Order';
require_once BASE_PATH . 'views/includes/header.php';
?>
<div class="container" style="max-width:600px;">
    <div class="page-header text-center">
        <h1><i class="bi bi-credit-card"></i> Bayaran</h1>
        <p>Sahkan kaedah bayaran anda</p>
    </div>

    <div class="card">
        <div class="card-body" style="padding:32px;">
            <!-- Order Summary -->
            <div style="text-align:center;margin-bottom:24px;">
                <div style="font-size:2rem;margin-bottom:8px;">🧾</div>
                <div class="text-muted fs-sm">No. Pesanan</div>
                <div class="fw-bold text-primary" style="font-size:1.2rem;"><?= htmlspecialchars($order['no_pesanan']) ?></div>
            </div>

            <div class="table-wrap mb-2">
                <table>
                    <thead><tr><th>Item</th><th style="text-align:center">Qty</th><th style="text-align:right">Harga</th></tr></thead>
                    <tbody>
                        <?php foreach ($items as $oi): ?>
                        <tr>
                            <td><?= htmlspecialchars($oi['nama_item']) ?></td>
                            <td style="text-align:center"><?= $oi['kuantiti'] ?></td>
                            <td style="text-align:right">RM <?= number_format($oi['jumlah'], 2) ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                    <tfoot>
                        <tr>
                            <td colspan="2" class="fw-bold">Jumlah</td>
                            <td style="text-align:right" class="fw-bold text-primary" style="font-size:1.2rem;">RM <?= number_format($order['jumlah_harga'], 2) ?></td>
                        </tr>
                    </tfoot>
                </table>
            </div>

            <!-- Payment Method -->
            <div style="background:var(--bg-input);padding:16px;border-radius:var(--radius-sm);margin-bottom:20px;">
                <div class="d-flex align-center gap-1">
                    <span style="font-size:1.5rem;"><?= $payment['kaedah'] === 'tunai' ? '💵' : '📱' ?></span>
                    <div>
                        <div class="fw-bold"><?= $payment['kaedah'] === 'tunai' ? 'Bayaran Tunai' : 'QR Pay' ?></div>
                        <div class="text-muted fs-sm"><?= $payment['kaedah'] === 'tunai' ? 'Bayar di kaunter' : 'Scan QR code untuk membayar' ?></div>
                    </div>
                </div>
            </div>

            <?php if ($payment['kaedah'] === 'tunai'): ?>
                <form method="POST" action="<?= url('payment-process') ?>">
                    <?= Security::csrfField() ?>
                    <input type="hidden" name="order_id" value="<?= $order['id'] ?>">
                    <input type="hidden" name="payment_id" value="<?= $payment['id'] ?>">
                    <div class="alert alert-info"><i class="bi bi-info-circle"></i> Sila bayar di kaunter. Tekan butang di bawah selepas bayar.</div>
                    <button type="submit" class="btn btn-success btn-block btn-lg"><i class="bi bi-check-circle"></i> Sahkan Bayaran Tunai</button>
                </form>
            <?php else: ?>
                <div class="text-center">
                    <?php
                    $qrImage = $_sModel->get('payment_qr_image', '');
                    if (!empty($qrImage)): 
                    ?>
                        <div style="background:white;padding:20px;border-radius:var(--radius-md);display:inline-block;margin-bottom:16px; border:1px solid var(--border-color);">
                            <img src="<?= APP_URL ?>/assets/uploads/<?= htmlspecialchars($qrImage) ?>" alt="QR Code" style="max-width:200px; max-height:200px; display:block;">
                        </div>
                        <p class="text-muted fs-sm mb-2">Scan QR di atas untuk membayar</p>
                        <a href="<?= APP_URL ?>/assets/uploads/<?= htmlspecialchars($qrImage) ?>" download="QR_Pay.png" class="btn btn-secondary btn-sm mb-2">
                            <i class="bi bi-download"></i> Muat Turun QR
                        </a>
                    <?php else: ?>
                        <div style="background:white;padding:20px;border-radius:var(--radius-md);display:inline-block;margin-bottom:16px; border:1px solid var(--border-color);">
                            <div style="width:200px;height:200px;background:#f0f0f0;display:flex;align-items:center;justify-content:center;font-size:0.8rem;color:#999;">Tiada QR Code Ditetapkan</div>
                        </div>
                        <p class="text-muted fs-sm text-danger">Maklumkan kepada pihak kedai untuk menetapkan gambar QR.</p>
                    <?php endif; ?>
                </div>
                <form method="POST" action="<?= url('payment-process') ?>">
                    <?= Security::csrfField() ?>
                    <input type="hidden" name="order_id" value="<?= $order['id'] ?>">
                    <input type="hidden" name="payment_id" value="<?= $payment['id'] ?>">
                    <button type="submit" class="btn btn-primary btn-block btn-lg mt-2"><i class="bi bi-check-circle"></i> Saya Sudah Bayar</button>
                </form>
            <?php endif; ?>
        </div>
    </div>

    <div class="text-center mt-2">
        <a href="<?= url('track-order?no=' . urlencode($order['no_pesanan'])) ?>" class="btn btn-secondary">
            <i class="bi bi-search"></i> Jejak Status Pesanan
        </a>
    </div>
</div>
<?php require_once BASE_PATH . 'views/includes/footer.php'; ?>
