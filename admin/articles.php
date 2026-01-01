<?php
// =====================================================
// ADMIN ARTICLES PAGE
// File: admin/articles.php
// =====================================================

require_once 'includes/auth.php';
requireLogin();

define('PAGE_TITLE', 'Artikel');
define('ACTIVE_MENU', 'articles');

$success = '';
$error = '';
$action = $_GET['action'] ?? 'list';
$editId = $_GET['id'] ?? null;

$db = getDB();

// Handle Delete
if ($action === 'delete' && $editId) {
    try {
        $stmt = $db->prepare("DELETE FROM articles WHERE id = ?");
        $stmt->execute([$editId]);
        logAdminActivity('delete_article', "Menghapus artikel ID: $editId");
        header('Location: articles.php?success=deleted');
        exit;
    } catch (PDOException $e) {
        $error = 'Gagal menghapus: ' . $e->getMessage();
    }
}

// Handle Form Submit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $slug = trim($_POST['slug'] ?? '');
    $title = trim($_POST['title'] ?? '');
    $title_highlight = trim($_POST['title_highlight'] ?? '');
    $subtitle = trim($_POST['subtitle'] ?? '');
    $hero_image = trim($_POST['hero_image'] ?? '');
    $content_image = trim($_POST['content_image'] ?? '');
    $content_title = trim($_POST['content_title'] ?? '');
    $content_text = trim($_POST['content_text'] ?? '');
    $cards_section_title = trim($_POST['cards_section_title'] ?? '');
    $cards_section_subtitle = trim($_POST['cards_section_subtitle'] ?? '');
    $cta_title = trim($_POST['cta_title'] ?? '');
    $cta_text = trim($_POST['cta_text'] ?? '');
    $meta_description = trim($_POST['meta_description'] ?? '');
    $is_active = isset($_POST['is_active']) ? 1 : 0;
    
    // Build features JSON
    $features = [];
    if (!empty($_POST['feature_icon'])) {
        foreach ($_POST['feature_icon'] as $i => $icon) {
            if (!empty($icon) || !empty($_POST['feature_title'][$i])) {
                $features[] = [
                    'icon' => $icon,
                    'title' => $_POST['feature_title'][$i] ?? '',
                    'desc' => $_POST['feature_desc'][$i] ?? ''
                ];
            }
        }
    }
    $features_json = json_encode($features);
    
    // Build cards JSON
    $cards = [];
    if (!empty($_POST['card_image'])) {
        foreach ($_POST['card_image'] as $i => $image) {
            if (!empty($image) || !empty($_POST['card_title'][$i])) {
                $cards[] = [
                    'image' => $image,
                    'title' => $_POST['card_title'][$i] ?? '',
                    'location' => $_POST['card_location'][$i] ?? '',
                    'rating' => (int)($_POST['card_rating'][$i] ?? 5)
                ];
            }
        }
    }
    $cards_json = json_encode($cards);
    
    // Validate
    $slug = preg_replace('/[^a-z0-9-]/', '', strtolower(str_replace(' ', '-', $slug)));
    
    if (empty($slug) || empty($title)) {
        $error = 'Slug dan judul harus diisi!';
    } else {
        try {
            if ($action === 'edit' && $editId) {
                $stmt = $db->prepare("
                    UPDATE articles SET 
                        slug = ?, title = ?, title_highlight = ?, subtitle = ?, hero_image = ?,
                        content_image = ?, content_title = ?, content_text = ?, features_json = ?,
                        cards_section_title = ?, cards_section_subtitle = ?, cards_json = ?,
                        cta_title = ?, cta_text = ?, meta_description = ?, is_active = ?
                    WHERE id = ?
                ");
                $stmt->execute([$slug, $title, $title_highlight, $subtitle, $hero_image,
                               $content_image, $content_title, $content_text, $features_json,
                               $cards_section_title, $cards_section_subtitle, $cards_json,
                               $cta_title, $cta_text, $meta_description, $is_active, $editId]);
                logAdminActivity('update_article', "Mengupdate artikel: $title");
                header('Location: articles.php?success=updated');
                exit;
            } else {
                $stmt = $db->prepare("
                    INSERT INTO articles (slug, title, title_highlight, subtitle, hero_image,
                        content_image, content_title, content_text, features_json,
                        cards_section_title, cards_section_subtitle, cards_json,
                        cta_title, cta_text, meta_description, is_active)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
                ");
                $stmt->execute([$slug, $title, $title_highlight, $subtitle, $hero_image,
                               $content_image, $content_title, $content_text, $features_json,
                               $cards_section_title, $cards_section_subtitle, $cards_json,
                               $cta_title, $cta_text, $meta_description, $is_active]);
                logAdminActivity('add_article', "Menambah artikel: $title");
                header('Location: articles.php?success=added');
                exit;
            }
        } catch (PDOException $e) {
            $error = 'Gagal menyimpan: ' . $e->getMessage();
        }
    }
}

// Get article for edit
$article = null;
$features = [];
$cards = [];
if ($action === 'edit' && $editId) {
    $stmt = $db->prepare("SELECT * FROM articles WHERE id = ?");
    $stmt->execute([$editId]);
    $article = $stmt->fetch();
    if ($article) {
        $features = json_decode($article['features_json'] ?? '[]', true) ?: [];
        $cards = json_decode($article['cards_json'] ?? '[]', true) ?: [];
    }
}

// Get all articles
$articles = $db->query("SELECT * FROM articles ORDER BY created_at DESC")->fetchAll();

// Success messages
if (isset($_GET['success'])) {
    $messages = ['added' => 'Artikel berhasil ditambahkan!', 'updated' => 'Artikel berhasil diupdate!', 'deleted' => 'Artikel berhasil dihapus!'];
    $success = $messages[$_GET['success']] ?? '';
}

require_once 'includes/header.php';
?>

<style>
.features-container, .cards-container { margin-bottom: 1.5rem; }
.feature-item, .card-item { 
    background: var(--bg-light); padding: 1rem; border-radius: 0.5rem; margin-bottom: 0.75rem;
    display: grid; grid-template-columns: 1fr 2fr 3fr auto; gap: 0.75rem; align-items: center;
}
.card-item { grid-template-columns: 2fr 2fr 2fr 1fr auto; }
.feature-item input, .card-item input { padding: 0.5rem; border: 1px solid #ddd; border-radius: 0.25rem; }
.btn-remove { background: #EF4444; color: white; border: none; padding: 0.5rem; border-radius: 0.25rem; cursor: pointer; }
.btn-add { background: var(--accent); color: var(--primary); border: none; padding: 0.5rem 1rem; border-radius: 0.25rem; cursor: pointer; margin-top: 0.5rem; }
</style>

<?php if ($success): ?>
    <div class="alert alert-success"><i class="fa-solid fa-check-circle"></i> <?= htmlspecialchars($success) ?></div>
<?php endif; ?>

<?php if ($error): ?>
    <div class="alert alert-error"><i class="fa-solid fa-exclamation-circle"></i> <?= htmlspecialchars($error) ?></div>
<?php endif; ?>

<?php if ($action === 'add' || ($action === 'edit' && $article)): ?>
<!-- Form Add/Edit -->
<div class="card">
    <div class="card-header">
        <h3 class="card-title"><?= $action === 'edit' ? 'Edit Artikel' : 'Tambah Artikel Baru' ?></h3>
        <a href="articles.php" class="btn btn-secondary btn-sm"><i class="fa-solid fa-arrow-left"></i> Kembali</a>
    </div>
    <div class="card-body">
        <form method="POST">
            <!-- Basic Info -->
            <h4 style="margin-bottom: 1rem; color: var(--primary);">Informasi Dasar</h4>
            <div class="form-row">
                <div class="form-group">
                    <label>Slug URL *</label>
                    <input type="text" name="slug" class="form-control" required
                           value="<?= htmlspecialchars($article['slug'] ?? '') ?>" 
                           placeholder="contoh: akomodasi-premium">
                    <small style="color: #888;">URL: article.php?slug=<strong>slug-anda</strong></small>
                </div>
                <div class="form-group">
                    <label>Status</label>
                    <label style="display: flex; align-items: center; gap: 0.5rem; margin-top: 0.5rem;">
                        <input type="checkbox" name="is_active" value="1" <?= ($article['is_active'] ?? 1) ? 'checked' : '' ?>>
                        Aktif
                    </label>
                </div>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label>Judul *</label>
                    <input type="text" name="title" class="form-control" required
                           value="<?= htmlspecialchars($article['title'] ?? '') ?>" placeholder="Akomodasi">
                </div>
                <div class="form-group">
                    <label>Judul Highlight (Gold)</label>
                    <input type="text" name="title_highlight" class="form-control" 
                           value="<?= htmlspecialchars($article['title_highlight'] ?? '') ?>" placeholder="Premium">
                </div>
            </div>
            
            <div class="form-group">
                <label>Subtitle</label>
                <input type="text" name="subtitle" class="form-control" 
                       value="<?= htmlspecialchars($article['subtitle'] ?? '') ?>" placeholder="Deskripsi singkat di hero section">
            </div>
            
            <div class="form-group">
                <label>Meta Description (SEO)</label>
                <input type="text" name="meta_description" class="form-control" 
                       value="<?= htmlspecialchars($article['meta_description'] ?? '') ?>" placeholder="Deskripsi untuk mesin pencari">
            </div>
            
            <hr style="margin: 2rem 0;">
            
            <!-- Images -->
            <h4 style="margin-bottom: 1rem; color: var(--primary);">Gambar</h4>
            <div class="form-row">
                <div class="form-group">
                    <label>Hero Background Image URL</label>
                    <input type="url" name="hero_image" class="form-control" 
                           value="<?= htmlspecialchars($article['hero_image'] ?? '') ?>" placeholder="https://...">
                </div>
                <div class="form-group">
                    <label>Content Image URL</label>
                    <input type="url" name="content_image" class="form-control" 
                           value="<?= htmlspecialchars($article['content_image'] ?? '') ?>" placeholder="https://...">
                </div>
            </div>
            
            <hr style="margin: 2rem 0;">
            
            <!-- Content -->
            <h4 style="margin-bottom: 1rem; color: var(--primary);">Konten Utama</h4>
            <div class="form-group">
                <label>Judul Konten</label>
                <input type="text" name="content_title" class="form-control" 
                       value="<?= htmlspecialchars($article['content_title'] ?? '') ?>" placeholder="Kenyamanan Terbaik untuk Ibadah Anda">
            </div>
            <div class="form-group">
                <label>Teks Konten</label>
                <textarea name="content_text" class="form-control" rows="5"><?= htmlspecialchars($article['content_text'] ?? '') ?></textarea>
            </div>
            
            <!-- Features List -->
            <h4 style="margin: 1.5rem 0 1rem; color: var(--primary);">List Fitur</h4>
            <div class="features-container" id="featuresContainer">
                <?php 
                if (empty($features)) $features = [['icon' => '', 'title' => '', 'desc' => '']];
                foreach ($features as $i => $f): 
                ?>
                <div class="feature-item">
                    <input type="text" name="feature_icon[]" placeholder="Icon (fa-solid fa-...)" value="<?= htmlspecialchars($f['icon']) ?>">
                    <input type="text" name="feature_title[]" placeholder="Judul" value="<?= htmlspecialchars($f['title']) ?>">
                    <input type="text" name="feature_desc[]" placeholder="Deskripsi" value="<?= htmlspecialchars($f['desc']) ?>">
                    <button type="button" class="btn-remove" onclick="this.parentElement.remove()"><i class="fa-solid fa-times"></i></button>
                </div>
                <?php endforeach; ?>
            </div>
            <button type="button" class="btn-add" onclick="addFeature()"><i class="fa-solid fa-plus"></i> Tambah Fitur</button>
            
            <hr style="margin: 2rem 0;">
            
            <!-- Cards Section -->
            <h4 style="margin-bottom: 1rem; color: var(--primary);">Section Kartu</h4>
            <div class="form-row">
                <div class="form-group">
                    <label>Judul Section</label>
                    <input type="text" name="cards_section_title" class="form-control" 
                           value="<?= htmlspecialchars($article['cards_section_title'] ?? '') ?>" placeholder="Hotel">
                </div>
                <div class="form-group">
                    <label>Subtitle Section</label>
                    <input type="text" name="cards_section_subtitle" class="form-control" 
                           value="<?= htmlspecialchars($article['cards_section_subtitle'] ?? '') ?>" placeholder="Partner Hotel">
                </div>
            </div>
            
            <h5 style="margin: 1rem 0;">Daftar Kartu</h5>
            <div class="cards-container" id="cardsContainer">
                <?php 
                if (empty($cards)) $cards = [['image' => '', 'title' => '', 'location' => '', 'rating' => 5]];
                foreach ($cards as $c): 
                ?>
                <div class="card-item">
                    <input type="url" name="card_image[]" placeholder="URL Gambar" value="<?= htmlspecialchars($c['image']) ?>">
                    <input type="text" name="card_title[]" placeholder="Judul" value="<?= htmlspecialchars($c['title']) ?>">
                    <input type="text" name="card_location[]" placeholder="Lokasi/Deskripsi" value="<?= htmlspecialchars($c['location']) ?>">
                    <input type="number" name="card_rating[]" placeholder="Rating" min="1" max="5" value="<?= $c['rating'] ?? 5 ?>">
                    <button type="button" class="btn-remove" onclick="this.parentElement.remove()"><i class="fa-solid fa-times"></i></button>
                </div>
                <?php endforeach; ?>
            </div>
            <button type="button" class="btn-add" onclick="addCard()"><i class="fa-solid fa-plus"></i> Tambah Kartu</button>
            
            <hr style="margin: 2rem 0;">
            
            <!-- CTA -->
            <h4 style="margin-bottom: 1rem; color: var(--primary);">Call to Action</h4>
            <div class="form-row">
                <div class="form-group">
                    <label>Judul CTA</label>
                    <input type="text" name="cta_title" class="form-control" 
                           value="<?= htmlspecialchars($article['cta_title'] ?? '') ?>" placeholder="Siap Menikmati Akomodasi Terbaik?">
                </div>
                <div class="form-group">
                    <label>Teks CTA</label>
                    <input type="text" name="cta_text" class="form-control" 
                           value="<?= htmlspecialchars($article['cta_text'] ?? '') ?>" placeholder="Hubungi kami untuk informasi lebih lanjut">
                </div>
            </div>
            
            <hr style="margin: 2rem 0;">
            
            <button type="submit" class="btn btn-primary">
                <i class="fa-solid fa-save"></i> Simpan Artikel
            </button>
        </form>
    </div>
</div>

<script>
function addFeature() {
    document.getElementById('featuresContainer').insertAdjacentHTML('beforeend', `
        <div class="feature-item">
            <input type="text" name="feature_icon[]" placeholder="Icon (fa-solid fa-...)">
            <input type="text" name="feature_title[]" placeholder="Judul">
            <input type="text" name="feature_desc[]" placeholder="Deskripsi">
            <button type="button" class="btn-remove" onclick="this.parentElement.remove()"><i class="fa-solid fa-times"></i></button>
        </div>
    `);
}

function addCard() {
    document.getElementById('cardsContainer').insertAdjacentHTML('beforeend', `
        <div class="card-item">
            <input type="url" name="card_image[]" placeholder="URL Gambar">
            <input type="text" name="card_title[]" placeholder="Judul">
            <input type="text" name="card_location[]" placeholder="Lokasi/Deskripsi">
            <input type="number" name="card_rating[]" placeholder="Rating" min="1" max="5" value="5">
            <button type="button" class="btn-remove" onclick="this.parentElement.remove()"><i class="fa-solid fa-times"></i></button>
        </div>
    `);
}
</script>

<?php else: ?>
<!-- List View -->
<div class="card">
    <div class="card-header">
        <h3 class="card-title">Daftar Artikel (<?= count($articles) ?>)</h3>
        <a href="articles.php?action=add" class="btn btn-primary btn-sm">
            <i class="fa-solid fa-plus"></i> Tambah Artikel
        </a>
    </div>
    <div class="card-body" style="padding: 0;">
        <?php if (empty($articles)): ?>
            <div class="empty-state">
                <i class="fa-solid fa-newspaper"></i>
                <p>Belum ada artikel. <a href="articles.php?action=add">Tambah sekarang</a></p>
            </div>
        <?php else: ?>
            <table class="table">
                <thead>
                    <tr>
                        <th>Judul</th>
                        <th>Slug</th>
                        <th>Status</th>
                        <th>Tanggal</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($articles as $a): ?>
                        <tr>
                            <td>
                                <strong><?= htmlspecialchars($a['title']) ?></strong>
                                <span style="color: var(--accent);"><?= htmlspecialchars($a['title_highlight']) ?></span>
                            </td>
                            <td>
                                <a href="../article.php?slug=<?= htmlspecialchars($a['slug']) ?>" target="_blank" style="color: var(--accent);">
                                    <?= htmlspecialchars($a['slug']) ?> <i class="fa-solid fa-external-link fa-xs"></i>
                                </a>
                            </td>
                            <td>
                                <span class="badge <?= $a['is_active'] ? 'badge-success' : 'badge-error' ?>">
                                    <?= $a['is_active'] ? 'Aktif' : 'Nonaktif' ?>
                                </span>
                            </td>
                            <td><small><?= date('d M Y', strtotime($a['created_at'])) ?></small></td>
                            <td>
                                <div class="action-btns">
                                    <a href="articles.php?action=edit&id=<?= $a['id'] ?>" class="btn btn-secondary btn-icon" title="Edit">
                                        <i class="fa-solid fa-edit"></i>
                                    </a>
                                    <a href="articles.php?action=delete&id=<?= $a['id'] ?>" class="btn btn-danger btn-icon" 
                                       data-confirm="Yakin ingin menghapus artikel ini?" title="Hapus">
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
