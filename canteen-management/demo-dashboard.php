<?php
/**
 * Demo Dashboard
 */

require_once 'config/database-demo.php';
require_once 'config/config.php';
require_once 'config/session.php';
require_once 'includes/functions.php';

if (!isLoggedIn()) {
    header('Location: demo-login.php');
    exit;
}

$pageTitle = 'Dashboard - DEMO MODE';

// Mock metrics
$activePackages = 2;
$totalStudents = 3;
$fullPackages = 1;
$singlePackages = 1;
$expiringPackages = 1;
$revenueThisMonth = 1560.00;
$revenueAllTime = 3280.00;
$newStudentsThisMonth = 2;

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        .demo-banner {
            background: linear-gradient(135deg, #ffc107 0%, #ff9800 100%);
            color: #000;
            padding: 10px;
            text-align: center;
            font-weight: bold;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            z-index: 10000;
            box-shadow: 0 2px 10px rgba(0,0,0,0.2);
        }
        body {
            padding-top: 100px !important;
        }
    </style>
</head>
<body>
    <!-- Demo Banner -->
    <div class="demo-banner">
        <i class="bi bi-exclamation-triangle-fill"></i> DEMO MODE - Using Simulated Data (No Database Connection)
    </div>

    <!-- Top Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary fixed-top" style="top: 40px;">
        <div class="container-fluid">
            <a class="navbar-brand" href="#">
                <i class="bi bi-building"></i>
                <strong><?php echo APP_SHORT_NAME; ?></strong>
            </a>
            <div class="navbar-nav ms-auto">
                <span class="nav-link text-white">
                    <i class="bi bi-person-circle"></i> <?php echo getCurrentUserFullName(); ?> (Demo)
                </span>
            </div>
        </div>
    </nav>

    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <nav class="col-md-3 col-lg-2 d-md-block bg-light sidebar">
                <div class="position-sticky pt-3">
                    <ul class="nav flex-column">
                        <li class="nav-item">
                            <a class="nav-link active" href="demo-dashboard.php">
                                <i class="bi bi-speedometer2"></i> Dashboard
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="#" onclick="alert('Demo: This would show the students list page')">
                                <i class="bi bi-people"></i> Students
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="#" onclick="alert('Demo: This would show the packages list page')">
                                <i class="bi bi-box-seam"></i> Packages
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="#" onclick="alert('Demo: This would show the payments page')">
                                <i class="bi bi-cash-stack"></i> Payments
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="#" onclick="alert('Demo: This would show the reports page')">
                                <i class="bi bi-file-earmark-text"></i> Reports
                            </a>
                        </li>
                        <li class="nav-item mt-3">
                            <a class="nav-link text-danger" href="demo.php">
                                <i class="bi bi-box-arrow-left"></i> Exit Demo
                            </a>
                        </li>
                    </ul>
                </div>
            </nav>

            <!-- Main Content -->
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
                <div class="pt-3 pb-2 mb-3">
                    <!-- Page Header -->
                    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pb-2 mb-3 border-bottom">
                        <h1 class="h2"><i class="bi bi-speedometer2"></i> Dashboard</h1>
                        <div class="btn-toolbar">
                            <button type="button" class="btn btn-sm btn-outline-secondary">
                                <i class="bi bi-arrow-clockwise"></i> Refresh
                            </button>
                        </div>
                    </div>

                    <!-- Metrics Cards -->
                    <div class="row mb-4">
                        <div class="col-xl-3 col-md-6 mb-3">
                            <div class="card border-left-primary shadow h-100">
                                <div class="card-body">
                                    <div class="row align-items-center">
                                        <div class="col">
                                            <div class="text-xs text-primary text-uppercase mb-1">Active Packages</div>
                                            <div class="h3 mb-0 font-weight-bold"><?php echo $activePackages; ?></div>
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
                                    <div class="row align-items-center">
                                        <div class="col">
                                            <div class="text-xs text-success text-uppercase mb-1">Total Students</div>
                                            <div class="h3 mb-0 font-weight-bold"><?php echo $totalStudents; ?></div>
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
                                    <div class="row align-items-center">
                                        <div class="col">
                                            <div class="text-xs text-info text-uppercase mb-1">Revenue This Month</div>
                                            <div class="h3 mb-0 font-weight-bold">¥ <?php echo number_format($revenueThisMonth, 2); ?></div>
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
                                    <div class="row align-items-center">
                                        <div class="col">
                                            <div class="text-xs text-warning text-uppercase mb-1">Expiring This Week</div>
                                            <div class="h3 mb-0 font-weight-bold"><?php echo $expiringPackages; ?></div>
                                        </div>
                                        <div class="col-auto">
                                            <i class="bi bi-exclamation-triangle text-warning" style="font-size: 2rem;"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Expiring Packages Alert -->
                    <div class="alert alert-danger shadow" role="alert">
                        <h4 class="alert-heading">
                            <i class="bi bi-exclamation-triangle-fill"></i> Urgent: Packages Expiring Soon!
                        </h4>
                        <p>There is <strong>1</strong> package expiring within the next 7 days.</p>
                        <hr>
                        <table class="table table-sm table-hover mb-0">
                            <thead>
                                <tr>
                                    <th>Student</th>
                                    <th>Passport</th>
                                    <th>WeChat</th>
                                    <th>Package</th>
                                    <th>Days Left</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>Maria Garcia</td>
                                    <td>P23456789</td>
                                    <td>maria_wx</td>
                                    <td>Single Package (Brunch)</td>
                                    <td><span class="badge bg-danger">4 days</span></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Quick Actions -->
                    <div class="row mb-4">
                        <div class="col-lg-12">
                            <div class="card shadow">
                                <div class="card-header bg-secondary text-white">
                                    <h5 class="mb-0"><i class="bi bi-lightning"></i> Quick Actions</h5>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-3 mb-2">
                                            <button class="btn btn-primary w-100" onclick="alert('Demo: This would open Add Student form')">
                                                <i class="bi bi-person-plus"></i> Add New Student
                                            </button>
                                        </div>
                                        <div class="col-md-3 mb-2">
                                            <button class="btn btn-success w-100" onclick="alert('Demo: This would open Add Package form')">
                                                <i class="bi bi-box-seam"></i> Add New Package
                                            </button>
                                        </div>
                                        <div class="col-md-3 mb-2">
                                            <button class="btn btn-info w-100" onclick="alert('Demo: This would open Financial Reports')">
                                                <i class="bi bi-file-earmark-text"></i> View Reports
                                            </button>
                                        </div>
                                        <div class="col-md-3 mb-2">
                                            <button class="btn btn-warning w-100" onclick="alert('Demo: This would open Students List')">
                                                <i class="bi bi-people"></i> View All Students
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Sample Data Info -->
                    <div class="card shadow">
                        <div class="card-header bg-info text-white">
                            <h5 class="mb-0"><i class="bi bi-info-circle"></i> Demo Information</h5>
                        </div>
                        <div class="card-body">
                            <h6>This is a demo version showing:</h6>
                            <ul>
                                <li><strong>3 Sample Students:</strong> John Smith, Maria Garcia, Ahmed Hassan</li>
                                <li><strong>2 Active Packages:</strong> 1 Full Package, 1 Single Package</li>
                                <li><strong>1 Expiring Package:</strong> Maria Garcia's package (4 days left)</li>
                                <li><strong>Mock Financial Data:</strong> Sample revenue and payment records</li>
                            </ul>

                            <div class="alert alert-warning mb-0">
                                <strong><i class="bi bi-exclamation-triangle"></i> Note:</strong>
                                To see the full system with all features, deploy to a server with MySQL database and import the <code>sql/database.sql</code> file. See README.md for complete setup instructions.
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <footer class="bg-light text-center mt-5">
        <div class="p-3">
            &copy; <?php echo date('Y'); ?> <?php echo UNIVERSITY_NAME; ?>. All rights reserved. | <strong><?php echo APP_NAME; ?></strong> v<?php echo APP_VERSION; ?> | DEMO MODE
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
