<?php
// =====================================================
// ADMIN SETTINGS PAGE
// File: admin/settings.php
// =====================================================

require_once 'includes/auth.php';
requireLogin();

define('PAGE_TITLE', 'Pengaturan');
define('ACTIVE_MENU', 'settings');

$success = '';
$error = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $db = getDB();
    
    try {
        foreach ($_POST as $key => $value) {
            if (strpos($key, 'setting_') === 0) {
                $settingKey = substr($key, 8); // Remove 'setting_' prefix
                $stmt = $db->prepare("UPDATE site_settings SET setting_value = ? WHERE setting_key = ?");
                $stmt->execute([trim($value), $settingKey]);
            }
        }
        
        logAdminActivity('update_settings', 'Mengupdate pengaturan website');
        $success = 'Pengaturan berhasil disimpan!';
    } catch (PDOException $e) {
        $error = 'Gagal menyimpan pengaturan: ' . $e->getMessage();
    }
}

// Get all settings grouped
try {
    $db = getDB();
    $stmt = $db->query("SELECT * FROM site_settings ORDER BY setting_group, id");
    $allSettings = $stmt->fetchAll();
    
    $settingGroups = [];
    foreach ($allSettings as $setting) {
        $settingGroups[$setting['setting_group']][] = $setting;
    }
} catch (PDOException $e) {
    $settingGroups = [];
    $error = 'Gagal mengambil data: ' . $e->getMessage();
}

$groupLabels = [
    'general' => ['label' => 'Umum', 'icon' => 'fa-cog'],
    'hero' => ['label' => 'Hero Section', 'icon' => 'fa-image'],
    'about' => ['label' => 'About Section', 'icon' => 'fa-info-circle'],
    'cta' => ['label' => 'CTA Section', 'icon' => 'fa-bullhorn'],
    'packages' => ['label' => 'Packages Section', 'icon' => 'fa-box']
];

require_once 'includes/header.php';
?>

<?php if ($success): ?>
    <div class="alert alert-success">
        <i class="fa-solid fa-check-circle"></i>
        <?= htmlspecialchars($success) ?>
    </div>
<?php endif; ?>

<?php if ($error): ?>
    <div class="alert alert-error">
        <i class="fa-solid fa-exclamation-circle"></i>
        <?= htmlspecialchars($error) ?>
    </div>
<?php endif; ?>

<div class="card">
    <div class="card-header">
        <h3 class="card-title">Pengaturan Website</h3>
    </div>
    <div class="card-body">
        <div class="tabs">
            <?php $first = true; foreach ($groupLabels as $groupKey => $groupInfo): ?>
                <?php if (isset($settingGroups[$groupKey])): ?>
                    <button class="tab-btn <?= $first ? 'active' : '' ?>" data-tab="tab-<?= $groupKey ?>">
                        <i class="fa-solid <?= $groupInfo['icon'] ?>"></i>
                        <?= $groupInfo['label'] ?>
                    </button>
                    <?php $first = false; ?>
                <?php endif; ?>
            <?php endforeach; ?>
        </div>

        <form method="POST" action="">
            <?php $first = true; foreach ($groupLabels as $groupKey => $groupInfo): ?>
                <?php if (isset($settingGroups[$groupKey])): ?>
                    <div class="tab-content <?= $first ? 'active' : '' ?>" id="tab-<?= $groupKey ?>">
                        <div class="form-row">
                            <?php foreach ($settingGroups[$groupKey] as $setting): ?>
                                <div class="form-group">
                                    <label for="setting_<?= $setting['setting_key'] ?>">
                                        <?= htmlspecialchars($setting['setting_label'] ?? $setting['setting_key']) ?>
                                    </label>
                                    
                                    <?php if ($setting['setting_type'] === 'textarea'): ?>
                                        <textarea 
                                            id="setting_<?= $setting['setting_key'] ?>"
                                            name="setting_<?= $setting['setting_key'] ?>"
                                            class="form-control"
                                            rows="3"
                                        ><?= htmlspecialchars($setting['setting_value']) ?></textarea>
                                    
                                    <?php elseif ($setting['setting_type'] === 'color'): ?>
                                        <div style="display: flex; gap: 0.5rem;">
                                            <input 
                                                type="color" 
                                                id="color_<?= $setting['setting_key'] ?>"
                                                value="<?= htmlspecialchars($setting['setting_value']) ?>"
                                                style="width: 50px; height: 38px; padding: 2px; cursor: pointer;"
                                                onchange="document.getElementById('setting_<?= $setting['setting_key'] ?>').value = this.value"
                                            >
                                            <input 
                                                type="text" 
                                                id="setting_<?= $setting['setting_key'] ?>"
                                                name="setting_<?= $setting['setting_key'] ?>"
                                                class="form-control"
                                                value="<?= htmlspecialchars($setting['setting_value']) ?>"
                                                placeholder="#000000"
                                            >
                                        </div>
                                    
                                    <?php elseif ($setting['setting_type'] === 'image'): ?>
                                        <input 
                                            type="url" 
                                            id="setting_<?= $setting['setting_key'] ?>"
                                            name="setting_<?= $setting['setting_key'] ?>"
                                            class="form-control"
                                            value="<?= htmlspecialchars($setting['setting_value']) ?>"
                                            placeholder="https://example.com/image.jpg"
                                        >
                                        <?php if (!empty($setting['setting_value'])): ?>
                                            <img src="<?= htmlspecialchars($setting['setting_value']) ?>" 
                                                 style="max-width: 200px; max-height: 100px; margin-top: 0.5rem; border-radius: 0.5rem; object-fit: cover;">
                                        <?php endif; ?>
                                    
                                    <?php else: ?>
                                        <input 
                                            type="<?= $setting['setting_type'] === 'number' ? 'number' : 'text' ?>" 
                                            id="setting_<?= $setting['setting_key'] ?>"
                                            name="setting_<?= $setting['setting_key'] ?>"
                                            class="form-control"
                                            value="<?= htmlspecialchars($setting['setting_value']) ?>"
                                        >
                                    <?php endif; ?>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <?php $first = false; ?>
                <?php endif; ?>
            <?php endforeach; ?>

            <div style="margin-top: 1.5rem; padding-top: 1.5rem; border-top: 1px solid #E2E8F0;">
                <button type="submit" class="btn btn-primary">
                    <i class="fa-solid fa-save"></i>
                    Simpan Pengaturan
                </button>
            </div>
        </form>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
