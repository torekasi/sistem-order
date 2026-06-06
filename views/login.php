<?php $pageTitle = 'Log Masuk - Sistem Order'; require_once BASE_PATH . 'views/includes/header.php'; ?>
<div class="auth-wrapper">
    <div class="auth-card">
        <div class="auth-logo">🍽️</div>
        <h2>Log Masuk</h2>
        <p class="subtitle">Masukkan maklumat akaun anda</p>
        <form method="POST" action="<?= url('auth-login') ?>" id="loginForm">
            <?= Security::csrfField() ?>
            <div class="form-group">
                <label class="form-label">No. Telefon / Email</label>
                <div class="input-icon-wrap">
                    <i class="bi bi-person input-icon"></i>
                    <input type="text" name="login_id" class="form-control" placeholder="0123456789 atau user@email.com" required autofocus>
                </div>
            </div>
            <div class="form-group">
                <label class="form-label">Kata Laluan</label>
                <div class="input-password-wrap">
                    <input type="password" name="kata_laluan" id="loginPassword" class="form-control" placeholder="Masukkan kata laluan" required>
                    <button type="button" class="btn-toggle-password" onclick="togglePassword('loginPassword', this)" tabindex="-1">
                        <i class="bi bi-eye"></i>
                    </button>
                </div>
            </div>
            <button type="submit" class="btn btn-primary btn-block btn-lg mt-2" id="loginBtn">
                <i class="bi bi-box-arrow-in-right"></i> Log Masuk
            </button>
        </form>
        <p class="text-center mt-2 fs-sm text-muted">Belum ada akaun? <a href="<?= url('register') ?>">Daftar di sini</a></p>
    </div>
</div>
<script>
function togglePassword(inputId, btn) {
    const input = document.getElementById(inputId);
    const icon  = btn.querySelector('i');
    if (input.type === 'password') {
        input.type = 'text';
        icon.className = 'bi bi-eye-slash';
    } else {
        input.type = 'password';
        icon.className = 'bi bi-eye';
    }
}
document.getElementById('loginForm').addEventListener('submit', function () {
    const btn = document.getElementById('loginBtn');
    btn.classList.add('btn-loading');
    btn.innerHTML = '<i class="bi bi-box-arrow-in-right"></i> Memproses...';
    btn.disabled = true;
});
</script>
<?php require_once BASE_PATH . 'views/includes/footer.php'; ?>
