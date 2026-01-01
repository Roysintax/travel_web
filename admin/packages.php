<?php
// =====================================================
// ADMIN PACKAGES PAGE
// File: admin/packages.php
// =====================================================

require_once 'includes/auth.php';
requireLogin();

define('PAGE_TITLE', 'Paket Umrah');
define('ACTIVE_MENU', 'packages');

$success = '';
$error = '';
$action = $_GET['action'] ?? 'list';
$editId = $_GET['id'] ?? null;

$db = getDB();

// Handle Delete
if ($action === 'delete' && $editId) {
    try {
        $stmt = $db->prepare("DELETE FROM packages WHERE id = ?");
        $stmt->execute([$editId]);
        logAdminActivity('delete_package', "Menghapus paket ID: $editId");
        header('Location: packages.php?success=deleted');
        exit;
    } catch (PDOException $e) {
        $error = 'Gagal menghapus: ' . $e->getMessage();
    }
}

// Handle Form Submit (Add/Edit)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $price = trim($_POST['price'] ?? '');
    $currency = trim($_POST['currency'] ?? 'Rp');
    $duration = trim($_POST['duration'] ?? '');
    $image_url = trim($_POST['image_url'] ?? '');
    $tag = trim($_POST['tag'] ?? '');
    $button_text = trim($_POST['button_text'] ?? 'Pesan Sekarang');
    $button_link = trim($_POST['button_link'] ?? '#contact');
    $package_order = (int)($_POST['package_order'] ?? 0);
    $is_active = isset($_POST['is_active']) ? 1 : 0;
    $features = array_filter($_POST['features'] ?? []);
    
    if (empty($name) || empty($price)) {
        $error = 'Nama dan harga harus diisi!';
    } else {
        try {
            if ($action === 'edit' && $editId) {
                // Update package
                $stmt = $db->prepare("
                    UPDATE packages SET 
                        name = ?, price = ?, currency = ?, duration = ?,
                        image_url = ?, tag = ?, button_text = ?, button_link = ?,
                        package_order = ?, is_active = ?
                    WHERE id = ?
                ");
                $stmt->execute([$name, $price, $currency, $duration, $image_url, $tag ?: null, 
                               $button_text, $button_link, $package_order, $is_active, $editId]);
                
                // Delete old features
                $db->prepare("DELETE FROM package_features WHERE package_id = ?")->execute([$editId]);
                
                // Insert new features
                $stmtFeature = $db->prepare("INSERT INTO package_features (package_id, feature_text, feature_order) VALUES (?, ?, ?)");
                $order = 1;
                foreach ($features as $feature) {
                    if (!empty(trim($feature))) {
                        $stmtFeature->execute([$editId, trim($feature), $order++]);
                    }
                }
                
                logAdminActivity('update_package', "Mengupdate paket: $name");
                header('Location: packages.php?success=updated');
                exit;
            } else {
                // Insert new package
                $stmt = $db->prepare("
                    INSERT INTO packages (name, price, currency, duration, image_url, tag, button_text, button_link, package_order, is_active)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
                ");
                $stmt->execute([$name, $price, $currency, $duration, $image_url, $tag ?: null, 
                               $button_text, $button_link, $package_order, $is_active]);
                
                $packageId = $db->lastInsertId();
                
                // Insert features
                $stmtFeature = $db->prepare("INSERT INTO package_features (package_id, feature_text, feature_order) VALUES (?, ?, ?)");
                $order = 1;
                foreach ($features as $feature) {
                    if (!empty(trim($feature))) {
                        $stmtFeature->execute([$packageId, trim($feature), $order++]);
                    }
                }
                
                logAdminActivity('add_package', "Menambah paket: $name");
                header('Location: packages.php?success=added');
                exit;
            }
        } catch (PDOException $e) {
            $error = 'Gagal menyimpan: ' . $e->getMessage();
        }
    }
}

// Get package for edit
$package = null;
$packageFeatures = [];
if ($action === 'edit' && $editId) {
    $stmt = $db->prepare("SELECT * FROM packages WHERE id = ?");
    $stmt->execute([$editId]);
    $package = $stmt->fetch();
    
    if ($package) {
        $stmt = $db->prepare("SELECT * FROM package_features WHERE package_id = ? ORDER BY feature_order");
        $stmt->execute([$editId]);
        $packageFeatures = $stmt->fetchAll();
    }
}

// Get all packages
$packages = $db->query("SELECT * FROM packages ORDER BY package_order, id")->fetchAll();

// Success messages
if (isset($_GET['success'])) {
    $messages = ['added' => 'Paket berhasil ditambahkan!', 'updated' => 'Paket berhasil diupdate!', 'deleted' => 'Paket berhasil dihapus!'];
    $success = $messages[$_GET['success']] ?? '';
}

require_once 'includes/header.php';
?>

<?php if ($success): ?>
    <div class="alert alert-success"><i class="fa-solid fa-check-circle"></i> <?= htmlspecialchars($success) ?></div>
<?php endif; ?>

<?php if ($error): ?>
    <div class="alert alert-error"><i class="fa-solid fa-exclamation-circle"></i> <?= htmlspecialchars($error) ?></div>
<?php endif; ?>

<?php if ($action === 'add' || ($action === 'edit' && $package)): ?>
<!-- Form Add/Edit -->
<div class="card">
    <div class="card-header">
        <h3 class="card-title"><?= $action === 'edit' ? 'Edit Paket' : 'Tambah Paket Baru' ?></h3>
        <a href="packages.php" class="btn btn-secondary btn-sm"><i class="fa-solid fa-arrow-left"></i> Kembali</a>
    </div>
    <div class="card-body">
        <form method="POST">
            <div class="form-row">
                <div class="form-group">
                    <label>Nama Paket *</label>
                    <input type="text" name="name" class="form-control" required 
                           value="<?= htmlspecialchars($package['name'] ?? '') ?>" placeholder="Umrah Premium 9 Hari">
                </div>
                <div class="form-group">
                    <label>Durasi</label>
                    <input type="text" name="duration" class="form-control" 
                           value="<?= htmlspecialchars($package['duration'] ?? '') ?>" placeholder="9 Hari">
                </div>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label>Harga *</label>
                    <input type="text" name="price" class="form-control" required 
                           value="<?= htmlspecialchars($package['price'] ?? '') ?>" placeholder="35.000.000">
                </div>
                <div class="form-group">
                    <label>Mata Uang</label>
                    <select name="currency" class="form-control">
                        <option value="Rp" <?= ($package['currency'] ?? 'Rp') === 'Rp' ? 'selected' : '' ?>>Rp (Rupiah)</option>
                        <option value="$" <?= ($package['currency'] ?? '') === '$' ? 'selected' : '' ?>>$ (Dollar)</option>
                    </select>
                </div>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label>Tag/Label</label>
                    <input type="text" name="tag" class="form-control" 
                           value="<?= htmlspecialchars($package['tag'] ?? '') ?>" placeholder="Most Popular">
                </div>
                <div class="form-group">
                    <label>Urutan</label>
                    <input type="number" name="package_order" class="form-control" 
                           value="<?= $package['package_order'] ?? 0 ?>" min="0">
                </div>
            </div>
            
            <div class="form-group">
                <label>URL Gambar</label>
                <input type="url" name="image_url" class="form-control" 
                       value="<?= htmlspecialchars($package['image_url'] ?? '') ?>" placeholder="https://...">
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label>Teks Tombol</label>
                    <input type="text" name="button_text" class="form-control" 
                           value="<?= htmlspecialchars($package['button_text'] ?? 'Pesan Sekarang') ?>">
                </div>
                <div class="form-group">
                    <label>Link Tombol</label>
                    <input type="text" name="button_link" class="form-control" 
                           value="<?= htmlspecialchars($package['button_link'] ?? '#contact') ?>">
                </div>
            </div>
            
            <div class="form-group">
                <label>Fitur Paket</label>
                <div id="features-list">
                    <?php 
                    $featuresList = !empty($packageFeatures) ? $packageFeatures : [['feature_text' => ''], ['feature_text' => ''], ['feature_text' => ''], ['feature_text' => '']];
                    foreach ($featuresList as $i => $f): 
                    ?>
                        <div class="feature-item" style="display: flex; gap: 0.5rem; margin-bottom: 0.5rem;">
                            <input type="text" name="features[]" class="form-control" 
                                   value="<?= htmlspecialchars($f['feature_text']) ?>" placeholder="Fitur <?= $i + 1 ?>">
                            <button type="button" class="btn btn-danger btn-sm" onclick="this.parentElement.remove()">
                                <i class="fa-solid fa-times"></i>
                            </button>
                        </div>
                    <?php endforeach; ?>
                </div>
                <button type="button" class="btn btn-secondary btn-sm" onclick="addFeature()">
                    <i class="fa-solid fa-plus"></i> Tambah Fitur
                </button>
            </div>
            
            <div class="form-group">
                <label style="display: flex; align-items: center; gap: 0.5rem;">
                    <input type="checkbox" name="is_active" value="1" 
                           <?= ($package['is_active'] ?? 1) ? 'checked' : '' ?>>
                    Aktif
                </label>
            </div>
            
            <button type="submit" class="btn btn-primary">
                <i class="fa-solid fa-save"></i> Simpan
            </button>
        </form>
    </div>
</div>

<script>
function addFeature() {
    const html = `
        <div class="feature-item" style="display: flex; gap: 0.5rem; margin-bottom: 0.5rem;">
            <input type="text" name="features[]" class="form-control" placeholder="Fitur baru">
            <button type="button" class="btn btn-danger btn-sm" onclick="this.parentElement.remove()">
                <i class="fa-solid fa-times"></i>
            </button>
        </div>
    `;
    document.getElementById('features-list').insertAdjacentHTML('beforeend', html);
}
</script>

<?php else: ?>
<!-- List View -->
<div class="card">
    <div class="card-header">
        <h3 class="card-title">Daftar Paket (<?= count($packages) ?>)</h3>
        <a href="packages.php?action=add" class="btn btn-primary btn-sm">
            <i class="fa-solid fa-plus"></i> Tambah Paket
        </a>
    </div>
    <div class="card-body" style="padding: 0;">
        <?php if (empty($packages)): ?>
            <div class="empty-state">
                <i class="fa-solid fa-box"></i>
                <p>Belum ada paket. <a href="packages.php?action=add">Tambah sekarang</a></p>
            </div>
        <?php else: ?>
            <table class="table">
                <thead>
                    <tr>
                        <th>Gambar</th>
                        <th>Nama Paket</th>
                        <th>Harga</th>
                        <th>Durasi</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($packages as $p): ?>
                        <tr>
                            <td>
                                <?php if ($p['image_url']): ?>
                                    <img src="<?= htmlspecialchars($p['image_url']) ?>" class="img-preview">
                                <?php else: ?>
                                    <div style="width:60px;height:60px;background:#E2E8F0;border-radius:0.5rem;display:flex;align-items:center;justify-content:center;color:#94A3B8;">
                                        <i class="fa-solid fa-image"></i>
                                    </div>
                                <?php endif; ?>
                            </td>
                            <td>
                                <strong><?= htmlspecialchars($p['name']) ?></strong>
                                <?php if ($p['tag']): ?>
                                    <br><span class="badge badge-warning"><?= htmlspecialchars($p['tag']) ?></span>
                                <?php endif; ?>
                            </td>
                            <td><?= htmlspecialchars($p['currency']) ?> <?= htmlspecialchars($p['price']) ?></td>
                            <td><?= htmlspecialchars($p['duration'] ?? '-') ?></td>
                            <td>
                                <span class="badge <?= $p['is_active'] ? 'badge-success' : 'badge-error' ?>">
                                    <?= $p['is_active'] ? 'Aktif' : 'Nonaktif' ?>
                                </span>
                            </td>
                            <td>
                                <div class="action-btns">
                                    <a href="packages.php?action=edit&id=<?= $p['id'] ?>" class="btn btn-secondary btn-icon" title="Edit">
                                        <i class="fa-solid fa-edit"></i>
                                    </a>
                                    <a href="packages.php?action=delete&id=<?= $p['id'] ?>" class="btn btn-danger btn-icon" 
                                       data-confirm="Yakin ingin menghapus paket ini?" title="Hapus">
                                        <i class="fa-solid fa-trash"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</div>
<?php endif; ?>

<?php require_once 'includes/footer.php'; ?>
