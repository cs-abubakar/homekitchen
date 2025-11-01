<?php
/**
 * System Settings
 * Canteen Management System - Yangtze University
 */

require_once '../auth/check_auth.php';
requireAdmin(); // Only admins can access

$pageTitle = 'System Settings';

// Fetch current settings
try {
    $db = getDB();
    $stmt = $db->query("SELECT * FROM system_settings ORDER BY setting_key");
    $settings = $stmt->fetchAll();
} catch (PDOException $e) {
    error_log("System settings error: " . $e->getMessage());
    $settings = [];
}

require_once '../includes/header.php';
require_once '../includes/navbar.php';
?>

<!-- Page Header -->
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pb-2 mb-3 border-bottom">
    <h1 class="h2"><i class="bi bi-gear"></i> System Settings</h1>
</div>

<!-- Settings Display -->
<div class="row">
    <div class="col-lg-10 mx-auto">
        <div class="card shadow">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0"><i class="bi bi-sliders"></i> Application Settings</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th style="width: 30%;">Setting</th>
                                <th style="width: 40%;">Value</th>
                                <th>Description</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($settings as $setting): ?>
                            <tr>
                                <td><strong><?php echo htmlspecialchars($setting['setting_key']); ?></strong></td>
                                <td>
                                    <code><?php echo htmlspecialchars($setting['setting_value'] ?? 'N/A'); ?></code>
                                </td>
                                <td class="text-muted">
                                    <small><?php echo htmlspecialchars($setting['description'] ?? ''); ?></small>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <div class="alert alert-info mt-4">
                    <i class="bi bi-info-circle"></i>
                    <strong>Note:</strong> To modify these settings, update the values directly in the database
                    or edit the <code>config/config.php</code> file.
                </div>
            </div>
        </div>

        <!-- Package Types -->
        <div class="card shadow mt-4">
            <div class="card-header bg-success text-white">
                <h5 class="mb-0"><i class="bi bi-box-seam"></i> Package Types</h5>
            </div>
            <div class="card-body">
                <?php
                $packageTypes = getActivePackageTypes();
                ?>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Price</th>
                                <th>Meals/Day</th>
                                <th>Meal Times</th>
                                <th>Duration</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($packageTypes as $type): ?>
                            <tr>
                                <td><strong><?php echo htmlspecialchars($type['name']); ?></strong></td>
                                <td><?php echo formatCurrency($type['price']); ?></td>
                                <td><?php echo $type['meals_per_day']; ?></td>
                                <td><?php echo htmlspecialchars($type['meal_times']); ?></td>
                                <td><?php echo $type['duration_days']; ?> days</td>
                                <td><?php echo getStatusBadge($type['is_active'] ? 'active' : 'inactive'); ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- System Information -->
        <div class="card shadow mt-4">
            <div class="card-header bg-info text-white">
                <h5 class="mb-0"><i class="bi bi-info-circle"></i> System Information</h5>
            </div>
            <div class="card-body">
                <div class="row mb-2">
                    <div class="col-md-4"><strong>Application Name:</strong></div>
                    <div class="col-md-8"><?php echo APP_NAME; ?></div>
                </div>
                <div class="row mb-2">
                    <div class="col-md-4"><strong>Version:</strong></div>
                    <div class="col-md-8"><?php echo APP_VERSION; ?></div>
                </div>
                <div class="row mb-2">
                    <div class="col-md-4"><strong>PHP Version:</strong></div>
                    <div class="col-md-8"><?php echo phpversion(); ?></div>
                </div>
                <div class="row mb-2">
                    <div class="col-md-4"><strong>Server Software:</strong></div>
                    <div class="col-md-8"><?php echo $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown'; ?></div>
                </div>
                <div class="row mb-2">
                    <div class="col-md-4"><strong>Base URL:</strong></div>
                    <div class="col-md-8"><code><?php echo BASE_URL; ?></code></div>
                </div>
                <div class="row">
                    <div class="col-md-4"><strong>Timezone:</strong></div>
                    <div class="col-md-8"><?php echo date_default_timezone_get(); ?></div>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="card shadow mt-4">
            <div class="card-header bg-warning">
                <h5 class="mb-0"><i class="bi bi-tools"></i> Quick Actions</h5>
            </div>
            <div class="card-body">
                <div class="d-grid gap-2">
                    <a href="<?php echo BASE_URL; ?>cron/update_status.php?token=CHANGE_THIS_TOKEN_12345"
                       class="btn btn-primary" target="_blank">
                        <i class="bi bi-arrow-clockwise"></i> Run Package Status Update Manually
                    </a>
                    <button type="button" class="btn btn-secondary" onclick="window.print()">
                        <i class="bi bi-printer"></i> Print Settings
                    </button>
                </div>
                <div class="alert alert-warning mt-3 mb-0">
                    <i class="bi bi-exclamation-triangle"></i>
                    <strong>Security Note:</strong> Make sure to change the cron security token in
                    <code>cron/update_status.php</code> before using the manual update feature.
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
