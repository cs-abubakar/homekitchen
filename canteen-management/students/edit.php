<?php
/**
 * Edit Student
 * Canteen Management System - Yangtze University
 */

require_once '../auth/check_auth.php';

$pageTitle = 'Edit Student';

// Get student ID
$studentId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($studentId === 0) {
    redirectWithMessage('list.php', 'error', 'Invalid student ID');
}

// Fetch student details
try {
    $db = getDB();
    $student = getStudentById($studentId);

    if (!$student) {
        redirectWithMessage('list.php', 'error', 'Student not found');
    }
} catch (PDOException $e) {
    error_log("Edit student error: " . $e->getMessage());
    redirectWithMessage('list.php', 'error', 'Failed to load student details');
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verify CSRF token
    if (!isset($_POST['csrf_token']) || !verifyCSRFToken($_POST['csrf_token'])) {
        redirectWithMessage('edit.php?id=' . $studentId, 'error', 'Invalid request');
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

    // Validate
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
        redirectWithMessage('edit.php?id=' . $studentId, 'error', implode('<br>', $errors));
    }

    try {
        // Check if passport number exists for another student
        $checkStmt = $db->prepare("SELECT id FROM students WHERE passport_number = :passport AND id != :id");
        $checkStmt->execute([':passport' => $passport_number, ':id' => $studentId]);

        if ($checkStmt->fetch()) {
            redirectWithMessage('edit.php?id=' . $studentId, 'error', 'Passport number already exists');
        }

        // Handle photo upload
        $photoFilename = $student['photo'];
        if (isset($_FILES['photo']) && $_FILES['photo']['error'] !== UPLOAD_ERR_NO_FILE) {
            $uploadResult = handleFileUpload($_FILES['photo'], STUDENT_PHOTO_PATH);

            if ($uploadResult['success']) {
                // Delete old photo if exists
                if (!empty($student['photo'])) {
                    deleteFile(STUDENT_PHOTO_PATH . $student['photo']);
                }
                $photoFilename = $uploadResult['filename'];
            } else {
                redirectWithMessage('edit.php?id=' . $studentId, 'error', 'Photo upload failed: ' . $uploadResult['error']);
            }
        }

        // Update student record
        $stmt = $db->prepare("
            UPDATE students SET
                passport_number = :passport_number,
                full_name = :full_name,
                email = :email,
                phone = :phone,
                wechat_id = :wechat_id,
                photo = :photo,
                batch_year = :batch_year,
                major = :major,
                home_address = :home_address,
                china_address = :china_address,
                emergency_contact = :emergency_contact,
                emergency_phone = :emergency_phone
            WHERE id = :id
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
            ':id' => $studentId
        ]);

        // Log activity
        logActivity(
            getCurrentUserId(),
            'UPDATE',
            'students',
            $studentId,
            "Updated student: {$full_name}"
        );

        redirectWithMessage('view.php?id=' . $studentId, 'success', 'Student updated successfully!');

    } catch (PDOException $e) {
        error_log("Update student error: " . $e->getMessage());
        redirectWithMessage('edit.php?id=' . $studentId, 'error', 'Failed to update student');
    }
}

require_once '../includes/header.php';
require_once '../includes/navbar.php';
?>

<!-- Page Header -->
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pb-2 mb-3 border-bottom">
    <h1 class="h2"><i class="bi bi-pencil-square"></i> Edit Student</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <a href="view.php?id=<?php echo $studentId; ?>" class="btn btn-secondary me-2">
            <i class="bi bi-eye"></i> View Profile
        </a>
        <a href="list.php" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i> Back to List
        </a>
    </div>
</div>

