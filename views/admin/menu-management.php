<?php $pageTitle = 'Urus Menu - Sistem Order'; require_once BASE_PATH . 'views/includes/header.php'; ?>
<div class="container">
    <div class="page-header d-flex justify-between align-center" style="flex-wrap:wrap;gap:12px;">
        <div><h1><i class="bi bi-pencil-square"></i> Pengurusan Menu</h1><p>Tambah, edit dan padam item menu</p></div>
        <button class="btn btn-primary" onclick="document.getElementById('addItemModal').classList.add('active')"><i class="bi bi-plus"></i> Tambah Item</button>
    </div>
    <div class="table-wrap">
        <table>
            <thead><tr><th>Gambar</th><th>Nama</th><th>Kategori</th><th style="text-align:right">Harga</th><th style="text-align:center">Status</th><th style="text-align:center">Popular</th><th>Tindakan</th></tr></thead>
            <tbody>
                <?php foreach ($items as $item): ?>
                <tr>
                    <td>
                        <?php if ($item['gambar']): ?>
                            <img src="<?= APP_URL ?>/<?= htmlspecialchars($item['gambar']) ?>" alt="img" style="width: 40px; height: 40px; object-fit: cover; border-radius: 4px;">
                        <?php else: ?>
                            <div style="width: 40px; height: 40px; background: var(--bg-input); border-radius: 4px; display: flex; align-items: center; justify-content: center; font-size: 1rem;">🍽️</div>
                        <?php endif; ?>
                    </td>
                    <td class="fw-bold"><?= htmlspecialchars($item['nama'], ENT_QUOTES, 'UTF-8', false) ?></td>
                    <td><?= htmlspecialchars($item['kategori_nama'], ENT_QUOTES, 'UTF-8', false) ?></td>
                    <td style="text-align:right">RM <?= number_format($item['harga'], 2) ?></td>
                    <td style="text-align:center"><span class="badge badge-<?= $item['status'] === 'tersedia' ? 'completed' : ($item['status'] === 'habis' ? 'cancelled' : 'pending') ?>"><?= ucfirst($item['status']) ?></span></td>
                    <td style="text-align:center"><?= $item['popular'] ? '⭐' : '-' ?></td>
                    <td>
                        <div class="d-flex gap-1">
                            <button class="btn btn-secondary btn-sm" onclick="editItem(<?= htmlspecialchars(json_encode($item)) ?>)"><i class="bi bi-pencil"></i></button>
                            <a href="<?= APP_URL ?>/index.php?page=admin-menu-delete&id=<?= $item['id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Padam item ini?')"><i class="bi bi-trash"></i></a>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Add Item Modal -->
<div class="modal-overlay" id="addItemModal">
    <div class="modal">
        <div class="modal-header"><h3>Tambah Item Menu</h3><button class="modal-close" onclick="this.closest('.modal-overlay').classList.remove('active')">&times;</button></div>
        <div class="modal-body">
            <form method="POST" action="<?= APP_URL ?>/index.php?page=admin-menu-add" enctype="multipart/form-data">
                <?= Security::csrfField() ?>
                <div class="form-group"><label class="form-label">Kategori</label><select name="category_id" class="form-control" required><?php foreach($categories as $c): ?><option value="<?=$c['id']?>"><?=htmlspecialchars($c['nama'])?></option><?php endforeach; ?></select></div>
                <div class="form-group"><label class="form-label">Nama Item</label><input type="text" name="nama" class="form-control" required></div>
                <div class="form-group"><label class="form-label">Penerangan</label><textarea name="penerangan" class="form-control" rows="2"></textarea></div>
                <div class="form-group"><label class="form-label">Harga (RM)</label><input type="number" name="harga" class="form-control" step="0.10" min="0.10" required></div>
                <div class="form-group"><label class="form-label">Gambar</label><input type="file" name="gambar" class="form-control" accept="image/*"></div>
                <div class="form-group"><label><input type="checkbox" name="popular"> Item Popular</label></div>
                <div class="modal-footer" style="padding:0;border:none;margin-top:16px;"><button type="submit" class="btn btn-primary btn-block"><i class="bi bi-check"></i> Simpan</button></div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Item Modal -->
<div class="modal-overlay" id="editItemModal">
    <div class="modal">
        <div class="modal-header"><h3>Edit Item Menu</h3><button class="modal-close" onclick="this.closest('.modal-overlay').classList.remove('active')">&times;</button></div>
        <div class="modal-body">
            <form method="POST" action="<?= APP_URL ?>/index.php?page=admin-menu-edit" enctype="multipart/form-data">
                <?= Security::csrfField() ?>
                <input type="hidden" name="id" id="editId">
                <div class="form-group"><label class="form-label">Kategori</label><select name="category_id" id="editCategory" class="form-control"><?php foreach($categories as $c): ?><option value="<?=$c['id']?>"><?=htmlspecialchars($c['nama'])?></option><?php endforeach; ?></select></div>
                <div class="form-group"><label class="form-label">Nama</label><input type="text" name="nama" id="editNama" class="form-control" required></div>
                <div class="form-group"><label class="form-label">Penerangan</label><textarea name="penerangan" id="editDesc" class="form-control" rows="2"></textarea></div>
                <div class="form-group"><label class="form-label">Harga (RM)</label><input type="number" name="harga" id="editHarga" class="form-control" step="0.10" required></div>
                <div class="form-group"><label class="form-label">Status</label><select name="status" id="editStatus" class="form-control"><option value="tersedia">Tersedia</option><option value="habis">Habis</option><option value="tidak_aktif">Tidak Aktif</option></select></div>
                <div class="form-group">
                    <label class="form-label">Gambar Semasa</label>
                    <div id="currentImageContainer" style="margin-bottom:10px; display:none;">
                        <img id="currentImage" src="" alt="Semasa" style="width: 80px; height: 80px; object-fit: cover; border-radius: 4px; border: 1px solid var(--border-color);">
                    </div>
                    <label class="form-label">Tukar Gambar Baru (Pilihan)</label>
                    <input type="file" name="gambar" class="form-control" accept="image/*">
                </div>
                <div class="form-group"><label><input type="checkbox" name="popular" id="editPopular"> Item Popular</label></div>
                <div class="modal-footer" style="padding:0;border:none;margin-top:16px;"><button type="submit" class="btn btn-primary btn-block"><i class="bi bi-check"></i> Kemaskini</button></div>
            </form>
        </div>
    </div>
</div>

<script>
const appUrl = '<?= APP_URL ?>';
function decodeHtmlEntities(str) {
    if (!str) return '';
    var txt = document.createElement("textarea");
    txt.innerHTML = str;
    return txt.value;
}
function editItem(item) {
    document.getElementById('editId').value = item.id;
    document.getElementById('editCategory').value = item.category_id;
    document.getElementById('editNama').value = decodeHtmlEntities(item.nama);
    document.getElementById('editDesc').value = decodeHtmlEntities(item.penerangan || '');
    document.getElementById('editHarga').value = item.harga;
    document.getElementById('editStatus').value = item.status;
    document.getElementById('editPopular').checked = item.popular == 1;
    
    // Papar gambar semasa (jika ada)
    const imgContainer = document.getElementById('currentImageContainer');
    const currImg = document.getElementById('currentImage');
    if (item.gambar) {
        currImg.src = appUrl + '/' + item.gambar;
        imgContainer.style.display = 'block';
    } else {
        imgContainer.style.display = 'none';
        currImg.src = '';
    }
    
    document.getElementById('editItemModal').classList.add('active');
}
</script>
<?php require_once BASE_PATH . 'views/includes/footer.php'; ?>
