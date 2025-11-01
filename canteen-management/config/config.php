<?php
/**
 * Application Configuration
 * Canteen Management System - Yangtze University
 */

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Timezone
date_default_timezone_set('Asia/Shanghai');

// Base URL (adjust for your environment)
define('BASE_URL', 'http://localhost/canteen-management/');
define('BASE_PATH', dirname(__DIR__) . '/');

// Application information
define('APP_NAME', 'Yangtze University Canteen Management System');
define('APP_SHORT_NAME', 'YUCMS');
define('APP_VERSION', '1.0.0');
define('UNIVERSITY_NAME', 'Yangtze University');

// File upload settings
define('UPLOAD_PATH', BASE_PATH . 'uploads/');
define('STUDENT_PHOTO_PATH', UPLOAD_PATH . 'students/');
define('MAX_FILE_SIZE', 5 * 1024 * 1024); // 5MB
define('ALLOWED_IMAGE_TYPES', ['image/jpeg', 'image/jpg', 'image/png']);
define('ALLOWED_IMAGE_EXTENSIONS', ['jpg', 'jpeg', 'png']);

// Pagination
define('ITEMS_PER_PAGE', 50);

// Session timeout (in seconds)
define('SESSION_TIMEOUT', 1800); // 30 minutes

// Package settings
define('EXPIRY_WARNING_DAYS', 7); // Days before expiry to show warning
define('PACKAGE_DURATION_DAYS', 30); // Default package duration

// Currency
define('CURRENCY', 'RMB');
define('CURRENCY_SYMBOL', '¥');

// Date format
define('DATE_FORMAT', 'Y-m-d');
define('DATETIME_FORMAT', 'Y-m-d H:i:s');
define('DISPLAY_DATE_FORMAT', 'd M Y');
define('DISPLAY_DATETIME_FORMAT', 'd M Y H:i');

// Error reporting (set to 0 in production)
if (defined('ENVIRONMENT') && ENVIRONMENT === 'production') {
    error_reporting(0);
    ini_set('display_errors', 0);
} else {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
}

// Error log file
define('ERROR_LOG_FILE', BASE_PATH . 'logs/error_log.txt');

// Create necessary directories if they don't exist
$directories = [
    UPLOAD_PATH,
    STUDENT_PHOTO_PATH,
    BASE_PATH . 'logs/'
];

foreach ($directories as $dir) {
    if (!is_dir($dir)) {
        mkdir($dir, 0755, true);
    }
}

// Security headers
header('X-Frame-Options: DENY');
header('X-Content-Type-Options: nosniff');
header('X-XSS-Protection: 1; mode=block');
header('Referrer-Policy: strict-origin-when-cross-origin');

// CSRF Token functions
function generateCSRFToken() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function verifyCSRFToken($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

// Package type IDs (from database)
define('PACKAGE_FULL', 1);           // 500 RMB - 2 meals
define('PACKAGE_SINGLE_BRUNCH', 2);  // 280 RMB - Brunch
define('PACKAGE_SINGLE_DINNER', 3);  // 280 RMB - Dinner

// Package prices
define('PRICE_FULL_PACKAGE', 500.00);
define('PRICE_SINGLE_PACKAGE', 280.00);

// User roles
define('ROLE_ADMIN', 'admin');
define('ROLE_STAFF', 'staff');

// Status types
define('STATUS_ACTIVE', 'active');
define('STATUS_INACTIVE', 'inactive');
define('STATUS_EXPIRED', 'expired');
define('STATUS_CANCELLED', 'cancelled');

// Contact information
define('CONTACT_EMAIL', 'canteen@yangtze.edu.cn');
define('CONTACT_PHONE', '+86 123 4567 8900');

?>
