<?php
$pageTitle = 'Cart - Sistem Order';
require_once BASE_PATH . 'views/includes/header.php';
?>

<div class="container">
    <div class="page-header">
        <h1><i class="bi bi-cart3"></i> Cart Anda</h1>
        <p>Semak pesanan sebelum checkout</p>
    </div>

    <?php if (empty($cart)): ?>
        <div class="empty-state">
            <div class="icon">🛒</div>
            <h3>Cart anda kosong</h3>
            <p class="text-muted">Sila tambah item dari menu</p>
            <a href="<?= APP_URL ?>/index.php?page=menu" class="btn btn-primary mt-2">
                <i class="bi bi-arrow-left"></i> Kembali ke Menu
            </a>
        </div>
    <?php else: ?>
        <div style="display:grid; grid-template-columns: 1fr 380px; gap:24px; align-items:start;">
            <!-- Cart Items -->
            <div>
                <?php foreach ($cart as $index => $item): ?>
                    <div class="card" style="margin-bottom:12px; padding:12px;">
                        <!-- Top Row: Image, Name, Unit Price, Trash -->
                        <div style="display:flex; gap:12px; margin-bottom: 12px; align-items: start;">
                            <?php if (!empty($item['gambar'])): ?>
                                <img src="<?= APP_URL ?>/<?= htmlspecialchars($item['gambar']) ?>" alt="gambar" style="width:60px; height:60px; object-fit:cover; border-radius:var(--radius-sm); flex-shrink:0;">
                            <?php else: ?>
                                <div style="width:60px;height:60px;border-radius:var(--radius-sm);background:var(--bg-input);display:flex;align-items:center;justify-content:center;font-size:1.5rem;flex-shrink:0;">🍽️</div>
                            <?php endif; ?>
                            
                            <div style="flex:1; min-width:0; padding-top:2px;">
                                <div class="fw-bold" style="font-size:0.9rem; line-height:1.3; margin-bottom:4px;"><?= htmlspecialchars($item['nama']) ?></div>
                                <div class="text-muted" style="font-size:0.8rem;">RM <?= number_format($item['harga'], 2) ?>/unit</div>
                            </div>

                            <button class="btn btn-danger btn-sm btn-icon btn-remove-cart" data-id="<?= $item['id'] ?>" style="padding:6px; margin-left:8px;">
                                <i class="bi bi-trash"></i>
                            </button>
                        </div>

                        <!-- Bottom Row: Qty Controls and Total -->
                        <div style="display:flex; justify-content:space-between; align-items:center; padding-top:8px; border-top:1px dashed var(--border-color);">
                            <div style="display:flex; align-items:center; gap:10px;">
                                <button class="btn btn-secondary btn-icon btn-sm btn-qty" data-id="<?= $item['id'] ?>" data-action="minus" style="width:28px;height:28px;padding:0;">
                                    <i class="bi bi-dash"></i>
                                </button>
                                <span class="fw-bold" id="qty-<?= $item['id'] ?>" style="min-width:24px;text-align:center;font-size:0.9rem;"><?= $item['kuantiti'] ?></span>
                                <button class="btn btn-secondary btn-icon btn-sm btn-qty" data-id="<?= $item['id'] ?>" data-action="plus" style="width:28px;height:28px;padding:0;">
                                    <i class="bi bi-plus"></i>
                                </button>
                            </div>
                            <div class="fw-bold text-primary" style="font-size:1rem;">
                                RM <?= number_format($item['harga'] * $item['kuantiti'], 2) ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>

                <a href="<?= APP_URL ?>/index.php?page=menu" class="btn btn-secondary mt-2">
                    <i class="bi bi-arrow-left"></i> Tambah Lagi
                </a>
            </div>

            <!-- Checkout Summary -->
            <div class="card" style="position:sticky; top:80px;">
                <div class="card-body">
                    <h3 style="margin-bottom:20px;">Ringkasan Pesanan</h3>
                    
                    <form method="POST" action="<?= APP_URL ?>/index.php?page=checkout">
                        <?= Security::csrfField() ?>
                        
                        <div class="form-group">
                            <label class="form-label">Nama Pelanggan</label>
                            <input type="text" name="nama_pelanggan" class="form-control" placeholder="Masukkan nama anda" 
                                value="<?= htmlspecialchars($_SESSION['user_nama'] ?? '') ?>" required>
                        </div>

                        <?php if ($_sModel->get('order_allow_table', '1') === '1'): ?>
                        <div class="form-group">
                            <label class="form-label">No. Meja (Pilihan)</label>
                            <input type="text" name="no_meja" class="form-control" placeholder="Contoh: A1, B2">
                        </div>
                        <?php endif; ?>

                        <div class="form-group">
                            <label class="form-label">Nota Tambahan</label>
                            <textarea name="nota" class="form-control" rows="2" placeholder="Contoh: Kurang pedas, tanpa sayur"></textarea>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Kaedah Bayaran</label>
                            <select name="kaedah_bayaran" class="form-control">
                                <option value="tunai">💵 Tunai (Cash)</option>
                                <option value="qr">📱 QR Pay</option>
                            </select>
                        </div>

                        <div style="border-top:1px solid var(--border-color); padding-top:16px; margin-top:16px;">
                            <div style="display:flex; justify-content:space-between; margin-bottom:8px;">
                                <span class="text-muted">Subtotal</span>
                                <span>RM <?= number_format($jumlah, 2) ?></span>
                            </div>
                            <div style="display:flex; justify-content:space-between; font-size:1.2rem;">
                                <span class="fw-bold">Jumlah</span>
                                <span class="fw-bold text-primary">RM <?= number_format($jumlah, 2) ?></span>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary btn-block btn-lg mt-2">
                            <i class="bi bi-check-circle"></i> Sahkan & Bayar
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <style>
            @media (max-width: 768px) {
                div[style*="grid-template-columns: 1fr 380px"] {
                    grid-template-columns: 1fr !important;
                }
            }
        </style>
    <?php endif; ?>
</div>

<?php require_once BASE_PATH . 'views/includes/footer.php'; ?>
