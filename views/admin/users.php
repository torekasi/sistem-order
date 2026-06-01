<?php
$pageTitle = 'Pengurusan Pengguna - Sistem Order';
require_once BASE_PATH . 'views/includes/header.php';

$roleLabels = [
    'superadmin' => 'Super Admin',
    'admin' => 'Admin',
    'staff' => 'Staff',
    'cashier'  => 'Cashier',
    'customer' => 'Pelanggan',
    'buyer' => 'Tukang Pasar',
];
$roleBadges = [
    'superadmin' => 'badge-danger',
    'admin' => 'badge-primary',
    'staff' => 'badge-warning',
    'cashier'  => 'badge-primary', /* or secondary */
    'customer' => 'badge-success',
    'buyer' => 'badge-info',
];
?>
<div class="container">
    <div class="page-header">
        <h1><i class="bi bi-people-fill"></i> Pengurusan Pengguna</h1>
        <p>Urus semua pengguna dan peranan mereka</p>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Nama</th>
                            <th>Email</th>
                            <th>Telefon</th>
                            <th>Peranan</th>
                            <th>Status</th>
                            <th>Tarikh Daftar</th>
                            <th style="text-align:center">Tindakan</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($users)): ?>
                        <tr><td colspan="8" class="text-center text-muted">Tiada pengguna</td></tr>
                        <?php else: ?>
                        <?php foreach ($users as $i => $u): ?>
                        <tr>
                            <td><?= $i + 1 ?></td>
                            <td class="fw-bold"><?= htmlspecialchars($u['nama']) ?></td>
                            <td><?= htmlspecialchars($u['email']) ?></td>
                            <td><?= htmlspecialchars($u['telefon'] ?? '-') ?></td>
                            <td><span class="badge <?= $roleBadges[$u['role']] ?? 'badge-secondary' ?>"><?= $roleLabels[$u['role']] ?? $u['role'] ?></span></td>
                            <td>
                                <span class="badge <?= $u['status'] === 'aktif' ? 'badge-success' : 'badge-danger' ?>">
                                    <?= ucfirst($u['status']) ?>
                                </span>
                            </td>
                            <td class="text-muted fs-sm"><?= date('d/m/Y', strtotime($u['created_at'])) ?></td>
                            <td style="text-align:center">
                                <?php if ($u['id'] !== Security::currentUserId()): ?>
                                <div style="display:flex;gap:6px;justify-content:center;">
                                    <button type="button" class="btn btn-primary btn-sm"
                                            onclick="editUser(<?= $u['id'] ?>, '<?= htmlspecialchars($u['nama'], ENT_QUOTES) ?>', '<?= $u['role'] ?>', '<?= $u['status'] ?>')">
                                        <i class="bi bi-pencil"></i> Edit
                                    </button>
                                    <button type="button" class="btn btn-danger btn-sm btn-delete-user"
                                            data-id="<?= $u['id'] ?>"
                                            data-nama="<?= htmlspecialchars($u['nama'], ENT_QUOTES) ?>">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </div>
                                <?php else: ?>
                                <span class="text-muted fs-sm">Anda</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Modal Edit User -->
<div class="modal-overlay" id="modalEditUser">
    <div class="modal-content">
        <div class="modal-header">
            <h3 id="editUserTitle">Edit Pengguna</h3>
            <button type="button" class="modal-close" onclick="closeModal('modalEditUser')">&times;</button>
        </div>
        <form method="POST" action="<?= APP_URL ?>/index.php?page=update-user-role">
            <?= Security::csrfField() ?>
            <input type="hidden" name="user_id" id="editUserId">
            <div class="form-group">
                <label class="fw-bold">Peranan</label>
                <select name="role" id="editUserRole" class="form-control">
                    <option value="superadmin">Super Admin</option>
                    <option value="admin">Admin</option>
                    <option value="staff">Staff</option>
                    <option value="cashier">Cashier</option>
                    <option value="customer">Pelanggan</option>
                    <option value="buyer">Tukang Pasar</option>
                </select>
            </div>
            <div class="form-group mt-1">
                <label class="fw-bold">Status</label>
                <select name="status" id="editUserStatus" class="form-control">
                    <option value="aktif">Aktif</option>
                    <option value="tidak_aktif">Tidak Aktif</option>
                </select>
            </div>
            <div class="d-flex gap-1 mt-2">
                <button type="submit" class="btn btn-primary"><i class="bi bi-check-circle"></i> Simpan</button>
                <button type="button" class="btn btn-secondary" onclick="closeModal('modalEditUser')">Batal</button>
            </div>
        </form>
    </div>
</div>

<script>
function editUser(id, nama, role, status) {
    document.getElementById('editUserId').value = id;
    document.getElementById('editUserTitle').textContent = 'Edit: ' + nama;
    document.getElementById('editUserRole').value = role;
    document.getElementById('editUserStatus').value = status;
    document.getElementById('modalEditUser').classList.add('active');
}
function closeModal(id) {
    document.getElementById(id).classList.remove('active');
}

// Delete user via AJAX + SOModal confirm
document.addEventListener('click', async function (e) {
    const btn = e.target.closest('.btn-delete-user');
    if (!btn) return;
    e.preventDefault();

    const userId = btn.dataset.id;
    const nama   = btn.dataset.nama;

    const confirmed = await SOModal.confirm({
        title:      'Padam Pengguna?',
        message:    'Anda akan memadam akaun "' + nama + '". Tindakan ini tidak boleh dibatalkan.',
        icon:       '🗑️',
        okText:     'Ya, Padam',
        okClass:    'btn-danger',
        cancelText: 'Batal',
    });
    if (!confirmed) return;

    showLoading('Mempadam...');

    const formData = new FormData();
    formData.append('user_id', userId);
    formData.append('<?= CSRF_TOKEN_NAME ?>', '<?= Security::generateCSRFToken() ?>');

    fetch('<?= APP_URL ?>/index.php?page=delete-user', {
        method: 'POST',
        body: formData,
    })
    .then(r => r.json())
    .then(data => {
        hideLoading();
        if (data.success) {
            SOToast.success(data.message);
            // Remove the row from the table
            const row = btn.closest('tr');
            if (row) {
                row.style.transition = 'opacity 0.4s ease';
                row.style.opacity = '0';
                setTimeout(() => row.remove(), 400);
            }
        } else {
            SOToast.error(data.message || 'Gagal memadam pengguna.');
        }
    })
    .catch(() => {
        hideLoading();
        SOToast.error('Ralat sambungan. Sila cuba lagi.');
    });
});
</script>
<?php require_once BASE_PATH . 'views/includes/footer.php'; ?>
