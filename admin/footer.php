<?php
// =====================================================
// ADMIN FOOTER SETTINGS PAGE
// File: admin/footer.php
// =====================================================

require_once 'includes/auth.php';
requireLogin();

define('PAGE_TITLE', 'Pengaturan Footer');
define('ACTIVE_MENU', 'footer');

$success = '';
$error = '';
$db = getDB();

// Handle form submit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $fields = [
            'brand_description', 'license_info', 'copyright_text', 'created_by',
            'contact_address', 'contact_phone', 'contact_email', 'contact_whatsapp'
        ];
        
        foreach ($fields as $field) {
            $value = trim($_POST[$field] ?? '');
            $stmt = $db->prepare("INSERT INTO footer_info (info_key, info_value) VALUES (?, ?) 
                                  ON DUPLICATE KEY UPDATE info_value = VALUES(info_value)");
            $stmt->execute([$field, $value]);
        }
        
        logAdminActivity('update_footer', 'Mengupdate pengaturan footer');
        $success = 'Pengaturan footer berhasil disimpan!';
    } catch (PDOException $e) {
        $error = 'Gagal menyimpan: ' . $e->getMessage();
    }
}

// Get all footer info
$footerData = [];
$stmt = $db->query("SELECT info_key, info_value FROM footer_info");
while ($row = $stmt->fetch()) {
    $footerData[$row['info_key']] = $row['info_value'];
}

require_once 'includes/header.php';
?>

<?php if ($success): ?>
    <div class="alert alert-success"><i class="fa-solid fa-check-circle"></i> <?= htmlspecialchars($success) ?></div>
<?php endif; ?>

<?php if ($error): ?>
    <div class="alert alert-error"><i class="fa-solid fa-exclamation-circle"></i> <?= htmlspecialchars($error) ?></div>
<?php endif; ?>

<form method="POST">
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Informasi Brand</h3>
        </div>
        <div class="card-body">
            <div class="form-group">
                <label>Deskripsi Brand</label>
                <textarea name="brand_description" class="form-control" rows="3"><?= htmlspecialchars($footerData['brand_description'] ?? '') ?></textarea>
                <small class="form-hint">Teks yang muncul di bawah logo footer</small>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label>Info Izin/Lisensi</label>
                    <input type="text" name="license_info" class="form-control" 
                           value="<?= htmlspecialchars($footerData['license_info'] ?? '') ?>"
                           placeholder="Izin Resmi Kemenag No. 123/2024">
                </div>
                <div class="form-group">
                    <label>Dibuat Oleh</label>
                    <input type="text" name="created_by" class="form-control" 
                           value="<?= htmlspecialchars($footerData['created_by'] ?? '') ?>"
                           placeholder="Nama pembuat website">
                </div>
            </div>
            <div class="form-group">
                <label>Copyright Text</label>
                <input type="text" name="copyright_text" class="form-control" 
                       value="<?= htmlspecialchars($footerData['copyright_text'] ?? '') ?>"
                       placeholder="© 2024 Tanah Suci Travel. All Rights Reserved.">
            </div>
        </div>
    </div>
    
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Informasi Kontak</h3>
        </div>
        <div class="card-body">
            <div class="form-group">
                <label>Alamat</label>
                <textarea name="contact_address" class="form-control" rows="2"><?= htmlspecialchars($footerData['contact_address'] ?? '') ?></textarea>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label>Nomor Telepon</label>
                    <input type="text" name="contact_phone" class="form-control" 
                           value="<?= htmlspecialchars($footerData['contact_phone'] ?? '') ?>"
                           placeholder="+62 812-3456-7890">
                </div>
                <div class="form-group">
                    <label>WhatsApp</label>
                    <input type="text" name="contact_whatsapp" class="form-control" 
                           value="<?= htmlspecialchars($footerData['contact_whatsapp'] ?? '') ?>"
                           placeholder="+62 812-3456-7890">
                    <small class="form-hint">Untuk tombol Chat WhatsApp</small>
                </div>
            </div>
            <div class="form-group">
                <label>Email</label>
                <input type="email" name="contact_email" class="form-control" 
                       value="<?= htmlspecialchars($footerData['contact_email'] ?? '') ?>"
                       placeholder="info@tanahsuci.com">
            </div>
        </div>
    </div>
    
    <button type="submit" class="btn btn-primary">
        <i class="fa-solid fa-save"></i> Simpan Pengaturan
    </button>
</form>

<?php require_once 'includes/footer.php'; ?>
