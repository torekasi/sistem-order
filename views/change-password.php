<?php
$pageTitle = 'Tukar Kata Laluan - Sistem Order';
require_once BASE_PATH . 'views/includes/header.php';
?>
<div class="container" style="max-width:500px;margin-top:40px;margin-bottom:40px;">
    <div class="card" style="padding:0;">
        <div class="card-body" style="padding:32px;">
            <div style="text-align:center;margin-bottom:28px;">
                <div style="width:56px;height:56px;background:linear-gradient(135deg,var(--primary),var(--primary-dark));border-radius:var(--radius-lg);display:flex;align-items:center;justify-content:center;margin:0 auto 16px;font-size:1.5rem;color:white;">
                    <i class="bi bi-key"></i>
                </div>
                <h2 style="font-size:1.4rem;font-weight:700;">Tukar Kata Laluan</h2>
                <p class="text-muted" style="font-size:0.85rem;">Masukkan kata laluan semasa dan kata laluan baru anda</p>
            </div>

            <form method="POST" action="<?= url('change-password-save') ?>">
                <?= Security::csrfField() ?>
                <div class="form-group">
                    <label class="fw-bold">Kata Laluan Semasa</label>
                    <input type="password" name="kata_laluan_semasa" class="form-control" placeholder="Masukkan kata laluan semasa" required>
                </div>
                <div class="form-group">
                    <label class="fw-bold">Kata Laluan Baru</label>
                    <input type="password" name="kata_laluan_baru" class="form-control" placeholder="Minimum 6 aksara" required>
                </div>
                <div class="form-group">
                    <label class="fw-bold">Ulang Kata Laluan Baru</label>
                    <input type="password" name="kata_laluan_ulang" class="form-control" placeholder="Taip semula kata laluan baru" required>
                </div>
                <div style="margin-top:24px;display:flex;gap:12px;">
                    <button type="submit" class="btn btn-primary btn-block"><i class="bi bi-check-circle"></i> Simpan</button>
                    <a href="<?= url('menu') ?>" class="btn btn-secondary btn-block">Batal</a>
                </div>
            </form>
        </div>
    </div>
</div>
<?php require_once BASE_PATH . 'views/includes/footer.php'; ?>