<?php
// =====================================================
// HOMEPAGE - TANAH SUCI
// File: index.php
// Fungsi: Halaman utama yang dinamis dari database
// =====================================================

require_once 'config/database.php';

// Ambil data dari database
$heroSettings = getSettingsByGroup('hero');
$aboutSettings = getSettingsByGroup('about');
$ctaSettings = getSettingsByGroup('cta');
$packagesSettings = getSettingsByGroup('packages');
$generalSettings = getSettingsByGroup('general');

$navMenu = getNavMenu();
$features = getFeatures();
$packages = getPackages();
$gallery = getGallery();
$contactInfo = getContactInfo();
$socialMedia = getSocialMedia();
$testimonials = getTestimonials();
$footerNavigation = getFooterLinks('navigasi');
$footerLayanan = getFooterLinks('layanan');

// Default values jika database kosong
$siteName = $generalSettings['site_name'] ?? 'Tanah';
$siteNameHighlight = $generalSettings['site_name_highlight'] ?? 'Suci';
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- Site Title - Bisa di-custom dari database -->
    <title><?= htmlspecialchars($siteName . $siteNameHighlight) ?> - <?= htmlspecialchars($generalSettings['site_tagline'] ?? 'Perjalanan Ibadah Premium') ?></title>
    <!-- Meta Description - Bisa di-custom dari database -->
    <meta name="description" content="<?= htmlspecialchars($generalSettings['site_description'] ?? '') ?>">

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cinzel:wght@400;600;800&family=Outfit:wght@300;400;500;600&display=swap" rel="stylesheet">

    <!-- Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <style>
        :root {
            /* Warna - Bisa di-custom dari database */
            --primary-color: <?= htmlspecialchars($generalSettings['primary_color'] ?? '#0F172A') ?>;
            --accent-color: <?= htmlspecialchars($generalSettings['accent_color'] ?? '#D4AF37') ?>;
            --accent-light: #F3E5AB;
            --text-light: #F8FAFC;
            --text-dark: #334155;
            --bg-light: #F1F5F9;
            --bg-dark: #020617;

            --font-heading: 'Cinzel', serif;
            --font-body: 'Outfit', sans-serif;

            --transition: all 0.3s ease;
            --shadow-sm: 0 4px 6px -1px rgb(0 0 0 / 0.1);
            --shadow-md: 0 10px 15px -3px rgb(0 0 0 / 0.1);
            --shadow-lg: 0 20px 25px -5px rgb(0 0 0 / 0.1);
        }

        /* Reset & Base Styles */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            scroll-behavior: smooth;
        }

        body {
            font-family: var(--font-body);
            color: var(--text-dark);
            line-height: 1.6;
            background-color: white;
            overflow-x: hidden;
        }

        h1, h2, h3, h4, h5, h6 {
            font-family: var(--font-heading);
            font-weight: 700;
            line-height: 1.2;
        }

        a {
            text-decoration: none;
            color: inherit;
            transition: var(--transition);
        }

        ul { list-style: none; }
        img { max-width: 100%; display: block; }

        .container {
            width: 90%;
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 1rem;
        }

        .gold { color: var(--accent-color); }
        .center { text-align: center; }

        /* Sections */
        .section { padding: 5rem 0; }
        .bg-light { background-color: var(--bg-light); }

        .section-header { margin-bottom: 3.5rem; }

        .subtitle {
            display: block;
            text-transform: uppercase;
            letter-spacing: 2px;
            font-size: 0.875rem;
            font-weight: 600;
            color: var(--accent-color);
            margin-bottom: 0.5rem;
        }

        .section-header h2 {
            font-size: 2.5rem;
            color: var(--primary-color);
        }

        /* Buttons */
        .btn {
            display: inline-block;
            padding: 0.8rem 2rem;
            border-radius: 50px;
            font-weight: 500;
            cursor: pointer;
            transition: var(--transition);
            border: 2px solid transparent;
        }

        .btn-primary {
            background-color: var(--accent-color);
            color: var(--primary-color);
        }

        .btn-primary:hover {
            background-color: #B5952F;
            transform: translateY(-2px);
        }

        .btn-outline {
            border-color: var(--text-light);
            color: var(--text-light);
        }

        .btn-outline:hover {
            background-color: var(--text-light);
            color: var(--primary-color);
        }

        .btn-gold {
            background-color: var(--accent-color);
            color: var(--primary-color);
            font-weight: 700;
        }

        .full-width { width: 100%; text-align: center; }

        /* Navigation */
        .navbar {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            z-index: 1000;
            padding: 1.5rem 0;
            transition: var(--transition);
            background: transparent;
        }

        .navbar.scrolled {
            background: rgba(15, 23, 42, 0.95);
            backdrop-filter: blur(10px);
            padding: 1rem 0;
            box-shadow: var(--shadow-md);
        }

        .nav-container {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .logo {
            font-family: var(--font-heading);
            font-size: 1.75rem;
            font-weight: 700;
            color: var(--text-light);
        }

        .nav-links {
            display: flex;
            align-items: center;
            gap: 2rem;
        }

        .nav-links a {
            color: var(--text-light);
            font-size: 0.95rem;
            font-weight: 400;
        }

        .nav-links a:hover { color: var(--accent-color); }

        .btn-nav {
            border: 1px solid var(--accent-color);
            padding: 0.5rem 1.5rem;
            border-radius: 50px;
            color: var(--accent-color) !important;
        }

        .btn-nav:hover {
            background-color: var(--accent-color);
            color: var(--primary-color) !important;
        }

        .hamburger { display: none; cursor: pointer; }

        .bar {
            display: block;
            width: 25px;
            height: 3px;
            margin: 5px auto;
            transition: var(--transition);
            background-color: var(--text-light);
        }

        /* Hero Section */
        .hero {
            height: 100vh;
            width: 100%;
            position: relative;
            display: flex;
            align-items: center;
            justify-content: center;
            text-align: center;
            /* Hero Background - Bisa di-custom dari database */
            background: url('<?= htmlspecialchars($heroSettings['hero_background_image'] ?? 'https://images.unsplash.com/photo-1565552629477-ff1459bb5a7f?q=80&w=2670&auto=format&fit=crop') ?>') no-repeat center center/cover;
        }

        .hero-overlay {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(rgba(15, 23, 42, 0.4), rgba(15, 23, 42, 0.8));
            z-index: 1;
        }

        .hero-content {
            position: relative;
            z-index: 2;
            color: var(--text-light);
        }

        .hero h1 {
            font-size: 3.5rem;
            margin-bottom: 1.5rem;
            letter-spacing: -1px;
        }

        .hero p {
            font-size: 1.25rem;
            max-width: 700px;
            margin: 0 auto 2.5rem;
            opacity: 0.9;
        }

        .hero-buttons {
            display: flex;
            gap: 1rem;
            justify-content: center;
        }

        /* Features/About Section */
        .features-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 2rem;
        }

        .feature-card {
            padding: 2.5rem;
            background: white;
            border-radius: 1rem;
            box-shadow: var(--shadow-sm);
            text-align: center;
            border: 1px solid #e2e8f0;
            transition: var(--transition);
        }

        .feature-card:hover {
            transform: translateY(-10px);
            box-shadow: var(--shadow-lg);
            border-color: var(--accent-color);
        }

        .icon-box {
            width: 70px;
            height: 70px;
            background-color: var(--bg-light);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1.5rem;
            color: var(--accent-color);
            font-size: 1.75rem;
            transition: var(--transition);
        }

        .feature-card:hover .icon-box {
            background-color: var(--accent-color);
            color: white;
        }

        .feature-card h3 {
            margin-bottom: 1rem;
            color: var(--primary-color);
        }

        .feature-card p {
            color: var(--text-dark);
            font-size: 0.95rem;
        }

        /* Packages Section */
        .packages-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
            gap: 2.5rem;
        }

        .package-card {
            background: white;
            border-radius: 1.5rem;
            overflow: hidden;
            box-shadow: var(--shadow-md);
            transition: var(--transition);
            position: relative;
            display: flex;
            flex-direction: column;
        }

        .package-card:hover {
            transform: translateY(-10px);
            box-shadow: var(--shadow-lg);
        }

        .package-image {
            height: 220px;
            position: relative;
            background-color: #ddd;
            background-size: cover;
            background-position: center;
        }

        .package-image .overlay {
            position: absolute;
            inset: 0;
            background: linear-gradient(to top, rgba(0, 0, 0, 0.6), transparent);
        }

        .pkg-tag {
            position: absolute;
            top: 1rem;
            right: 1rem;
            background: var(--accent-color);
            color: var(--primary-color);
            padding: 0.25rem 1rem;
            border-radius: 50px;
            font-size: 0.75rem;
            font-weight: 700;
            text-transform: uppercase;
        }

        .package-details {
            padding: 2rem;
            flex: 1;
            display: flex;
            flex-direction: column;
        }

        .package-details h3 {
            font-size: 1.25rem;
            margin-bottom: 0.5rem;
            color: var(--primary-color);
        }

        .price {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--accent-color);
            font-family: var(--font-heading);
            margin-bottom: 1.5rem;
        }

        .pkg-features {
            margin-bottom: 2rem;
            flex: 1;
        }

        .pkg-features li {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            margin-bottom: 0.75rem;
            font-size: 0.9rem;
            color: #64748b;
        }

        .pkg-features li i { color: var(--accent-color); }

        /* CTA Section */
        .cta-section {
            padding: 6rem 0;
            position: relative;
            background: var(--primary-color);
            color: var(--text-light);
            overflow: hidden;
        }

        .cta-overlay {
            position: absolute;
            inset: 0;
            background: url('https://www.transparenttextures.com/patterns/arabesque.png');
            opacity: 0.05;
        }

        .cta-content {
            position: relative;
            z-index: 2;
        }

        .cta-content h2 {
            font-size: 2.5rem;
            margin-bottom: 1.5rem;
        }

        .cta-content p {
            font-size: 1.1rem;
            margin-bottom: 2.5rem;
            opacity: 0.9;
            max-width: 600px;
            margin-left: auto;
            margin-right: auto;
        }

        /* Gallery Section */
        .gallery-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 1.5rem;
        }

        .gallery-item {
            position: relative;
            border-radius: 1rem;
            overflow: hidden;
            height: 300px;
            cursor: pointer;
            box-shadow: var(--shadow-sm);
        }

        .gallery-item img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.5s ease;
        }

        .gallery-item:hover img { transform: scale(1.1); }

        .gallery-overlay {
            position: absolute;
            inset: 0;
            background: rgba(15, 23, 42, 0.4);
            display: flex;
            align-items: center;
            justify-content: center;
            opacity: 0;
            transition: var(--transition);
        }

        .gallery-item:hover .gallery-overlay { opacity: 1; }

        .gallery-overlay i {
            color: white;
            font-size: 2rem;
            transform: scale(0.8);
            transition: var(--transition);
        }

        .gallery-item:hover .gallery-overlay i { transform: scale(1); }

        /* Footer */
        footer {
            background-color: var(--bg-dark);
            color: #94a3b8;
            padding-top: 4rem;
        }

        .footer-content {
            display: grid;
            grid-template-columns: 2fr 1fr 1fr 1.5fr;
            gap: 3rem;
            padding-bottom: 3rem;
            border-bottom: 1px solid #1e293b;
        }

        .footer-brand .logo {
            display: block;
            margin-bottom: 1.5rem;
        }

        .footer-brand p {
            font-size: 0.9rem;
            margin-bottom: 1.5rem;
            line-height: 1.6;
        }

        .social-links {
            display: flex;
            gap: 1rem;
        }

        .social-links a {
            width: 40px;
            height: 40px;
            border: 1px solid #334155;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--text-light);
            transition: var(--transition);
        }

        .social-links a:hover {
            background-color: var(--accent-color);
            border-color: var(--accent-color);
            color: var(--primary-color);
        }

        .footer-links h4,
        .footer-contact h4 {
            color: var(--text-light);
            margin-bottom: 1.5rem;
            font-family: var(--font-heading);
        }

        .footer-links ul li { margin-bottom: 0.75rem; }

        .footer-links ul li a:hover {
            color: var(--accent-color);
            padding-left: 5px;
        }

        .footer-contact ul li {
            display: flex;
            gap: 1rem;
            margin-bottom: 1rem;
            font-size: 0.9rem;
        }

        .footer-contact i {
            color: var(--accent-color);
            margin-top: 5px;
        }

        .footer-bottom {
            padding: 1.5rem 0;
            text-align: center;
            font-size: 0.85rem;
        }

        /* Animations */
        .fade-in-up {
            opacity: 0;
            transform: translateY(30px);
            animation: fadeInUp 0.8s forwards ease-out;
        }

        .delay-1 { animation-delay: 0.2s; }
        .delay-2 { animation-delay: 0.4s; }

        @keyframes fadeInUp {
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .scroll-reveal {
            opacity: 0;
            transform: translateY(30px);
            transition: all 0.8s ease-out;
        }

        .scroll-reveal.visible {
            opacity: 1;
            transform: translateY(0);
        }

        /* Responsive */
        @media (max-width: 768px) {
            .hamburger { display: block; z-index: 1001; }

            .hamburger.active .bar:nth-child(1) { transform: translateY(8px) rotate(45deg); }
            .hamburger.active .bar:nth-child(2) { opacity: 0; }
            .hamburger.active .bar:nth-child(3) { transform: translateY(-8px) rotate(-45deg); }

            .nav-links {
                position: fixed;
                right: -100%;
                top: 0;
                height: 100vh;
                width: 80%;
                background-color: var(--bg-dark);
                flex-direction: column;
                justify-content: center;
                transition: 0.3s ease-in-out;
                box-shadow: -5px 0 15px rgba(0, 0, 0, 0.5);
            }

            .nav-links.active { right: 0; }

            .hero h1 { font-size: 2.5rem; }
            .hero p { font-size: 1.1rem; padding: 0 1rem; }
            .section { padding: 4rem 0; }
            .section-header h2 { font-size: 2rem; }
            .features-grid { grid-template-columns: 1fr; }
            .packages-grid { grid-template-columns: 1fr; }
            .gallery-grid { grid-template-columns: 1fr; }
            .footer-content { grid-template-columns: 1fr; gap: 2rem; }
            .cta-content h2 { font-size: 2rem; }
        }

        @media (max-width: 390px) {
            .container { width: 95%; padding: 0 0.75rem; }
            .logo { font-size: 1.5rem; }
            .hero h1 { font-size: 2rem; line-height: 1.3; }
            .hero p { font-size: 1rem; }
            .hero-buttons { flex-direction: column; gap: 0.75rem; }
            .btn { padding: 0.75rem 1.5rem; font-size: 0.9rem; }
            .section-header h2 { font-size: 1.75rem; }
        }

        /* Testimonials Section */
        .testimonials-section {
            padding: 6rem 0;
            background: linear-gradient(135deg, #0F172A 0%, #1E293B 100%);
            position: relative;
            overflow: hidden;
        }

        .testimonials-section::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('https://www.transparenttextures.com/patterns/arabesque.png');
            opacity: 0.03;
        }

        /* Bubble Animation */
        .bubbles {
            position: absolute;
            width: 100%;
            height: 100%;
            top: 0;
            left: 0;
            overflow: hidden;
            pointer-events: none;
        }

        .bubble {
            position: absolute;
            bottom: -100px;
            background: linear-gradient(135deg, rgba(212, 175, 55, 0.3), rgba(212, 175, 55, 0.1));
            border-radius: 50%;
            animation: rise 15s infinite ease-in;
            opacity: 0;
        }

        .bubble:nth-child(1) { width: 40px; height: 40px; left: 10%; animation-duration: 12s; animation-delay: 0s; }
        .bubble:nth-child(2) { width: 20px; height: 20px; left: 20%; animation-duration: 15s; animation-delay: 2s; }
        .bubble:nth-child(3) { width: 50px; height: 50px; left: 35%; animation-duration: 18s; animation-delay: 4s; }
        .bubble:nth-child(4) { width: 80px; height: 80px; left: 50%; animation-duration: 14s; animation-delay: 1s; }
        .bubble:nth-child(5) { width: 35px; height: 35px; left: 55%; animation-duration: 16s; animation-delay: 3s; }
        .bubble:nth-child(6) { width: 25px; height: 25px; left: 65%; animation-duration: 13s; animation-delay: 5s; }
        .bubble:nth-child(7) { width: 60px; height: 60px; left: 75%; animation-duration: 17s; animation-delay: 2s; }
        .bubble:nth-child(8) { width: 30px; height: 30px; left: 85%; animation-duration: 14s; animation-delay: 6s; }
        .bubble:nth-child(9) { width: 45px; height: 45px; left: 90%; animation-duration: 11s; animation-delay: 1s; }
        .bubble:nth-child(10) { width: 55px; height: 55px; left: 5%; animation-duration: 19s; animation-delay: 4s; }

        @keyframes rise {
            0% { bottom: -100px; transform: translateX(0) scale(0.8); opacity: 0; }
            10% { opacity: 0.6; }
            50% { opacity: 0.4; }
            100% { bottom: 110%; transform: translateX(50px) scale(1.1); opacity: 0; }
        }

        .testimonials-section .section-header { position: relative; z-index: 2; }
        .testimonials-section .subtitle { color: var(--accent-color); }
        .testimonials-section .section-header h2 { color: var(--text-light); }

        .testimonials-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
            gap: 2rem;
            position: relative;
            z-index: 2;
        }

        .testimonial-card {
            background: rgba(255, 255, 255, 0.05);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(212, 175, 55, 0.2);
            border-radius: 1.5rem;
            padding: 2rem;
            position: relative;
            transition: all 0.4s ease;
        }

        .testimonial-card::before {
            content: '"';
            position: absolute;
            top: 1rem;
            right: 1.5rem;
            font-size: 5rem;
            font-family: Georgia, serif;
            color: var(--accent-color);
            opacity: 0.15;
            line-height: 1;
        }

        .testimonial-card:hover {
            transform: translateY(-10px);
            background: rgba(255, 255, 255, 0.1);
            border-color: var(--accent-color);
            box-shadow: 0 25px 50px rgba(212, 175, 55, 0.15);
        }

        .testimonial-header {
            display: flex;
            align-items: center;
            gap: 1rem;
            margin-bottom: 1.25rem;
        }

        .testimonial-avatar {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            object-fit: cover;
            border: 3px solid var(--accent-color);
        }

        .testimonial-info h4 {
            color: var(--text-light);
            font-size: 1.1rem;
            margin-bottom: 0.25rem;
        }

        .testimonial-package {
            color: var(--accent-color);
            font-size: 0.85rem;
            font-weight: 500;
        }

        .testimonial-text {
            color: rgba(248, 250, 252, 0.85);
            font-size: 0.95rem;
            line-height: 1.7;
            margin-bottom: 1.25rem;
        }

        .testimonial-rating {
            color: var(--accent-color);
            font-size: 0.9rem;
        }

        .testimonial-rating i { margin-right: 2px; }

        @media (max-width: 768px) {
            .testimonials-grid { grid-template-columns: 1fr; }
            .testimonial-card { padding: 1.5rem; }
        }
    </style>
