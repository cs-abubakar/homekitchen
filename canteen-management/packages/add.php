<?php
/**
 * Add New Package
 * Canteen Management System - Yangtze University
 */

require_once '../auth/check_auth.php';

$pageTitle = 'Add New Package';

// Get student ID if passed from URL
$preselectedStudentId = isset($_GET['student_id']) ? (int)$_GET['student_id'] : 0;

// Fetch all active students for dropdown
try {
    $db = getDB();

    $stmt = $db->query("
        SELECT id, passport_number, full_name, wechat_id
        FROM students
        WHERE is_active = 1
        ORDER BY full_name ASC
    ");
    $students = $stmt->fetchAll();

    // Get package types
    $packageTypes = getActivePackageTypes();

} catch (PDOException $e) {
    error_log("Add package error: " . $e->getMessage());
    $students = [];
    $packageTypes = [];
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verify CSRF token
    if (!isset($_POST['csrf_token']) || !verifyCSRFToken($_POST['csrf_token'])) {
        redirectWithMessage('add.php', 'error', 'Invalid request');
    }

    // Get form data
    $student_id = (int)$_POST['student_id'];
    $package_type_id = (int)$_POST['package_type_id'];
    $start_date = $_POST['start_date'];
    $amount_paid = (float)$_POST['amount_paid'];
    $payment_date = $_POST['payment_date'];
    $notes = sanitize($_POST['notes'] ?? '');

    // Validate
    if ($student_id === 0 || $package_type_id === 0) {
        redirectWithMessage('add.php', 'error', 'Please select student and package type');
    }

    try {
        $db->beginTransaction();

        // Get package type details
        $packageType = getPackageType($package_type_id);
        if (!$packageType) {
            throw new Exception('Invalid package type');
        }

        // Calculate end date
        $startDateObj = new DateTime($start_date);
        $endDateObj = clone $startDateObj;
        $endDateObj->modify('+' . $packageType['duration_days'] . ' days');
        $end_date = $endDateObj->format('Y-m-d');

        // Generate package number
        $package_number = generatePackageNumber();

        // Insert package
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
            ':package_number' => $package_number,
            ':student_id' => $student_id,
            ':package_type_id' => $package_type_id,
            ':start_date' => $start_date,
            ':end_date' => $end_date,
            ':amount_paid' => $amount_paid,
            ':payment_date' => $payment_date,
            ':remaining_days' => $remaining_days,
            ':notes' => $notes,
            ':created_by' => getCurrentUserId()
        ]);

        $package_id = $db->lastInsertId();

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
            ':package_id' => $package_id,
            ':student_id' => $student_id,
            ':amount' => $amount_paid,
            ':payment_date' => $payment_date,
            ':received_by' => getCurrentUserId(),
            ':notes' => 'Package: ' . $package_number
        ]);

        // Get student details for log
        $student = getStudentById($student_id);

        // Log activity
        logActivity(
            getCurrentUserId(),
            'CREATE',
            'packages',
            $package_id,
            "Created package {$package_number} for {$student['full_name']}"
        );

        $db->commit();

        redirectWithMessage(
            '../students/view.php?id=' . $student_id,
            'success',
            "Package {$package_number} created successfully! Receipt: {$receipt_number}"
        );

    } catch (Exception $e) {
        $db->rollback();
        error_log("Add package error: " . $e->getMessage());
        redirectWithMessage('add.php', 'error', 'Failed to create package: ' . $e->getMessage());
    }
}

require_once '../includes/header.php';
require_once '../includes/navbar.php';
?>

<!-- Page Header -->
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pb-2 mb-3 border-bottom">
    <h1 class="h2"><i class="bi bi-box-seam"></i> Add New Package</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <a href="list.php" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i> Back to List
        </a>
    </div>
</div>

