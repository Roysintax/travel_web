<?php
// =====================================================
// ADMIN FEATURES PAGE
// File: admin/features.php
// =====================================================

require_once 'includes/auth.php';
requireLogin();

define('PAGE_TITLE', 'Fitur');
define('ACTIVE_MENU', 'features');

$success = '';
$error = '';
$action = $_GET['action'] ?? 'list';
$editId = $_GET['id'] ?? null;

$db = getDB();

// Handle Delete
if ($action === 'delete' && $editId) {
    try {
        $stmt = $db->prepare("DELETE FROM features WHERE id = ?");
        $stmt->execute([$editId]);
        logAdminActivity('delete_feature', "Menghapus fitur ID: $editId");
        header('Location: features.php?success=deleted');
        exit;
    } catch (PDOException $e) {
        $error = 'Gagal menghapus: ' . $e->getMessage();
    }
}

// Handle Form Submit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $icon = trim($_POST['icon'] ?? '');
    $title = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $link_url = trim($_POST['link_url'] ?? '#');
    $link_text = trim($_POST['link_text'] ?? 'Baca Selengkapnya');
    $feature_order = (int)($_POST['feature_order'] ?? 0);
    $is_active = isset($_POST['is_active']) ? 1 : 0;
    
    if (empty($title) || empty($description)) {
        $error = 'Judul dan deskripsi harus diisi!';
    } else {
        try {
            if ($action === 'edit' && $editId) {
                $stmt = $db->prepare("
                    UPDATE features SET 
                        icon = ?, title = ?, description = ?, link_url = ?, 
                        link_text = ?, feature_order = ?, is_active = ?
                    WHERE id = ?
                ");
                $stmt->execute([$icon, $title, $description, $link_url, $link_text, $feature_order, $is_active, $editId]);
                logAdminActivity('update_feature', "Mengupdate fitur: $title");
                header('Location: features.php?success=updated');
                exit;
            } else {
                $stmt = $db->prepare("
                    INSERT INTO features (icon, title, description, link_url, link_text, feature_order, is_active)
                    VALUES (?, ?, ?, ?, ?, ?, ?)
                ");
                $stmt->execute([$icon, $title, $description, $link_url, $link_text, $feature_order, $is_active]);
                logAdminActivity('add_feature', "Menambah fitur: $title");
                header('Location: features.php?success=added');
                exit;
            }
        } catch (PDOException $e) {
            $error = 'Gagal menyimpan: ' . $e->getMessage();
        }
    }
}

// Get feature for edit
$feature = null;
if ($action === 'edit' && $editId) {
    $stmt = $db->prepare("SELECT * FROM features WHERE id = ?");
    $stmt->execute([$editId]);
    $feature = $stmt->fetch();
}

// Get all features
$features = $db->query("SELECT * FROM features ORDER BY feature_order, id")->fetchAll();

// Success messages
if (isset($_GET['success'])) {
    $messages = ['added' => 'Fitur berhasil ditambahkan!', 'updated' => 'Fitur berhasil diupdate!', 'deleted' => 'Fitur berhasil dihapus!'];
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

<?php if ($action === 'add' || ($action === 'edit' && $feature)): ?>
<!-- Form Add/Edit -->
<div class="card">
    <div class="card-header">
        <h3 class="card-title"><?= $action === 'edit' ? 'Edit Fitur' : 'Tambah Fitur Baru' ?></h3>
        <a href="features.php" class="btn btn-secondary btn-sm"><i class="fa-solid fa-arrow-left"></i> Kembali</a>
    </div>
    <div class="card-body">
        <form method="POST">
            <div class="form-row">
                <div class="form-group">
                    <label>Icon (Font Awesome Class)</label>
                    <input type="text" name="icon" class="form-control" 
                           value="<?= htmlspecialchars($feature['icon'] ?? 'fa-solid fa-star') ?>" 
                           placeholder="fa-solid fa-hotel">
                    <span class="form-hint">Contoh: fa-solid fa-hotel, fa-solid fa-kaaba, fa-solid fa-plane</span>
                </div>
                <div class="form-group">
                    <label>Urutan</label>
                    <input type="number" name="feature_order" class="form-control" 
                           value="<?= $feature['feature_order'] ?? 0 ?>" min="0">
                </div>
            </div>
            
            <div class="form-group">
                <label>Judul *</label>
                <input type="text" name="title" class="form-control" required
                       value="<?= htmlspecialchars($feature['title'] ?? '') ?>" placeholder="Akomodasi Premium">
            </div>
            
            <div class="form-group">
                <label>Deskripsi *</label>
                <textarea name="description" class="form-control" required rows="3"
                          placeholder="Deskripsi singkat tentang fitur ini"><?= htmlspecialchars($feature['description'] ?? '') ?></textarea>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label>URL Link</label>
                    <input type="text" name="link_url" class="form-control" 
                           value="<?= htmlspecialchars($feature['link_url'] ?? '#') ?>" placeholder="akomodasi-premium.html">
                </div>
                <div class="form-group">
                    <label>Teks Link</label>
                    <input type="text" name="link_text" class="form-control" 
                           value="<?= htmlspecialchars($feature['link_text'] ?? 'Baca Selengkapnya') ?>">
                </div>
            </div>
            
            <div class="form-group">
                <label style="display: flex; align-items: center; gap: 0.5rem;">
                    <input type="checkbox" name="is_active" value="1" 
                           <?= ($feature['is_active'] ?? 1) ? 'checked' : '' ?>>
                    Aktif
                </label>
            </div>
            
            <button type="submit" class="btn btn-primary">
                <i class="fa-solid fa-save"></i> Simpan
            </button>
        </form>
    </div>
</div>

<?php else: ?>
<!-- List View -->
<div class="card">
    <div class="card-header">
        <h3 class="card-title">Daftar Fitur (<?= count($features) ?>)</h3>
        <a href="features.php?action=add" class="btn btn-primary btn-sm">
            <i class="fa-solid fa-plus"></i> Tambah Fitur
        </a>
    </div>
    <div class="card-body" style="padding: 0;">
        <?php if (empty($features)): ?>
            <div class="empty-state">
                <i class="fa-solid fa-star"></i>
                <p>Belum ada fitur. <a href="features.php?action=add">Tambah sekarang</a></p>
            </div>
        <?php else: ?>
            <table class="table">
                <thead>
                    <tr>
                        <th>Icon</th>
                        <th>Judul</th>
                        <th>Deskripsi</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($features as $f): ?>
                        <tr>
                            <td>
                                <div style="width:50px;height:50px;background:#FEF3C7;border-radius:50%;display:flex;align-items:center;justify-content:center;color:#D97706;font-size:1.25rem;">
                                    <i class="<?= htmlspecialchars($f['icon']) ?>"></i>
                                </div>
                            </td>
                            <td><strong><?= htmlspecialchars($f['title']) ?></strong></td>
                            <td style="max-width:300px;">
                                <?= htmlspecialchars(substr($f['description'], 0, 100)) ?><?= strlen($f['description']) > 100 ? '...' : '' ?>
                            </td>
                            <td>
                                <span class="badge <?= $f['is_active'] ? 'badge-success' : 'badge-error' ?>">
                                    <?= $f['is_active'] ? 'Aktif' : 'Nonaktif' ?>
                                </span>
                            </td>
                            <td>
                                <div class="action-btns">
                                    <a href="features.php?action=edit&id=<?= $f['id'] ?>" class="btn btn-secondary btn-icon" title="Edit">
                                        <i class="fa-solid fa-edit"></i>
                                    </a>
                                    <a href="features.php?action=delete&id=<?= $f['id'] ?>" class="btn btn-danger btn-icon" 
                                       data-confirm="Yakin ingin menghapus fitur ini?" title="Hapus">
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
