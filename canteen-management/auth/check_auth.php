<?php
/**
 * Authentication Check
 * Include this file at the top of protected pages
 */

require_once __DIR__ . '/../init.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../includes/functions.php';

// Check if user is authenticated and session is valid
requireAuth();
