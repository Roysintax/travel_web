<?php
// =====================================================
// ADMIN DASHBOARD
// File: admin/index.php
// =====================================================

require_once 'includes/auth.php';

// Cek login
requireLogin();

// Get current admin
$currentAdmin = getCurrentAdmin();

// Get statistics
try {
    $db = getDB();
    
    // Count packages
    $packagesCount = $db->query("SELECT COUNT(*) FROM packages WHERE is_active = 1")->fetchColumn();
    
    // Count features
    $featuresCount = $db->query("SELECT COUNT(*) FROM features WHERE is_active = 1")->fetchColumn();
    
    // Count gallery
    $galleryCount = $db->query("SELECT COUNT(*) FROM gallery WHERE is_active = 1")->fetchColumn();
    
    // Count new inquiries
    $newInquiriesCount = $db->query("SELECT COUNT(*) FROM inquiries WHERE status = 'new'")->fetchColumn();
    
    // Count admins
    $adminsCount = $db->query("SELECT COUNT(*) FROM admins WHERE is_active = 1")->fetchColumn();
    
    // Recent inquiries
    $recentInquiries = $db->query("SELECT * FROM inquiries ORDER BY created_at DESC LIMIT 5")->fetchAll();
    
    // Recent admin logs
    $recentLogs = $db->query("
        SELECT al.*, a.username 
        FROM admin_logs al 
        JOIN admins a ON al.admin_id = a.id 
        ORDER BY al.created_at DESC 
        LIMIT 10
    ")->fetchAll();
    
} catch (PDOException $e) {
    $packagesCount = $featuresCount = $galleryCount = $newInquiriesCount = $adminsCount = 0;
    $recentInquiries = $recentLogs = [];
}

// Log activity
logAdminActivity('view_dashboard', 'Melihat dashboard');
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Admin Tanah Suci</title>
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

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

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

        .sidebar-logo span {
            color: var(--accent);
        }

        .sidebar-nav {
            list-style: none;
        }

        .nav-section {
            margin-bottom: 1.5rem;
        }

        .nav-section-title {
            color: var(--text-muted);
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 0.75rem;
            padding-left: 0.5rem;
        }

        .nav-item {
            margin-bottom: 0.25rem;
        }

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

        .nav-link i {
            width: 20px;
            text-align: center;
        }

        .nav-badge {
            margin-left: auto;
            background: var(--error);
            color: white;
            font-size: 0.7rem;
            padding: 0.15rem 0.5rem;
            border-radius: 50px;
        }

        /* Main Content */
        .main-content {
            margin-left: var(--sidebar-width);
            min-height: 100vh;
        }

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

        .header-left {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

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

        .header-right {
            display: flex;
            align-items: center;
            gap: 1.5rem;
        }

        .user-dropdown {
            position: relative;
        }

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

        .user-btn:hover {
            background: var(--bg-light);
        }

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

        .user-info {
            text-align: left;
        }

        .user-name {
            font-weight: 600;
            color: var(--text-dark);
            font-size: 0.9rem;
        }

        .user-role {
            font-size: 0.75rem;
            color: var(--text-muted);
            text-transform: capitalize;
        }

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

        .dropdown-menu.show {
            display: block;
        }

        .dropdown-item {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 0.75rem 1rem;
            color: var(--text-dark);
            text-decoration: none;
            transition: background 0.3s ease;
        }

        .dropdown-item:hover {
            background: var(--bg-light);
        }

        .dropdown-item.text-danger {
            color: var(--error);
        }

        .dropdown-divider {
            border-top: 1px solid #E2E8F0;
            margin: 0.5rem 0;
        }

        /* Content */
        .content {
            padding: 2rem;
        }

        .welcome-banner {
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-light) 100%);
            border-radius: 1rem;
            padding: 2rem;
            color: white;
            margin-bottom: 2rem;
        }

        .welcome-banner h2 {
            font-size: 1.5rem;
            margin-bottom: 0.5rem;
        }

        .welcome-banner p {
            opacity: 0.8;
        }

        /* Stats Grid */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .stat-card {
            background: white;
            border-radius: 1rem;
            padding: 1.5rem;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .stat-icon {
            width: 50px;
            height: 50px;
            border-radius: 0.75rem;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.25rem;
        }

        .stat-icon.blue { background: #DBEAFE; color: #2563EB; }
        .stat-icon.gold { background: #FEF3C7; color: #D97706; }
        .stat-icon.green { background: #DCFCE7; color: #16A34A; }
        .stat-icon.purple { background: #F3E8FF; color: #9333EA; }
        .stat-icon.red { background: #FEE2E2; color: #DC2626; }

        .stat-details h3 {
            font-size: 1.75rem;
            font-weight: 700;
            color: var(--primary);
        }

        .stat-details p {
            color: var(--text-muted);
            font-size: 0.9rem;
        }

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

        .card-body {
            padding: 1.5rem;
        }

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
        }

        .table td {
            color: var(--text-dark);
        }

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

        /* Grid */
        .grid-2 {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 1.5rem;
        }

        /* Quick Actions */
        .quick-actions {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 1rem;
        }

        .quick-action-btn {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 0.75rem;
            padding: 1.5rem;
            background: var(--bg-light);
            border-radius: 0.75rem;
            text-decoration: none;
            color: var(--text-dark);
            transition: all 0.3s ease;
        }

        .quick-action-btn:hover {
            background: var(--accent);
            color: var(--primary);
        }

        .quick-action-btn i {
            font-size: 1.5rem;
        }

        .quick-action-btn span {
            font-size: 0.9rem;
            font-weight: 500;
        }

        /* Responsive */
        @media (max-width: 1024px) {
            .grid-2 {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 768px) {
            .sidebar {
                transform: translateX(-100%);
            }

            .sidebar.active {
                transform: translateX(0);
            }

            .main-content {
                margin-left: 0;
            }

            .menu-toggle {
                display: block;
            }

            .content {
                padding: 1rem;
            }

            .header {
                padding: 1rem;
            }

            .stats-grid {
                grid-template-columns: 1fr 1fr;
            }
        }

        @media (max-width: 480px) {
            .stats-grid {
                grid-template-columns: 1fr;
            }

            .user-info {
                display: none;
            }
        }

        /* Overlay for mobile */
        .sidebar-overlay {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(0, 0, 0, 0.5);
            z-index: 99;
        }

        .sidebar-overlay.active {
            display: block;
        }
    </style>
</head>
<body>
    <!-- Sidebar Overlay -->
    <div class="sidebar-overlay" id="sidebarOverlay"></div>

    <!-- Sidebar -->
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
                            <a href="index.php" class="nav-link active">
                                <i class="fa-solid fa-gauge-high"></i>
                                Dashboard
                            </a>
                        </li>
                    </ul>
                </li>
                
                <li class="nav-section">
                    <div class="nav-section-title">Konten</div>
                    <ul>
                        <li class="nav-item">
                            <a href="settings.php" class="nav-link">
                                <i class="fa-solid fa-cog"></i>
                                Pengaturan
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="packages.php" class="nav-link">
                                <i class="fa-solid fa-box"></i>
                                Paket Umrah
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="features.php" class="nav-link">
                                <i class="fa-solid fa-star"></i>
                                Fitur
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="gallery.php" class="nav-link">
                                <i class="fa-solid fa-images"></i>
                                Galeri
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="testimonials.php" class="nav-link">
                                <i class="fa-solid fa-quote-right"></i>
                                Testimoni
                            </a>
                        </li>
                    </ul>
                </li>
                
                <li class="nav-section">
                    <div class="nav-section-title">Manajemen</div>
                    <ul>
                        <li class="nav-item">
                            <a href="inquiries.php" class="nav-link">
                                <i class="fa-solid fa-envelope"></i>
                                Pesan Masuk
                                <?php if ($newInquiriesCount > 0): ?>
                                    <span class="nav-badge"><?= $newInquiriesCount ?></span>
                                <?php endif; ?>
                            </a>
                        </li>
                        <?php if (hasRole(['super_admin', 'admin'])): ?>
                        <li class="nav-item">
                            <a href="admins.php" class="nav-link">
                                <i class="fa-solid fa-users-gear"></i>
                                Admin
                            </a>
                        </li>
                        <?php endif; ?>
                    </ul>
                </li>
            </ul>
        </nav>
    </aside>

    <!-- Main Content -->
    <main class="main-content">
        <!-- Header -->
        <header class="header">
            <div class="header-left">
                <button class="menu-toggle" id="menuToggle">
                    <i class="fa-solid fa-bars"></i>
                </button>
                <h1 class="page-title">Dashboard</h1>
            </div>
            
            <div class="header-right">
                <a href="../index.php" target="_blank" style="color: var(--text-muted); text-decoration: none;">
                    <i class="fa-solid fa-external-link-alt"></i> Lihat Website
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
                            <i class="fa-solid fa-user"></i>
                            Profil
                        </a>
                        <div class="dropdown-divider"></div>
                        <a href="logout.php" class="dropdown-item text-danger">
                            <i class="fa-solid fa-right-from-bracket"></i>
                            Keluar
                        </a>
                    </div>
                </div>
            </div>
        </header>

        <!-- Content -->
        <div class="content">
            <!-- Welcome Banner -->
            <div class="welcome-banner">
                <h2>Selamat Datang, <?= htmlspecialchars($_SESSION['admin_fullname'] ?? 'Admin') ?>! 👋</h2>
                <p>Kelola website Tanah Suci Travel dengan mudah dari dashboard ini.</p>
            </div>

            <!-- Stats Grid -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon blue">
                        <i class="fa-solid fa-box"></i>
                    </div>
                    <div class="stat-details">
                        <h3><?= $packagesCount ?></h3>
                        <p>Paket Aktif</p>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon gold">
                        <i class="fa-solid fa-star"></i>
                    </div>
                    <div class="stat-details">
                        <h3><?= $featuresCount ?></h3>
                        <p>Fitur</p>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon green">
                        <i class="fa-solid fa-images"></i>
                    </div>
                    <div class="stat-details">
                        <h3><?= $galleryCount ?></h3>
                        <p>Galeri</p>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon red">
                        <i class="fa-solid fa-envelope"></i>
                    </div>
                    <div class="stat-details">
                        <h3><?= $newInquiriesCount ?></h3>
                        <p>Pesan Baru</p>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon purple">
                        <i class="fa-solid fa-users-gear"></i>
                    </div>
                    <div class="stat-details">
                        <h3><?= $adminsCount ?></h3>
                        <p>Admin</p>
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Aksi Cepat</h3>
                </div>
                <div class="card-body">
                    <div class="quick-actions">
                        <a href="settings.php" class="quick-action-btn">
                            <i class="fa-solid fa-cog"></i>
                            <span>Pengaturan</span>
                        </a>
                        <a href="packages.php?action=add" class="quick-action-btn">
                            <i class="fa-solid fa-plus"></i>
                            <span>Tambah Paket</span>
                        </a>
                        <a href="gallery.php?action=add" class="quick-action-btn">
                            <i class="fa-solid fa-upload"></i>
                            <span>Upload Galeri</span>
                        </a>
                        <a href="inquiries.php" class="quick-action-btn">
                            <i class="fa-solid fa-envelope-open"></i>
                            <span>Lihat Pesan</span>
                        </a>
                    </div>
                </div>
            </div>

            <!-- Two Column Layout -->
            <div class="grid-2">
                <!-- Recent Inquiries -->
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Pesan Terbaru</h3>
                        <a href="inquiries.php" style="color: var(--accent); text-decoration: none; font-size: 0.9rem;">
                            Lihat Semua →
                        </a>
                    </div>
                    <div class="card-body" style="padding: 0;">
                        <?php if (empty($recentInquiries)): ?>
                            <p style="padding: 1.5rem; color: var(--text-muted); text-align: center;">
                                Belum ada pesan masuk.
                            </p>
                        <?php else: ?>
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Nama</th>
                                        <th>Status</th>
                                        <th>Waktu</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($recentInquiries as $inquiry): ?>
                                        <tr>
                                            <td>
                                                <strong><?= htmlspecialchars($inquiry['name']) ?></strong><br>
                                                <small style="color: var(--text-muted);"><?= htmlspecialchars($inquiry['email']) ?></small>
                                            </td>
                                            <td>
                                                <?php
                                                $statusBadge = [
                                                    'new' => 'badge-warning',
                                                    'read' => 'badge-info',
                                                    'replied' => 'badge-success',
                                                    'closed' => 'badge-error'
                                                ][$inquiry['status']] ?? 'badge-info';
                                                $statusText = [
                                                    'new' => 'Baru',
                                                    'read' => 'Dibaca',
                                                    'replied' => 'Dijawab',
                                                    'closed' => 'Ditutup'
                                                ][$inquiry['status']] ?? $inquiry['status'];
                                                ?>
                                                <span class="badge <?= $statusBadge ?>"><?= $statusText ?></span>
                                            </td>
                                            <td>
                                                <small><?= date('d M Y, H:i', strtotime($inquiry['created_at'])) ?></small>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Activity Log -->
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Aktivitas Terakhir</h3>
                    </div>
                    <div class="card-body" style="padding: 0;">
                        <?php if (empty($recentLogs)): ?>
                            <p style="padding: 1.5rem; color: var(--text-muted); text-align: center;">
                                Belum ada aktivitas.
                            </p>
                        <?php else: ?>
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Admin</th>
                                        <th>Aksi</th>
                                        <th>Waktu</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($recentLogs as $log): ?>
                                        <tr>
                                            <td><?= htmlspecialchars($log['username']) ?></td>
                                            <td><?= htmlspecialchars($log['action']) ?></td>
                                            <td><small><?= date('d M Y, H:i', strtotime($log['created_at'])) ?></small></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <script>
        // Sidebar toggle
        const menuToggle = document.getElementById('menuToggle');
        const sidebar = document.getElementById('sidebar');
        const sidebarOverlay = document.getElementById('sidebarOverlay');

        menuToggle.addEventListener('click', () => {
            sidebar.classList.toggle('active');
            sidebarOverlay.classList.toggle('active');
        });

        sidebarOverlay.addEventListener('click', () => {
            sidebar.classList.remove('active');
            sidebarOverlay.classList.remove('active');
        });

        // User dropdown
        const userBtn = document.getElementById('userBtn');
        const dropdownMenu = document.getElementById('dropdownMenu');

        userBtn.addEventListener('click', () => {
            dropdownMenu.classList.toggle('show');
        });

        // Close dropdown when clicking outside
        document.addEventListener('click', (e) => {
            if (!userBtn.contains(e.target) && !dropdownMenu.contains(e.target)) {
                dropdownMenu.classList.remove('show');
            }
        });
    </script>
</body>
</html>
