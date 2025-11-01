<?php
/**
 * Session Management
 * Canteen Management System - Yangtze University
 */

// Ensure session is started
if (session_status() === PHP_SESSION_NONE) {
    // Configure session settings for security
    ini_set('session.cookie_httponly', 1);
    ini_set('session.use_only_cookies', 1);
    ini_set('session.cookie_secure', 0); // Set to 1 if using HTTPS
    ini_set('session.cookie_samesite', 'Strict');

    session_start();
}

/**
 * Check if user is logged in
 * @return bool
 */
function isLoggedIn() {
    return isset($_SESSION['user_id']) && isset($_SESSION['username']);
}

/**
 * Check if user is admin
 * @return bool
 */
function isAdmin() {
    return isLoggedIn() && isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}

/**
 * Check if user is staff
 * @return bool
 */
function isStaff() {
    return isLoggedIn() && isset($_SESSION['role']) && $_SESSION['role'] === 'staff';
}

/**
 * Get current user ID
 * @return int|null
 */
function getCurrentUserId() {
    return $_SESSION['user_id'] ?? null;
}

/**
 * Get current username
 * @return string|null
 */
function getCurrentUsername() {
    return $_SESSION['username'] ?? null;
}

/**
 * Get current user full name
 * @return string|null
 */
function getCurrentUserFullName() {
    return $_SESSION['full_name'] ?? null;
}

/**
 * Get current user role
 * @return string|null
 */
function getCurrentUserRole() {
    return $_SESSION['role'] ?? null;
}

/**
 * Set user session data after successful login
 * @param array $userData User data from database
 */
function setUserSession($userData) {
    $_SESSION['user_id'] = $userData['id'];
    $_SESSION['username'] = $userData['username'];
    $_SESSION['full_name'] = $userData['full_name'];
    $_SESSION['role'] = $userData['role'];
    $_SESSION['email'] = $userData['email'] ?? '';
    $_SESSION['login_time'] = time();
    $_SESSION['last_activity'] = time();

    // Regenerate session ID for security
    session_regenerate_id(true);
}

/**
 * Check session timeout
 * @return bool True if session is valid, false if expired
 */
function checkSessionTimeout() {
    if (!isLoggedIn()) {
        return false;
    }

    $timeout = SESSION_TIMEOUT;
    $lastActivity = $_SESSION['last_activity'] ?? 0;

    if (time() - $lastActivity > $timeout) {
        // Session expired
        destroySession();
        return false;
    }

    // Update last activity time
    $_SESSION['last_activity'] = time();
    return true;
}

/**
 * Destroy user session (logout)
 */
function destroySession() {
    // Unset all session variables
    $_SESSION = array();

    // Delete session cookie
    if (isset($_COOKIE[session_name()])) {
        setcookie(session_name(), '', time() - 3600, '/');
    }

    // Destroy session
    session_destroy();
}

/**
 * Require authentication - redirect to login if not logged in
 * @param string $redirectTo URL to redirect after login
 */
function requireAuth($redirectTo = null) {
    if (!checkSessionTimeout()) {
        $redirect = $redirectTo ?? $_SERVER['REQUEST_URI'];
        $_SESSION['redirect_after_login'] = $redirect;
        header('Location: ' . BASE_URL . 'index.php?error=session_expired');
        exit;
    }
}

/**
 * Require admin role - show 403 if not admin
 */
function requireAdmin() {
    requireAuth();
    if (!isAdmin()) {
        header('HTTP/1.1 403 Forbidden');
        die('Access denied. Admin privileges required.');
    }
}

/**
 * Get user's IP address
 * @return string
 */
function getUserIP() {
    if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
        $ip = $_SERVER['HTTP_CLIENT_IP'];
    } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
    } else {
        $ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
    }
    return $ip;
}

/**
 * Set flash message
 * @param string $type Type of message (success, error, warning, info)
 * @param string $message The message text
 */
function setFlashMessage($type, $message) {
    $_SESSION['flash_message'] = [
        'type' => $type,
        'message' => $message
    ];
}

/**
 * Get and clear flash message
 * @return array|null Flash message array or null
 */
function getFlashMessage() {
    if (isset($_SESSION['flash_message'])) {
        $message = $_SESSION['flash_message'];
        unset($_SESSION['flash_message']);
        return $message;
    }
    return null;
}

/**
 * Check if there's a flash message
 * @return bool
 */
function hasFlashMessage() {
    return isset($_SESSION['flash_message']);
}

?>
