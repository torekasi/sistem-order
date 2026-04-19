<!-- Footer -->
<footer class="footer">
    <div class="container">
        <p>&copy; <?= date('Y') ?> Sistem Order. Hak cipta terpelihara.</p>
        <div style="margin-top: 10px;">
            <?php if (isset($isLoggedIn) && $isLoggedIn): ?>
                <a href="<?= APP_URL ?>/index.php?page=logout" style="color: var(--danger); text-decoration: none; font-size: 0.9rem;">
                    <i class="bi bi-box-arrow-right"></i> Log Keluar
                </a>
            <?php else: ?>
                <a href="<?= APP_URL ?>/index.php?page=login" style="color: var(--text-muted); text-decoration: none; font-size: 0.9rem;">
                    <i class="bi bi-person"></i> Log Masuk
                </a>
            <?php endif; ?>
        </div>
    </div>
</footer>

<script src="<?= APP_URL ?>/assets/js/app.js"></script>
<?php if (isset($extraJS)): ?>
    <?php foreach ($extraJS as $js): ?>
        <script src="<?= APP_URL ?>/assets/js/<?= $js ?>"></script>
    <?php endforeach; ?>
<?php endif; ?>
</body>
</html>
