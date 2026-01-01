<?php
// =====================================================
// ADMIN TESTIMONIALS PAGE
// File: admin/testimonials.php
// =====================================================

require_once 'includes/auth.php';
requireLogin();

define('PAGE_TITLE', 'Testimoni');
define('ACTIVE_MENU', 'testimonials');

$success = '';
$error = '';
$action = $_GET['action'] ?? 'list';
$editId = $_GET['id'] ?? null;

$db = getDB();

// Handle Delete
if ($action === 'delete' && $editId) {
    try {
        $stmt = $db->prepare("DELETE FROM testimonials WHERE id = ?");
        $stmt->execute([$editId]);
        logAdminActivity('delete_testimonial', "Menghapus testimoni ID: $editId");
        header('Location: testimonials.php?success=deleted');
        exit;
    } catch (PDOException $e) {
        $error = 'Gagal menghapus: ' . $e->getMessage();
    }
}

// Handle Form Submit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $customer_name = trim($_POST['customer_name'] ?? '');
    $customer_photo = trim($_POST['customer_photo'] ?? '');
    $package_name = trim($_POST['package_name'] ?? '');
    $testimonial_text = trim($_POST['testimonial_text'] ?? '');
    $rating = (int)($_POST['rating'] ?? 5);
    $testimonial_order = (int)($_POST['testimonial_order'] ?? 0);
    $is_active = isset($_POST['is_active']) ? 1 : 0;
    
    if (empty($customer_name) || empty($testimonial_text)) {
        $error = 'Nama pelanggan dan testimoni harus diisi!';
    } else {
        try {
            if ($action === 'edit' && $editId) {
                $stmt = $db->prepare("
                    UPDATE testimonials SET 
                        customer_name = ?, customer_photo = ?, package_name = ?, 
                        testimonial_text = ?, rating = ?, testimonial_order = ?, is_active = ?
                    WHERE id = ?
                ");
                $stmt->execute([$customer_name, $customer_photo ?: null, $package_name ?: null, 
                               $testimonial_text, $rating, $testimonial_order, $is_active, $editId]);
                logAdminActivity('update_testimonial', "Mengupdate testimoni: $customer_name");
                header('Location: testimonials.php?success=updated');
                exit;
            } else {
                $stmt = $db->prepare("
                    INSERT INTO testimonials (customer_name, customer_photo, package_name, testimonial_text, rating, testimonial_order, is_active)
                    VALUES (?, ?, ?, ?, ?, ?, ?)
                ");
                $stmt->execute([$customer_name, $customer_photo ?: null, $package_name ?: null, 
                               $testimonial_text, $rating, $testimonial_order, $is_active]);
                logAdminActivity('add_testimonial', "Menambah testimoni: $customer_name");
                header('Location: testimonials.php?success=added');
                exit;
            }
        } catch (PDOException $e) {
            $error = 'Gagal menyimpan: ' . $e->getMessage();
        }
    }
}

// Get testimonial for edit
$testimonial = null;
if ($action === 'edit' && $editId) {
    $stmt = $db->prepare("SELECT * FROM testimonials WHERE id = ?");
    $stmt->execute([$editId]);
    $testimonial = $stmt->fetch();
}

// Get all testimonials
$testimonials = $db->query("SELECT * FROM testimonials ORDER BY testimonial_order, id DESC")->fetchAll();

// Success messages
if (isset($_GET['success'])) {
    $messages = ['added' => 'Testimoni berhasil ditambahkan!', 'updated' => 'Testimoni berhasil diupdate!', 'deleted' => 'Testimoni berhasil dihapus!'];
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

<?php if ($action === 'add' || ($action === 'edit' && $testimonial)): ?>
<!-- Form Add/Edit -->
<div class="card">
    <div class="card-header">
        <h3 class="card-title"><?= $action === 'edit' ? 'Edit Testimoni' : 'Tambah Testimoni Baru' ?></h3>
        <a href="testimonials.php" class="btn btn-secondary btn-sm"><i class="fa-solid fa-arrow-left"></i> Kembali</a>
    </div>
    <div class="card-body">
        <form method="POST">
            <div class="form-row">
                <div class="form-group">
                    <label>Nama Pelanggan *</label>
                    <input type="text" name="customer_name" class="form-control" required
                           value="<?= htmlspecialchars($testimonial['customer_name'] ?? '') ?>" 
                           placeholder="Ahmad Yusuf">
                </div>
                <div class="form-group">
                    <label>Paket yang Diambil</label>
                    <input type="text" name="package_name" class="form-control" 
                           value="<?= htmlspecialchars($testimonial['package_name'] ?? '') ?>" 
                           placeholder="Umrah Premium 9 Hari">
                </div>
            </div>
            
            <div class="form-group">
                <label>URL Foto Pelanggan</label>
                <input type="url" name="customer_photo" class="form-control" 
                       value="<?= htmlspecialchars($testimonial['customer_photo'] ?? '') ?>" 
                       placeholder="https://...">
            </div>
            
            <div class="form-group">
                <label>Testimoni *</label>
                <textarea name="testimonial_text" class="form-control" required rows="4"
                          placeholder="Isi testimoni dari pelanggan..."><?= htmlspecialchars($testimonial['testimonial_text'] ?? '') ?></textarea>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label>Rating</label>
                    <select name="rating" class="form-control">
                        <?php for ($i = 5; $i >= 1; $i--): ?>
                            <option value="<?= $i ?>" <?= ($testimonial['rating'] ?? 5) == $i ? 'selected' : '' ?>>
                                <?= str_repeat('⭐', $i) ?> (<?= $i ?>)
                            </option>
                        <?php endfor; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>Urutan</label>
                    <input type="number" name="testimonial_order" class="form-control" 
                           value="<?= $testimonial['testimonial_order'] ?? 0 ?>" min="0">
                </div>
            </div>
            
            <div class="form-group">
                <label style="display: flex; align-items: center; gap: 0.5rem;">
                    <input type="checkbox" name="is_active" value="1" 
                           <?= ($testimonial['is_active'] ?? 1) ? 'checked' : '' ?>>
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
        <h3 class="card-title">Daftar Testimoni (<?= count($testimonials) ?>)</h3>
        <a href="testimonials.php?action=add" class="btn btn-primary btn-sm">
            <i class="fa-solid fa-plus"></i> Tambah Testimoni
        </a>
    </div>
    <div class="card-body" style="padding: 0;">
        <?php if (empty($testimonials)): ?>
            <div class="empty-state">
                <i class="fa-solid fa-quote-right"></i>
                <p>Belum ada testimoni. <a href="testimonials.php?action=add">Tambah sekarang</a></p>
            </div>
        <?php else: ?>
            <table class="table">
                <thead>
                    <tr>
                        <th>Pelanggan</th>
                        <th>Testimoni</th>
                        <th>Rating</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($testimonials as $t): ?>
                        <tr>
                            <td>
                                <div style="display: flex; align-items: center; gap: 0.75rem;">
                                    <?php if ($t['customer_photo']): ?>
                                        <img src="<?= htmlspecialchars($t['customer_photo']) ?>" 
                                             style="width: 40px; height: 40px; border-radius: 50%; object-fit: cover;">
                                    <?php else: ?>
                                        <div style="width:40px;height:40px;background:#D4AF37;border-radius:50%;display:flex;align-items:center;justify-content:center;color:white;font-weight:600;">
                                            <?= strtoupper(substr($t['customer_name'], 0, 1)) ?>
                                        </div>
                                    <?php endif; ?>
                                    <div>
                                        <strong><?= htmlspecialchars($t['customer_name']) ?></strong>
                                        <?php if ($t['package_name']): ?>
                                            <br><small style="color: var(--text-muted);"><?= htmlspecialchars($t['package_name']) ?></small>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </td>
                            <td style="max-width: 300px;">
                                "<?= htmlspecialchars(substr($t['testimonial_text'], 0, 100)) ?><?= strlen($t['testimonial_text']) > 100 ? '...' : '' ?>"
                            </td>
                            <td>
                                <span style="color: #F59E0B;"><?= str_repeat('★', $t['rating']) ?><?= str_repeat('☆', 5 - $t['rating']) ?></span>
                            </td>
                            <td>
                                <span class="badge <?= $t['is_active'] ? 'badge-success' : 'badge-error' ?>">
                                    <?= $t['is_active'] ? 'Aktif' : 'Nonaktif' ?>
                                </span>
                            </td>
                            <td>
                                <div class="action-btns">
                                    <a href="testimonials.php?action=edit&id=<?= $t['id'] ?>" class="btn btn-secondary btn-icon" title="Edit">
                                        <i class="fa-solid fa-edit"></i>
                                    </a>
                                    <a href="testimonials.php?action=delete&id=<?= $t['id'] ?>" class="btn btn-danger btn-icon" 
                                       data-confirm="Yakin ingin menghapus testimoni ini?" title="Hapus">
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