</head>

<body>

    <!-- Navigation - Data dari database -->
    <nav class="navbar">
        <div class="container nav-container">
            <a href="#" class="logo"><?= htmlspecialchars($siteName) ?><span class="gold"><?= htmlspecialchars($siteNameHighlight) ?></span>.</a>
            <ul class="nav-links">
                <?php foreach ($navMenu as $menu): ?>
                    <li>
                        <a href="<?= htmlspecialchars($menu['menu_link']) ?>" 
                           <?= $menu['is_button'] ? 'class="btn-nav"' : '' ?>>
                            <?= htmlspecialchars($menu['menu_label']) ?>
                        </a>
                    </li>
                <?php endforeach; ?>
            </ul>
            <div class="hamburger">
                <span class="bar"></span>
                <span class="bar"></span>
                <span class="bar"></span>
            </div>
        </div>
    </nav>

    <!-- Hero Section - Data dari database -->
    <header id="hero" class="hero">
        <div class="hero-overlay"></div>
        <div class="container hero-content">
            <h1 class="fade-in-up">
                <?= htmlspecialchars($heroSettings['hero_title_line1'] ?? 'Menuju Baitullah') ?> <br>
                <span class="gold"><?= htmlspecialchars($heroSettings['hero_title_line2'] ?? 'Dengan Ketenangan Hati') ?></span>
            </h1>
            <p class="fade-in-up delay-1"><?= htmlspecialchars($heroSettings['hero_subtitle'] ?? '') ?></p>
            <div class="hero-buttons fade-in-up delay-2">
                <a href="<?= htmlspecialchars($heroSettings['hero_btn_primary_link'] ?? '#packages') ?>" class="btn btn-primary">
                    <?= htmlspecialchars($heroSettings['hero_btn_primary_text'] ?? 'Lihat Paket') ?>
                </a>
                <a href="https://wa.me/6282283374116" class="btn btn-outline" target="_blank">
                    <i class="fa-brands fa-whatsapp"></i> <?= htmlspecialchars($heroSettings['hero_btn_secondary_text'] ?? 'Konsultasi Gratis') ?>
                </a>
            </div>
        </div>
    </header>

    <!-- Excellence / About Section - Data dari database -->
    <section id="about" class="section">
        <div class="container">
            <div class="section-header center">
                <span class="subtitle"><?= htmlspecialchars($aboutSettings['about_subtitle'] ?? 'Mengapa Memilih Kami') ?></span>
                <h2><?= htmlspecialchars($aboutSettings['about_title'] ?? 'Melayani Tamu Allah') ?> <span class="gold"><?= htmlspecialchars($aboutSettings['about_title_highlight'] ?? 'Sepenuh Hati') ?></span></h2>
                <p><?= htmlspecialchars($aboutSettings['about_description'] ?? '') ?></p>
            </div>

            <div class="features-grid">
                <?php foreach ($features as $feature): ?>
                    <a href="<?= htmlspecialchars($feature['link_url']) ?>" 
                       class="feature-card scroll-reveal <?= htmlspecialchars($feature['delay_class']) ?>"
                       style="display: block; text-decoration: none;">
                        <div class="icon-box"><i class="<?= htmlspecialchars($feature['icon']) ?>"></i></div>
                        <h3><?= htmlspecialchars($feature['title']) ?></h3>
                        <p>
                            <?= htmlspecialchars($feature['description']) ?>
                            <span style="font-size: 0.85rem; color: var(--accent-color); font-weight: 600; text-decoration: underline;">
                                <?= htmlspecialchars($feature['link_text']) ?>
                            </span>
                        </p>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- Packages Section - Data dari database -->
    <section id="packages" class="section bg-light">
        <div class="container">
            <div class="section-header center">
                <span class="subtitle"><?= htmlspecialchars($packagesSettings['packages_subtitle'] ?? 'Pilihan Paket') ?></span>
                <h2><?= htmlspecialchars($packagesSettings['packages_title'] ?? 'Paket') ?> <span class="gold"><?= htmlspecialchars($packagesSettings['packages_title_highlight'] ?? 'Umrah & Haji') ?></span></h2>
            </div>

            <div class="packages-grid">
                <?php foreach ($packages as $package): ?>
                    <div class="package-card scroll-reveal <?= htmlspecialchars($package['delay_class']) ?>">
                        <div class="package-image" style="background-image: url('<?= htmlspecialchars($package['image_url']) ?>');">
                            <div class="overlay"></div>
                            <?php if (!empty($package['tag'])): ?>
                                <div class="pkg-tag"><?= htmlspecialchars($package['tag']) ?></div>
                            <?php endif; ?>
                        </div>
                        <div class="package-details">
                            <h3><?= htmlspecialchars($package['name']) ?></h3>
                            <div class="price"><?= htmlspecialchars($package['currency']) ?> <?= htmlspecialchars($package['price']) ?></div>
                            <ul class="pkg-features">
                                <?php foreach ($package['features'] as $pf): ?>
                                    <li><i class="fa-solid fa-check"></i> <?= htmlspecialchars($pf['feature_text']) ?></li>
                                <?php endforeach; ?>
                            </ul>
                            <a href="<?= htmlspecialchars($package['button_link']) ?>" class="btn btn-primary full-width">
                                <?= htmlspecialchars($package['button_text']) ?>
                            </a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- Gallery Section - Data dari database -->
    <?php if (!empty($gallery)): ?>
    <section id="gallery" class="section">
        <div class="container">
            <div class="section-header center">
                <span class="subtitle">Galeri</span>
                <h2>Momen <span class="gold">Berkesan</span></h2>
            </div>
            <div class="gallery-grid">
                <?php foreach ($gallery as $item): ?>
                    <div class="gallery-item scroll-reveal">
                        <img src="<?= htmlspecialchars($item['image_url']) ?>" alt="<?= htmlspecialchars($item['alt_text'] ?? 'Gallery Image') ?>">
                        <div class="gallery-overlay">
                            <i class="fa-solid fa-expand"></i>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
    <?php endif; ?>

    <!-- CTA Section - Data dari database -->
    <section class="cta-section" id="contact">
        <div class="cta-overlay"></div>
        <div class="container cta-content center scroll-reveal">
            <h2><?= htmlspecialchars($ctaSettings['cta_title'] ?? 'Siap Menjadi Tamu Allah?') ?></h2>
            <p><?= htmlspecialchars($ctaSettings['cta_description'] ?? '') ?></p>
            <a href="https://wa.me/<?= htmlspecialchars($ctaSettings['cta_whatsapp_number'] ?? '') ?>" class="btn btn-gold">
                <i class="fa-brands fa-whatsapp"></i> <?= htmlspecialchars($ctaSettings['cta_button_text'] ?? 'Chat WhatsApp') ?>
            </a>
        </div>
    </section>

    <!-- Testimonials Section - Data dari database -->
    <?php if (!empty($testimonials)): ?>
    <section class="testimonials-section" id="testimonials">
        <!-- Animated Bubbles -->
        <div class="bubbles">
            <div class="bubble"></div>
            <div class="bubble"></div>
            <div class="bubble"></div>
            <div class="bubble"></div>
            <div class="bubble"></div>
            <div class="bubble"></div>
            <div class="bubble"></div>
            <div class="bubble"></div>
            <div class="bubble"></div>
            <div class="bubble"></div>
        </div>

        <div class="container">
            <div class="section-header center">
                <span class="subtitle">Testimoni Jamaah</span>
                <h2>Apa Kata <span class="gold">Mereka</span></h2>
            </div>

            <div class="testimonials-grid">
                <?php foreach ($testimonials as $testi): ?>
                    <div class="testimonial-card scroll-reveal">
                        <div class="testimonial-header">
                            <img src="<?= htmlspecialchars($testi['customer_photo'] ?? 'https://via.placeholder.com/60') ?>" 
                                 alt="<?= htmlspecialchars($testi['customer_name']) ?>" 
                                 class="testimonial-avatar">
                            <div class="testimonial-info">
                                <h4><?= htmlspecialchars($testi['customer_name']) ?></h4>
                                <span class="testimonial-package"><?= htmlspecialchars($testi['package_name'] ?? 'Jamaah Umrah') ?></span>
                            </div>
                        </div>
                        <p class="testimonial-text"><?= htmlspecialchars($testi['testimonial_text']) ?></p>
                        <div class="testimonial-rating">
                            <?php for ($i = 0; $i < ($testi['rating'] ?? 5); $i++): ?>
                                <i class="fa-solid fa-star"></i>
                            <?php endfor; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
    <?php endif; ?>

    <!-- Footer - Data dari database -->
    <footer>
        <div class="container footer-content">
            <div class="footer-brand">
                <a href="#" class="logo"><?= htmlspecialchars($siteName) ?><span class="gold"><?= htmlspecialchars($siteNameHighlight) ?></span>.</a>
                <p><?= htmlspecialchars(getFooterInfo('brand_description', '')) ?> <?= htmlspecialchars(getFooterInfo('license_info', '')) ?></p>
                <div class="social-links">
                    <?php foreach ($socialMedia as $social): ?>
                        <a href="<?= htmlspecialchars($social['url']) ?>" target="_blank" rel="noopener noreferrer">
                            <i class="<?= htmlspecialchars($social['icon']) ?>"></i>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>
            <div class="footer-links">
                <h4>Navigasi</h4>
                <ul>
                    <?php foreach ($footerNavigation as $link): ?>
                        <li><a href="<?= htmlspecialchars($link['link_url']) ?>"><?= htmlspecialchars($link['link_label']) ?></a></li>
                    <?php endforeach; ?>
                </ul>
            </div>
            <div class="footer-links">
                <h4>Layanan</h4>
                <ul>
                    <?php foreach ($footerLayanan as $link): ?>
                        <li><a href="<?= htmlspecialchars($link['link_url']) ?>"><?= htmlspecialchars($link['link_label']) ?></a></li>
                    <?php endforeach; ?>
                </ul>
            </div>
            <div class="footer-contact">
                <h4>Kontak</h4>
                <ul>
                    <li><i class="fa-solid fa-location-dot"></i> <?= htmlspecialchars(getFooterInfo('contact_address', 'Jl. Sultan Iskandar Muda No. 8, Jakarta Selatan')) ?></li>
                    <li><i class="fa-solid fa-phone"></i> <?= htmlspecialchars(getFooterInfo('contact_phone', '+62 812-3456-7890')) ?></li>
                    <li><i class="fa-solid fa-envelope"></i> <?= htmlspecialchars(getFooterInfo('contact_email', 'info@tanahsuci.com')) ?></li>
                </ul>
            </div>
        </div>
        <div class="footer-bottom">
            <div class="container">
                <p><?= htmlspecialchars(getFooterInfo('copyright_text', '© 2024 Tanah Suci Travel. All Rights Reserved.')) ?></p>
                <p style="margin-top: 0.5rem; color: var(--accent-color);">Dibuat oleh <strong><?= htmlspecialchars(getFooterInfo('created_by', 'Amrina Farza')) ?></strong></p>
            </div>
        </div>
    </footer>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            // Navbar Scroll Effect
            const navbar = document.querySelector('.navbar');
            window.addEventListener('scroll', () => {
                navbar.classList.toggle('scrolled', window.scrollY > 50);
            });

            // Mobile Navigation
            const hamburger = document.querySelector('.hamburger');
            const navLinks = document.querySelector('.nav-links');
            const navLinksItems = document.querySelectorAll('.nav-links li a');

            hamburger.addEventListener('click', () => {
                hamburger.classList.toggle('active');
                navLinks.classList.toggle('active');
            });

            navLinksItems.forEach(link => {
                link.addEventListener('click', () => {
                    hamburger.classList.remove('active');
                    navLinks.classList.remove('active');
                });
            });

            // Scroll Reveal Animation
            const revealElements = document.querySelectorAll('.scroll-reveal');
            const revealObserver = new IntersectionObserver((entries, observer) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        entry.target.classList.add('visible');
                        observer.unobserve(entry.target);
                    }
                });
            }, { root: null, threshold: 0.15, rootMargin: "0px" });

            revealElements.forEach(el => revealObserver.observe(el));
        });
    </script>

    <!-- Floating Chat Button -->
    <a href="komunitas.php" class="floating-chat-btn" title="Chat Komunitas">
        <i class="fa-solid fa-comments"></i>
        <span class="chat-tooltip">Chat</span>
    </a>

    <style>
        .floating-chat-btn {
            position: fixed;
            bottom: 2rem;
            right: 2rem;
            width: 60px;
            height: 60px;
            background: linear-gradient(135deg, var(--accent-color) 0%, #B5952F 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--primary-color);
            font-size: 1.5rem;
            text-decoration: none;
            box-shadow: 0 4px 20px rgba(212, 175, 55, 0.4);
            transition: all 0.3s ease;
            z-index: 9999;
            animation: pulse 2s infinite;
        }
        
        .floating-chat-btn:hover {
            transform: scale(1.1);
            box-shadow: 0 6px 30px rgba(212, 175, 55, 0.6);
            animation: none;
        }
        
        .floating-chat-btn .chat-tooltip {
            position: absolute;
            right: 70px;
            background: var(--primary-color);
            color: var(--text-light);
            padding: 0.5rem 1rem;
            border-radius: 0.5rem;
            font-size: 0.85rem;
            white-space: nowrap;
            opacity: 0;
            pointer-events: none;
            transition: opacity 0.3s ease;
        }
        
        .floating-chat-btn:hover .chat-tooltip {
            opacity: 1;
        }
        
        @keyframes pulse {
            0%, 100% { box-shadow: 0 4px 20px rgba(212, 175, 55, 0.4); }
            50% { box-shadow: 0 4px 30px rgba(212, 175, 55, 0.7); }
        }
        
        @media (max-width: 768px) {
            .floating-chat-btn {
                bottom: 1.5rem;
                right: 1.5rem;
                width: 55px;
                height: 55px;
                font-size: 1.3rem;
            }
            .floating-chat-btn .chat-tooltip { display: none; }
        }
    </style>
</body>

</html>

