<?php
// =====================================================
// ADMIN INQUIRIES PAGE
// File: admin/inquiries.php
// =====================================================

require_once 'includes/auth.php';
requireLogin();

define('PAGE_TITLE', 'Pesan Masuk');
define('ACTIVE_MENU', 'inquiries');

$success = '';
$error = '';
$action = $_GET['action'] ?? 'list';
$viewId = $_GET['id'] ?? null;

$db = getDB();

// Handle status update
if ($action === 'update_status' && $viewId && isset($_GET['status'])) {
    $newStatus = $_GET['status'];
    $validStatuses = ['new', 'read', 'replied', 'closed'];
    
    if (in_array($newStatus, $validStatuses)) {
        try {
            $stmt = $db->prepare("UPDATE inquiries SET status = ? WHERE id = ?");
            $stmt->execute([$newStatus, $viewId]);
            logAdminActivity('update_inquiry_status', "Mengubah status pesan ID: $viewId menjadi $newStatus");
            header('Location: inquiries.php?success=status_updated');
            exit;
        } catch (PDOException $e) {
            $error = 'Gagal mengupdate status: ' . $e->getMessage();
        }
    }
}

// Handle Delete
if ($action === 'delete' && $viewId) {
    try {
        $stmt = $db->prepare("DELETE FROM inquiries WHERE id = ?");
        $stmt->execute([$viewId]);
        logAdminActivity('delete_inquiry', "Menghapus pesan ID: $viewId");
        header('Location: inquiries.php?success=deleted');
        exit;
    } catch (PDOException $e) {
        $error = 'Gagal menghapus: ' . $e->getMessage();
    }
}

// Get inquiry for view
$inquiry = null;
if ($action === 'view' && $viewId) {
    $stmt = $db->prepare("SELECT i.*, p.name as package_name FROM inquiries i LEFT JOIN packages p ON i.package_interest = p.id WHERE i.id = ?");
    $stmt->execute([$viewId]);
    $inquiry = $stmt->fetch();
    
    // Mark as read if new
    if ($inquiry && $inquiry['status'] === 'new') {
        $db->prepare("UPDATE inquiries SET status = 'read' WHERE id = ?")->execute([$viewId]);
        $inquiry['status'] = 'read';
    }
}

// Get filter
$filter = $_GET['filter'] ?? 'all';

// Get all inquiries
$sql = "SELECT i.*, p.name as package_name FROM inquiries i LEFT JOIN packages p ON i.package_interest = p.id";
if ($filter !== 'all') {
    $sql .= " WHERE i.status = ?";
    $stmt = $db->prepare($sql . " ORDER BY i.created_at DESC");
    $stmt->execute([$filter]);
    $inquiries = $stmt->fetchAll();
} else {
    $inquiries = $db->query($sql . " ORDER BY i.created_at DESC")->fetchAll();
}

// Count by status
$counts = $db->query("SELECT status, COUNT(*) as count FROM inquiries GROUP BY status")->fetchAll(PDO::FETCH_KEY_PAIR);
$totalCount = array_sum($counts);

