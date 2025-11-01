<?php
/**
 * Payment History
 * Canteen Management System - Yangtze University
 */

require_once '../auth/check_auth.php';

$pageTitle = 'Payment History';

// Fetch payments with filters
try {
    $db = getDB();

    // Get filters
    $dateFrom = $_GET['date_from'] ?? '';
    $dateTo = $_GET['date_to'] ?? '';
    $searchTerm = $_GET['search'] ?? '';

    // Build query
    $query = "
        SELECT
            pay.*,
            s.full_name as student_name,
            s.passport_number,
            pkg.package_number,
            u.full_name as received_by_name
        FROM payments pay
        JOIN students s ON pay.student_id = s.id
        JOIN packages pkg ON pay.package_id = pkg.id
        LEFT JOIN users u ON pay.received_by = u.id
        WHERE 1=1
    ";
    $params = [];

    if (!empty($dateFrom)) {
        $query .= " AND pay.payment_date >= :date_from";
        $params[':date_from'] = $dateFrom;
    }

    if (!empty($dateTo)) {
        $query .= " AND pay.payment_date <= :date_to";
        $params[':date_to'] = $dateTo;
    }

    if (!empty($searchTerm)) {
        $query .= " AND (s.full_name LIKE :search OR pay.receipt_number LIKE :search OR pkg.package_number LIKE :search)";
        $params[':search'] = '%' . $searchTerm . '%';
    }

    $query .= " ORDER BY pay.payment_date DESC, pay.created_at DESC";

    $stmt = $db->prepare($query);
    $stmt->execute($params);
    $payments = $stmt->fetchAll();

    // Calculate totals
    $totalAmount = array_sum(array_column($payments, 'amount'));

} catch (PDOException $e) {
    error_log("Payment history error: " . $e->getMessage());
    $payments = [];
    $totalAmount = 0;
}

require_once '../includes/header.php';
require_once '../includes/navbar.php';
?>

<!-- Page Header -->
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pb-2 mb-3 border-bottom">
    <h1 class="h2"><i class="bi bi-cash-stack"></i> Payment History</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <button type="button" class="btn btn-success me-2" onclick="window.print()">
            <i class="bi bi-printer"></i> Print
        </button>
        <a href="<?php echo BASE_URL; ?>reports/financial.php" class="btn btn-primary">
            <i class="bi bi-graph-up"></i> Financial Reports
        </a>
    </div>
</div>

<!-- Filters -->
<div class="card mb-3">
    <div class="card-body">
        <form method="GET" class="row g-3" id="filterForm">
            <div class="col-md-3">
                <label for="date_from" class="form-label">From Date</label>
                <input type="date" class="form-control" id="date_from" name="date_from" value="<?php echo htmlspecialchars($dateFrom); ?>">
            </div>

            <div class="col-md-3">
                <label for="date_to" class="form-label">To Date</label>
                <input type="date" class="form-control" id="date_to" name="date_to" value="<?php echo htmlspecialchars($dateTo); ?>">
            </div>

            <div class="col-md-4">
                <label for="search" class="form-label">Search</label>
                <input type="text" class="form-control" id="search" name="search"
                       placeholder="Receipt #, Student name, Package #"
                       value="<?php echo htmlspecialchars($searchTerm); ?>">
            </div>

            <div class="col-md-2 d-flex align-items-end">
                <button type="submit" class="btn btn-primary me-2">
                    <i class="bi bi-funnel"></i> Filter
                </button>
                <a href="history.php" class="btn btn-secondary">
                    <i class="bi bi-x-circle"></i> Clear
                </a>
            </div>
        </form>
    </div>
</div>

<!-- Summary Cards -->
<div class="row mb-3">
    <div class="col-md-4">
        <div class="card shadow border-primary">
            <div class="card-body text-center">
                <h6 class="text-primary">Total Payments</h6>
                <h2><?php echo count($payments); ?></h2>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card shadow border-success">
            <div class="card-body text-center">
                <h6 class="text-success">Total Amount</h6>
                <h2><?php echo formatCurrency($totalAmount); ?></h2>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card shadow border-info">
            <div class="card-body text-center">
                <h6 class="text-info">Average Payment</h6>
                <h2><?php echo formatCurrency(count($payments) > 0 ? $totalAmount / count($payments) : 0); ?></h2>
            </div>
        </div>
    </div>
</div>

<!-- Payments Table -->
<div class="card shadow">
    <div class="card-header bg-primary text-white">
        <h5 class="mb-0"><i class="bi bi-table"></i> All Payments (<?php echo count($payments); ?>)</h5>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table id="paymentsTable" class="table table-striped table-hover">
                <thead>
                    <tr>
                        <th>Receipt #</th>
                        <th>Date</th>
                        <th>Student</th>
                        <th>Package #</th>
                        <th>Amount</th>
                        <th>Method</th>
                        <th>Received By</th>
                        <th>Notes</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($payments as $payment): ?>
                    <tr>
                        <td>
                            <strong><?php echo htmlspecialchars($payment['receipt_number']); ?></strong>
                        </td>
                        <td><?php echo formatDate($payment['payment_date']); ?></td>
                        <td>
                            <a href="<?php echo BASE_URL; ?>students/view.php?id=<?php echo $payment['student_id']; ?>">
                                <?php echo htmlspecialchars($payment['student_name']); ?>
                            </a>
                            <br>
                            <small class="text-muted"><?php echo htmlspecialchars($payment['passport_number']); ?></small>
                        </td>
                        <td><?php echo htmlspecialchars($payment['package_number']); ?></td>
                        <td>
                            <strong class="text-success"><?php echo formatCurrency($payment['amount']); ?></strong>
                        </td>
                        <td>
                            <span class="badge bg-info"><?php echo htmlspecialchars($payment['payment_method']); ?></span>
                        </td>
                        <td><?php echo htmlspecialchars($payment['received_by_name'] ?? 'N/A'); ?></td>
                        <td><?php echo htmlspecialchars($payment['notes'] ?? '-'); ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
                <tfoot>
                    <tr class="table-success">
                        <th colspan="4" class="text-end">Total:</th>
                        <th><strong><?php echo formatCurrency($totalAmount); ?></strong></th>
                        <th colspan="3"></th>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>

<script>
// Initialize DataTable
$(document).ready(function() {
    $('#paymentsTable').DataTable({
        "pageLength": 50,
        "order": [[1, "desc"]],
        "language": {
            "search": "Search payments:",
            "lengthMenu": "Show _MENU_ payments per page",
            "info": "Showing _START_ to _END_ of _TOTAL_ payments"
        }
    });
});
</script>

<?php require_once '../includes/footer.php'; ?>
