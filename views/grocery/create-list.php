<?php
$pageTitle = 'Buat Senarai Belanja - Sistem Order';
require_once BASE_PATH . 'views/includes/header.php';
?>
<div class="container" style="max-width:700px;">
    <div class="page-header">
        <h1><i class="bi bi-plus-circle"></i> Buat Senarai Belanja</h1>
        <p>Buat senarai belanja pasar secara manual</p>
    </div>
    <div class="card">
        <div class="card-body">
            <form method="POST" action="<?= APP_URL ?>/index.php?page=grocery-create">
                <?= Security::csrfField() ?>
                <div class="form-group">
                    <label class="form-label">Tajuk Senarai</label>
                    <input type="text" name="tajuk" class="form-control" placeholder="Contoh: Belanja Pasar Rabu" value="Belanja Pasar - <?= date('d/m/Y') ?>" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Tarikh Belanja</label>
                    <input type="date" name="tarikh" class="form-control" value="<?= date('Y-m-d') ?>" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Nota</label>
                    <textarea name="nota" class="form-control" rows="2" placeholder="Nota tambahan (pilihan)"></textarea>
                </div>

                <h3 style="margin:24px 0 12px;">Senarai Item</h3>
                <div id="itemsList">
                    <div class="d-flex gap-1 mb-1 align-center item-row">
                        <input type="text" name="item_nama[]" class="form-control" placeholder="Nama barang" style="flex:2;">
                        <input type="number" name="item_kuantiti[]" class="form-control" placeholder="Qty" value="1" step="0.1" style="width:70px;">
                        <select name="item_unit[]" class="form-control" style="width:100px;">
                            <option>unit</option><option>kg</option><option>gram</option><option>liter</option><option>bungkus</option><option>pek</option><option>biji</option><option>kotak</option><option>botol</option>
                        </select>
                        <input type="number" name="item_harga[]" class="form-control" placeholder="RM" step="0.10" style="width:90px;">
                    </div>
                </div>
                <button type="button" class="btn btn-secondary btn-sm mt-1" onclick="addItemRow()"><i class="bi bi-plus"></i> Tambah Baris</button>

                <div style="margin-top:24px;display:flex;gap:10px;justify-content:flex-end;">
                    <a href="<?= APP_URL ?>/index.php?page=grocery" class="btn btn-secondary">Batal</a>
                    <button type="submit" class="btn btn-primary"><i class="bi bi-check"></i> Simpan Senarai</button>
                </div>
            </form>
        </div>
    </div>
</div>
<script>
function addItemRow() {
    const row = document.createElement('div');
    row.className = 'd-flex gap-1 mb-1 align-center item-row';
    row.innerHTML = '<input type="text" name="item_nama[]" class="form-control" placeholder="Nama barang" style="flex:2;"><input type="number" name="item_kuantiti[]" class="form-control" placeholder="Qty" value="1" step="0.1" style="width:70px;"><select name="item_unit[]" class="form-control" style="width:100px;"><option>unit</option><option>kg</option><option>gram</option><option>liter</option><option>bungkus</option><option>pek</option><option>biji</option><option>kotak</option><option>botol</option></select><input type="number" name="item_harga[]" class="form-control" placeholder="RM" step="0.10" style="width:90px;"><button type="button" class="btn btn-danger btn-icon btn-sm" onclick="this.parentElement.remove()"><i class="bi bi-x"></i></button>';
    document.getElementById('itemsList').appendChild(row);
}
</script>
<?php require_once BASE_PATH . 'views/includes/footer.php'; ?>
