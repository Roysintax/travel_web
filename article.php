<?php
/**
 * Halaman Artikel Dinamis
 * File: article.php
 * URL: article.php?slug=nama-artikel
 */

require_once 'config/database.php';

// Get slug from URL
$slug = $_GET['slug'] ?? '';

if (empty($slug)) {
    header('Location: index.php');
    exit;
}

// Get article from database
$db = getDB();
$stmt = $db->prepare("SELECT * FROM articles WHERE slug = ? AND is_active = 1");
$stmt->execute([$slug]);
$article = $stmt->fetch();

if (!$article) {
    header('HTTP/1.0 404 Not Found');
    echo '<h1>Halaman tidak ditemukan</h1><p><a href="index.php">Kembali ke Beranda</a></p>';
    exit;
}

// Parse JSON data
$features = json_decode($article['features_json'] ?? '[]', true) ?: [];
$cards = json_decode($article['cards_json'] ?? '[]', true) ?: [];

// Get site settings
$siteName = getSetting('site_name', 'TanahSuci');
$primaryColor = getSetting('primary_color', '#0F172A');
$accentColor = getSetting('accent_color', '#D4AF37');

// Get navigation & footer
$navMenu = getNavMenu();
$contactInfo = getContactInfo();
$footerInfo = [
    'copyright_text' => getFooterInfo('copyright_text', '© 2024 Tanah Suci Travel'),
    'created_by' => getFooterInfo('created_by', 'Amrina Farza')
];
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($article['title'] . ' ' . $article['title_highlight']) ?> - <?= htmlspecialchars($siteName) ?></title>
    <meta name="description" content="<?= htmlspecialchars($article['meta_description'] ?? $article['subtitle']) ?>">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cinzel:wght@400;600;800&family=Outfit:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <style>
        :root {
            --primary-color: <?= htmlspecialchars($primaryColor) ?>;
            --accent-color: <?= htmlspecialchars($accentColor) ?>;
            --text-light: #F8FAFC;
            --text-dark: #334155;
            --bg-light: #F1F5F9;
            --bg-dark: #020617;
            --font-heading: 'Cinzel', serif;
            --font-body: 'Outfit', sans-serif;
            --transition: all 0.3s ease;
            --shadow-md: 0 10px 15px -3px rgb(0 0 0 / 0.1);
            --shadow-lg: 0 20px 25px -5px rgb(0 0 0 / 0.1);
        }

        * { margin: 0; padding: 0; box-sizing: border-box; scroll-behavior: smooth; }
        body { font-family: var(--font-body); color: var(--text-dark); line-height: 1.6; background-color: white; }
        h1, h2, h3 { font-family: var(--font-heading); font-weight: 700; }
        a { text-decoration: none; color: inherit; transition: var(--transition); }
        ul { list-style: none; }
        img { max-width: 100%; display: block; }
        .container { width: 90%; max-width: 1200px; margin: 0 auto; padding: 0 1rem; }
        .gold { color: var(--accent-color); }

        .navbar { position: fixed; top: 0; left: 0; width: 100%; z-index: 1000; padding: 1.5rem 0; background: rgba(15, 23, 42, 0.95); backdrop-filter: blur(10px); }
        .nav-container { display: flex; justify-content: space-between; align-items: center; }
        .logo { font-family: var(--font-heading); font-size: 1.75rem; font-weight: 700; color: var(--text-light); }
        .nav-links { display: flex; align-items: center; gap: 2rem; }
        .nav-links a { color: var(--text-light); font-size: 0.95rem; }
        .nav-links a:hover { color: var(--accent-color); }
        .btn-nav { border: 1px solid var(--accent-color); padding: 0.5rem 1.5rem; border-radius: 50px; color: var(--accent-color) !important; }
        .btn-nav:hover { background-color: var(--accent-color); color: var(--primary-color) !important; }
        .hamburger { display: none; cursor: pointer; }
        .bar { display: block; width: 25px; height: 3px; margin: 5px auto; transition: var(--transition); background-color: var(--text-light); }

        .page-hero { height: 60vh; min-height: 400px; position: relative; display: flex; align-items: center; justify-content: center; text-align: center; background: url('<?= htmlspecialchars($article['hero_image']) ?>') no-repeat center center/cover; }
        .page-hero::before { content: ''; position: absolute; inset: 0; background: linear-gradient(rgba(15, 23, 42, 0.6), rgba(15, 23, 42, 0.85)); }
        .page-hero-content { position: relative; z-index: 2; color: var(--text-light); }
        .page-hero h1 { font-size: 3rem; margin-bottom: 1rem; }
        .page-hero p { font-size: 1.2rem; opacity: 0.9; max-width: 600px; margin: 0 auto; }
        .breadcrumb { margin-top: 1.5rem; font-size: 0.9rem; }
        .breadcrumb a { color: var(--accent-color); }

        .content-section { padding: 5rem 0; }
        .content-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 4rem; align-items: center; }
        .content-image img { border-radius: 1rem; box-shadow: var(--shadow-lg); }
        .content-text h2 { font-size: 2rem; color: var(--primary-color); margin-bottom: 1.5rem; }
        .content-text p { margin-bottom: 1.5rem; white-space: pre-line; }
        .feature-list { margin: 2rem 0; }
        .feature-list li { display: flex; align-items: flex-start; gap: 1rem; margin-bottom: 1rem; padding: 1rem; background: var(--bg-light); border-radius: 0.5rem; }
        .feature-list li i { color: var(--accent-color); font-size: 1.25rem; margin-top: 3px; }
        .feature-list li strong { display: block; color: var(--primary-color); margin-bottom: 0.25rem; }

        .cards-section { padding: 5rem 0; background: var(--bg-light); }
        .section-header { text-align: center; margin-bottom: 3rem; }
        .subtitle { display: block; text-transform: uppercase; letter-spacing: 2px; font-size: 0.875rem; font-weight: 600; color: var(--accent-color); margin-bottom: 0.5rem; }
        .section-header h2 { font-size: 2.5rem; color: var(--primary-color); }
        .cards-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 2rem; }
        .card { background: white; border-radius: 1rem; overflow: hidden; box-shadow: var(--shadow-md); transition: var(--transition); text-align: center; }
        .card:hover { transform: translateY(-10px); box-shadow: var(--shadow-lg); }
        .card img { width: 100%; height: 200px; object-fit: cover; }
        .card.logo-card img { height: 80px; width: auto; margin: 2rem auto; object-fit: contain; }
        .card-info { padding: 1.5rem; }
        .card-info h3 { color: var(--primary-color); margin-bottom: 0.5rem; font-size: 1.1rem; }
        .card-location { color: var(--accent-color); font-size: 0.9rem; margin-bottom: 0.75rem; }
        .card-rating { color: var(--accent-color); }

        .btn { display: inline-block; padding: 0.8rem 2rem; border-radius: 50px; font-weight: 500; cursor: pointer; transition: var(--transition); }
        .btn-primary { background-color: var(--accent-color); color: var(--primary-color); }
        .btn-primary:hover { background-color: #B5952F; transform: translateY(-2px); }

        .cta-section { padding: 5rem 0; background: var(--primary-color); text-align: center; color: var(--text-light); }
        .cta-section h2 { font-size: 2rem; margin-bottom: 1rem; }
        .cta-section p { margin-bottom: 2rem; opacity: 0.9; }

        footer { background-color: var(--bg-dark); color: #94a3b8; padding-top: 3rem; }
        .footer-simple { text-align: center; padding: 2rem 0; border-top: 1px solid #1e293b; }
        .footer-simple .logo { margin-bottom: 1rem; display: inline-block; }
        .footer-simple p { margin-bottom: 0.5rem; font-size: 0.9rem; }
        .footer-simple .credit { color: var(--accent-color); }

        @media (max-width: 992px) { .content-grid { grid-template-columns: 1fr; } }
        @media (max-width: 768px) { 
            .page-hero h1 { font-size: 2rem; } 
            .hamburger { display: block; z-index: 1001; }
            .hamburger.active .bar:nth-child(1) { transform: translateY(8px) rotate(45deg); }
            .hamburger.active .bar:nth-child(2) { opacity: 0; }
            .hamburger.active .bar:nth-child(3) { transform: translateY(-8px) rotate(-45deg); }
            .nav-links { position: fixed; right: -100%; top: 0; height: 100vh; width: 80%; background-color: var(--bg-dark); flex-direction: column; justify-content: center; transition: 0.3s ease-in-out; }
            .nav-links.active { right: 0; }
        }
    </style>
</head>

<body>
    <nav class="navbar">
        <div class="container nav-container">
            <a href="index.php" class="logo">Tanah<span class="gold">Suci</span>.</a>
            <ul class="nav-links">
                <?php foreach ($navMenu as $menu): ?>
                    <?php if ($menu['is_button']): ?>
                        <li><a href="index.php<?= htmlspecialchars($menu['menu_link']) ?>" class="btn-nav"><?= htmlspecialchars($menu['menu_label']) ?></a></li>
                    <?php else: ?>
                        <li><a href="index.php<?= htmlspecialchars($menu['menu_link']) ?>"><?= htmlspecialchars($menu['menu_label']) ?></a></li>
                    <?php endif; ?>
                <?php endforeach; ?>
            </ul>
            <div class="hamburger">
                <span class="bar"></span>
                <span class="bar"></span>
                <span class="bar"></span>
            </div>
        </div>
    </nav>

    <section class="page-hero">
        <div class="page-hero-content">
            <h1><?= htmlspecialchars($article['title']) ?> <span class="gold"><?= htmlspecialchars($article['title_highlight']) ?></span></h1>
            <p><?= htmlspecialchars($article['subtitle']) ?></p>
            <div class="breadcrumb">
                <a href="index.php">Beranda</a> <span>/ <?= htmlspecialchars($article['title'] . ' ' . $article['title_highlight']) ?></span>
            </div>
        </div>
    </section>

    <section class="content-section">
        <div class="container">
            <div class="content-grid">
                <div class="content-image">
                    <img src="<?= htmlspecialchars($article['content_image']) ?>" alt="<?= htmlspecialchars($article['title']) ?>">
                </div>
                <div class="content-text">
                    <h2><?= htmlspecialchars($article['content_title']) ?></h2>
                    <p><?= htmlspecialchars($article['content_text']) ?></p>

                    <?php if (!empty($features)): ?>
                    <ul class="feature-list">
                        <?php foreach ($features as $f): ?>
                            <li>
                                <i class="<?= htmlspecialchars($f['icon']) ?>"></i>
                                <div>
                                    <strong><?= htmlspecialchars($f['title']) ?></strong>
                                    <?= htmlspecialchars($f['desc']) ?>
                                </div>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                    <?php endif; ?>

                    <a href="index.php#packages" class="btn btn-primary">Lihat Paket Kami</a>
                </div>
            </div>
        </div>
    </section>

    <?php if (!empty($cards)): ?>
    <section class="cards-section">
        <div class="container">
            <div class="section-header">
                <span class="subtitle"><?= htmlspecialchars($article['cards_section_subtitle']) ?></span>
                <h2><?= htmlspecialchars($article['cards_section_title']) ?> <span class="gold">Pilihan Kami</span></h2>
            </div>
            <div class="cards-grid">
                <?php 
                // Check if cards have small images (logo style)
                $isLogoStyle = strpos($cards[0]['image'] ?? '', 'wikimedia') !== false;
                foreach ($cards as $card): 
                ?>
                    <div class="card <?= $isLogoStyle ? 'logo-card' : '' ?>">
                        <img src="<?= htmlspecialchars($card['image']) ?>" alt="<?= htmlspecialchars($card['title']) ?>">
                        <div class="card-info">
                            <h3><?= htmlspecialchars($card['title']) ?></h3>
                            <p class="card-location"><?= htmlspecialchars($card['location']) ?></p>
                            <?php if (!$isLogoStyle && isset($card['rating'])): ?>
                            <div class="card-rating">
                                <?php for ($i = 0; $i < ($card['rating'] ?? 5); $i++): ?>
                                    <i class="fa-solid fa-star"></i>
                                <?php endfor; ?>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
    <?php endif; ?>

    <section class="cta-section">
        <div class="container">
            <h2><?= htmlspecialchars($article['cta_title']) ?></h2>
            <p><?= htmlspecialchars($article['cta_text']) ?></p>
            <a href="https://wa.me/<?= preg_replace('/[^0-9]/', '', $contactInfo['phone'] ?? '') ?>" class="btn btn-primary">
                <i class="fa-brands fa-whatsapp"></i> Chat WhatsApp
            </a>
        </div>
    </section>

    <footer>
        <div class="container footer-simple">
            <a href="index.php" class="logo">Tanah<span class="gold">Suci</span>.</a>
            <p><?= htmlspecialchars($footerInfo['copyright_text']) ?></p>
            <p class="credit">Dibuat oleh <strong><?= htmlspecialchars($footerInfo['created_by']) ?></strong></p>
        </div>
    </footer>

    <script>
        const hamburger = document.querySelector('.hamburger');
        const navLinks = document.querySelector('.nav-links');
        hamburger.addEventListener('click', () => {
            hamburger.classList.toggle('active');
            navLinks.classList.toggle('active');
        });
        document.querySelectorAll('.nav-links li a').forEach(link => {
            link.addEventListener('click', () => {
                hamburger.classList.remove('active');
                navLinks.classList.remove('active');
            });
        });
    </script>
</body>

</html>
