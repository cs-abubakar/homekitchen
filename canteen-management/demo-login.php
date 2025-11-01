<?php
/**
 * Demo Login - Auto-login for demo mode
 */

// Override database to use demo mode
require_once 'config/database-demo.php';
require_once 'config/config.php';
require_once 'config/session.php';

// Auto-login demo user
$demoUser = [
    'id' => 1,
    'username' => 'admin',
    'full_name' => 'Demo Administrator',
    'email' => 'admin@demo.com',
    'role' => 'admin'
];

setUserSession($demoUser);

// Redirect to dashboard
header('Location: demo-dashboard.php');
exit;
?>
