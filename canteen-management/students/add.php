<?php
/**
 * Add New Student
 * Canteen Management System - Yangtze University
 */

require_once '../auth/check_auth.php';

$pageTitle = 'Add New Student';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verify CSRF token
    if (!isset($_POST['csrf_token']) || !verifyCSRFToken($_POST['csrf_token'])) {
        redirectWithMessage('add.php', 'error', 'Invalid request. Please try again.');
    }

    // Get and sanitize form data
    $passport_number = sanitize($_POST['passport_number'] ?? '');
    $full_name = sanitize($_POST['full_name'] ?? '');
    $email = sanitize($_POST['email'] ?? '');
    $phone = sanitize($_POST['phone'] ?? '');
    $wechat_id = sanitize($_POST['wechat_id'] ?? '');
    $batch_year = sanitize($_POST['batch_year'] ?? '');
    $major = sanitize($_POST['major'] ?? '');
    $home_address = sanitize($_POST['home_address'] ?? '');
    $china_address = sanitize($_POST['china_address'] ?? '');
    $emergency_contact = sanitize($_POST['emergency_contact'] ?? '');
    $emergency_phone = sanitize($_POST['emergency_phone'] ?? '');

    // Validate required fields
    $errors = [];

    if (empty($passport_number)) {
        $errors[] = 'Passport number is required';
    }

    if (empty($full_name)) {
        $errors[] = 'Full name is required';
    }

    if (!empty($email) && !isValidEmail($email)) {
        $errors[] = 'Invalid email address';
    }

    if (!empty($errors)) {
        $errorMsg = implode('<br>', $errors);
        redirectWithMessage('add.php', 'error', $errorMsg);
    }

    try {
        $db = getDB();

        // Check if passport number already exists
        $checkStmt = $db->prepare("SELECT id FROM students WHERE passport_number = :passport");
        $checkStmt->execute([':passport' => $passport_number]);

        if ($checkStmt->fetch()) {
            redirectWithMessage('add.php', 'error', 'A student with this passport number already exists.');
        }

        // Handle photo upload
        $photoFilename = null;
        if (isset($_FILES['photo']) && $_FILES['photo']['error'] !== UPLOAD_ERR_NO_FILE) {
            $uploadResult = handleFileUpload($_FILES['photo'], STUDENT_PHOTO_PATH);

            if ($uploadResult['success']) {
                $photoFilename = $uploadResult['filename'];
            } else {
                redirectWithMessage('add.php', 'error', 'Photo upload failed: ' . $uploadResult['error']);
            }
        }

        // Insert student record
        $stmt = $db->prepare("
            INSERT INTO students (
                passport_number, full_name, email, phone, wechat_id, photo,
                batch_year, major, home_address, china_address,
                emergency_contact, emergency_phone, created_by
            ) VALUES (
                :passport_number, :full_name, :email, :phone, :wechat_id, :photo,
                :batch_year, :major, :home_address, :china_address,
                :emergency_contact, :emergency_phone, :created_by
            )
        ");

        $stmt->execute([
            ':passport_number' => $passport_number,
            ':full_name' => $full_name,
            ':email' => $email,
            ':phone' => $phone,
            ':wechat_id' => $wechat_id,
            ':photo' => $photoFilename,
            ':batch_year' => $batch_year,
            ':major' => $major,
            ':home_address' => $home_address,
            ':china_address' => $china_address,
            ':emergency_contact' => $emergency_contact,
            ':emergency_phone' => $emergency_phone,
            ':created_by' => getCurrentUserId()
        ]);

        $studentId = $db->lastInsertId();

        // Log activity
        logActivity(
            getCurrentUserId(),
            'CREATE',
            'students',
            $studentId,
            "Added new student: {$full_name}"
        );

        redirectWithMessage('view.php?id=' . $studentId, 'success', 'Student added successfully!');

    } catch (PDOException $e) {
        error_log("Add student error: " . $e->getMessage());
        redirectWithMessage('add.php', 'error', 'Failed to add student. Please try again.');
    }
}

require_once '../includes/header.php';
require_once '../includes/navbar.php';
?>

<!-- Page Header -->
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pb-2 mb-3 border-bottom">
    <h1 class="h2"><i class="bi bi-person-plus"></i> Add New Student</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <a href="list.php" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i> Back to List
        </a>
    </div>
</div>

