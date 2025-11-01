<?php
/**
 * Navigation Bar Template
 * Canteen Management System - Yangtze University
 */

// Get current page for active menu highlighting
$currentPage = basename($_SERVER['PHP_SELF']);
$currentDir = basename(dirname($_SERVER['PHP_SELF']));

// Get expiring packages count for notification
$expiringCount = 0;
try {
    $db = getDB();
    $stmt = $db->query("SELECT COUNT(*) as count FROM v_expiring_packages");
    $result = $stmt->fetch();
    $expiringCount = $result['count'];
} catch (Exception $e) {
    error_log("Error getting expiring packages count: " . $e->getMessage());
}
?>

<!-- Top Navbar -->
<nav class="navbar navbar-expand-lg navbar-dark bg-primary fixed-top">
    <div class="container-fluid">
        <a class="navbar-brand" href="<?php echo BASE_URL; ?>dashboard/">
            <i class="bi bi-building"></i>
            <strong><?php echo APP_SHORT_NAME; ?></strong>
        </a>

        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto">
                <!-- Notifications -->
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle position-relative" href="#" id="notificationsDropdown" role="button" data-bs-toggle="dropdown">
                        <i class="bi bi-bell"></i>
                        <?php if ($expiringCount > 0): ?>
                            <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                                <?php echo $expiringCount; ?>
                            </span>
                        <?php endif; ?>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end" style="width: 300px;">
                        <li><h6 class="dropdown-header">Notifications</h6></li>
                        <li><hr class="dropdown-divider"></li>
                        <?php if ($expiringCount > 0): ?>
                            <li>
                                <a class="dropdown-item" href="<?php echo BASE_URL; ?>packages/expiring.php">
                                    <i class="bi bi-exclamation-triangle text-warning"></i>
                                    <?php echo $expiringCount; ?> package(s) expiring soon
                                </a>
                            </li>
                        <?php else: ?>
                            <li><span class="dropdown-item text-muted">No new notifications</span></li>
                        <?php endif; ?>
                    </ul>
                </li>

                <!-- User Profile -->
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown">
                        <i class="bi bi-person-circle"></i>
                        <?php echo htmlspecialchars(getCurrentUserFullName() ?? getCurrentUsername()); ?>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li>
                            <a class="dropdown-item" href="<?php echo BASE_URL; ?>settings/profile.php">
                                <i class="bi bi-person"></i> My Profile
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item" href="<?php echo BASE_URL; ?>settings/system.php">
                                <i class="bi bi-gear"></i> Settings
                            </a>
                        </li>
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <a class="dropdown-item text-danger" href="<?php echo BASE_URL; ?>auth/logout.php">
                                <i class="bi bi-box-arrow-right"></i> Logout
                            </a>
                        </li>
                    </ul>
                </li>
            </ul>
        </div>
    </div>
</nav>

