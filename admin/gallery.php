<?php
// =====================================================
// ADMIN GALLERY PAGE
// File: admin/gallery.php
// =====================================================

require_once 'includes/auth.php';
requireLogin();

define('PAGE_TITLE', 'Galeri');
define('ACTIVE_MENU', 'gallery');

$success = '';
$error = '';
$action = $_GET['action'] ?? 'list';
$editId = $_GET['id'] ?? null;

$db = getDB();

// Handle Delete
if ($action === 'delete' && $editId) {
    try {
        $stmt = $db->prepare("DELETE FROM gallery WHERE id = ?");
        $stmt->execute([$editId]);
        logAdminActivity('delete_gallery', "Menghapus galeri ID: $editId");
        header('Location: gallery.php?success=deleted');
        exit;
    } catch (PDOException $e) {
        $error = 'Gagal menghapus: ' . $e->getMessage();
    }
}

// Handle Form Submit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $image_url = trim($_POST['image_url'] ?? '');
    $alt_text = trim($_POST['alt_text'] ?? '');
    $caption = trim($_POST['caption'] ?? '');
    $gallery_order = (int)($_POST['gallery_order'] ?? 0);
    $is_active = isset($_POST['is_active']) ? 1 : 0;
    
    if (empty($image_url)) {
        $error = 'URL gambar harus diisi!';
    } else {
        try {
            if ($action === 'edit' && $editId) {
                $stmt = $db->prepare("
                    UPDATE gallery SET 
                        image_url = ?, alt_text = ?, caption = ?, gallery_order = ?, is_active = ?
                    WHERE id = ?
                ");
                $stmt->execute([$image_url, $alt_text, $caption, $gallery_order, $is_active, $editId]);
                logAdminActivity('update_gallery', "Mengupdate galeri ID: $editId");
                header('Location: gallery.php?success=updated');
                exit;
            } else {
                $stmt = $db->prepare("
                    INSERT INTO gallery (image_url, alt_text, caption, gallery_order, is_active)
                    VALUES (?, ?, ?, ?, ?)
                ");
                $stmt->execute([$image_url, $alt_text, $caption, $gallery_order, $is_active]);
                logAdminActivity('add_gallery', "Menambah galeri baru");
                header('Location: gallery.php?success=added');
                exit;
            }
        } catch (PDOException $e) {
            $error = 'Gagal menyimpan: ' . $e->getMessage();
        }
    }
}

// Get gallery for edit
$gallery = null;
if ($action === 'edit' && $editId) {
    $stmt = $db->prepare("SELECT * FROM gallery WHERE id = ?");
    $stmt->execute([$editId]);
    $gallery = $stmt->fetch();
}

// Get all gallery
$galleries = $db->query("SELECT * FROM gallery ORDER BY gallery_order, id")->fetchAll();

// Success messages
if (isset($_GET['success'])) {
    $messages = ['added' => 'Gambar berhasil ditambahkan!', 'updated' => 'Gambar berhasil diupdate!', 'deleted' => 'Gambar berhasil dihapus!'];
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

<?php if ($action === 'add' || ($action === 'edit' && $gallery)): ?>
<!-- Form Add/Edit -->
<div class="card">
    <div class="card-header">
        <h3 class="card-title"><?= $action === 'edit' ? 'Edit Gambar' : 'Tambah Gambar Baru' ?></h3>
        <a href="gallery.php" class="btn btn-secondary btn-sm"><i class="fa-solid fa-arrow-left"></i> Kembali</a>
    </div>
    <div class="card-body">
        <form method="POST">
            <div class="form-group">
                <label>URL Gambar *</label>
                <input type="url" name="image_url" class="form-control" required
                       value="<?= htmlspecialchars($gallery['image_url'] ?? '') ?>" 
                       placeholder="https://images.unsplash.com/..." id="imageUrl">
                <div id="imagePreview" style="margin-top: 1rem;">
                    <?php if (!empty($gallery['image_url'])): ?>
                        <img src="<?= htmlspecialchars($gallery['image_url']) ?>" 
                             style="max-width: 300px; max-height: 200px; border-radius: 0.5rem; object-fit: cover;">
                    <?php endif; ?>
                </div>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label>Alt Text</label>
                    <input type="text" name="alt_text" class="form-control" 
                           value="<?= htmlspecialchars($gallery['alt_text'] ?? '') ?>" placeholder="Deskripsi gambar">
                </div>
                <div class="form-group">
                    <label>Urutan</label>
                    <input type="number" name="gallery_order" class="form-control" 
                           value="<?= $gallery['gallery_order'] ?? 0 ?>" min="0">
                </div>
            </div>
            
            <div class="form-group">
                <label>Caption</label>
                <input type="text" name="caption" class="form-control" 
                       value="<?= htmlspecialchars($gallery['caption'] ?? '') ?>" placeholder="Keterangan gambar (opsional)">
            </div>
            
            <div class="form-group">
                <label style="display: flex; align-items: center; gap: 0.5rem;">
                    <input type="checkbox" name="is_active" value="1" 
                           <?= ($gallery['is_active'] ?? 1) ? 'checked' : '' ?>>
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
document.getElementById('imageUrl').addEventListener('input', function() {
    const preview = document.getElementById('imagePreview');
    if (this.value) {
        preview.innerHTML = `<img src="${this.value}" style="max-width: 300px; max-height: 200px; border-radius: 0.5rem; object-fit: cover;" onerror="this.style.display='none'">`;
    } else {
        preview.innerHTML = '';
    }
});
</script>

<?php else: ?>
<!-- Grid View -->
<div class="card">
    <div class="card-header">
        <h3 class="card-title">Galeri (<?= count($galleries) ?> gambar)</h3>
        <a href="gallery.php?action=add" class="btn btn-primary btn-sm">
            <i class="fa-solid fa-plus"></i> Tambah Gambar
        </a>
    </div>
    <div class="card-body">
        <?php if (empty($galleries)): ?>
            <div class="empty-state">
                <i class="fa-solid fa-images"></i>
                <p>Belum ada gambar. <a href="gallery.php?action=add">Tambah sekarang</a></p>
            </div>
        <?php else: ?>
            <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: 1rem;">
                <?php foreach ($galleries as $g): ?>
                    <div style="position: relative; border-radius: 0.75rem; overflow: hidden; background: #E2E8F0;">
                        <img src="<?= htmlspecialchars($g['image_url']) ?>" 
                             style="width: 100%; height: 150px; object-fit: cover;"
                             onerror="this.src='data:image/svg+xml,<svg xmlns=%22http://www.w3.org/2000/svg%22 viewBox=%220 0 100 100%22><rect fill=%22%23ccc%22 width=%22100%22 height=%22100%22/><text x=%2250%22 y=%2250%22 text-anchor=%22middle%22 dy=%22.3em%22 fill=%22%23666%22>Error</text></svg>'">
                        
                        <?php if (!$g['is_active']): ?>
                            <div style="position: absolute; top: 0.5rem; left: 0.5rem;">
                                <span class="badge badge-error">Nonaktif</span>
                            </div>
                        <?php endif; ?>
                        
                        <div style="padding: 0.75rem; background: white;">
                            <p style="font-size: 0.85rem; color: var(--text-muted); margin-bottom: 0.5rem; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
                                <?= htmlspecialchars($g['alt_text'] ?: $g['caption'] ?: 'Tanpa deskripsi') ?>
                            </p>
                            <div class="action-btns">
                                <a href="gallery.php?action=edit&id=<?= $g['id'] ?>" class="btn btn-secondary btn-sm">
                                    <i class="fa-solid fa-edit"></i> Edit
                                </a>
                                <a href="gallery.php?action=delete&id=<?= $g['id'] ?>" class="btn btn-danger btn-sm" 
                                   data-confirm="Yakin ingin menghapus gambar ini?">
                                    <i class="fa-solid fa-trash"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>
<?php endif; ?>

<?php require_once 'includes/footer.php'; ?>
