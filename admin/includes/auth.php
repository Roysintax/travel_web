<?php
// =====================================================
// ADMIN AUTH FUNCTIONS
// File: admin/includes/auth.php
// Fungsi: Helper functions untuk autentikasi admin
// =====================================================

session_start();

require_once __DIR__ . '/../../config/database.php';

/**
 * Cek apakah admin sudah login
 * @return bool
 */
function isLoggedIn() {
    // Cek session
    if (isset($_SESSION['admin_id']) && isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
        return true;
    }
    
    // Cek remember me cookie
    if (isset($_COOKIE['remember_token'])) {
        $token = $_COOKIE['remember_token'];
        $admin = getAdminByRememberToken($token);
        
        if ($admin && strtotime($admin['token_expires']) > time()) {
            // Token valid, login otomatis
            loginAdmin($admin);
            return true;
        } else {
            // Token invalid/expired, hapus cookie
            setcookie('remember_token', '', time() - 3600, '/');
        }
    }
    
    return false;
}

/**
 * Redirect jika belum login
 */
function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: login.php');
        exit;
    }
}

/**
 * Redirect jika sudah login
 */
function redirectIfLoggedIn() {
    if (isLoggedIn()) {
        header('Location: index.php');
        exit;
    }
}

/**
 * Login admin - Set session
 * @param array $admin - Data admin dari database
 */
function loginAdmin($admin) {
    $_SESSION['admin_id'] = $admin['id'];
    $_SESSION['admin_username'] = $admin['username'];
    $_SESSION['admin_fullname'] = $admin['full_name'];
    $_SESSION['admin_email'] = $admin['email'];
    $_SESSION['admin_role'] = $admin['role'];
    $_SESSION['admin_avatar'] = $admin['avatar'];
    $_SESSION['admin_logged_in'] = true;
    
    // Update last login
    updateLastLogin($admin['id']);
}

/**
 * Logout admin
 */
function logoutAdmin() {
    // Hapus remember token dari database
    if (isset($_SESSION['admin_id'])) {
        clearRememberToken($_SESSION['admin_id']);
    }
    
    // Hapus cookie
    if (isset($_COOKIE['remember_token'])) {
        setcookie('remember_token', '', time() - 3600, '/');
    }
    
    // Destroy session
    session_unset();
    session_destroy();
}

/**
 * Verifikasi password
 * @param string $password
 * @param string $hash
 * @return bool
 */
function verifyPassword($password, $hash) {
    return password_verify($password, $hash);
}

/**
 * Hash password
 * @param string $password
 * @return string
 */
function hashPassword($password) {
    return password_hash($password, PASSWORD_DEFAULT);
}

/**
 * Generate remember token
 * @return string
 */
function generateRememberToken() {
    return bin2hex(random_bytes(32));
}

/**
 * Set remember token untuk admin
 * @param int $adminId
 * @param string $token
 * @param int $days - Berapa hari token berlaku
 */
function setRememberToken($adminId, $token, $days = 30) {
    try {
        $db = getDB();
        $expires = date('Y-m-d H:i:s', strtotime("+{$days} days"));
        $stmt = $db->prepare("UPDATE admins SET remember_token = ?, token_expires = ? WHERE id = ?");
        $stmt->execute([$token, $expires, $adminId]);
        
        // Set cookie
        setcookie('remember_token', $token, time() + ($days * 24 * 60 * 60), '/');
    } catch (PDOException $e) {
        // Silent fail
    }
}

/**
 * Clear remember token
 * @param int $adminId
 */
function clearRememberToken($adminId) {
    try {
        $db = getDB();
        $stmt = $db->prepare("UPDATE admins SET remember_token = NULL, token_expires = NULL WHERE id = ?");
        $stmt->execute([$adminId]);
    } catch (PDOException $e) {
        // Silent fail
    }
}

/**
 * Get admin by remember token
 * @param string $token
 * @return array|null
 */
function getAdminByRememberToken($token) {
    try {
        $db = getDB();
        $stmt = $db->prepare("SELECT * FROM admins WHERE remember_token = ? AND is_active = 1");
        $stmt->execute([$token]);
        return $stmt->fetch();
    } catch (PDOException $e) {
        return null;
    }
}

/**
 * Get admin by username
 * @param string $username
 * @return array|null
 */
