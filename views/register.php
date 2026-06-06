<?php $pageTitle = 'Daftar - Sistem Order'; $old = $_SESSION['old'] ?? []; unset($_SESSION['old']); require_once BASE_PATH . 'views/includes/header.php'; ?>
<div class="auth-wrapper">
    <div class="auth-card">
        <div style="text-align:center;font-size:2.5rem;margin-bottom:8px;">🍽️</div>
        <h2>Daftar Akaun</h2>
        <p class="subtitle">Buat akaun baru untuk mula memesan</p>
        <form method="POST" action="<?= url('auth-register') ?>">
            <?= Security::csrfField() ?>
            <div class="form-group">
                <label class="form-label">Nama Penuh</label>
                <input type="text" name="nama" class="form-control" placeholder="Nama penuh anda" value="<?= htmlspecialchars($old['nama'] ?? '') ?>" required>
            </div>
            <div class="form-group">
                <label class="form-label">Email</label>
                <input type="email" name="email" class="form-control" placeholder="contoh@email.com" value="<?= htmlspecialchars($old['email'] ?? '') ?>" required>
            </div>
            <div class="form-group">
                <label class="form-label">No. Telefon</label>
                <input type="tel" name="telefon" class="form-control" placeholder="01X-XXXXXXX" value="<?= htmlspecialchars($old['telefon'] ?? '') ?>">
            </div>
            <div class="form-group">
                <label class="form-label">Kata Laluan</label>
                <input type="password" name="kata_laluan" class="form-control" placeholder="Minimum 6 aksara" required minlength="6">
            </div>
            <div class="form-group">
                <label class="form-label">Sahkan Kata Laluan</label>
                <input type="password" name="kata_laluan2" class="form-control" placeholder="Taip semula kata laluan" required>
            </div>
            <button type="submit" class="btn btn-primary btn-block btn-lg mt-2"><i class="bi bi-person-plus"></i> Daftar</button>
        </form>
        <p class="text-center mt-2 fs-sm text-muted">Sudah ada akaun? <a href="<?= url('login') ?>">Log masuk</a></p>
    </div>
</div>
<?php require_once BASE_PATH . 'views/includes/footer.php'; ?>
