<?php
/**
 * Financial Reports
 * Canteen Management System - Yangtze University
 */

require_once '../auth/check_auth.php';

$pageTitle = 'Financial Reports';

try {
    $db = getDB();

    // Get selected month/year or default to current
    $selectedMonth = $_GET['month'] ?? date('m');
    $selectedYear = $_GET['year'] ?? date('Y');

    // Monthly revenue for selected month
    $stmt = $db->prepare("
        SELECT
            SUM(amount) as total_revenue,
            COUNT(*) as total_payments
        FROM payments
        WHERE MONTH(payment_date) = :month
        AND YEAR(payment_date) = :year
    ");
    $stmt->execute([':month' => $selectedMonth, ':year' => $selectedYear]);
    $monthlyData = $stmt->fetch();

    // Daily breakdown for selected month
    $stmt = $db->prepare("
        SELECT
            payment_date,
            COUNT(*) as payment_count,
            SUM(amount) as daily_total
        FROM payments
        WHERE MONTH(payment_date) = :month
        AND YEAR(payment_date) = :year
        GROUP BY payment_date
        ORDER BY payment_date ASC
    ");
    $stmt->execute([':month' => $selectedMonth, ':year' => $selectedYear]);
    $dailyBreakdown = $stmt->fetchAll();

    // Revenue by package type for selected month
    $stmt = $db->prepare("
        SELECT
            pt.name as package_type,
            COUNT(*) as count,
            SUM(pay.amount) as total
        FROM payments pay
        JOIN packages pkg ON pay.package_id = pkg.id
        JOIN package_types pt ON pkg.package_type_id = pt.id
        WHERE MONTH(pay.payment_date) = :month
        AND YEAR(pay.payment_date) = :year
        GROUP BY pt.name
        ORDER BY total DESC
    ");
    $stmt->execute([':month' => $selectedMonth, ':year' => $selectedYear]);
    $revenueByType = $stmt->fetchAll();

    // Yearly summary
    $stmt = $db->prepare("
        SELECT
            SUM(amount) as year_total,
            COUNT(*) as year_payments
        FROM payments
        WHERE YEAR(payment_date) = :year
    ");
    $stmt->execute([':year' => $selectedYear]);
    $yearlyData = $stmt->fetch();

    // Monthly comparison for the year
    $stmt = $db->prepare("
        SELECT
            MONTH(payment_date) as month,
            COUNT(*) as payment_count,
            SUM(amount) as monthly_total
        FROM payments
        WHERE YEAR(payment_date) = :year
        GROUP BY MONTH(payment_date)
        ORDER BY month ASC
    ");
    $stmt->execute([':year' => $selectedYear]);
    $monthlyComparison = $stmt->fetchAll();

} catch (PDOException $e) {
    error_log("Financial reports error: " . $e->getMessage());
    $monthlyData = ['total_revenue' => 0, 'total_payments' => 0];
    $dailyBreakdown = [];
    $revenueByType = [];
    $yearlyData = ['year_total' => 0, 'year_payments' => 0];
    $monthlyComparison = [];
}

require_once '../includes/header.php';
require_once '../includes/navbar.php';
?>

<!-- Page Header -->
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pb-2 mb-3 border-bottom">
    <h1 class="h2"><i class="bi bi-graph-up"></i> Financial Reports</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <button type="button" class="btn btn-success" onclick="window.print()">
            <i class="bi bi-printer"></i> Print Report
        </button>
    </div>
</div>

<!-- Report Filters -->
<div class="card mb-3">
    <div class="card-body">
        <form method="GET" class="row g-3">
            <div class="col-md-4">
                <label for="month" class="form-label">Select Month</label>
                <select class="form-select" id="month" name="month">
                    <?php for ($m = 1; $m <= 12; $m++): ?>
                        <option value="<?php echo str_pad($m, 2, '0', STR_PAD_LEFT); ?>"
                            <?php echo ($selectedMonth == str_pad($m, 2, '0', STR_PAD_LEFT)) ? 'selected' : ''; ?>>
                            <?php echo date('F', mktime(0, 0, 0, $m, 1)); ?>
                        </option>
                    <?php endfor; ?>
                </select>
            </div>

            <div class="col-md-4">
                <label for="year" class="form-label">Select Year</label>
                <select class="form-select" id="year" name="year">
                    <?php for ($y = date('Y'); $y >= 2023; $y--): ?>
                        <option value="<?php echo $y; ?>" <?php echo ($selectedYear == $y) ? 'selected' : ''; ?>>
                            <?php echo $y; ?>
                        </option>
                    <?php endfor; ?>
                </select>
            </div>

            <div class="col-md-4 d-flex align-items-end">
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-search"></i> Generate Report
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Summary Cards -->
<h4 class="mb-3">Monthly Summary - <?php echo date('F Y', mktime(0, 0, 0, $selectedMonth, 1, $selectedYear)); ?></h4>
<div class="row mb-4">
    <div class="col-md-6">
        <div class="card shadow border-success">
            <div class="card-body text-center">
                <h6 class="text-success">Total Revenue</h6>
                <h2 class="text-success"><?php echo formatCurrency($monthlyData['total_revenue']); ?></h2>
                <p class="text-muted mb-0">From <?php echo $monthlyData['total_payments']; ?> payment(s)</p>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card shadow border-primary">
            <div class="card-body text-center">
                <h6 class="text-primary">Average Payment</h6>
                <h2 class="text-primary">
                    <?php echo formatCurrency($monthlyData['total_payments'] > 0 ? $monthlyData['total_revenue'] / $monthlyData['total_payments'] : 0); ?>
                </h2>
                <p class="text-muted mb-0">Per transaction</p>
            </div>
        </div>
    </div>
</div>

<!-- Revenue by Package Type -->
<div class="row mb-4">
    <div class="col-lg-6">
        <div class="card shadow">
            <div class="card-header bg-info text-white">
                <h5 class="mb-0"><i class="bi bi-pie-chart"></i> Revenue by Package Type</h5>
            </div>
            <div class="card-body">
                <?php if (!empty($revenueByType)): ?>
                <canvas id="packageTypeChart" height="200"></canvas>
                <?php else: ?>
                <p class="text-center text-muted my-5">No data available for this period</p>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="col-lg-6">
        <div class="card shadow">
            <div class="card-header bg-success text-white">
                <h5 class="mb-0"><i class="bi bi-table"></i> Package Type Breakdown</h5>
            </div>
            <div class="card-body">
                <?php if (!empty($revenueByType)): ?>
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th>Package Type</th>
                            <th>Count</th>
                            <th>Revenue</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($revenueByType as $type): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($type['package_type']); ?></td>
                            <td><?php echo $type['count']; ?></td>
                            <td><strong><?php echo formatCurrency($type['total']); ?></strong></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <?php else: ?>
                <p class="text-center text-muted my-5">No data available</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Daily Breakdown -->
<div class="card shadow mb-4">
    <div class="card-header bg-primary text-white">
        <h5 class="mb-0"><i class="bi bi-calendar-day"></i> Daily Breakdown</h5>
    </div>
    <div class="card-body">
        <?php if (!empty($dailyBreakdown)): ?>
        <div class="table-responsive">
            <table class="table table-striped table-hover">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Payments</th>
                        <th>Total Revenue</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($dailyBreakdown as $day): ?>
                    <tr>
                        <td><?php echo formatDate($day['payment_date']); ?></td>
                        <td><?php echo $day['payment_count']; ?></td>
                        <td><strong><?php echo formatCurrency($day['daily_total']); ?></strong></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
                <tfoot class="table-success">
                    <tr>
                        <th>Total</th>
                        <th><?php echo $monthlyData['total_payments']; ?></th>
                        <th><strong><?php echo formatCurrency($monthlyData['total_revenue']); ?></strong></th>
                    </tr>
                </tfoot>
            </table>
        </div>
        <?php else: ?>
        <p class="text-center text-muted my-5">No payments recorded for this month</p>
        <?php endif; ?>
    </div>
</div>

<!-- Yearly Summary -->
<h4 class="mb-3">Yearly Summary - <?php echo $selectedYear; ?></h4>
<div class="row mb-4">
    <div class="col-md-12">
        <div class="card shadow">
            <div class="card-header bg-warning">
                <h5 class="mb-0"><i class="bi bi-bar-chart"></i> Monthly Comparison</h5>
            </div>
            <div class="card-body">
                <?php if (!empty($monthlyComparison)): ?>
                <canvas id="yearlyChart" height="80"></canvas>
                <div class="text-center mt-3">
                    <h5>Total Revenue for <?php echo $selectedYear; ?>: <strong class="text-success"><?php echo formatCurrency($yearlyData['year_total']); ?></strong></h5>
                    <p class="text-muted">From <?php echo $yearlyData['year_payments']; ?> payment(s)</p>
                </div>
                <?php else: ?>
                <p class="text-center text-muted my-5">No data available for this year</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script>
<?php if (!empty($revenueByType)): ?>
// Package Type Chart
const packageTypeCtx = document.getElementById('packageTypeChart').getContext('2d');
const packageTypeData = <?php echo json_encode($revenueByType); ?>;

new Chart(packageTypeCtx, {
    type: 'pie',
    data: {
        labels: packageTypeData.map(item => item.package_type),
        datasets: [{
            data: packageTypeData.map(item => parseFloat(item.total)),
            backgroundColor: [
                'rgba(13, 110, 253, 0.8)',
                'rgba(25, 135, 84, 0.8)',
                'rgba(220, 53, 69, 0.8)',
                'rgba(255, 193, 7, 0.8)'
            ]
        }]
    },
    options: {
        responsive: true,
        plugins: {
            legend: {
                position: 'bottom'
            }
        }
    }
});
<?php endif; ?>

<?php if (!empty($monthlyComparison)): ?>
// Yearly Comparison Chart
const yearlyCtx = document.getElementById('yearlyChart').getContext('2d');
const yearlyData = <?php echo json_encode($monthlyComparison); ?>;
const monthNames = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];

new Chart(yearlyCtx, {
    type: 'bar',
    data: {
        labels: yearlyData.map(item => monthNames[item.month - 1]),
        datasets: [{
            label: 'Revenue (RMB)',
            data: yearlyData.map(item => parseFloat(item.monthly_total)),
            backgroundColor: 'rgba(255, 193, 7, 0.8)',
            borderColor: 'rgb(255, 193, 7)',
            borderWidth: 1
        }]
    },
    options: {
        responsive: true,
        scales: {
            y: {
                beginAtZero: true,
                ticks: {
                    callback: function(value) {
                        return '¥ ' + value.toLocaleString();
                    }
                }
            }
        }
    }
});
<?php endif; ?>
</script>

<?php require_once '../includes/footer.php'; ?>
