<?php
/**
 * DEMO MODE - Testing without MySQL
 * This is a special demo version that works without a database
 */

// Override the database configuration to use demo mode
define('DEMO_MODE', true);

// Include demo database
require_once 'config/database-demo.php';

// Include other configs
require_once 'config/config.php';
require_once 'config/session.php';

// Demo banner
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Demo Mode - <?php echo APP_SHORT_NAME; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
        }
        .demo-card {
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card demo-card">
                    <div class="card-header bg-primary text-white text-center py-4">
                        <h2><i class="bi bi-building"></i> <?php echo UNIVERSITY_NAME; ?></h2>
                        <h4>Canteen Management System</h4>
                        <span class="badge bg-warning text-dark">DEMO MODE</span>
                    </div>
                    <div class="card-body p-5">
                        <div class="alert alert-info">
                            <h5><i class="bi bi-info-circle"></i> Demo Mode Active</h5>
                            <p>This is a demonstration version that works without a MySQL database. It uses simulated data to show you how the system works.</p>
                        </div>

                        <h5 class="mb-3">Features You Can Test:</h5>
                        <ul class="list-unstyled">
                            <li class="mb-2"><i class="bi bi-check-circle-fill text-success"></i> <strong>Dashboard</strong> - View metrics and charts</li>
                            <li class="mb-2"><i class="bi bi-check-circle-fill text-success"></i> <strong>Student Management</strong> - View student list</li>
                            <li class="mb-2"><i class="bi bi-check-circle-fill text-success"></i> <strong>Package Management</strong> - View packages</li>
                            <li class="mb-2"><i class="bi bi-check-circle-fill text-success"></i> <strong>Payment History</strong> - View payments</li>
                            <li class="mb-2"><i class="bi bi-check-circle-fill text-success"></i> <strong>Financial Reports</strong> - View reports</li>
                        </ul>

                        <div class="alert alert-warning">
                            <strong><i class="bi bi-exclamation-triangle"></i> Note:</strong>
                            Data modifications (add/edit/delete) won't persist in demo mode. This is for viewing and testing the interface only.
                        </div>

                        <h5 class="mb-3 mt-4">Demo Credentials:</h5>
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <div class="card bg-light">
                                    <div class="card-body">
                                        <strong>Username:</strong> <code class="fs-5">admin</code><br>
                                        <strong>Password:</strong> <code class="fs-5">admin123</code>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="d-grid gap-2">
                            <a href="demo-login.php" class="btn btn-primary btn-lg">
                                <i class="bi bi-box-arrow-in-right"></i> Enter Demo Mode
                            </a>
                            <a href="README.md" class="btn btn-outline-secondary" target="_blank">
                                <i class="bi bi-book"></i> View Documentation
                            </a>
                        </div>

                        <div class="mt-4 text-center">
                            <p class="text-muted mb-2">
                                <small>For production use, deploy to a server with MySQL/MariaDB</small>
                            </p>
                            <p class="mb-0">
                                <small class="text-muted">© <?php echo date('Y'); ?> <?php echo UNIVERSITY_NAME; ?></small>
                            </p>
                        </div>
                    </div>
                </div>

                <div class="text-center mt-4">
                    <div class="card demo-card">
                        <div class="card-body">
                            <h6 class="mb-3">Sample Data Included:</h6>
                            <div class="row text-center">
                                <div class="col-3">
                                    <h3 class="text-primary">3</h3>
                                    <small>Students</small>
                                </div>
                                <div class="col-3">
                                    <h3 class="text-success">2</h3>
                                    <small>Packages</small>
                                </div>
                                <div class="col-3">
                                    <h3 class="text-info">2</h3>
                                    <small>Payments</small>
                                </div>
                                <div class="col-3">
                                    <h3 class="text-warning">1</h3>
                                    <small>Expiring</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
