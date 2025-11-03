<?php
/**
 * Initialization Script
 * Auto-detects environment and sets up the application
 * Canteen Management System - Yangtze University
 */

// Prevent direct access
if (!defined('INIT_LOADED')) {
    define('INIT_LOADED', true);
}

// Detect if running on PHP built-in server
$isBuiltInServer = php_sapi_name() === 'cli-server';

// Detect macOS
$isMacOS = PHP_OS === 'Darwin';

// Auto-detect BASE_URL
if ($isBuiltInServer) {
    // Running with php -S
    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost:8000';
    $baseUrl = $protocol . '://' . $host . '/';
} else {
    // Running on Apache/Nginx
    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    $scriptName = dirname($_SERVER['SCRIPT_NAME']);
    $scriptName = $scriptName === '/' ? '' : $scriptName;
    $baseUrl = $protocol . '://' . $host . $scriptName . '/';
}

define('BASE_URL', $baseUrl);
define('BASE_PATH', __DIR__ . '/');
define('IS_MACOS', $isMacOS);
define('IS_BUILTIN_SERVER', $isBuiltInServer);

// Setup directories with proper permissions for macOS
$directories = [
    BASE_PATH . 'tmp',
    BASE_PATH . 'tmp/sessions',
    BASE_PATH . 'uploads',
    BASE_PATH . 'uploads/students',
    BASE_PATH . 'logs',
];

foreach ($directories as $dir) {
    if (!is_dir($dir)) {
        @mkdir($dir, 0777, true);
        @chmod($dir, 0777); // Ensure writable on macOS
    }
}

// Configure session for macOS
$sessionPath = BASE_PATH . 'tmp/sessions';
if (is_dir($sessionPath) && is_writable($sessionPath)) {
    ini_set('session.save_path', $sessionPath);
}

// Configure session settings
ini_set('session.cookie_httponly', '1');
ini_set('session.use_only_cookies', '1');
ini_set('session.cookie_secure', '0'); // Set to 1 if using HTTPS
ini_set('session.cookie_samesite', 'Strict');
ini_set('session.gc_maxlifetime', '1800'); // 30 minutes

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Set timezone
date_default_timezone_set('Asia/Shanghai');

// Error reporting for development
if (!defined('ENVIRONMENT')) {
    define('ENVIRONMENT', 'development');
}

if (ENVIRONMENT === 'development') {
    error_reporting(E_ALL);
    ini_set('display_errors', '1');
    ini_set('display_startup_errors', '1');
} else {
    error_reporting(0);
    ini_set('display_errors', '0');
}

// Set error log
ini_set('error_log', BASE_PATH . 'logs/php_errors.log');

// Database configuration for macOS
if (IS_MACOS) {
    // Use 127.0.0.1 instead of localhost for macOS MySQL compatibility
    define('DB_HOST', '127.0.0.1');
} else {
    define('DB_HOST', 'localhost');
}

define('DB_NAME', 'canteen_management');
define('DB_USER', 'root');
define('DB_PASS', ''); // Set your MySQL password here if needed
define('DB_CHARSET', 'utf8mb4');

// Application constants
define('APP_NAME', 'Yangtze University Canteen Management System');
define('APP_SHORT_NAME', 'YUCMS');
define('APP_VERSION', '2.0.0');
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
define('EXPIRY_WARNING_DAYS', 7);
define('PACKAGE_DURATION_DAYS', 30);

// Currency
define('CURRENCY', 'RMB');
define('CURRENCY_SYMBOL', '¥');

// Date formats
define('DATE_FORMAT', 'Y-m-d');
define('DATETIME_FORMAT', 'Y-m-d H:i:s');
define('DISPLAY_DATE_FORMAT', 'd M Y');
define('DISPLAY_DATETIME_FORMAT', 'd M Y H:i');

// Package type IDs
define('PACKAGE_FULL', 1);
define('PACKAGE_SINGLE_BRUNCH', 2);
define('PACKAGE_SINGLE_DINNER', 3);

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

// Display environment info (only in development)
if (ENVIRONMENT === 'development' && !isset($_SESSION['init_info_shown'])) {
    $_SESSION['init_info_shown'] = true;
    error_log("=== Canteen Management System Initialized ===");
    error_log("Environment: " . (IS_MACOS ? 'macOS' : 'Other'));
    error_log("Server: " . (IS_BUILTIN_SERVER ? 'PHP Built-in' : 'Apache/Nginx'));
    error_log("BASE_URL: " . BASE_URL);
    error_log("DB_HOST: " . DB_HOST);
    error_log("Session Path: " . session_save_path());
    error_log("===========================================");
}
