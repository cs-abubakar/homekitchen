<?php
/**
 * Session Management
 * Canteen Management System - Yangtze University
 *
 * Version 3.0.0 - No Authentication
 * Session is started in init.php
 * This file provides utility functions (no authentication required)
 */

/**
 * Stub functions for compatibility (v3.0.0 - no authentication)
 * These return dummy values since authentication is removed
 */
function isLoggedIn() {
    return true; // Always return true (no login required)
}

function isAdmin() {
    return true; // Everyone is admin (no authentication)
}

function isStaff() {
    return true; // Everyone is staff (no authentication)
}

function getCurrentUserId() {
    return 1; // Return default ID
}

function getCurrentUsername() {
    return 'System'; // Return default username
}

function getCurrentUserFullName() {
    return 'System User'; // Return default full name
}

function getCurrentUserRole() {
    return 'admin'; // Return default role
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
