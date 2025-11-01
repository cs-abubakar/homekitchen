<?php
/**
 * Dashboard
 * Canteen Management System - Yangtze University
 */

require_once '../auth/check_auth.php';

$pageTitle = 'Dashboard';

// Fetch dashboard metrics
try {
    $db = getDB();

    // Total Active Packages
    $stmt = $db->query("SELECT COUNT(*) as count FROM packages WHERE status = 'active'");
    $activePackages = $stmt->fetch()['count'];

    // Total Students
    $stmt = $db->query("SELECT COUNT(*) as count FROM students WHERE is_active = 1");
    $totalStudents = $stmt->fetch()['count'];

    // Active Full Packages
    $stmt = $db->query("SELECT COUNT(*) as count FROM packages WHERE status = 'active' AND package_type_id = 1");
    $fullPackages = $stmt->fetch()['count'];

    // Active Single Packages
    $stmt = $db->query("SELECT COUNT(*) as count FROM packages WHERE status = 'active' AND package_type_id IN (2, 3)");
    $singlePackages = $stmt->fetch()['count'];

    // Expiring This Week
    $stmt = $db->query("SELECT COUNT(*) as count FROM v_expiring_packages");
    $expiringPackages = $stmt->fetch()['count'];

    // Total Revenue This Month
    $stmt = $db->query("
        SELECT COALESCE(SUM(amount), 0) as total
        FROM payments
        WHERE MONTH(payment_date) = MONTH(CURDATE())
        AND YEAR(payment_date) = YEAR(CURDATE())
    ");
    $revenueThisMonth = $stmt->fetch()['total'];

    // Total Revenue All Time
    $stmt = $db->query("SELECT COALESCE(SUM(amount), 0) as total FROM payments");
    $revenueAllTime = $stmt->fetch()['total'];

    // New Students This Month
    $stmt = $db->query("
        SELECT COUNT(*) as count
        FROM students
        WHERE MONTH(created_at) = MONTH(CURDATE())
        AND YEAR(created_at) = YEAR(CURDATE())
    ");
    $newStudentsThisMonth = $stmt->fetch()['count'];

    // Expiring Packages Details (for alert box)
    $stmt = $db->query("
        SELECT * FROM v_expiring_packages
        ORDER BY days_remaining ASC
        LIMIT 10
    ");
    $expiringPackagesList = $stmt->fetchAll();

    // Recent Activities
    $stmt = $db->query("
        SELECT al.*, u.full_name as user_name
        FROM activity_logs al
        LEFT JOIN users u ON al.user_id = u.id
        ORDER BY al.created_at DESC
        LIMIT 10
    ");
    $recentActivities = $stmt->fetchAll();

    // Monthly Revenue for Chart (last 6 months)
    $stmt = $db->query("
        SELECT
            DATE_FORMAT(payment_date, '%Y-%m') as month,
            SUM(amount) as total
        FROM payments
        WHERE payment_date >= DATE_SUB(CURDATE(), INTERVAL 6 MONTH)
        GROUP BY DATE_FORMAT(payment_date, '%Y-%m')
        ORDER BY month ASC
    ");
    $monthlyRevenue = $stmt->fetchAll();

    // Package Distribution for Chart
    $stmt = $db->query("
        SELECT
            pt.name,
            COUNT(*) as count
        FROM packages p
        JOIN package_types pt ON p.package_type_id = pt.id
        WHERE p.status = 'active'
        GROUP BY pt.name
    ");
    $packageDistribution = $stmt->fetchAll();

    // Students by Batch for Chart
    $stmt = $db->query("
        SELECT
            batch_year,
            COUNT(*) as count
        FROM students
        WHERE is_active = 1
        GROUP BY batch_year
        ORDER BY batch_year DESC
    ");
    $studentsByBatch = $stmt->fetchAll();

} catch (PDOException $e) {
    error_log("Dashboard error: " . $e->getMessage());
    $activePackages = $totalStudents = $fullPackages = $singlePackages = 0;
    $expiringPackages = $revenueThisMonth = $revenueAllTime = $newStudentsThisMonth = 0;
    $expiringPackagesList = $recentActivities = $monthlyRevenue = $packageDistribution = $studentsByBatch = [];
}

require_once '../includes/header.php';
require_once '../includes/navbar.php';
?>

<!-- Page Header -->
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pb-2 mb-3 border-bottom">
    <h1 class="h2"><i class="bi bi-speedometer2"></i> Dashboard</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <div class="btn-group me-2">
            <button type="button" class="btn btn-sm btn-outline-secondary" onclick="window.print()">
                <i class="bi bi-printer"></i> Print
            </button>
            <button type="button" class="btn btn-sm btn-outline-secondary" onclick="location.reload()">
                <i class="bi bi-arrow-clockwise"></i> Refresh
            </button>
        </div>
    </div>
</div>

<!-- Metrics Cards -->
<div class="row mb-4">
    <div class="col-xl-3 col-md-6 mb-3">
        <div class="card border-left-primary shadow h-100">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                            Active Packages
                        </div>
                        <div class="h3 mb-0 font-weight-bold text-gray-800"><?php echo $activePackages; ?></div>
                    </div>
                    <div class="col-auto">
                        <i class="bi bi-box-seam text-primary" style="font-size: 2rem;"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6 mb-3">
        <div class="card border-left-success shadow h-100">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                            Total Students
                        </div>
                        <div class="h3 mb-0 font-weight-bold text-gray-800"><?php echo $totalStudents; ?></div>
                    </div>
                    <div class="col-auto">
                        <i class="bi bi-people text-success" style="font-size: 2rem;"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6 mb-3">
        <div class="card border-left-info shadow h-100">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                            Revenue This Month
                        </div>
                        <div class="h3 mb-0 font-weight-bold text-gray-800"><?php echo formatCurrency($revenueThisMonth); ?></div>
                    </div>
                    <div class="col-auto">
                        <i class="bi bi-currency-dollar text-info" style="font-size: 2rem;"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6 mb-3">
        <div class="card border-left-warning shadow h-100">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                            Expiring This Week
                        </div>
                        <div class="h3 mb-0 font-weight-bold text-gray-800"><?php echo $expiringPackages; ?></div>
                    </div>
                    <div class="col-auto">
                        <i class="bi bi-exclamation-triangle text-warning" style="font-size: 2rem;"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Second Row of Metrics -->
<div class="row mb-4">
    <div class="col-xl-3 col-md-6 mb-3">
        <div class="card border-primary shadow h-100">
            <div class="card-body">
                <div class="text-center">
                    <h6 class="text-primary">Full Packages (500 RMB)</h6>
                    <h2 class="mb-0"><?php echo $fullPackages; ?></h2>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6 mb-3">
        <div class="card border-secondary shadow h-100">
            <div class="card-body">
                <div class="text-center">
                    <h6 class="text-secondary">Single Packages (280 RMB)</h6>
                    <h2 class="mb-0"><?php echo $singlePackages; ?></h2>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6 mb-3">
        <div class="card border-success shadow h-100">
            <div class="card-body">
                <div class="text-center">
                    <h6 class="text-success">Total Revenue</h6>
                    <h2 class="mb-0"><?php echo formatCurrency($revenueAllTime); ?></h2>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6 mb-3">
        <div class="card border-info shadow h-100">
            <div class="card-body">
                <div class="text-center">
                    <h6 class="text-info">New Students This Month</h6>
                    <h2 class="mb-0"><?php echo $newStudentsThisMonth; ?></h2>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Expiring Packages Alert -->
<?php if ($expiringPackages > 0): ?>
<div class="alert alert-danger alert-dismissible fade show shadow" role="alert">
    <h4 class="alert-heading">
        <i class="bi bi-exclamation-triangle-fill"></i> Urgent: Packages Expiring Soon!
    </h4>
    <p>There are <strong><?php echo $expiringPackages; ?></strong> package(s) expiring within the next 7 days. Please contact these students for renewal.</p>
    <hr>
    <div class="table-responsive">
        <table class="table table-sm table-hover mb-0">
            <thead>
                <tr>
                    <th>Student</th>
                    <th>Passport</th>
                    <th>WeChat</th>
                    <th>Phone</th>
                    <th>Package</th>
                    <th>Days Left</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach (array_slice($expiringPackagesList, 0, 5) as $pkg): ?>
                <tr>
                    <td><?php echo htmlspecialchars($pkg['student_name']); ?></td>
                    <td><?php echo htmlspecialchars($pkg['passport_number']); ?></td>
                    <td><?php echo htmlspecialchars($pkg['wechat_id'] ?? 'N/A'); ?></td>
                    <td><?php echo htmlspecialchars($pkg['phone'] ?? 'N/A'); ?></td>
                    <td><?php echo htmlspecialchars($pkg['package_type']); ?></td>
                    <td>
                        <span class="badge bg-danger"><?php echo $pkg['days_remaining']; ?> days</span>
                    </td>
                    <td>
                        <a href="<?php echo BASE_URL; ?>students/view.php?id=<?php echo $pkg['student_id']; ?>" class="btn btn-sm btn-primary">
                            View
                        </a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php if ($expiringPackages > 5): ?>
        <div class="text-center mt-3">
            <a href="<?php echo BASE_URL; ?>packages/expiring.php" class="btn btn-danger">
                View All <?php echo $expiringPackages; ?> Expiring Packages
            </a>
        </div>
    <?php endif; ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>

<!-- Charts Row -->
<div class="row mb-4">
    <!-- Monthly Revenue Chart -->
    <div class="col-lg-6 mb-3">
        <div class="card shadow">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0"><i class="bi bi-graph-up"></i> Monthly Revenue Trend</h5>
            </div>
            <div class="card-body">
                <canvas id="revenueChart" height="200"></canvas>
            </div>
        </div>
    </div>

    <!-- Package Distribution Chart -->
    <div class="col-lg-6 mb-3">
        <div class="card shadow">
            <div class="card-header bg-success text-white">
                <h5 class="mb-0"><i class="bi bi-pie-chart"></i> Package Distribution</h5>
            </div>
            <div class="card-body">
                <canvas id="packageChart" height="200"></canvas>
            </div>
        </div>
    </div>
</div>

<!-- Students by Batch Chart -->
<div class="row mb-4">
    <div class="col-lg-12 mb-3">
        <div class="card shadow">
            <div class="card-header bg-info text-white">
                <h5 class="mb-0"><i class="bi bi-bar-chart"></i> Students by Batch/Year</h5>
            </div>
            <div class="card-body">
                <canvas id="batchChart" height="80"></canvas>
            </div>
        </div>
    </div>
</div>

<!-- Quick Actions and Recent Activities -->
<div class="row mb-4">
    <!-- Quick Actions -->
    <div class="col-lg-4 mb-3">
        <div class="card shadow">
            <div class="card-header bg-secondary text-white">
                <h5 class="mb-0"><i class="bi bi-lightning"></i> Quick Actions</h5>
            </div>
            <div class="card-body">
                <div class="d-grid gap-2">
                    <a href="<?php echo BASE_URL; ?>students/add.php" class="btn btn-primary btn-lg">
                        <i class="bi bi-person-plus"></i> Add New Student
                    </a>
                    <a href="<?php echo BASE_URL; ?>packages/add.php" class="btn btn-success btn-lg">
                        <i class="bi bi-box-seam"></i> Add New Package
                    </a>
                    <a href="<?php echo BASE_URL; ?>reports/financial.php" class="btn btn-info btn-lg">
                        <i class="bi bi-file-earmark-text"></i> View Reports
                    </a>
                    <a href="<?php echo BASE_URL; ?>students/list.php" class="btn btn-warning btn-lg">
                        <i class="bi bi-people"></i> View All Students
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Activities -->
    <div class="col-lg-8 mb-3">
        <div class="card shadow">
            <div class="card-header bg-dark text-white">
                <h5 class="mb-0"><i class="bi bi-clock-history"></i> Recent Activities</h5>
            </div>
            <div class="card-body">
                <?php if (!empty($recentActivities)): ?>
                    <div class="table-responsive">
                        <table class="table table-sm table-hover mb-0">
                            <thead>
                                <tr>
                                    <th>User</th>
                                    <th>Action</th>
                                    <th>Description</th>
                                    <th>Time</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($recentActivities as $activity): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($activity['user_name'] ?? 'System'); ?></td>
                                    <td>
                                        <span class="badge bg-secondary"><?php echo htmlspecialchars($activity['action']); ?></span>
                                    </td>
                                    <td><?php echo htmlspecialchars($activity['description'] ?? 'N/A'); ?></td>
                                    <td class="text-muted">
                                        <small><?php echo formatDate($activity['created_at'], 'd M Y H:i'); ?></small>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <p class="text-muted text-center mb-0">No recent activities</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Chart.js Scripts -->
<script>
// Monthly Revenue Chart
const revenueCtx = document.getElementById('revenueChart').getContext('2d');
const revenueData = <?php echo json_encode($monthlyRevenue); ?>;
const revenueLabels = revenueData.map(item => item.month);
const revenueValues = revenueData.map(item => parseFloat(item.total));

new Chart(revenueCtx, {
    type: 'line',
    data: {
        labels: revenueLabels,
        datasets: [{
            label: 'Revenue (RMB)',
            data: revenueValues,
            borderColor: 'rgb(13, 110, 253)',
            backgroundColor: 'rgba(13, 110, 253, 0.1)',
            tension: 0.4,
            fill: true
        }]
    },
    options: {
        responsive: true,
        plugins: {
            legend: {
                display: true,
                position: 'top'
            }
        },
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

// Package Distribution Chart
const packageCtx = document.getElementById('packageChart').getContext('2d');
const packageData = <?php echo json_encode($packageDistribution); ?>;
const packageLabels = packageData.map(item => item.name);
const packageValues = packageData.map(item => parseInt(item.count));

new Chart(packageCtx, {
    type: 'pie',
    data: {
        labels: packageLabels,
        datasets: [{
            data: packageValues,
            backgroundColor: [
                'rgba(13, 110, 253, 0.8)',
                'rgba(25, 135, 84, 0.8)',
                'rgba(220, 53, 69, 0.8)',
                'rgba(255, 193, 7, 0.8)'
            ],
            borderWidth: 2,
            borderColor: '#fff'
        }]
    },
    options: {
        responsive: true,
        plugins: {
            legend: {
                display: true,
                position: 'right'
            }
        }
    }
});

// Students by Batch Chart
const batchCtx = document.getElementById('batchChart').getContext('2d');
const batchData = <?php echo json_encode($studentsByBatch); ?>;
const batchLabels = batchData.map(item => item.batch_year);
const batchValues = batchData.map(item => parseInt(item.count));

new Chart(batchCtx, {
    type: 'bar',
    data: {
        labels: batchLabels,
        datasets: [{
            label: 'Number of Students',
            data: batchValues,
            backgroundColor: 'rgba(13, 202, 240, 0.8)',
            borderColor: 'rgb(13, 202, 240)',
            borderWidth: 1
        }]
    },
    options: {
        responsive: true,
        plugins: {
            legend: {
                display: false
            }
        },
        scales: {
            y: {
                beginAtZero: true,
                ticks: {
                    stepSize: 1
                }
            }
        }
    }
});
</script>

<?php require_once '../includes/footer.php'; ?>
