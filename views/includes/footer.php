<!-- =========================================================
     GLOBAL MODAL SYSTEM
     ========================================================= -->

<!-- Notification Toast -->
<div id="soToastContainer" class="so-toast-container"></div>

<!-- Confirmation Modal -->
<div class="so-modal-overlay" id="soConfirmOverlay">
    <div class="so-modal" id="soConfirmModal" role="dialog" aria-modal="true" aria-labelledby="soConfirmTitle">
        <div class="so-modal-icon" id="soConfirmIcon">⚠️</div>
        <h3 class="so-modal-title" id="soConfirmTitle">Adakah anda pasti?</h3>
        <p class="so-modal-message" id="soConfirmMessage">Tindakan ini tidak boleh dibatalkan.</p>
        <div class="so-modal-actions">
            <button class="btn btn-secondary" id="soConfirmCancel">Batal</button>
            <button class="btn btn-danger" id="soConfirmOk">Ya, Teruskan</button>
        </div>
    </div>
</div>

<!-- Alert Modal (info / success / error / warning) -->
<div class="so-modal-overlay" id="soAlertOverlay">
    <div class="so-modal" id="soAlertModal" role="dialog" aria-modal="true" aria-labelledby="soAlertTitle">
        <div class="so-modal-icon" id="soAlertIcon">ℹ️</div>
        <h3 class="so-modal-title" id="soAlertTitle">Notifikasi</h3>
        <p class="so-modal-message" id="soAlertMessage"></p>
        <div class="so-modal-actions">
            <button class="btn btn-primary" id="soAlertOk">OK</button>
        </div>
    </div>
</div>

<!-- Footer -->
<footer class="footer">
    <div class="container">
        <p>&copy; <?= date('Y') ?> Sistem Order. Hak cipta terpelihara.</p>
        <div style="margin-top: 10px;">
            <?php if (isset($isLoggedIn) && $isLoggedIn): ?>
                <a href="<?= url('logout') ?>" style="color: var(--danger); text-decoration: none; font-size: 0.9rem;"><i class="bi bi-box-arrow-right"></i> Log Keluar</a>
            <?php else: ?>
                <a href="<?= url('login') ?>" style="color: var(--text-muted); text-decoration: none; font-size: 0.9rem;"><i class="bi bi-person"></i> Log Masuk</a>
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
