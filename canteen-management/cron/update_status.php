<?php
/**
 * Automated Package Status Update Script
 * Run this script daily via cron job
 *
 * Cron job example (run daily at midnight):
 * 0 0 * * * /usr/bin/php /path/to/canteen-management/cron/update_status.php
 */

// Prevent direct browser access
if (php_sapi_name() !== 'cli') {
    // Allow execution via HTTP for shared hosting (with security token)
    $securityToken = $_GET['token'] ?? '';
    $expectedToken = 'CHANGE_THIS_TOKEN_12345'; // Change this to a random string

    if ($securityToken !== $expectedToken) {
        die('Unauthorized access');
    }
}

// Include required files
require_once dirname(__DIR__) . '/config/database.php';
require_once dirname(__DIR__) . '/config/config.php';

echo "===========================================\n";
echo "Package Status Update Script\n";
echo "Started at: " . date('Y-m-d H:i:s') . "\n";
echo "===========================================\n\n";

try {
    $db = getDB();

    // Call the stored procedure to update package statuses
    $stmt = $db->prepare("CALL update_package_statuses()");
    $stmt->execute();
    $result = $stmt->fetch();

    $updatedCount = $result['packages_updated'] ?? 0;

    echo "✓ Updated {$updatedCount} package(s)\n";

    // Get count of expired packages
    $expiredStmt = $db->query("SELECT COUNT(*) as count FROM packages WHERE status = 'expired'");
    $expiredCount = $expiredStmt->fetch()['count'];

    echo "✓ Total expired packages: {$expiredCount}\n";

    // Get count of expiring packages (within 7 days)
    $expiringStmt = $db->query("SELECT COUNT(*) as count FROM v_expiring_packages");
    $expiringCount = $expiringStmt->fetch()['count'];

    echo "✓ Packages expiring soon (7 days): {$expiringCount}\n";

    // Optional: Send email notifications for expiring packages
    if ($expiringCount > 0) {
        echo "\n⚠ WARNING: {$expiringCount} package(s) expiring soon!\n";

        // Fetch expiring package details
        $expiringPackages = $db->query("SELECT * FROM v_expiring_packages LIMIT 5")->fetchAll();

        echo "\nExpiring Packages:\n";
        foreach ($expiringPackages as $pkg) {
            echo "  - {$pkg['student_name']} ({$pkg['package_number']}) - {$pkg['days_remaining']} days left\n";
        }

        // TODO: Implement email notification here
        // sendExpiryNotifications($expiringPackages);
    }

    echo "\n===========================================\n";
    echo "Script completed successfully\n";
    echo "Completed at: " . date('Y-m-d H:i:s') . "\n";
    echo "===========================================\n";

} catch (PDOException $e) {
    echo "✗ Error: " . $e->getMessage() . "\n";
    error_log("Cron script error: " . $e->getMessage());
    exit(1);
}

exit(0);
?>
