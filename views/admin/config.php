<?php
$pageTitle = 'Konfigurasi Sistem - Sistem Order';
require_once BASE_PATH . 'views/includes/header.php';

$activeGroup = Security::sanitize($_GET['group'] ?? '');
?>
<div class="container">
    <div class="page-header">
        <h1><i class="bi bi-gear-wide-connected"></i> Konfigurasi Sistem</h1>
        <p>Tetapan untuk konfigurasi server dan aplikasi</p>
    </div>

    <!-- Nav Group Tabs -->
    <div class="d-flex gap-1 mb-2" style="flex-wrap:wrap;">
        <?php foreach ($settingsByGroup as $group => $settings): ?>
        <a href="<?= APP_URL ?>/index.php?page=config&group=<?= $group ?>" 
           class="btn <?= ($activeGroup === $group || (!$activeGroup && $group === array_key_first($settingsByGroup))) ? 'btn-primary' : 'btn-secondary' ?>" 
           style="font-size:0.85rem;">
            <?= htmlspecialchars($groupLabels[$group] ?? ucfirst($group)) ?>
        </a>
        <?php endforeach; ?>
    </div>

    <!-- Settings Form -->
    <form method="POST" action="<?= APP_URL ?>/index.php?page=config-save" id="configForm" enctype="multipart/form-data">
        <?= Security::csrfField() ?>

        <?php 
        foreach ($settingsByGroup as $group => $settings):
            $isActive = ($activeGroup === $group || (!$activeGroup && $group === array_key_first($settingsByGroup)));
            if (!$isActive) continue;
        ?>
        <div class="card mb-2">
            <div class="card-header">
                <h3 style="margin:0;font-size:1.1rem;"><?= htmlspecialchars($groupLabels[$group] ?? ucfirst($group)) ?></h3>
            </div>
            <div class="card-body" style="padding:24px;">
                <?php if ($group === 'database'): ?>
                <div class="alert alert-warning mb-2">
                    <i class="bi bi-exclamation-triangle"></i>
                    <strong>Amaran:</strong> Perubahan tetapan pangkalan data memerlukan restart aplikasi. Pastikan maklumat betul sebelum menyimpan.
                </div>
                <?php endif; ?>

                <?php foreach ($settings as $setting): ?>
                <div class="form-group" style="margin-bottom:20px;">
                    <label for="s_<?= $setting['setting_key'] ?>" class="fw-bold" style="display:block;margin-bottom:6px;">
                        <?= htmlspecialchars($setting['setting_label']) ?>
                    </label>
                    <?php if ($setting['setting_description']): ?>
                    <div class="text-muted fs-sm" style="margin-bottom:6px;"><?= htmlspecialchars($setting['setting_description']) ?></div>
                    <?php endif; ?>

                    <?php if ($setting['setting_type'] === 'boolean'): ?>
                        <label class="d-flex align-center gap-1" style="cursor:pointer;">
                            <input type="hidden" name="settings[<?= $setting['setting_key'] ?>]" value="0">
                            <input type="checkbox" name="settings[<?= $setting['setting_key'] ?>]" value="1" 
                                   <?= $setting['setting_value'] ? 'checked' : '' ?> 
                                   id="s_<?= $setting['setting_key'] ?>"
                                   style="width:18px;height:18px;">
                            <span><?= $setting['setting_value'] ? 'Aktif' : 'Tidak Aktif' ?></span>
                        </label>

                    <?php elseif ($setting['setting_type'] === 'select'): ?>
                        <select name="settings[<?= $setting['setting_key'] ?>]" id="s_<?= $setting['setting_key'] ?>" class="form-control">
                            <?php foreach (explode(',', $setting['setting_options'] ?? '') as $opt): ?>
                            <option value="<?= htmlspecialchars(trim($opt)) ?>" <?= $setting['setting_value'] === trim($opt) ? 'selected' : '' ?>>
                                <?= htmlspecialchars(trim($opt)) ?>
                            </option>
                            <?php endforeach; ?>
                        </select>

                    <?php elseif ($setting['setting_type'] === 'textarea'): ?>
                        <textarea name="settings[<?= $setting['setting_key'] ?>]" id="s_<?= $setting['setting_key'] ?>" 
                                  class="form-control" rows="3"><?= htmlspecialchars($setting['setting_value'] ?? '') ?></textarea>

                    <?php elseif ($setting['setting_type'] === 'password'): ?>
                        <div class="d-flex gap-1">
                            <input type="password" name="settings[<?= $setting['setting_key'] ?>]" id="s_<?= $setting['setting_key'] ?>" 
                                   class="form-control" value="<?= htmlspecialchars($setting['setting_value'] ?? '') ?>" 
                                   autocomplete="off" style="flex:1;">
                            <button type="button" class="btn btn-secondary" onclick="togglePassword('s_<?= $setting['setting_key'] ?>')" style="white-space:nowrap;">
                                <i class="bi bi-eye"></i>
                            </button>
                        </div>

                    <?php elseif ($setting['setting_type'] === 'number'): ?>
                        <input type="number" name="settings[<?= $setting['setting_key'] ?>]" id="s_<?= $setting['setting_key'] ?>" 
                               class="form-control" value="<?= htmlspecialchars($setting['setting_value'] ?? '') ?>" step="any">

                    <?php elseif ($setting['setting_type'] === 'image'): ?>
                        <?php if (!empty($setting['setting_value'])): ?>
                            <div style="margin-bottom: 10px;">
                                <img src="<?= APP_URL ?>/assets/uploads/<?= htmlspecialchars($setting['setting_value']) ?>" 
                                     alt="<?= htmlspecialchars($setting['setting_label']) ?>"
                                     style="max-height: 80px; border-radius: 4px; border: 1px solid var(--border-color);">
                            </div>
                        <?php endif; ?>
                        <input type="file" name="file_settings[<?= $setting['setting_key'] ?>]" id="s_<?= $setting['setting_key'] ?>" 
                               class="form-control" accept=".jpg,.jpeg,.png,.webp">
                        <input type="hidden" name="settings[<?= $setting['setting_key'] ?>]" value="<?= htmlspecialchars($setting['setting_value'] ?? '') ?>">

                    <?php else: ?>
                        <input type="text" name="settings[<?= $setting['setting_key'] ?>]" id="s_<?= $setting['setting_key'] ?>" 
                               class="form-control" value="<?= htmlspecialchars($setting['setting_value'] ?? '') ?>">
                    <?php endif; ?>
                </div>
                <?php endforeach; ?>

                <?php if ($group === 'database'): ?>
                <button type="button" class="btn btn-secondary" id="btnTestDb" onclick="testDbConnection()">
                    <i class="bi bi-plug"></i> Test Sambungan DB
                </button>
                <span id="dbTestResult" style="margin-left:12px;"></span>
                <?php endif; ?>
            </div>
        </div>
        <?php endforeach; ?>

        <div class="d-flex gap-1 mt-2">
            <button type="submit" class="btn btn-primary btn-lg">
                <i class="bi bi-check-circle"></i> Simpan Tetapan
            </button>
            <a href="<?= APP_URL ?>/index.php?page=menu" class="btn btn-secondary btn-lg">Kembali</a>
        </div>
    </form>
