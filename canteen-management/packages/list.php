<?php
/**
 * Packages List
 * Canteen Management System - Yangtze University
 */

require_once '../auth/check_auth.php';

$pageTitle = 'Packages List';

// Fetch packages with filters
try {
    $db = getDB();

    // Get filters
    $statusFilter = $_GET['status'] ?? '';
    $typeFilter = $_GET['type'] ?? '';
    $dateFrom = $_GET['date_from'] ?? '';
    $dateTo = $_GET['date_to'] ?? '';

    // Build query
    $query = "
        SELECT
            p.*,
            s.full_name as student_name,
            s.passport_number,
            s.wechat_id,
            pt.name as package_type_name,
            pt.price as package_price
        FROM packages p
        JOIN students s ON p.student_id = s.id
        JOIN package_types pt ON p.package_type_id = pt.id
        WHERE 1=1
    ";
    $params = [];

    if (!empty($statusFilter)) {
        $query .= " AND p.status = :status";
        $params[':status'] = $statusFilter;
    }

    if (!empty($typeFilter)) {
        $query .= " AND p.package_type_id = :type";
        $params[':type'] = $typeFilter;
    }

    if (!empty($dateFrom)) {
        $query .= " AND p.start_date >= :date_from";
        $params[':date_from'] = $dateFrom;
    }

    if (!empty($dateTo)) {
        $query .= " AND p.end_date <= :date_to";
        $params[':date_to'] = $dateTo;
    }

    $query .= " ORDER BY p.created_at DESC";

    $stmt = $db->prepare($query);
    $stmt->execute($params);
    $packages = $stmt->fetchAll();

    // Get package types for filter
    $packageTypes = getActivePackageTypes();

} catch (PDOException $e) {
    error_log("Packages list error: " . $e->getMessage());
    $packages = [];
    $packageTypes = [];
}

require_once '../includes/header.php';
require_once '../includes/navbar.php';
?>

<!-- Page Header -->
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pb-2 mb-3 border-bottom">
    <h1 class="h2"><i class="bi bi-box-seam"></i> Packages List</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <a href="add.php" class="btn btn-primary">
            <i class="bi bi-plus-circle"></i> Add New Package
        </a>
    </div>
</div>

<!-- Filters -->
<div class="card mb-3">
    <div class="card-body">
        <form method="GET" class="row g-3" id="filterForm">
            <div class="col-md-3">
                <label for="status" class="form-label">Status</label>
                <select class="form-select" id="status" name="status">
                    <option value="">All Status</option>
                    <option value="active" <?php echo ($statusFilter === 'active') ? 'selected' : ''; ?>>Active</option>
                    <option value="expired" <?php echo ($statusFilter === 'expired') ? 'selected' : ''; ?>>Expired</option>
                    <option value="cancelled" <?php echo ($statusFilter === 'cancelled') ? 'selected' : ''; ?>>Cancelled</option>
                </select>
            </div>

            <div class="col-md-3">
                <label for="type" class="form-label">Package Type</label>
                <select class="form-select" id="type" name="type">
                    <option value="">All Types</option>
                    <?php foreach ($packageTypes as $type): ?>
                        <option value="<?php echo $type['id']; ?>"
                            <?php echo ($typeFilter == $type['id']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($type['name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="col-md-2">
                <label for="date_from" class="form-label">From Date</label>
                <input type="date" class="form-control" id="date_from" name="date_from" value="<?php echo htmlspecialchars($dateFrom); ?>">
            </div>

            <div class="col-md-2">
                <label for="date_to" class="form-label">To Date</label>
                <input type="date" class="form-control" id="date_to" name="date_to" value="<?php echo htmlspecialchars($dateTo); ?>">
            </div>

            <div class="col-md-2 d-flex align-items-end">
                <button type="submit" class="btn btn-primary me-2">
                    <i class="bi bi-funnel"></i> Filter
                </button>
                <a href="list.php" class="btn btn-secondary">
                    <i class="bi bi-x-circle"></i> Clear
                </a>
            </div>
        </form>
    </div>
</div>

<!-- Packages Table -->
<div class="card shadow">
    <div class="card-header bg-primary text-white">
        <h5 class="mb-0"><i class="bi bi-table"></i> All Packages (<?php echo count($packages); ?>)</h5>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table id="packagesTable" class="table table-striped table-hover">
                <thead>
                    <tr>
                        <th>Package #</th>
                        <th>Student</th>
                        <th>Package Type</th>
                        <th>Start Date</th>
                        <th>End Date</th>
                        <th>Amount</th>
                        <th>Status</th>
                        <th>Days Left</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($packages as $pkg): ?>
                    <tr class="<?php echo ($pkg['status'] === 'active' && $pkg['remaining_days'] <= 7) ? 'table-warning' : ''; ?>">
                        <td>
                            <strong><?php echo htmlspecialchars($pkg['package_number']); ?></strong>
                        </td>
                        <td>
                            <a href="<?php echo BASE_URL; ?>students/view.php?id=<?php echo $pkg['student_id']; ?>">
                                <?php echo htmlspecialchars($pkg['student_name']); ?>
                            </a>
                            <br>
                            <small class="text-muted"><?php echo htmlspecialchars($pkg['passport_number']); ?></small>
                        </td>
                        <td><?php echo htmlspecialchars($pkg['package_type_name']); ?></td>
                        <td><?php echo formatDate($pkg['start_date']); ?></td>
                        <td><?php echo formatDate($pkg['end_date']); ?></td>
                        <td><?php echo formatCurrency($pkg['amount_paid']); ?></td>
                        <td>
                            <?php
                            $statusBadge = $pkg['status'];
                            if ($pkg['status'] === 'active' && $pkg['remaining_days'] <= 7) {
                                $statusBadge = 'expiring';
                            }
                            echo getStatusBadge($statusBadge);
                            ?>
                        </td>
                        <td>
                            <?php if ($pkg['status'] === 'active'): ?>
                                <span class="badge <?php echo ($pkg['remaining_days'] <= 7) ? 'bg-danger' : 'bg-success'; ?>">
                                    <?php echo $pkg['remaining_days']; ?> days
                                </span>
                            <?php else: ?>
                                <span class="text-muted">-</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <div class="btn-group btn-group-sm" role="group">
                                <a href="<?php echo BASE_URL; ?>students/view.php?id=<?php echo $pkg['student_id']; ?>"
                                   class="btn btn-info" title="View Student">
                                    <i class="bi bi-eye"></i>
                                </a>
                                <?php if ($pkg['status'] === 'active'): ?>
                                <a href="renew.php?package_id=<?php echo $pkg['id']; ?>"
                                   class="btn btn-success" title="Renew">
                                    <i class="bi bi-arrow-repeat"></i>
                                </a>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
// Initialize DataTable
$(document).ready(function() {
    $('#packagesTable').DataTable({
        "pageLength": 50,
        "order": [[0, "desc"]],
        "language": {
            "search": "Search packages:",
            "lengthMenu": "Show _MENU_ packages per page",
            "info": "Showing _START_ to _END_ of _TOTAL_ packages",
            "infoEmpty": "No packages found",
            "infoFiltered": "(filtered from _MAX_ total packages)"
        }
    });
});
</script>

<?php require_once '../includes/footer.php'; ?>
