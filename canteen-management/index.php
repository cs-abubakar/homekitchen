<?php
/**
 * Index Page - Redirect to Dashboard
 * Canteen Management System - Yangtze University
 *
 * Version 3.0.0 - No Authentication Required
 * System opens directly to dashboard
 */

require_once 'init.php';

// Redirect directly to dashboard (no login required in v3.0.0)
header('Location: ' . BASE_URL . 'dashboard/');
exit;