<!-- Add Student Form -->
<div class="row">
    <div class="col-lg-10 mx-auto">
        <div class="card shadow">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0"><i class="bi bi-file-earmark-person"></i> Student Information</h5>
            </div>
            <div class="card-body">
                <form method="POST" action="add.php" enctype="multipart/form-data" id="addStudentForm">
                    <!-- Personal Information Section -->
                    <h5 class="border-bottom pb-2 mb-3">
                        <i class="bi bi-person"></i> Personal Information
                    </h5>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="passport_number" class="form-label">
                                Passport Number <span class="text-danger">*</span>
                            </label>
                            <input type="text" class="form-control" id="passport_number"
                                   name="passport_number" required>
                        </div>

                        <div class="col-md-6">
                            <label for="full_name" class="form-label">
                                Full Name <span class="text-danger">*</span>
                            </label>
                            <input type="text" class="form-control" id="full_name"
                                   name="full_name" required>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="email" class="form-label">Email Address</label>
                            <input type="email" class="form-control" id="email" name="email">
                        </div>

                        <div class="col-md-6">
                            <label for="phone" class="form-label">Phone Number</label>
                            <input type="tel" class="form-control" id="phone" name="phone">
                        </div>
                    </div>

                    <div class="row mb-4">
                        <div class="col-md-6">
                            <label for="wechat_id" class="form-label">WeChat ID</label>
                            <input type="text" class="form-control" id="wechat_id" name="wechat_id">
                        </div>

                        <div class="col-md-6">
                            <label for="photo" class="form-label">Photo</label>
                            <input type="file" class="form-control" id="photo" name="photo"
                                   accept=".jpg,.jpeg,.png">
                            <small class="form-text text-muted">
                                Max 5MB. Allowed: JPG, PNG
                            </small>
                        </div>
                    </div>

                    <!-- Academic Information Section -->
                    <h5 class="border-bottom pb-2 mb-3 mt-4">
                        <i class="bi bi-mortarboard"></i> Academic Information
                    </h5>

                    <div class="row mb-4">
                        <div class="col-md-6">
                            <label for="batch_year" class="form-label">Batch/Year</label>
                            <input type="text" class="form-control" id="batch_year"
                                   name="batch_year" placeholder="e.g., 2024">
                        </div>

                        <div class="col-md-6">
                            <label for="major" class="form-label">Major</label>
                            <input type="text" class="form-control" id="major"
                                   name="major" placeholder="e.g., Computer Science">
                        </div>
                    </div>

                    <!-- Address Information Section -->
                    <h5 class="border-bottom pb-2 mb-3 mt-4">
                        <i class="bi bi-house"></i> Address Information
                    </h5>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="home_address" class="form-label">Home Address (Country)</label>
                            <textarea class="form-control" id="home_address" name="home_address"
                                      rows="3" placeholder="Home country address"></textarea>
                        </div>

                        <div class="col-md-6">
                            <label for="china_address" class="form-label">China Address (Dorm/Flat)</label>
                            <textarea class="form-control" id="china_address" name="china_address"
                                      rows="3" placeholder="Dorm building, room number, etc."></textarea>
                        </div>
                    </div>

                    <!-- Emergency Contact Section -->
                    <h5 class="border-bottom pb-2 mb-3 mt-4">
                        <i class="bi bi-telephone"></i> Emergency Contact
                    </h5>

                    <div class="row mb-4">
                        <div class="col-md-6">
                            <label for="emergency_contact" class="form-label">Emergency Contact Name</label>
                            <input type="text" class="form-control" id="emergency_contact"
                                   name="emergency_contact" placeholder="Name of emergency contact">
                        </div>

                        <div class="col-md-6">
                            <label for="emergency_phone" class="form-label">Emergency Phone Number</label>
                            <input type="tel" class="form-control" id="emergency_phone"
                                   name="emergency_phone" placeholder="Emergency contact phone">
                        </div>
                    </div>

                    <!-- Photo Preview -->
                    <div id="photoPreview" class="mb-3" style="display: none;">
                        <label class="form-label">Photo Preview:</label>
                        <div>
                            <img id="previewImage" src="" alt="Preview" class="img-thumbnail" style="max-width: 200px;">
                        </div>
                    </div>

                    <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">

                    <!-- Form Actions -->
                    <div class="d-grid gap-2 d-md-flex justify-content-md-end mt-4">
                        <a href="list.php" class="btn btn-secondary me-2">
                            <i class="bi bi-x-circle"></i> Cancel
                        </a>
                        <button type="submit" class="btn btn-primary btn-lg">
                            <i class="bi bi-save"></i> Save Student
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
// Photo preview
document.getElementById('photo').addEventListener('change', function(e) {
    const file = e.target.files[0];
    if (file) {
        const reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById('previewImage').src = e.target.result;
            document.getElementById('photoPreview').style.display = 'block';
        }
        reader.readAsDataURL(file);
    }
});

// Form validation
document.getElementById('addStudentForm').addEventListener('submit', function(e) {
    const passport = document.getElementById('passport_number').value.trim();
    const fullName = document.getElementById('full_name').value.trim();

    if (passport === '' || fullName === '') {
        e.preventDefault();
        alert('Please fill in all required fields (marked with *)');
        return false;
    }
});
</script>

<?php require_once '../includes/footer.php'; ?>
