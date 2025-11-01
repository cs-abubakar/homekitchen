<?php
/**
 * Renew Package
 * Canteen Management System - Yangtze University
 */

require_once '../auth/check_auth.php';

$pageTitle = 'Renew Package';

// Get package ID
$packageId = isset($_GET['package_id']) ? (int)$_GET['package_id'] : 0;

if ($packageId === 0) {
    redirectWithMessage('list.php', 'error', 'Invalid package ID');
}

// Fetch package details
try {
    $db = getDB();

    $stmt = $db->prepare("
        SELECT p.*, s.full_name as student_name, s.passport_number,
               pt.name as package_type_name, pt.price, pt.duration_days, pt.id as package_type_id
        FROM packages p
        JOIN students s ON p.student_id = s.id
        JOIN package_types pt ON p.package_type_id = pt.id
        WHERE p.id = :id
    ");
    $stmt->execute([':id' => $packageId]);
    $package = $stmt->fetch();

    if (!$package) {
        redirectWithMessage('list.php', 'error', 'Package not found');
    }

    // Get all package types for selection
    $packageTypes = getActivePackageTypes();

} catch (PDOException $e) {
    error_log("Renew package error: " . $e->getMessage());
    redirectWithMessage('list.php', 'error', 'Failed to load package details');
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verify CSRF token
    if (!isset($_POST['csrf_token']) || !verifyCSRFToken($_POST['csrf_token'])) {
        redirectWithMessage('renew.php?package_id=' . $packageId, 'error', 'Invalid request');
    }

    // Get form data
    $new_package_type_id = (int)$_POST['package_type_id'];
    $start_date = $_POST['start_date'];
    $amount_paid = (float)$_POST['amount_paid'];
    $payment_date = $_POST['payment_date'];
    $notes = sanitize($_POST['notes'] ?? '');

    try {
        $db->beginTransaction();

        // Get new package type details
        $newPackageType = getPackageType($new_package_type_id);
        if (!$newPackageType) {
            throw new Exception('Invalid package type');
        }

        // Calculate new end date
        $startDateObj = new DateTime($start_date);
        $endDateObj = clone $startDateObj;
        $endDateObj->modify('+' . $newPackageType['duration_days'] . ' days');
        $end_date = $endDateObj->format('Y-m-d');

        // Generate new package number
        $new_package_number = generatePackageNumber();

        // Insert new package
        $stmt = $db->prepare("
            INSERT INTO packages (
                package_number, student_id, package_type_id, start_date, end_date,
                amount_paid, payment_date, status, remaining_days, notes, created_by
            ) VALUES (
                :package_number, :student_id, :package_type_id, :start_date, :end_date,
                :amount_paid, :payment_date, 'active', :remaining_days, :notes, :created_by
            )
        ");

        $remaining_days = calculateRemainingDays($end_date);

        $stmt->execute([
            ':package_number' => $new_package_number,
            ':student_id' => $package['student_id'],
            ':package_type_id' => $new_package_type_id,
            ':start_date' => $start_date,
            ':end_date' => $end_date,
            ':amount_paid' => $amount_paid,
            ':payment_date' => $payment_date,
            ':remaining_days' => $remaining_days,
            ':notes' => $notes . ' (Renewal of ' . $package['package_number'] . ')',
            ':created_by' => getCurrentUserId()
        ]);

        $new_package_id = $db->lastInsertId();

        // Update old package status to expired
        $updateStmt = $db->prepare("UPDATE packages SET status = 'expired' WHERE id = :id");
        $updateStmt->execute([':id' => $packageId]);

        // Generate receipt number
        $receipt_number = generateReceiptNumber();

        // Insert payment record
        $paymentStmt = $db->prepare("
            INSERT INTO payments (
                receipt_number, package_id, student_id, amount, payment_date,
                payment_method, received_by, notes
            ) VALUES (
                :receipt_number, :package_id, :student_id, :amount, :payment_date,
                'Cash', :received_by, :notes
            )
        ");

        $paymentStmt->execute([
            ':receipt_number' => $receipt_number,
            ':package_id' => $new_package_id,
            ':student_id' => $package['student_id'],
            ':amount' => $amount_paid,
            ':payment_date' => $payment_date,
            ':received_by' => getCurrentUserId(),
            ':notes' => 'Renewal - Package: ' . $new_package_number
        ]);

        // Log activity
        logActivity(
            getCurrentUserId(),
            'RENEW',
            'packages',
            $new_package_id,
            "Renewed package {$package['package_number']} -> {$new_package_number} for {$package['student_name']}"
        );

        $db->commit();

        redirectWithMessage(
            '../students/view.php?id=' . $package['student_id'],
            'success',
            "Package renewed successfully! New Package: {$new_package_number}, Receipt: {$receipt_number}"
        );

    } catch (Exception $e) {
        $db->rollback();
        error_log("Renew package error: " . $e->getMessage());
        redirectWithMessage('renew.php?package_id=' . $packageId, 'error', 'Failed to renew package: ' . $e->getMessage());
    }
}

// Calculate suggested start date (day after current package ends)
$suggestedStartDate = new DateTime($package['end_date']);
$suggestedStartDate->modify('+1 day');

require_once '../includes/header.php';
require_once '../includes/navbar.php';
?>

<!-- Page Header -->
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pb-2 mb-3 border-bottom">
    <h1 class="h2"><i class="bi bi-arrow-repeat"></i> Renew Package</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <a href="<?php echo BASE_URL; ?>students/view.php?id=<?php echo $package['student_id']; ?>" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i> Back to Student
        </a>
    </div>
</div>

<div class="row">
    <div class="col-lg-8 mx-auto">
        <!-- Current Package Info -->
        <div class="card shadow mb-3 border-info">
            <div class="card-header bg-info text-white">
                <h5 class="mb-0"><i class="bi bi-info-circle"></i> Current Package Details</h5>
            </div>
            <div class="card-body">
                <div class="row mb-2">
                    <div class="col-md-4"><strong>Student:</strong></div>
                    <div class="col-md-8"><?php echo htmlspecialchars($package['student_name']); ?></div>
                </div>
                <div class="row mb-2">
                    <div class="col-md-4"><strong>Passport:</strong></div>
                    <div class="col-md-8"><?php echo htmlspecialchars($package['passport_number']); ?></div>
                </div>
                <div class="row mb-2">
                    <div class="col-md-4"><strong>Package Number:</strong></div>
                    <div class="col-md-8"><strong><?php echo htmlspecialchars($package['package_number']); ?></strong></div>
                </div>
                <div class="row mb-2">
                    <div class="col-md-4"><strong>Package Type:</strong></div>
                    <div class="col-md-8"><?php echo htmlspecialchars($package['package_type_name']); ?></div>
                </div>
                <div class="row mb-2">
                    <div class="col-md-4"><strong>Current Period:</strong></div>
                    <div class="col-md-8">
                        <?php echo formatDate($package['start_date']); ?> to <?php echo formatDate($package['end_date']); ?>
                    </div>
                </div>
                <div class="row mb-2">
                    <div class="col-md-4"><strong>Status:</strong></div>
                    <div class="col-md-8"><?php echo getStatusBadge($package['status']); ?></div>
                </div>
                <?php if ($package['status'] === 'active'): ?>
                <div class="row">
                    <div class="col-md-4"><strong>Days Remaining:</strong></div>
                    <div class="col-md-8">
                        <span class="badge <?php echo ($package['remaining_days'] <= 7) ? 'bg-danger' : 'bg-success'; ?>">
                            <?php echo $package['remaining_days']; ?> days
                        </span>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Renewal Form -->
        <div class="card shadow">
            <div class="card-header bg-success text-white">
                <h5 class="mb-0"><i class="bi bi-plus-circle"></i> New Package Details</h5>
            </div>
            <div class="card-body">
                <form method="POST" action="renew.php?package_id=<?php echo $packageId; ?>" id="renewForm">
                    <!-- Package Type Selection -->
                    <div class="mb-4">
                        <label class="form-label">
                            Select Package Type <span class="text-danger">*</span>
                        </label>
                        <?php foreach ($packageTypes as $type): ?>
                        <div class="form-check">
                            <input class="form-check-input package-type-radio" type="radio"
                                   name="package_type_id" id="package_type_<?php echo $type['id']; ?>"
                                   value="<?php echo $type['id']; ?>"
                                   data-price="<?php echo $type['price']; ?>"
                                   data-duration="<?php echo $type['duration_days']; ?>"
                                   <?php echo ($type['id'] == $package['package_type_id']) ? 'checked' : ''; ?>
                                   required>
                            <label class="form-check-label" for="package_type_<?php echo $type['id']; ?>">
                                <strong><?php echo htmlspecialchars($type['name']); ?></strong> -
                                <?php echo formatCurrency($type['price']); ?>
                                (<?php echo $type['meals_per_day']; ?> meal(s)/day - <?php echo htmlspecialchars($type['meal_times']); ?>)
                            </label>
                        </div>
                        <?php endforeach; ?>
                    </div>

                    <!-- Dates and Amount -->
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="start_date" class="form-label">
                                New Start Date <span class="text-danger">*</span>
                            </label>
                            <input type="date" class="form-control" id="start_date"
                                   name="start_date" value="<?php echo $suggestedStartDate->format('Y-m-d'); ?>" required>
                            <small class="form-text text-muted">
                                Suggested: <?php echo $suggestedStartDate->format('d M Y'); ?>
                            </small>
                        </div>

                        <div class="col-md-6">
                            <label for="end_date" class="form-label">New End Date</label>
                            <input type="text" class="form-control" id="end_date" readonly>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="amount_paid" class="form-label">
                                Amount Paid <span class="text-danger">*</span>
                            </label>
                            <div class="input-group">
                                <span class="input-group-text"><?php echo CURRENCY_SYMBOL; ?></span>
                                <input type="number" class="form-control" id="amount_paid"
                                       name="amount_paid" step="0.01" value="<?php echo $package['price']; ?>" required>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <label for="payment_date" class="form-label">
                                Payment Date <span class="text-danger">*</span>
                            </label>
                            <input type="date" class="form-control" id="payment_date"
                                   name="payment_date" value="<?php echo date('Y-m-d'); ?>" required>
                        </div>
                    </div>

                    <!-- Notes -->
                    <div class="mb-4">
                        <label for="notes" class="form-label">Notes (Optional)</label>
                        <textarea class="form-control" id="notes" name="notes" rows="2"></textarea>
                    </div>

                    <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">

                    <!-- Summary Box -->
                    <div id="summaryBox" class="alert alert-success">
                        <h5><i class="bi bi-info-circle"></i> Renewal Summary</h5>
                        <p class="mb-1"><strong>Package Type:</strong> <span id="summaryType"></span></p>
                        <p class="mb-1"><strong>Duration:</strong> <span id="summaryDuration"></span> days</p>
                        <p class="mb-1"><strong>New Period:</strong> <span id="summaryPeriod"></span></p>
                        <p class="mb-0"><strong>Amount:</strong> <span id="summaryAmount"></span></p>
                    </div>

                    <!-- Form Actions -->
                    <div class="d-grid gap-2 d-md-flex justify-content-md-end mt-4">
                        <a href="<?php echo BASE_URL; ?>students/view.php?id=<?php echo $package['student_id']; ?>" class="btn btn-secondary me-2">
                            <i class="bi bi-x-circle"></i> Cancel
                        </a>
                        <button type="submit" class="btn btn-success btn-lg">
                            <i class="bi bi-arrow-repeat"></i> Renew Package
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    // Calculate end date and update summary
    function updateCalculations() {
        const selectedType = $('.package-type-radio:checked');
        const startDate = $('#start_date').val();

        if (selectedType.length && startDate) {
            const price = parseFloat(selectedType.data('price'));
            const duration = parseInt(selectedType.data('duration'));
            const typeName = selectedType.parent().find('label strong').text();

            // Set amount
            $('#amount_paid').val(price.toFixed(2));

            // Calculate end date
            const start = new Date(startDate);
            const end = new Date(start);
            end.setDate(end.getDate() + duration);

            const endDateStr = end.toISOString().split('T')[0];
            $('#end_date').val(endDateStr);

            // Update summary
            $('#summaryType').text(typeName);
            $('#summaryDuration').text(duration);
            $('#summaryPeriod').text(startDate + ' to ' + endDateStr);
            $('#summaryAmount').text('¥ ' + price.toFixed(2));
        }
    }

    $('.package-type-radio').on('change', updateCalculations);
    $('#start_date').on('change', updateCalculations);

    // Initial calculation
    updateCalculations();
});
</script>

<?php require_once '../includes/footer.php'; ?>
