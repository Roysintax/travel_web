<?php
// =====================================================
// ADMIN LOGOUT
// File: admin/logout.php
// =====================================================

require_once 'includes/auth.php';

// Log activity before logout
if (isLoggedIn()) {
    logAdminActivity('logout', 'Logout dari sistem');
}

// Logout
logoutAdmin();

// Redirect to login
header('Location: login.php');
exit;
