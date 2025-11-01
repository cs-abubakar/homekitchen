<?php
/**
 * Expiring Packages Alert
 * Canteen Management System - Yangtze University
 */

require_once '../auth/check_auth.php';

$pageTitle = 'Expiring Packages';

// Fetch expiring packages
try {
    $db = getDB();

    $stmt = $db->query("
        SELECT * FROM v_expiring_packages
        ORDER BY days_remaining ASC
    ");
    $expiringPackages = $stmt->fetchAll();

} catch (PDOException $e) {
    error_log("Expiring packages error: " . $e->getMessage());
    $expiringPackages = [];
}

require_once '../includes/header.php';
require_once '../includes/navbar.php';
?>

<!-- Page Header -->
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pb-2 mb-3 border-bottom">
    <h1 class="h2">
        <i class="bi bi-exclamation-triangle text-danger"></i> Expiring Packages
    </h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <div class="btn-group me-2">
            <button type="button" class="btn btn-success" onclick="window.print()">
                <i class="bi bi-printer"></i> Print
            </button>
            <button type="button" class="btn btn-info" onclick="exportToExcel()">
                <i class="bi bi-file-earmark-excel"></i> Export to Excel
            </button>
        </div>
        <a href="list.php" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i> Back to List
        </a>
    </div>
</div>

<!-- Alert Box -->
<div class="alert alert-danger shadow" role="alert">
    <h4 class="alert-heading">
        <i class="bi bi-exclamation-triangle-fill"></i> Urgent Action Required!
    </h4>
    <p>
        The following <strong><?php echo count($expiringPackages); ?></strong> package(s) are expiring within the next 7 days.
        Please contact these students immediately to renew their packages.
    </p>
    <hr>
    <p class="mb-0">
        <i class="bi bi-info-circle"></i>
        Students without active packages will not be able to access canteen meals after the expiry date.
    </p>
</div>

<?php if (!empty($expiringPackages)): ?>
<!-- Expiring Packages Table -->
<div class="card shadow">
    <div class="card-header bg-danger text-white">
        <h5 class="mb-0">
            <i class="bi bi-clock-history"></i> Packages Expiring Soon (<?php echo count($expiringPackages); ?>)
        </h5>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table id="expiringTable" class="table table-hover">
                <thead>
                    <tr>
                        <th>Days Left</th>
                        <th>Package #</th>
                        <th>Student Name</th>
                        <th>Passport</th>
                        <th>WeChat ID</th>
                        <th>Phone</th>
                        <th>Package Type</th>
                        <th>End Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($expiringPackages as $pkg): ?>
                    <tr class="<?php echo ($pkg['days_remaining'] <= 3) ? 'table-danger' : 'table-warning'; ?>">
                        <td>
                            <span class="badge <?php echo ($pkg['days_remaining'] <= 3) ? 'bg-danger' : 'bg-warning'; ?> fs-6">
                                <?php echo $pkg['days_remaining']; ?> day(s)
                            </span>
                        </td>
                        <td>
                            <strong><?php echo htmlspecialchars($pkg['package_number']); ?></strong>
                        </td>
                        <td>
                            <strong><?php echo htmlspecialchars($pkg['student_name']); ?></strong>
                        </td>
                        <td><?php echo htmlspecialchars($pkg['passport_number']); ?></td>
                        <td>
                            <?php if (!empty($pkg['wechat_id'])): ?>
                                <a href="weixin://dl/chat?<?php echo htmlspecialchars($pkg['wechat_id']); ?>" target="_blank">
                                    <?php echo htmlspecialchars($pkg['wechat_id']); ?>
                                </a>
                            <?php else: ?>
                                <span class="text-muted">N/A</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if (!empty($pkg['phone'])): ?>
                                <a href="tel:<?php echo htmlspecialchars($pkg['phone']); ?>">
                                    <?php echo htmlspecialchars($pkg['phone']); ?>
                                </a>
                            <?php else: ?>
                                <span class="text-muted">N/A</span>
                            <?php endif; ?>
                        </td>
                        <td><?php echo htmlspecialchars($pkg['package_type']); ?></td>
                        <td>
                            <span class="text-danger">
                                <strong><?php echo formatDate($pkg['end_date']); ?></strong>
                            </span>
                        </td>
                        <td>
                            <div class="btn-group btn-group-sm" role="group">
                                <a href="<?php echo BASE_URL; ?>students/view.php?id=<?php echo $pkg['student_id']; ?>"
                                   class="btn btn-info" title="View Student">
                                    <i class="bi bi-eye"></i> View
                                </a>
                                <a href="renew.php?package_id=<?php echo $pkg['id']; ?>"
                                   class="btn btn-success" title="Renew Package">
                                    <i class="bi bi-arrow-repeat"></i> Renew
                                </a>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Contact Instructions -->
<div class="card mt-3 shadow">
    <div class="card-header bg-info text-white">
        <h5 class="mb-0"><i class="bi bi-chat-dots"></i> Contact Instructions</h5>
    </div>
    <div class="card-body">
        <h6>How to Contact Students:</h6>
        <ul>
            <li><strong>WeChat:</strong> Click on the WeChat ID to open chat (if WeChat is installed)</li>
            <li><strong>Phone:</strong> Click on the phone number to make a call</li>
            <li><strong>Email:</strong> You can view full student details by clicking "View" button</li>
        </ul>
        <h6>Renewal Process:</h6>
        <ol>
            <li>Contact the student via WeChat or phone</li>
            <li>Confirm their intention to renew the package</li>
            <li>Collect payment and click the "Renew" button</li>
            <li>The system will automatically create a new package starting from the expiry date</li>
        </ol>
    </div>
</div>

<?php else: ?>
<!-- No Expiring Packages -->
<div class="card shadow">
    <div class="card-body text-center py-5">
        <i class="bi bi-check-circle text-success" style="font-size: 4rem;"></i>
        <h3 class="mt-3">Great News!</h3>
        <p class="text-muted">No packages are expiring in the next 7 days.</p>
        <a href="list.php" class="btn btn-primary mt-3">
            <i class="bi bi-box-seam"></i> View All Packages
        </a>
    </div>
</div>
<?php endif; ?>

<script>
// Initialize DataTable
$(document).ready(function() {
    $('#expiringTable').DataTable({
        "pageLength": 25,
        "order": [[0, "asc"]],
        "language": {
            "search": "Search:",
            "lengthMenu": "Show _MENU_ entries"
        }
    });
});

// Export to Excel function (simplified - in production use a library like PHPSpreadsheet)
function exportToExcel() {
    // Get table
    var table = document.getElementById('expiringTable');
    var html = table.outerHTML;

    // Create download
    var url = 'data:application/vnd.ms-excel,' + encodeURIComponent(html);
    var link = document.createElement('a');
    link.href = url;
    link.download = 'expiring_packages_' + new Date().toISOString().split('T')[0] + '.xls';
    link.click();
}
</script>

<?php require_once '../includes/footer.php'; ?>