// Success messages
if (isset($_GET['success'])) {
    $messages = [
        'status_updated' => 'Status berhasil diupdate!', 
        'deleted' => 'Pesan berhasil dihapus!'
    ];
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

<?php if ($action === 'view' && $inquiry): ?>
<!-- View Detail -->
<div class="card">
    <div class="card-header">
        <h3 class="card-title">Detail Pesan</h3>
        <a href="inquiries.php" class="btn btn-secondary btn-sm"><i class="fa-solid fa-arrow-left"></i> Kembali</a>
    </div>
    <div class="card-body">
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 2rem;">
            <div>
                <h4 style="margin-bottom: 1rem; color: var(--primary);">Informasi Pengirim</h4>
                <table style="width: 100%;">
                    <tr>
                        <td style="padding: 0.5rem 0; color: var(--text-muted); width: 100px;">Nama</td>
                        <td style="padding: 0.5rem 0;"><strong><?= htmlspecialchars($inquiry['name']) ?></strong></td>
                    </tr>
                    <tr>
                        <td style="padding: 0.5rem 0; color: var(--text-muted);">Email</td>
                        <td style="padding: 0.5rem 0;">
                            <a href="mailto:<?= htmlspecialchars($inquiry['email']) ?>" style="color: var(--accent);">
                                <?= htmlspecialchars($inquiry['email']) ?>
                            </a>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding: 0.5rem 0; color: var(--text-muted);">Telepon</td>
                        <td style="padding: 0.5rem 0;">
                            <?php if ($inquiry['phone']): ?>
                                <a href="tel:<?= htmlspecialchars($inquiry['phone']) ?>" style="color: var(--accent);">
                                    <?= htmlspecialchars($inquiry['phone']) ?>
                                </a>
                            <?php else: ?>
                                <span style="color: var(--text-muted);">-</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding: 0.5rem 0; color: var(--text-muted);">Paket</td>
                        <td style="padding: 0.5rem 0;">
                            <?= htmlspecialchars($inquiry['package_name'] ?? '-') ?>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding: 0.5rem 0; color: var(--text-muted);">Tanggal</td>
                        <td style="padding: 0.5rem 0;"><?= date('d M Y, H:i', strtotime($inquiry['created_at'])) ?></td>
                    </tr>
                    <tr>
                        <td style="padding: 0.5rem 0; color: var(--text-muted);">Status</td>
                        <td style="padding: 0.5rem 0;">
                            <?php
                            $statusBadge = ['new' => 'badge-warning', 'read' => 'badge-info', 'replied' => 'badge-success', 'closed' => 'badge-error'][$inquiry['status']] ?? 'badge-info';
                            $statusText = ['new' => 'Baru', 'read' => 'Dibaca', 'replied' => 'Dijawab', 'closed' => 'Ditutup'][$inquiry['status']] ?? $inquiry['status'];
                            ?>
                            <span class="badge <?= $statusBadge ?>"><?= $statusText ?></span>
                        </td>
                    </tr>
                </table>
            </div>
            
            <div>
                <h4 style="margin-bottom: 1rem; color: var(--primary);">Ubah Status</h4>
                <div style="display: flex; gap: 0.5rem; flex-wrap: wrap;">
                    <a href="inquiries.php?action=update_status&id=<?= $inquiry['id'] ?>&status=read" 
                       class="btn btn-sm <?= $inquiry['status'] === 'read' ? 'btn-primary' : 'btn-secondary' ?>">
                        <i class="fa-solid fa-eye"></i> Dibaca
                    </a>
                    <a href="inquiries.php?action=update_status&id=<?= $inquiry['id'] ?>&status=replied" 
                       class="btn btn-sm <?= $inquiry['status'] === 'replied' ? 'btn-primary' : 'btn-secondary' ?>">
                        <i class="fa-solid fa-reply"></i> Dijawab
                    </a>
                    <a href="inquiries.php?action=update_status&id=<?= $inquiry['id'] ?>&status=closed" 
                       class="btn btn-sm <?= $inquiry['status'] === 'closed' ? 'btn-primary' : 'btn-secondary' ?>">
                        <i class="fa-solid fa-check"></i> Ditutup
                    </a>
                </div>
                
                <div style="margin-top: 1.5rem;">
                    <h4 style="margin-bottom: 0.5rem; color: var(--primary);">Aksi Cepat</h4>
                    <div style="display: flex; gap: 0.5rem; flex-wrap: wrap;">
                        <a href="mailto:<?= htmlspecialchars($inquiry['email']) ?>?subject=Re: <?= htmlspecialchars($inquiry['subject'] ?? 'Pertanyaan') ?>" 
                           class="btn btn-sm btn-success">
                            <i class="fa-solid fa-envelope"></i> Balas Email
                        </a>
                        <?php if ($inquiry['phone']): ?>
                            <a href="https://wa.me/<?= preg_replace('/[^0-9]/', '', $inquiry['phone']) ?>" 
                               class="btn btn-sm btn-success" target="_blank">
                                <i class="fa-brands fa-whatsapp"></i> WhatsApp
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
        
        <hr style="margin: 1.5rem 0; border: none; border-top: 1px solid #E2E8F0;">
        
        <div>
            <h4 style="margin-bottom: 0.5rem; color: var(--primary);">
                <?= $inquiry['subject'] ? htmlspecialchars($inquiry['subject']) : 'Pesan' ?>
            </h4>
            <div style="background: var(--bg-light); padding: 1.5rem; border-radius: 0.5rem; line-height: 1.8;">
                <?= nl2br(htmlspecialchars($inquiry['message'])) ?>
            </div>
        </div>
    </div>
</div>

<?php else: ?>
<!-- List View -->
<div class="card">
    <div class="card-header">
        <h3 class="card-title">Pesan Masuk (<?= $totalCount ?>)</h3>
    </div>
    <div class="card-body">
        <!-- Filter Tabs -->
        <div class="tabs" style="margin-bottom: 1.5rem;">
            <a href="inquiries.php" class="tab-btn <?= $filter === 'all' ? 'active' : '' ?>" style="text-decoration: none;">
                Semua (<?= $totalCount ?>)
            </a>
            <a href="inquiries.php?filter=new" class="tab-btn <?= $filter === 'new' ? 'active' : '' ?>" style="text-decoration: none;">
                Baru (<?= $counts['new'] ?? 0 ?>)
            </a>
            <a href="inquiries.php?filter=read" class="tab-btn <?= $filter === 'read' ? 'active' : '' ?>" style="text-decoration: none;">
                Dibaca (<?= $counts['read'] ?? 0 ?>)
            </a>
            <a href="inquiries.php?filter=replied" class="tab-btn <?= $filter === 'replied' ? 'active' : '' ?>" style="text-decoration: none;">
                Dijawab (<?= $counts['replied'] ?? 0 ?>)
            </a>
            <a href="inquiries.php?filter=closed" class="tab-btn <?= $filter === 'closed' ? 'active' : '' ?>" style="text-decoration: none;">
                Ditutup (<?= $counts['closed'] ?? 0 ?>)
            </a>
        </div>
        
        <?php if (empty($inquiries)): ?>
            <div class="empty-state">
                <i class="fa-solid fa-envelope-open"></i>
                <p>Tidak ada pesan<?= $filter !== 'all' ? " dengan status \"$filter\"" : '' ?>.</p>
            </div>
        <?php else: ?>
            <div style="overflow-x: auto;">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Pengirim</th>
                            <th>Subjek</th>
                            <th>Paket Minat</th>
                            <th>Status</th>
                            <th>Tanggal</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($inquiries as $inq): ?>
                            <tr style="<?= $inq['status'] === 'new' ? 'background: #FEF9E7;' : '' ?>">
                                <td>
                                    <strong><?= htmlspecialchars($inq['name']) ?></strong>
                                    <br><small style="color: var(--text-muted);"><?= htmlspecialchars($inq['email']) ?></small>
                                </td>
                                <td>
                                    <?= htmlspecialchars($inq['subject'] ?? '-') ?>
                                    <br><small style="color: var(--text-muted);">
                                        <?= htmlspecialchars(substr($inq['message'], 0, 50)) ?><?= strlen($inq['message']) > 50 ? '...' : '' ?>
                                    </small>
                                </td>
                                <td><?= htmlspecialchars($inq['package_name'] ?? '-') ?></td>
                                <td>
                                    <?php
                                    $statusBadge = ['new' => 'badge-warning', 'read' => 'badge-info', 'replied' => 'badge-success', 'closed' => 'badge-error'][$inq['status']] ?? 'badge-info';
                                    $statusText = ['new' => 'Baru', 'read' => 'Dibaca', 'replied' => 'Dijawab', 'closed' => 'Ditutup'][$inq['status']] ?? $inq['status'];
                                    ?>
                                    <span class="badge <?= $statusBadge ?>"><?= $statusText ?></span>
                                </td>
                                <td>
                                    <small><?= date('d M Y', strtotime($inq['created_at'])) ?></small>
                                    <br><small style="color: var(--text-muted);"><?= date('H:i', strtotime($inq['created_at'])) ?></small>
                                </td>
                                <td>
                                    <div class="action-btns">
                                        <a href="inquiries.php?action=view&id=<?= $inq['id'] ?>" class="btn btn-secondary btn-icon" title="Lihat">
                                            <i class="fa-solid fa-eye"></i>
                                        </a>
                                        <a href="inquiries.php?action=delete&id=<?= $inq['id'] ?>" class="btn btn-danger btn-icon" 
                                           data-confirm="Yakin ingin menghapus pesan ini?" title="Hapus">
                                            <i class="fa-solid fa-trash"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>
<?php endif; ?>

<?php require_once 'includes/footer.php'; ?>