</div>

<script>
function togglePassword(id) {
    const input = document.getElementById(id);
    input.type = input.type === 'password' ? 'text' : 'password';
}

function testDbConnection() {
    const btn = document.getElementById('btnTestDb');
    const result = document.getElementById('dbTestResult');
    btn.disabled = true;
    result.textContent = 'Menguji...';
    result.style.color = 'var(--text-muted)';

    const host = document.getElementById('s_db_host')?.value || '';
    const name = document.getElementById('s_db_name')?.value || '';
    const user = document.getElementById('s_db_user')?.value || '';
    const pass = document.getElementById('s_db_pass')?.value || '';

    fetch(getBaseUrl() + '/index.php?page=config-test-db', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'host=' + encodeURIComponent(host) + '&name=' + encodeURIComponent(name) + 
              '&user=' + encodeURIComponent(user) + '&pass=' + encodeURIComponent(pass)
    })
    .then(r => r.json())
    .then(data => {
        result.textContent = data.message;
        result.style.color = data.success ? 'var(--success)' : 'var(--danger)';
        btn.disabled = false;
    })
    .catch(() => {
        result.textContent = 'Ralat sambungan';
        result.style.color = 'var(--danger)';
        btn.disabled = false;
    });
}
</script>
<?php require_once BASE_PATH . 'views/includes/footer.php'; ?>