function getAdminByUsername($username) {
    try {
        $db = getDB();
        $stmt = $db->prepare("SELECT * FROM admins WHERE username = ?");
        $stmt->execute([$username]);
        return $stmt->fetch();
    } catch (PDOException $e) {
        return null;
    }
}

/**
 * Get admin by email
 * @param string $email
 * @return array|null
 */
function getAdminByEmail($email) {
    try {
        $db = getDB();
        $stmt = $db->prepare("SELECT * FROM admins WHERE email = ?");
        $stmt->execute([$email]);
        return $stmt->fetch();
    } catch (PDOException $e) {
        return null;
    }
}

/**
 * Get admin by ID
 * @param int $id
 * @return array|null
 */
function getAdminById($id) {
    try {
        $db = getDB();
        $stmt = $db->prepare("SELECT * FROM admins WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    } catch (PDOException $e) {
        return null;
    }
}

/**
 * Update last login time
 * @param int $adminId
 */
function updateLastLogin($adminId) {
    try {
        $db = getDB();
        $stmt = $db->prepare("UPDATE admins SET last_login = NOW() WHERE id = ?");
        $stmt->execute([$adminId]);
    } catch (PDOException $e) {
        // Silent fail
    }
}

/**
 * Register new admin
 * @param array $data
 * @return int|false - ID admin baru atau false jika gagal
 */
function registerAdmin($data) {
    try {
        $db = getDB();
        $stmt = $db->prepare("
            INSERT INTO admins (username, password, full_name, email, role, is_active) 
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $data['username'],
            hashPassword($data['password']),
            $data['full_name'],
            $data['email'],
            $data['role'] ?? 'editor', // Default role editor untuk keamanan
            1 // Active by default
        ]);
        return $db->lastInsertId();
    } catch (PDOException $e) {
        return false;
    }
}

/**
 * Log admin activity
 * @param string $action
 * @param string $description
 */
function logAdminActivity($action, $description = '') {
    if (!isset($_SESSION['admin_id'])) return;
    
    try {
        $db = getDB();
        $stmt = $db->prepare("
            INSERT INTO admin_logs (admin_id, action, description, ip_address, user_agent) 
            VALUES (?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $_SESSION['admin_id'],
            $action,
            $description,
            $_SERVER['REMOTE_ADDR'] ?? 'unknown',
            $_SERVER['HTTP_USER_AGENT'] ?? 'unknown'
        ]);
    } catch (PDOException $e) {
        // Silent fail
    }
}

/**
 * Get current admin data
 * @return array|null
 */
function getCurrentAdmin() {
    if (!isset($_SESSION['admin_id'])) return null;
    return getAdminById($_SESSION['admin_id']);
}

/**
 * Check if current admin has role
 * @param string|array $roles
 * @return bool
 */
function hasRole($roles) {
    if (!isset($_SESSION['admin_role'])) return false;
    
    if (is_array($roles)) {
        return in_array($_SESSION['admin_role'], $roles);
    }
    
    return $_SESSION['admin_role'] === $roles;
}

/**
 * Require specific role(s)
 * @param string|array $roles
 */
function requireRole($roles) {
    if (!hasRole($roles)) {
        header('HTTP/1.0 403 Forbidden');
        die('Akses ditolak. Anda tidak memiliki izin untuk halaman ini.');
    }
}

/**
 * Generate CSRF token
 * @return string
 */
function generateCSRFToken() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Verify CSRF token
 * @param string $token
 * @return bool
 */
function verifyCSRFToken($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Get CSRF input field HTML
 * @return string
 */
function csrfField() {
    return '<input type="hidden" name="csrf_token" value="' . generateCSRFToken() . '">';
}

/**
 * Flash message - Set
 * @param string $type - success, error, warning, info
 * @param string $message
 */
function setFlash($type, $message) {
    $_SESSION['flash'] = [
        'type' => $type,
        'message' => $message
    ];
}

/**
 * Flash message - Get
 * @return array|null
 */
function getFlash() {
    if (isset($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $flash;
    }
    return null;
}

/**
 * Get all admins
 * @return array
 */
function getAllAdmins() {
    try {
        $db = getDB();
        $stmt = $db->query("SELECT id, username, full_name, email, role, is_active, last_login, created_at FROM admins ORDER BY created_at DESC");
        return $stmt->fetchAll();
    } catch (PDOException $e) {
        return [];
    }
}