<!-- Sidebar and Main Content Wrapper -->
<div class="container-fluid" style="margin-top: 60px;">
    <div class="row">
        <!-- Sidebar -->
        <nav id="sidebar" class="col-md-3 col-lg-2 d-md-block bg-light sidebar collapse">
            <div class="position-sticky pt-3">
                <ul class="nav flex-column">
                    <!-- Dashboard -->
                    <li class="nav-item">
                        <a class="nav-link <?php echo ($currentDir === 'dashboard') ? 'active' : ''; ?>" href="<?php echo BASE_URL; ?>dashboard/">
                            <i class="bi bi-speedometer2"></i>
                            Dashboard
                        </a>
                    </li>

                    <!-- Students -->
                    <li class="nav-item">
                        <a class="nav-link <?php echo ($currentDir === 'students') ? 'active' : ''; ?>" href="#studentsSubmenu" data-bs-toggle="collapse">
                            <i class="bi bi-people"></i>
                            Students
                            <i class="bi bi-chevron-down float-end"></i>
                        </a>
                        <ul class="collapse nav flex-column ms-3 <?php echo ($currentDir === 'students') ? 'show' : ''; ?>" id="studentsSubmenu">
                            <li class="nav-item">
                                <a class="nav-link <?php echo ($currentPage === 'list.php' && $currentDir === 'students') ? 'active' : ''; ?>" href="<?php echo BASE_URL; ?>students/list.php">
                                    <i class="bi bi-list-ul"></i> All Students
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link <?php echo ($currentPage === 'add.php' && $currentDir === 'students') ? 'active' : ''; ?>" href="<?php echo BASE_URL; ?>students/add.php">
                                    <i class="bi bi-plus-circle"></i> Add New
                                </a>
                            </li>
                        </ul>
                    </li>

                    <!-- Packages -->
                    <li class="nav-item">
                        <a class="nav-link <?php echo ($currentDir === 'packages') ? 'active' : ''; ?>" href="#packagesSubmenu" data-bs-toggle="collapse">
                            <i class="bi bi-box-seam"></i>
                            Packages
                            <i class="bi bi-chevron-down float-end"></i>
                        </a>
                        <ul class="collapse nav flex-column ms-3 <?php echo ($currentDir === 'packages') ? 'show' : ''; ?>" id="packagesSubmenu">
                            <li class="nav-item">
                                <a class="nav-link <?php echo ($currentPage === 'list.php' && $currentDir === 'packages') ? 'active' : ''; ?>" href="<?php echo BASE_URL; ?>packages/list.php">
                                    <i class="bi bi-list-ul"></i> All Packages
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link <?php echo ($currentPage === 'add.php' && $currentDir === 'packages') ? 'active' : ''; ?>" href="<?php echo BASE_URL; ?>packages/add.php">
                                    <i class="bi bi-plus-circle"></i> Add New
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link <?php echo ($currentPage === 'expiring.php') ? 'active' : ''; ?>" href="<?php echo BASE_URL; ?>packages/expiring.php">
                                    <i class="bi bi-exclamation-triangle"></i> Expiring Soon
                                    <?php if ($expiringCount > 0): ?>
                                        <span class="badge bg-danger"><?php echo $expiringCount; ?></span>
                                    <?php endif; ?>
                                </a>
                            </li>
                        </ul>
                    </li>

                    <!-- Payments -->
                    <li class="nav-item">
                        <a class="nav-link <?php echo ($currentDir === 'payments') ? 'active' : ''; ?>" href="<?php echo BASE_URL; ?>payments/history.php">
                            <i class="bi bi-cash-stack"></i>
                            Payments
                        </a>
                    </li>

                    <!-- Reports -->
                    <li class="nav-item">
                        <a class="nav-link <?php echo ($currentDir === 'reports') ? 'active' : ''; ?>" href="#reportsSubmenu" data-bs-toggle="collapse">
                            <i class="bi bi-file-earmark-text"></i>
                            Reports
                            <i class="bi bi-chevron-down float-end"></i>
                        </a>
                        <ul class="collapse nav flex-column ms-3 <?php echo ($currentDir === 'reports') ? 'show' : ''; ?>" id="reportsSubmenu">
                            <li class="nav-item">
                                <a class="nav-link" href="<?php echo BASE_URL; ?>reports/students.php">
                                    <i class="bi bi-people"></i> Student Reports
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="<?php echo BASE_URL; ?>reports/packages.php">
                                    <i class="bi bi-box-seam"></i> Package Reports
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="<?php echo BASE_URL; ?>reports/financial.php">
                                    <i class="bi bi-graph-up"></i> Financial Reports
                                </a>
                            </li>
                        </ul>
                    </li>

                    <!-- Settings -->
                    <?php if (isAdmin()): ?>
                    <li class="nav-item">
                        <a class="nav-link <?php echo ($currentDir === 'settings') ? 'active' : ''; ?>" href="#settingsSubmenu" data-bs-toggle="collapse">
                            <i class="bi bi-gear"></i>
                            Settings
                            <i class="bi bi-chevron-down float-end"></i>
                        </a>
                        <ul class="collapse nav flex-column ms-3 <?php echo ($currentDir === 'settings') ? 'show' : ''; ?>" id="settingsSubmenu">
                            <li class="nav-item">
                                <a class="nav-link" href="<?php echo BASE_URL; ?>settings/profile.php">
                                    <i class="bi bi-person"></i> My Profile
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="<?php echo BASE_URL; ?>settings/users.php">
                                    <i class="bi bi-people"></i> Manage Users
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="<?php echo BASE_URL; ?>settings/system.php">
                                    <i class="bi bi-sliders"></i> System Settings
                                </a>
                            </li>
                        </ul>
                    </li>
                    <?php endif; ?>
                </ul>
            </div>
        </nav>

        <!-- Main Content -->
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
            <div class="pt-3 pb-2 mb-3">
