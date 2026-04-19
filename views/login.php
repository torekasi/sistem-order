<?php $pageTitle = 'Log Masuk - Sistem Order'; require_once BASE_PATH . 'views/includes/header.php'; ?>
<div class="auth-wrapper">
    <div class="auth-card">
        <div style="text-align:center;font-size:2.5rem;margin-bottom:8px;">🍽️</div>
        <h2>Log Masuk</h2>
        <p class="subtitle">Masukkan maklumat akaun anda</p>
        <form method="POST" action="<?= APP_URL ?>/index.php?page=auth-login">
            <?= Security::csrfField() ?>
            <div class="form-group">
                <label class="form-label">No. Telefon / Email</label>
                <input type="text" name="login_id" class="form-control" placeholder="Contoh: 0123456789 atau user@email.com" required autofocus>
            </div>
            <div class="form-group">
                <label class="form-label">Kata Laluan</label>
                <input type="password" name="kata_laluan" class="form-control" placeholder="Masukkan kata laluan" required>
            </div>
            <button type="submit" class="btn btn-primary btn-block btn-lg mt-2"><i class="bi bi-box-arrow-in-right"></i> Log Masuk</button>
        </form>
        <p class="text-center mt-2 fs-sm text-muted">Belum ada akaun? <a href="<?= APP_URL ?>/index.php?page=register">Daftar di sini</a></p>
    </div>
</div>
<?php require_once BASE_PATH . 'views/includes/footer.php'; ?>
