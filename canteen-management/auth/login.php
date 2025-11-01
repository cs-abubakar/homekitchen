<?php
/**
 * Login Processing
 * Canteen Management System - Yangtze University
 */

require_once '../config/config.php';
require_once '../config/database.php';
require_once '../config/session.php';
require_once '../includes/functions.php';

// Check if form was submitted
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . BASE_URL . 'index.php');
    exit;
}

// Verify CSRF token
if (!isset($_POST['csrf_token']) || !verifyCSRFToken($_POST['csrf_token'])) {
    header('Location: ' . BASE_URL . 'index.php?error=invalid_request');
    exit;
}

// Get and sanitize input
$username = trim($_POST['username'] ?? '');
$password = $_POST['password'] ?? '';
$remember = isset($_POST['remember']);

// Validate input
if (empty($username) || empty($password)) {
    header('Location: ' . BASE_URL . 'index.php?error=invalid_credentials');
    exit;
}

try {
    $db = getDB();

    // Query user by username
    $stmt = $db->prepare("
        SELECT id, username, password, full_name, email, role, is_active
        FROM users
        WHERE username = :username
    ");
    $stmt->execute([':username' => $username]);
    $user = $stmt->fetch();

    // Check if user exists and is active
    if (!$user) {
        // User not found
        header('Location: ' . BASE_URL . 'index.php?error=invalid_credentials');
        exit;
    }

    if ($user['is_active'] != 1) {
        // User account is deactivated
        header('Location: ' . BASE_URL . 'index.php?error=account_disabled');
        exit;
    }

    // Verify password
    if (!password_verify($password, $user['password'])) {
        // Invalid password
        header('Location: ' . BASE_URL . 'index.php?error=invalid_credentials');
        exit;
    }

    // Login successful - set session
    setUserSession($user);

    // Update last login time
    $updateStmt = $db->prepare("UPDATE users SET last_login = NOW() WHERE id = :id");
    $updateStmt->execute([':id' => $user['id']]);

    // Log activity
    logActivity($user['id'], 'LOGIN', 'users', $user['id'], 'User logged in successfully');

    // Set remember me cookie if checked (optional - not implemented for security)
    // if ($remember) {
    //     // Implementation here
    // }

    // Check if there's a redirect URL
    $redirectUrl = $_SESSION['redirect_after_login'] ?? BASE_URL . 'dashboard/';
    unset($_SESSION['redirect_after_login']);

    // Redirect to dashboard or requested page
    header('Location: ' . $redirectUrl);
    exit;

} catch (PDOException $e) {
    error_log("Login error: " . $e->getMessage());
    header('Location: ' . BASE_URL . 'index.php?error=system_error');
    exit;
}
?>
