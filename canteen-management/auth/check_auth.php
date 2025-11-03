<?php
/**
 * Authentication Check (DISABLED in v3.0.0)
 * Include this file at the top of protected pages
 *
 * Version 3.0.0 - No Authentication Required
 * This file now only includes necessary dependencies without any auth checks
 */

require_once __DIR__ . '/../init.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../includes/functions.php';

// No authentication check required in v3.0.0
// All pages are accessible without login
