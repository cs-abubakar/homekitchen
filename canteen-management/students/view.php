<?php
/**
 * View Student Profile
 * Canteen Management System - Yangtze University
 */

require_once '../auth/check_auth.php';

$pageTitle = 'Student Profile';

// Get student ID
$studentId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($studentId === 0) {
    redirectWithMessage('list.php', 'error', 'Invalid student ID');
}

// Fetch student details
try {
    $db = getDB();

    // Get student info
    $student = getStudentById($studentId);

    if (!$student) {
        redirectWithMessage('list.php', 'error', 'Student not found');
    }

    // Get student's packages
    $stmt = $db->prepare("
        SELECT p.*, pt.name as package_name, pt.price, pt.meals_per_day
        FROM packages p
        JOIN package_types pt ON p.package_type_id = pt.id
        WHERE p.student_id = :student_id
        ORDER BY p.created_at DESC
    ");
    $stmt->execute([':student_id' => $studentId]);
    $packages = $stmt->fetchAll();

    // Get student's payments
    $stmt = $db->prepare("
        SELECT pay.*, pkg.package_number
        FROM payments pay
        JOIN packages pkg ON pay.package_id = pkg.id
        WHERE pay.student_id = :student_id
        ORDER BY pay.payment_date DESC
    ");
    $stmt->execute([':student_id' => $studentId]);
    $payments = $stmt->fetchAll();

    // Get active package
    $activePackage = getStudentActivePackage($studentId);

} catch (PDOException $e) {
    error_log("View student error: " . $e->getMessage());
    redirectWithMessage('list.php', 'error', 'Failed to load student details');
}

require_once '../includes/header.php';
require_once '../includes/navbar.php';
?>

<!-- Page Header -->
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pb-2 mb-3 border-bottom">
    <h1 class="h2"><i class="bi bi-person"></i> Student Profile</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <div class="btn-group me-2">
            <a href="edit.php?id=<?php echo $studentId; ?>" class="btn btn-warning">
                <i class="bi bi-pencil"></i> Edit
            </a>
            <a href="<?php echo BASE_URL; ?>packages/add.php?student_id=<?php echo $studentId; ?>" class="btn btn-success">
                <i class="bi bi-plus-circle"></i> Add Package
            </a>
            <button type="button" class="btn btn-info" onclick="window.print()">
                <i class="bi bi-printer"></i> Print
            </button>
        </div>
        <a href="list.php" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i> Back
        </a>
    </div>
</div>

<div class="row">
    <!-- Left Column - Student Info -->
    <div class="col-lg-4 mb-4">
        <!-- Basic Info Card -->
        <div class="card shadow mb-3">
            <div class="card-body text-center">
                <?php if (!empty($student['photo']) && file_exists(STUDENT_PHOTO_PATH . $student['photo'])): ?>
                    <img src="<?php echo BASE_URL; ?>uploads/students/<?php echo htmlspecialchars($student['photo']); ?>"
                         alt="<?php echo htmlspecialchars($student['full_name']); ?>"
                         class="rounded-circle mb-3" width="150" height="150" style="object-fit: cover;">
                <?php else: ?>
                    <div class="rounded-circle bg-secondary text-white mx-auto mb-3 d-inline-flex align-items-center justify-content-center"
                         style="width: 150px; height: 150px; font-size: 3rem;">
                        <?php echo strtoupper(substr($student['full_name'], 0, 1)); ?>
                    </div>
                <?php endif; ?>

                <h4><?php echo htmlspecialchars($student['full_name']); ?></h4>
                <p class="text-muted"><?php echo htmlspecialchars($student['passport_number']); ?></p>
                <?php echo getStatusBadge($student['is_active'] ? 'active' : 'inactive'); ?>
            </div>
        </div>

        <!-- Current Package Card -->
        <?php if ($activePackage): ?>
        <div class="card shadow mb-3 border-success">
            <div class="card-header bg-success text-white">
                <h6 class="mb-0"><i class="bi bi-box-seam"></i> Current Active Package</h6>
            </div>
            <div class="card-body">
                <h5><?php echo htmlspecialchars($activePackage['package_name']); ?></h5>
                <p class="mb-2">
                    <strong>Package #:</strong> <?php echo htmlspecialchars($activePackage['package_number']); ?>
                </p>
                <p class="mb-2">
                    <strong>Meals:</strong> <?php echo $activePackage['meals_per_day']; ?> meal(s) per day
                </p>
                <p class="mb-2">
                    <strong>Period:</strong><br>
                    <?php echo formatDate($activePackage['start_date']); ?> to <?php echo formatDate($activePackage['end_date']); ?>
                </p>
                <p class="mb-2">
                    <strong>Remaining Days:</strong>
                    <span class="badge <?php echo ($activePackage['remaining_days'] <= 7) ? 'bg-danger' : 'bg-success'; ?>">
                        <?php echo $activePackage['remaining_days']; ?> days
                    </span>
                </p>
                <div class="d-grid gap-2 mt-3">
                    <a href="<?php echo BASE_URL; ?>packages/renew.php?package_id=<?php echo $activePackage['id']; ?>" class="btn btn-sm btn-primary">
                        <i class="bi bi-arrow-repeat"></i> Renew Package
                    </a>
                </div>
            </div>
        </div>
        <?php else: ?>
        <div class="card shadow mb-3 border-warning">
            <div class="card-header bg-warning">
                <h6 class="mb-0"><i class="bi bi-exclamation-triangle"></i> No Active Package</h6>
            </div>
            <div class="card-body">
                <p class="text-muted">This student doesn't have an active package.</p>
                <a href="<?php echo BASE_URL; ?>packages/add.php?student_id=<?php echo $studentId; ?>" class="btn btn-success btn-sm">
                    <i class="bi bi-plus-circle"></i> Add Package
                </a>
            </div>
        </div>
        <?php endif; ?>

        <!-- Quick Stats -->
        <div class="card shadow">
            <div class="card-header bg-info text-white">
                <h6 class="mb-0"><i class="bi bi-graph-up"></i> Quick Stats</h6>
            </div>
            <div class="card-body">
                <div class="d-flex justify-content-between mb-2">
                    <span>Total Packages:</span>
                    <strong><?php echo count($packages); ?></strong>
                </div>
                <div class="d-flex justify-content-between mb-2">
                    <span>Total Payments:</span>
                    <strong><?php echo count($payments); ?></strong>
                </div>
                <div class="d-flex justify-content-between">
                    <span>Total Amount Paid:</span>
                    <strong><?php echo formatCurrency(array_sum(array_column($payments, 'amount'))); ?></strong>
                </div>
            </div>
        </div>
    </div>

    <!-- Right Column - Detailed Info -->
    <div class="col-lg-8">
        <!-- Personal Information -->
        <div class="card shadow mb-3">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0"><i class="bi bi-person-lines-fill"></i> Personal Information</h5>
            </div>
            <div class="card-body">
                <div class="row mb-2">
                    <div class="col-md-4"><strong>Full Name:</strong></div>
                    <div class="col-md-8"><?php echo htmlspecialchars($student['full_name']); ?></div>
                </div>
                <div class="row mb-2">
                    <div class="col-md-4"><strong>Passport Number:</strong></div>
                    <div class="col-md-8"><?php echo htmlspecialchars($student['passport_number']); ?></div>
                </div>
                <div class="row mb-2">
                    <div class="col-md-4"><strong>Email:</strong></div>
                    <div class="col-md-8"><?php echo htmlspecialchars($student['email'] ?? 'N/A'); ?></div>
                </div>
                <div class="row mb-2">
                    <div class="col-md-4"><strong>Phone:</strong></div>
                    <div class="col-md-8"><?php echo htmlspecialchars($student['phone'] ?? 'N/A'); ?></div>
                </div>
                <div class="row mb-2">
                    <div class="col-md-4"><strong>WeChat ID:</strong></div>
                    <div class="col-md-8"><?php echo htmlspecialchars($student['wechat_id'] ?? 'N/A'); ?></div>
                </div>
                <div class="row mb-2">
                    <div class="col-md-4"><strong>Batch/Year:</strong></div>
                    <div class="col-md-8"><?php echo htmlspecialchars($student['batch_year'] ?? 'N/A'); ?></div>
                </div>
                <div class="row">
                    <div class="col-md-4"><strong>Major:</strong></div>
                    <div class="col-md-8"><?php echo htmlspecialchars($student['major'] ?? 'N/A'); ?></div>
                </div>
            </div>
        </div>

        <!-- Address Information -->
        <div class="card shadow mb-3">
            <div class="card-header bg-secondary text-white">
                <h5 class="mb-0"><i class="bi bi-house"></i> Address Information</h5>
            </div>
            <div class="card-body">
                <div class="row mb-3">
                    <div class="col-md-4"><strong>Home Address:</strong></div>
                    <div class="col-md-8"><?php echo nl2br(htmlspecialchars($student['home_address'] ?? 'N/A')); ?></div>
                </div>
                <div class="row">
                    <div class="col-md-4"><strong>China Address:</strong></div>
                    <div class="col-md-8"><?php echo nl2br(htmlspecialchars($student['china_address'] ?? 'N/A')); ?></div>
                </div>
            </div>
        </div>

        <!-- Emergency Contact -->
        <div class="card shadow mb-3">
            <div class="card-header bg-danger text-white">
                <h5 class="mb-0"><i class="bi bi-telephone"></i> Emergency Contact</h5>
            </div>
            <div class="card-body">
                <div class="row mb-2">
                    <div class="col-md-4"><strong>Contact Name:</strong></div>
                    <div class="col-md-8"><?php echo htmlspecialchars($student['emergency_contact'] ?? 'N/A'); ?></div>
                </div>
                <div class="row">
                    <div class="col-md-4"><strong>Contact Phone:</strong></div>
                    <div class="col-md-8"><?php echo htmlspecialchars($student['emergency_phone'] ?? 'N/A'); ?></div>
                </div>
            </div>
        </div>

        <!-- Package History -->
        <div class="card shadow mb-3">
            <div class="card-header bg-success text-white">
                <h5 class="mb-0"><i class="bi bi-box-seam"></i> Package History</h5>
            </div>
            <div class="card-body">
                <?php if (!empty($packages)): ?>
                <div class="table-responsive">
                    <table class="table table-hover table-sm">
                        <thead>
                            <tr>
                                <th>Package #</th>
                                <th>Type</th>
                                <th>Period</th>
                                <th>Amount</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($packages as $pkg): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($pkg['package_number']); ?></td>
                                <td><?php echo htmlspecialchars($pkg['package_name']); ?></td>
                                <td>
                                    <?php echo formatDate($pkg['start_date']); ?> to
                                    <?php echo formatDate($pkg['end_date']); ?>
                                </td>
                                <td><?php echo formatCurrency($pkg['amount_paid']); ?></td>
                                <td><?php echo getStatusBadge($pkg['status']); ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php else: ?>
                    <p class="text-muted text-center mb-0">No packages found</p>
                <?php endif; ?>
            </div>
        </div>

        <!-- Payment History -->
        <div class="card shadow">
            <div class="card-header bg-info text-white">
                <h5 class="mb-0"><i class="bi bi-cash-stack"></i> Payment History</h5>
            </div>
            <div class="card-body">
                <?php if (!empty($payments)): ?>
                <div class="table-responsive">
                    <table class="table table-hover table-sm">
                        <thead>
                            <tr>
                                <th>Receipt #</th>
                                <th>Package #</th>
                                <th>Date</th>
                                <th>Amount</th>
                                <th>Method</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($payments as $payment): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($payment['receipt_number']); ?></td>
                                <td><?php echo htmlspecialchars($payment['package_number']); ?></td>
                                <td><?php echo formatDate($payment['payment_date']); ?></td>
                                <td><?php echo formatCurrency($payment['amount']); ?></td>
                                <td><?php echo htmlspecialchars($payment['payment_method']); ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php else: ?>
                    <p class="text-muted text-center mb-0">No payments found</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
