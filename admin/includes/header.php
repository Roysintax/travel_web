<?php
// =====================================================
// ADMIN HEADER TEMPLATE
// File: admin/includes/header.php
// =====================================================

if (!defined('PAGE_TITLE')) {
    define('PAGE_TITLE', 'Admin');
}

if (!defined('ACTIVE_MENU')) {
    define('ACTIVE_MENU', 'dashboard');
}

// Get new inquiries count for badge
try {
    $db = getDB();
    $newInquiriesCount = $db->query("SELECT COUNT(*) FROM inquiries WHERE status = 'new'")->fetchColumn();
} catch (PDOException $e) {
    $newInquiriesCount = 0;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= PAGE_TITLE ?> - Admin Tanah Suci</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary: #0F172A;
            --primary-light: #1E293B;
            --accent: #D4AF37;
            --accent-hover: #B5952F;
            --text-light: #F8FAFC;
            --text-muted: #94A3B8;
            --text-dark: #334155;
            --bg-light: #F1F5F9;
            --success: #22C55E;
            --warning: #F59E0B;
            --error: #EF4444;
            --info: #3B82F6;
            --sidebar-width: 260px;
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: 'Outfit', sans-serif;
            background: var(--bg-light);
            min-height: 100vh;
        }

        /* Sidebar */
        .sidebar {
            position: fixed;
            left: 0;
            top: 0;
            width: var(--sidebar-width);
            height: 100vh;
            background: var(--primary);
            padding: 1.5rem;
            overflow-y: auto;
            z-index: 100;
            transition: transform 0.3s ease;
        }

        .sidebar-header {
            padding-bottom: 1.5rem;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            margin-bottom: 1.5rem;
        }

        .sidebar-logo {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--text-light);
        }

        .sidebar-logo span { color: var(--accent); }

        .sidebar-nav { list-style: none; }

        .nav-section { margin-bottom: 1.5rem; }

        .nav-section-title {
            color: var(--text-muted);
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 0.75rem;
            padding-left: 0.5rem;
        }

        .nav-item { margin-bottom: 0.25rem; }

        .nav-link {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 0.75rem 1rem;
            color: var(--text-muted);
            text-decoration: none;
            border-radius: 0.5rem;
            transition: all 0.3s ease;
        }

        .nav-link:hover, .nav-link.active {
            background: rgba(212, 175, 55, 0.1);
            color: var(--accent);
        }

        .nav-link i { width: 20px; text-align: center; }

        .nav-badge {
            margin-left: auto;
            background: var(--error);
            color: white;
            font-size: 0.7rem;
            padding: 0.15rem 0.5rem;
            border-radius: 50px;
        }

        /* Main Content */
        .main-content { margin-left: var(--sidebar-width); min-height: 100vh; }

        /* Header */
        .header {
            background: white;
            padding: 1rem 2rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            position: sticky;
            top: 0;
            z-index: 50;
        }

        .header-left { display: flex; align-items: center; gap: 1rem; }

        .menu-toggle {
            display: none;
            background: none;
            border: none;
            font-size: 1.5rem;
            color: var(--text-dark);
            cursor: pointer;
        }

        .page-title {
            font-size: 1.25rem;
            font-weight: 600;
            color: var(--primary);
        }

        .header-right { display: flex; align-items: center; gap: 1.5rem; }

        .user-dropdown { position: relative; }

        .user-btn {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            background: none;
            border: none;
            cursor: pointer;
            padding: 0.5rem;
            border-radius: 0.5rem;
            transition: background 0.3s ease;
        }

        .user-btn:hover { background: var(--bg-light); }

        .user-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: var(--accent);
            color: var(--primary);
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
        }

        .user-info { text-align: left; }
        .user-name { font-weight: 600; color: var(--text-dark); font-size: 0.9rem; }
        .user-role { font-size: 0.75rem; color: var(--text-muted); text-transform: capitalize; }

        .dropdown-menu {
            position: absolute;
            right: 0;
            top: 100%;
            background: white;
            border-radius: 0.5rem;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15);
            min-width: 180px;
            display: none;
            z-index: 100;
        }

        .dropdown-menu.show { display: block; }

        .dropdown-item {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 0.75rem 1rem;
            color: var(--text-dark);
            text-decoration: none;
            transition: background 0.3s ease;
        }

        .dropdown-item:hover { background: var(--bg-light); }
        .dropdown-item.text-danger { color: var(--error); }
        .dropdown-divider { border-top: 1px solid #E2E8F0; margin: 0.5rem 0; }

        /* Content */
        .content { padding: 2rem; }

        /* Cards */
        .card {
            background: white;
            border-radius: 1rem;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            margin-bottom: 1.5rem;
        }

        .card-header {
            padding: 1.25rem 1.5rem;
            border-bottom: 1px solid #E2E8F0;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .card-title {
            font-size: 1.1rem;
            font-weight: 600;
            color: var(--primary);
        }

        .card-body { padding: 1.5rem; }

        /* Forms */
        .form-group { margin-bottom: 1.25rem; }

        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            color: var(--text-dark);
            font-weight: 500;
            font-size: 0.9rem;
        }

        .form-control {
            width: 100%;
            padding: 0.75rem 1rem;
            border: 2px solid #E2E8F0;
            border-radius: 0.5rem;
            font-size: 1rem;
            font-family: inherit;
            transition: all 0.3s ease;
        }

        .form-control:focus {
            outline: none;
            border-color: var(--accent);
            box-shadow: 0 0 0 3px rgba(212, 175, 55, 0.1);
        }

        textarea.form-control { min-height: 100px; resize: vertical; }

        .form-row {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 1rem;
        }

        .form-hint {
            font-size: 0.8rem;
            color: var(--text-muted);
            margin-top: 0.25rem;
        }

        /* Buttons */
        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            padding: 0.75rem 1.5rem;
            border: none;
            border-radius: 0.5rem;
            font-size: 0.9rem;
            font-weight: 600;
            font-family: inherit;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
        }

        .btn-primary { background: var(--accent); color: var(--primary); }
        .btn-primary:hover { background: var(--accent-hover); }
        .btn-secondary { background: var(--bg-light); color: var(--text-dark); }
        .btn-secondary:hover { background: #E2E8F0; }
        .btn-success { background: var(--success); color: white; }
        .btn-danger { background: var(--error); color: white; }
        .btn-sm { padding: 0.5rem 1rem; font-size: 0.8rem; }

        /* Table */
        .table {
            width: 100%;
            border-collapse: collapse;
        }

        .table th, .table td {
            padding: 0.75rem 1rem;
            text-align: left;
            border-bottom: 1px solid #E2E8F0;
        }

        .table th {
            font-weight: 600;
            color: var(--text-muted);
            font-size: 0.85rem;
            text-transform: uppercase;
            background: var(--bg-light);
        }

        .table td { color: var(--text-dark); }

        .table tbody tr:hover { background: rgba(241, 245, 249, 0.5); }

        /* Badges */
        .badge {
            padding: 0.25rem 0.75rem;
            border-radius: 50px;
            font-size: 0.75rem;
            font-weight: 600;
        }

        .badge-success { background: #DCFCE7; color: #16A34A; }
        .badge-warning { background: #FEF3C7; color: #D97706; }
        .badge-error { background: #FEE2E2; color: #DC2626; }
        .badge-info { background: #DBEAFE; color: #2563EB; }

        /* Alerts */
        .alert {
            padding: 1rem 1.25rem;
            border-radius: 0.5rem;
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .alert-success { background: #DCFCE7; color: #16A34A; border: 1px solid #BBF7D0; }
        .alert-error { background: #FEE2E2; color: #DC2626; border: 1px solid #FECACA; }
        .alert-warning { background: #FEF3C7; color: #D97706; border: 1px solid #FDE68A; }
        .alert-info { background: #DBEAFE; color: #2563EB; border: 1px solid #BFDBFE; }

        /* Tabs */
        .tabs {
            display: flex;
            gap: 0.5rem;
            margin-bottom: 1.5rem;
            border-bottom: 2px solid #E2E8F0;
            padding-bottom: 0;
        }

        .tab-btn {
            padding: 0.75rem 1.5rem;
            background: none;
            border: none;
            font-size: 0.9rem;
            font-weight: 500;
            color: var(--text-muted);
            cursor: pointer;
            border-bottom: 2px solid transparent;
            margin-bottom: -2px;
            transition: all 0.3s ease;
        }

        .tab-btn:hover { color: var(--text-dark); }
        .tab-btn.active { color: var(--accent); border-bottom-color: var(--accent); }

        .tab-content { display: none; }
        .tab-content.active { display: block; }

        /* Action Buttons */
        .action-btns {
            display: flex;
            gap: 0.5rem;
        }

        .btn-icon {
            width: 32px;
            height: 32px;
            padding: 0;
            border-radius: 0.375rem;
        }

        /* Image Preview */
        .img-preview {
            width: 60px;
            height: 60px;
            object-fit: cover;
            border-radius: 0.5rem;
        }

        /* Empty State */
        .empty-state {
            text-align: center;
            padding: 3rem;
            color: var(--text-muted);
        }

        .empty-state i {
            font-size: 3rem;
            margin-bottom: 1rem;
            opacity: 0.5;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .sidebar { transform: translateX(-100%); }
            .sidebar.active { transform: translateX(0); }
            .main-content { margin-left: 0; }
            .menu-toggle { display: block; }
            .content { padding: 1rem; }
            .header { padding: 1rem; }
            .form-row { grid-template-columns: 1fr; }
            .user-info { display: none; }
        }

        .sidebar-overlay {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(0, 0, 0, 0.5);
            z-index: 99;
        }

        .sidebar-overlay.active { display: block; }
    </style>
</head>
<body>
    <div class="sidebar-overlay" id="sidebarOverlay"></div>

    <aside class="sidebar" id="sidebar">
        <div class="sidebar-header">
            <div class="sidebar-logo">Tanah<span>Suci</span>.</div>
        </div>
        
        <nav>
            <ul class="sidebar-nav">
                <li class="nav-section">
                    <div class="nav-section-title">Menu Utama</div>
                    <ul>
                        <li class="nav-item">
                            <a href="index.php" class="nav-link <?= ACTIVE_MENU === 'dashboard' ? 'active' : '' ?>">
                                <i class="fa-solid fa-gauge-high"></i> Dashboard
                            </a>
                        </li>
                    </ul>
                </li>
                
                <li class="nav-section">
                    <div class="nav-section-title">Konten</div>
                    <ul>
                        <li class="nav-item">
                            <a href="settings.php" class="nav-link <?= ACTIVE_MENU === 'settings' ? 'active' : '' ?>">
                                <i class="fa-solid fa-cog"></i> Pengaturan
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="packages.php" class="nav-link <?= ACTIVE_MENU === 'packages' ? 'active' : '' ?>">
                                <i class="fa-solid fa-box"></i> Paket Umrah
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="features.php" class="nav-link <?= ACTIVE_MENU === 'features' ? 'active' : '' ?>">
                                <i class="fa-solid fa-star"></i> Fitur
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="gallery.php" class="nav-link <?= ACTIVE_MENU === 'gallery' ? 'active' : '' ?>">
                                <i class="fa-solid fa-images"></i> Galeri
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="testimonials.php" class="nav-link <?= ACTIVE_MENU === 'testimonials' ? 'active' : '' ?>">
                                <i class="fa-solid fa-quote-right"></i> Testimoni
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="articles.php" class="nav-link <?= ACTIVE_MENU === 'articles' ? 'active' : '' ?>">
                                <i class="fa-solid fa-newspaper"></i> Artikel
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="footer.php" class="nav-link <?= ACTIVE_MENU === 'footer' ? 'active' : '' ?>">
                                <i class="fa-solid fa-shoe-prints"></i> Footer
                            </a>
                        </li>
                    </ul>
                </li>
                
                <li class="nav-section">
                    <div class="nav-section-title">Manajemen</div>
                    <ul>
                        <li class="nav-item">
                            <a href="inquiries.php" class="nav-link <?= ACTIVE_MENU === 'inquiries' ? 'active' : '' ?>">
                                <i class="fa-solid fa-envelope"></i> Pesan Masuk
                                <?php if ($newInquiriesCount > 0): ?>
                                    <span class="nav-badge"><?= $newInquiriesCount ?></span>
                                <?php endif; ?>
                            </a>
                        </li>
                        <?php if (hasRole(['super_admin', 'admin'])): ?>
                        <li class="nav-item">
                            <a href="admins.php" class="nav-link <?= ACTIVE_MENU === 'admins' ? 'active' : '' ?>">
                                <i class="fa-solid fa-users-gear"></i> Admin
                            </a>
                        </li>
                        <?php endif; ?>
                    </ul>
                </li>
            </ul>
        </nav>
    </aside>

    <main class="main-content">
        <header class="header">
            <div class="header-left">
                <button class="menu-toggle" id="menuToggle">
                    <i class="fa-solid fa-bars"></i>
                </button>
                <h1 class="page-title"><?= PAGE_TITLE ?></h1>
            </div>
            
            <div class="header-right">
                <a href="../index.php" target="_blank" style="color: var(--text-muted); text-decoration: none;">
                    <i class="fa-solid fa-external-link-alt"></i> Website
                </a>
                
                <div class="user-dropdown">
                    <button class="user-btn" id="userBtn">
                        <div class="user-avatar">
                            <?= strtoupper(substr($_SESSION['admin_fullname'] ?? 'A', 0, 1)) ?>
                        </div>
                        <div class="user-info">
                            <div class="user-name"><?= htmlspecialchars($_SESSION['admin_fullname'] ?? 'Admin') ?></div>
                            <div class="user-role"><?= str_replace('_', ' ', $_SESSION['admin_role'] ?? 'admin') ?></div>
                        </div>
                        <i class="fa-solid fa-chevron-down" style="color: var(--text-muted);"></i>
                    </button>
                    
                    <div class="dropdown-menu" id="dropdownMenu">
                        <a href="profile.php" class="dropdown-item">
                            <i class="fa-solid fa-user"></i> Profil
                        </a>
                        <div class="dropdown-divider"></div>
                        <a href="logout.php" class="dropdown-item text-danger">
                            <i class="fa-solid fa-right-from-bracket"></i> Keluar
                        </a>
                    </div>
                </div>
            </div>
        </header>

        <div class="content">
