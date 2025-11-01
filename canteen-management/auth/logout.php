<?php
/**
 * Logout Processing
 * Canteen Management System - Yangtze University
 */

require_once '../config/config.php';
require_once '../config/database.php';
require_once '../config/session.php';
require_once '../includes/functions.php';

// Log activity before destroying session
if (isLoggedIn()) {
    $userId = getCurrentUserId();
    logActivity($userId, 'LOGOUT', 'users', $userId, 'User logged out');
}

// Destroy session
destroySession();

// Redirect to login page
header('Location: ' . BASE_URL . 'index.php');
exit;
?>