<!-- Add Package Form -->
<div class="row">
    <div class="col-lg-8 mx-auto">
        <div class="card shadow">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0"><i class="bi bi-plus-circle"></i> Package Information</h5>
            </div>
            <div class="card-body">
                <form method="POST" action="add.php" id="addPackageForm">
                    <!-- Student Selection -->
                    <div class="mb-4">
                        <label for="student_id" class="form-label">
                            Select Student <span class="text-danger">*</span>
                        </label>
                        <select class="form-select select2" id="student_id" name="student_id" required>
                            <option value="">-- Select Student --</option>
                            <?php foreach ($students as $student): ?>
                                <option value="<?php echo $student['id']; ?>"
                                    <?php echo ($preselectedStudentId === $student['id']) ? 'selected' : ''; ?>
                                    data-passport="<?php echo htmlspecialchars($student['passport_number']); ?>"
                                    data-wechat="<?php echo htmlspecialchars($student['wechat_id'] ?? 'N/A'); ?>">
                                    <?php echo htmlspecialchars($student['full_name']); ?> - <?php echo htmlspecialchars($student['passport_number']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- Selected Student Info -->
                    <div id="studentInfo" class="alert alert-info" style="display: none;">
                        <h6>Selected Student:</h6>
                        <p class="mb-1"><strong>Name:</strong> <span id="infoName"></span></p>
                        <p class="mb-1"><strong>Passport:</strong> <span id="infoPassport"></span></p>
                        <p class="mb-0"><strong>WeChat:</strong> <span id="infoWechat"></span></p>
                    </div>

                    <!-- Package Type Selection -->
                    <div class="mb-4">
                        <label class="form-label">
                            Package Type <span class="text-danger">*</span>
                        </label>
                        <?php foreach ($packageTypes as $type): ?>
                        <div class="form-check">
                            <input class="form-check-input package-type-radio" type="radio"
                                   name="package_type_id" id="package_type_<?php echo $type['id']; ?>"
                                   value="<?php echo $type['id']; ?>"
                                   data-price="<?php echo $type['price']; ?>"
                                   data-duration="<?php echo $type['duration_days']; ?>"
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
                                Start Date <span class="text-danger">*</span>
                            </label>
                            <input type="date" class="form-control" id="start_date"
                                   name="start_date" value="<?php echo date('Y-m-d'); ?>" required>
                        </div>

                        <div class="col-md-6">
                            <label for="end_date" class="form-label">End Date (Auto-calculated)</label>
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
                                       name="amount_paid" step="0.01" required>
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
                        <textarea class="form-control" id="notes" name="notes" rows="3"></textarea>
                    </div>

                    <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">

                    <!-- Summary Box -->
                    <div id="summaryBox" class="alert alert-success" style="display: none;">
                        <h5><i class="bi bi-info-circle"></i> Package Summary</h5>
                        <p class="mb-1"><strong>Package Type:</strong> <span id="summaryType"></span></p>
                        <p class="mb-1"><strong>Duration:</strong> <span id="summaryDuration"></span> days</p>
                        <p class="mb-1"><strong>Period:</strong> <span id="summaryPeriod"></span></p>
                        <p class="mb-0"><strong>Amount:</strong> <span id="summaryAmount"></span></p>
                    </div>

                    <!-- Form Actions -->
                    <div class="d-grid gap-2 d-md-flex justify-content-md-end mt-4">
                        <a href="list.php" class="btn btn-secondary me-2">
                            <i class="bi bi-x-circle"></i> Cancel
                        </a>
                        <button type="submit" class="btn btn-primary btn-lg">
                            <i class="bi bi-save"></i> Create Package
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    // Initialize Select2
    $('.select2').select2({
        theme: 'bootstrap-5',
        placeholder: '-- Select Student --',
        allowClear: true
    });

    // Show student info when selected
    $('#student_id').on('change', function() {
        const selectedOption = $(this).find(':selected');
        if (selectedOption.val()) {
            $('#infoName').text(selectedOption.text().split(' - ')[0]);
            $('#infoPassport').text(selectedOption.data('passport'));
            $('#infoWechat').text(selectedOption.data('wechat'));
            $('#studentInfo').show();
        } else {
            $('#studentInfo').hide();
        }
    });

    // Trigger change if preselected
    <?php if ($preselectedStudentId > 0): ?>
    $('#student_id').trigger('change');
    <?php endif; ?>

    // Calculate end date and amount when package type or start date changes
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
            $('#summaryBox').show();
        }
    }

    $('.package-type-radio').on('change', updateCalculations);
    $('#start_date').on('change', updateCalculations);
});
</script>

<?php require_once '../includes/footer.php'; ?>
