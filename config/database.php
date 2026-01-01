<?php
// =====================================================
// DATABASE CONFIGURATION
// File: config/database.php
// Fungsi: Koneksi ke database MySQL
// =====================================================

// Konfigurasi Database - Auto-detect localhost vs production
if ($_SERVER['HTTP_HOST'] === 'localhost' || $_SERVER['HTTP_HOST'] === '127.0.0.1') {
    // LOCAL DEVELOPMENT (XAMPP)
    define('DB_HOST', 'localhost');
    define('DB_NAME', 'tanah_suci_db');
    define('DB_USER', 'root');
    define('DB_PASS', '');
} else {
    // PRODUCTION (InfinityFree)
    define('DB_HOST', 'sql303.infinityfree.com');
    define('DB_NAME', 'if0_40794045_tanah_suci_db');
    define('DB_USER', 'if0_40794045');
    define('DB_PASS', 'I0TgQEhZ1F');
}
define('DB_CHARSET', 'utf8mb4');

/**
 * Class Database
 * Mengelola koneksi database menggunakan PDO
 */
class Database {
    private static $instance = null;
    private $connection;
    
    /**
     * Constructor - Membuat koneksi database
     */
    private function __construct() {
        try {
            $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
            $options = [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ];
            
            $this->connection = new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch (PDOException $e) {
            die("Koneksi database gagal: " . $e->getMessage());
        }
    }
    
    /**
     * Singleton - Mendapatkan instance database
     */
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Mendapatkan koneksi PDO
     */
    public function getConnection() {
        return $this->connection;
    }
    
    /**
     * Mencegah cloning
     */
    private function __clone() {}
    
    /**
     * Mencegah unserialize
     */
    public function __wakeup() {
        throw new Exception("Cannot unserialize singleton");
    }
}

/**
 * Fungsi helper untuk mendapatkan koneksi database
 */
function getDB() {
    return Database::getInstance()->getConnection();
}

// =====================================================
// HELPER FUNCTIONS - Fungsi-fungsi pembantu
// =====================================================

/**
 * Mendapatkan nilai setting dari database
 * @param string $key - Kunci setting
 * @param string $default - Nilai default jika tidak ditemukan
 * @return string
 */
function getSetting($key, $default = '') {
    try {
        $db = getDB();
        $stmt = $db->prepare("SELECT setting_value FROM site_settings WHERE setting_key = ?");
        $stmt->execute([$key]);
        $result = $stmt->fetch();
        return $result ? $result['setting_value'] : $default;
    } catch (PDOException $e) {
        return $default;
    }
}

/**
 * Mendapatkan semua settings berdasarkan grup
 * @param string $group - Nama grup setting
 * @return array
 */
function getSettingsByGroup($group) {
    try {
        $db = getDB();
        $stmt = $db->prepare("SELECT setting_key, setting_value FROM site_settings WHERE setting_group = ?");
        $stmt->execute([$group]);
        $settings = [];
        while ($row = $stmt->fetch()) {
            $settings[$row['setting_key']] = $row['setting_value'];
        }
        return $settings;
    } catch (PDOException $e) {
        return [];
    }
}

/**
 * Update nilai setting
 * @param string $key - Kunci setting
 * @param string $value - Nilai baru
 * @return bool
 */
function updateSetting($key, $value) {
    try {
        $db = getDB();
        $stmt = $db->prepare("UPDATE site_settings SET setting_value = ? WHERE setting_key = ?");
        return $stmt->execute([$value, $key]);
    } catch (PDOException $e) {
        return false;
    }
}

/**
 * Mendapatkan semua menu navigasi aktif
 * @return array
 */
function getNavMenu() {
    try {
        $db = getDB();
        $stmt = $db->query("SELECT * FROM navigation_menu WHERE is_active = 1 ORDER BY menu_order ASC");
        return $stmt->fetchAll();
    } catch (PDOException $e) {
        return [];
    }
}

/**
 * Mendapatkan semua feature aktif
 * @return array
 */
function getFeatures() {
    try {
        $db = getDB();
        $stmt = $db->query("SELECT * FROM features WHERE is_active = 1 ORDER BY feature_order ASC");
        return $stmt->fetchAll();
    } catch (PDOException $e) {
        return [];
    }
}

/**
 * Mendapatkan semua paket aktif beserta fitur-fiturnya
 * @return array
 */
function getPackages() {
    try {
        $db = getDB();
        $stmt = $db->query("SELECT * FROM packages WHERE is_active = 1 ORDER BY package_order ASC");
        $packages = $stmt->fetchAll();
        
        // Ambil fitur untuk setiap paket
        foreach ($packages as &$package) {
            $stmtFeatures = $db->prepare("SELECT * FROM package_features WHERE package_id = ? AND is_active = 1 ORDER BY feature_order ASC");
            $stmtFeatures->execute([$package['id']]);
            $package['features'] = $stmtFeatures->fetchAll();
        }
        
        return $packages;
    } catch (PDOException $e) {
        return [];
    }
}

/**
 * Mendapatkan satu paket berdasarkan ID
 * @param int $id - ID paket
 * @return array|null
 */
function getPackageById($id) {
    try {
        $db = getDB();
        $stmt = $db->prepare("SELECT * FROM packages WHERE id = ?");
        $stmt->execute([$id]);
        $package = $stmt->fetch();
        
        if ($package) {
            $stmtFeatures = $db->prepare("SELECT * FROM package_features WHERE package_id = ? AND is_active = 1 ORDER BY feature_order ASC");
            $stmtFeatures->execute([$id]);
            $package['features'] = $stmtFeatures->fetchAll();
        }
        
        return $package;
    } catch (PDOException $e) {
        return null;
    }
}

/**
 * Mendapatkan semua gambar galeri aktif
 * @return array
 */
function getGallery() {
    try {
        $db = getDB();
        $stmt = $db->query("SELECT * FROM gallery WHERE is_active = 1 ORDER BY gallery_order ASC");
        return $stmt->fetchAll();
    } catch (PDOException $e) {
        return [];
    }
}

/**
 * Mendapatkan footer links berdasarkan section
 * @param string $section - Nama section
 * @return array
 */
function getFooterLinks($section) {
    try {
        $db = getDB();
        $stmt = $db->prepare("SELECT * FROM footer_links WHERE section = ? AND is_active = 1 ORDER BY link_order ASC");
        $stmt->execute([$section]);
        return $stmt->fetchAll();
    } catch (PDOException $e) {
        return [];
    }
}

/**
 * Mendapatkan informasi kontak
 * @return array
 */
function getContactInfo() {
    try {
        $db = getDB();
        $stmt = $db->query("SELECT * FROM contact_info WHERE is_active = 1 ORDER BY info_order ASC");
        return $stmt->fetchAll();
    } catch (PDOException $e) {
        return [];
    }
}

/**
 * Mendapatkan link social media
 * @return array
 */
function getSocialMedia() {
    try {
        $db = getDB();
        $stmt = $db->query("SELECT * FROM social_media WHERE is_active = 1 ORDER BY social_order ASC");
        return $stmt->fetchAll();
    } catch (PDOException $e) {
        return [];
    }
}

/**
 * Mendapatkan info footer berdasarkan key
 * @param string $key - Kunci info
 * @param string $default - Nilai default
 * @return string
 */
function getFooterInfo($key, $default = '') {
    try {
        $db = getDB();
        $stmt = $db->prepare("SELECT info_value FROM footer_info WHERE info_key = ?");
        $stmt->execute([$key]);
        $result = $stmt->fetch();
        return $result ? $result['info_value'] : $default;
    } catch (PDOException $e) {
        return $default;
    }
}

/**
 * Mendapatkan semua testimoni aktif
 * @return array
 */
function getTestimonials() {
    try {
        $db = getDB();
        $stmt = $db->query("SELECT * FROM testimonials WHERE is_active = 1 ORDER BY testimonial_order ASC");
        return $stmt->fetchAll();
    } catch (PDOException $e) {
        return [];
    }
}

/**
 * Menyimpan inquiry/pesan dari pengunjung
 * @param array $data - Data pesan
 * @return bool|int - ID pesan baru atau false jika gagal
 */
function saveInquiry($data) {
    try {
        $db = getDB();
        $stmt = $db->prepare("
            INSERT INTO inquiries (name, email, phone, subject, message, package_interest) 
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $data['name'],
            $data['email'],
            $data['phone'] ?? null,
            $data['subject'] ?? null,
            $data['message'],
            $data['package_interest'] ?? null
        ]);
        return $db->lastInsertId();
    } catch (PDOException $e) {
        return false;
    }
}

/**
 * Menyimpan subscriber baru
 * @param string $email - Email subscriber
 * @param string $name - Nama subscriber (opsional)
 * @return bool
 */
function saveSubscriber($email, $name = null) {
    try {
        $db = getDB();
        $stmt = $db->prepare("INSERT INTO subscribers (email, name) VALUES (?, ?) ON DUPLICATE KEY UPDATE is_active = 1");
        return $stmt->execute([$email, $name]);
    } catch (PDOException $e) {
        return false;
    }
}

/**
 * Mendapatkan page section
 * @param string $pageName - Nama halaman
 * @param string $sectionName - Nama section
 * @return array|null
 */
function getPageSection($pageName, $sectionName) {
    try {
        $db = getDB();
        $stmt = $db->prepare("SELECT * FROM page_sections WHERE page_name = ? AND section_name = ? AND is_active = 1");
        $stmt->execute([$pageName, $sectionName]);
        return $stmt->fetch();
    } catch (PDOException $e) {
        return null;
    }
}