<!-- Edit Student Form -->
<div class="row">
    <div class="col-lg-10 mx-auto">
        <div class="card shadow">
            <div class="card-header bg-warning">
                <h5 class="mb-0"><i class="bi bi-file-earmark-person"></i> Edit Student Information</h5>
            </div>
            <div class="card-body">
                <form method="POST" action="edit.php?id=<?php echo $studentId; ?>" enctype="multipart/form-data" id="editStudentForm">
                    <!-- Personal Information -->
                    <h5 class="border-bottom pb-2 mb-3">
                        <i class="bi bi-person"></i> Personal Information
                    </h5>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="passport_number" class="form-label">
                                Passport Number <span class="text-danger">*</span>
                            </label>
                            <input type="text" class="form-control" id="passport_number"
                                   name="passport_number" value="<?php echo htmlspecialchars($student['passport_number']); ?>" required>
                        </div>

                        <div class="col-md-6">
                            <label for="full_name" class="form-label">
                                Full Name <span class="text-danger">*</span>
                            </label>
                            <input type="text" class="form-control" id="full_name"
                                   name="full_name" value="<?php echo htmlspecialchars($student['full_name']); ?>" required>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="email" class="form-label">Email Address</label>
                            <input type="email" class="form-control" id="email"
                                   name="email" value="<?php echo htmlspecialchars($student['email'] ?? ''); ?>">
                        </div>

                        <div class="col-md-6">
                            <label for="phone" class="form-label">Phone Number</label>
                            <input type="tel" class="form-control" id="phone"
                                   name="phone" value="<?php echo htmlspecialchars($student['phone'] ?? ''); ?>">
                        </div>
                    </div>

                    <div class="row mb-4">
                        <div class="col-md-6">
                            <label for="wechat_id" class="form-label">WeChat ID</label>
                            <input type="text" class="form-control" id="wechat_id"
                                   name="wechat_id" value="<?php echo htmlspecialchars($student['wechat_id'] ?? ''); ?>">
                        </div>

                        <div class="col-md-6">
                            <label for="photo" class="form-label">Change Photo</label>
                            <input type="file" class="form-control" id="photo" name="photo" accept=".jpg,.jpeg,.png">
                            <small class="form-text text-muted">
                                Leave empty to keep current photo
                            </small>
                        </div>
                    </div>

                    <!-- Current Photo -->
                    <?php if (!empty($student['photo']) && file_exists(STUDENT_PHOTO_PATH . $student['photo'])): ?>
                    <div class="mb-3">
                        <label class="form-label">Current Photo:</label>
                        <div>
                            <img src="<?php echo BASE_URL; ?>uploads/students/<?php echo htmlspecialchars($student['photo']); ?>"
                                 alt="Current photo" class="img-thumbnail" style="max-width: 150px;">
                        </div>
                    </div>
                    <?php endif; ?>

                    <!-- Academic Information -->
                    <h5 class="border-bottom pb-2 mb-3 mt-4">
                        <i class="bi bi-mortarboard"></i> Academic Information
                    </h5>

                    <div class="row mb-4">
                        <div class="col-md-6">
                            <label for="batch_year" class="form-label">Batch/Year</label>
                            <input type="text" class="form-control" id="batch_year"
                                   name="batch_year" value="<?php echo htmlspecialchars($student['batch_year'] ?? ''); ?>">
                        </div>

                        <div class="col-md-6">
                            <label for="major" class="form-label">Major</label>
                            <input type="text" class="form-control" id="major"
                                   name="major" value="<?php echo htmlspecialchars($student['major'] ?? ''); ?>">
                        </div>
                    </div>

                    <!-- Address Information -->
                    <h5 class="border-bottom pb-2 mb-3 mt-4">
                        <i class="bi bi-house"></i> Address Information
                    </h5>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="home_address" class="form-label">Home Address</label>
                            <textarea class="form-control" id="home_address" name="home_address" rows="3"><?php echo htmlspecialchars($student['home_address'] ?? ''); ?></textarea>
                        </div>

                        <div class="col-md-6">
                            <label for="china_address" class="form-label">China Address</label>
                            <textarea class="form-control" id="china_address" name="china_address" rows="3"><?php echo htmlspecialchars($student['china_address'] ?? ''); ?></textarea>
                        </div>
                    </div>

                    <!-- Emergency Contact -->
                    <h5 class="border-bottom pb-2 mb-3 mt-4">
                        <i class="bi bi-telephone"></i> Emergency Contact
                    </h5>

                    <div class="row mb-4">
                        <div class="col-md-6">
                            <label for="emergency_contact" class="form-label">Emergency Contact Name</label>
                            <input type="text" class="form-control" id="emergency_contact"
                                   name="emergency_contact" value="<?php echo htmlspecialchars($student['emergency_contact'] ?? ''); ?>">
                        </div>

                        <div class="col-md-6">
                            <label for="emergency_phone" class="form-label">Emergency Phone</label>
                            <input type="tel" class="form-control" id="emergency_phone"
                                   name="emergency_phone" value="<?php echo htmlspecialchars($student['emergency_phone'] ?? ''); ?>">
                        </div>
                    </div>

                    <!-- New Photo Preview -->
                    <div id="photoPreview" class="mb-3" style="display: none;">
                        <label class="form-label">New Photo Preview:</label>
                        <div>
                            <img id="previewImage" src="" alt="Preview" class="img-thumbnail" style="max-width: 200px;">
                        </div>
                    </div>

                    <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">

                    <!-- Form Actions -->
                    <div class="d-grid gap-2 d-md-flex justify-content-md-end mt-4">
                        <a href="view.php?id=<?php echo $studentId; ?>" class="btn btn-secondary me-2">
                            <i class="bi bi-x-circle"></i> Cancel
                        </a>
                        <button type="submit" class="btn btn-warning btn-lg">
                            <i class="bi bi-save"></i> Update Student
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
</script>

<?php require_once '../includes/footer.php'; ?>
