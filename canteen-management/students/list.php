<?php
/**
 * Students List
 * Canteen Management System - Yangtze University
 */

require_once '../auth/check_auth.php';

$pageTitle = 'Students List';

// Fetch all students
try {
    $db = getDB();

    // Get filters
    $batchFilter = $_GET['batch'] ?? '';
    $majorFilter = $_GET['major'] ?? '';
    $statusFilter = $_GET['status'] ?? '';

    // Build query
    $query = "SELECT * FROM students WHERE 1=1";
    $params = [];

    if (!empty($batchFilter)) {
        $query .= " AND batch_year = :batch";
        $params[':batch'] = $batchFilter;
    }

    if (!empty($majorFilter)) {
        $query .= " AND major LIKE :major";
        $params[':major'] = '%' . $majorFilter . '%';
    }

    if ($statusFilter !== '') {
        $query .= " AND is_active = :status";
        $params[':status'] = $statusFilter;
    }

    $query .= " ORDER BY created_at DESC";

    $stmt = $db->prepare($query);
    $stmt->execute($params);
    $students = $stmt->fetchAll();

    // Get distinct batch years for filter
    $batchStmt = $db->query("SELECT DISTINCT batch_year FROM students ORDER BY batch_year DESC");
    $batches = $batchStmt->fetchAll();

    // Get distinct majors for filter
    $majorStmt = $db->query("SELECT DISTINCT major FROM students ORDER BY major ASC");
    $majors = $majorStmt->fetchAll();

} catch (PDOException $e) {
    error_log("Students list error: " . $e->getMessage());
    $students = [];
    $batches = [];
    $majors = [];
}

require_once '../includes/header.php';
require_once '../includes/navbar.php';
?>

<!-- Page Header -->
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pb-2 mb-3 border-bottom">
    <h1 class="h2"><i class="bi bi-people"></i> Students List</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <a href="add.php" class="btn btn-primary">
            <i class="bi bi-plus-circle"></i> Add New Student
        </a>
    </div>
</div>

<!-- Filters -->
<div class="card mb-3">
    <div class="card-body">
        <form method="GET" class="row g-3" id="filterForm">
            <div class="col-md-3">
                <label for="batch" class="form-label">Batch/Year</label>
                <select class="form-select" id="batch" name="batch">
                    <option value="">All Batches</option>
                    <?php foreach ($batches as $batch): ?>
                        <option value="<?php echo htmlspecialchars($batch['batch_year']); ?>"
                            <?php echo ($batchFilter === $batch['batch_year']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($batch['batch_year']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="col-md-3">
                <label for="major" class="form-label">Major</label>
                <select class="form-select" id="major" name="major">
                    <option value="">All Majors</option>
                    <?php foreach ($majors as $major): ?>
                        <option value="<?php echo htmlspecialchars($major['major']); ?>"
                            <?php echo ($majorFilter === $major['major']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($major['major']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="col-md-3">
                <label for="status" class="form-label">Status</label>
                <select class="form-select" id="status" name="status">
                    <option value="">All Status</option>
                    <option value="1" <?php echo ($statusFilter === '1') ? 'selected' : ''; ?>>Active</option>
                    <option value="0" <?php echo ($statusFilter === '0') ? 'selected' : ''; ?>>Inactive</option>
                </select>
            </div>

            <div class="col-md-3 d-flex align-items-end">
                <button type="submit" class="btn btn-primary me-2">
                    <i class="bi bi-funnel"></i> Filter
                </button>
                <a href="list.php" class="btn btn-secondary">
                    <i class="bi bi-x-circle"></i> Clear
                </a>
            </div>
        </form>
    </div>
</div>

<!-- Students Table -->
<div class="card shadow">
    <div class="card-header bg-primary text-white">
        <h5 class="mb-0"><i class="bi bi-table"></i> All Students (<?php echo count($students); ?>)</h5>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table id="studentsTable" class="table table-striped table-hover">
                <thead>
                    <tr>
                        <th>Photo</th>
                        <th>Name</th>
                        <th>Passport</th>
                        <th>WeChat</th>
                        <th>Phone</th>
                        <th>Batch</th>
                        <th>Major</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($students as $student): ?>
                    <tr>
                        <td>
                            <?php if (!empty($student['photo']) && file_exists(STUDENT_PHOTO_PATH . $student['photo'])): ?>
                                <img src="<?php echo BASE_URL; ?>uploads/students/<?php echo htmlspecialchars($student['photo']); ?>"
                                     alt="<?php echo htmlspecialchars($student['full_name']); ?>"
                                     class="rounded-circle" width="40" height="40">
                            <?php else: ?>
                                <div class="rounded-circle bg-secondary text-white d-inline-flex align-items-center justify-content-center"
                                     style="width: 40px; height: 40px;">
                                    <?php echo strtoupper(substr($student['full_name'], 0, 1)); ?>
                                </div>
                            <?php endif; ?>
                        </td>
                        <td><?php echo htmlspecialchars($student['full_name']); ?></td>
                        <td><?php echo htmlspecialchars($student['passport_number']); ?></td>
                        <td><?php echo htmlspecialchars($student['wechat_id'] ?? 'N/A'); ?></td>
                        <td><?php echo htmlspecialchars($student['phone'] ?? 'N/A'); ?></td>
                        <td><?php echo htmlspecialchars($student['batch_year'] ?? 'N/A'); ?></td>
                        <td><?php echo htmlspecialchars($student['major'] ?? 'N/A'); ?></td>
                        <td><?php echo getStatusBadge($student['is_active'] ? 'active' : 'inactive'); ?></td>
                        <td>
                            <div class="btn-group btn-group-sm" role="group">
                                <a href="view.php?id=<?php echo $student['id']; ?>"
                                   class="btn btn-info" title="View">
                                    <i class="bi bi-eye"></i>
                                </a>
                                <a href="edit.php?id=<?php echo $student['id']; ?>"
                                   class="btn btn-warning" title="Edit">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <?php if (isAdmin()): ?>
                                <button type="button" class="btn btn-danger" title="Delete"
                                        onclick="confirmDelete(<?php echo $student['id']; ?>, '<?php echo htmlspecialchars($student['full_name'], ENT_QUOTES); ?>')">
                                    <i class="bi bi-trash"></i>
                                </button>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title"><i class="bi bi-exclamation-triangle"></i> Confirm Delete</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete this student?</p>
                <p><strong id="studentName"></strong></p>
                <p class="text-muted">This will mark the student as inactive. All related data will be preserved.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <a href="#" id="confirmDeleteBtn" class="btn btn-danger">Delete</a>
            </div>
        </div>
    </div>
</div>

<script>
// Initialize DataTable
$(document).ready(function() {
    $('#studentsTable').DataTable({
        "pageLength": 50,
        "order": [[1, "asc"]],
        "language": {
            "search": "Search students:",
            "lengthMenu": "Show _MENU_ students per page",
            "info": "Showing _START_ to _END_ of _TOTAL_ students",
            "infoEmpty": "No students found",
            "infoFiltered": "(filtered from _MAX_ total students)"
        }
    });
});

// Delete confirmation
function confirmDelete(studentId, studentName) {
    document.getElementById('studentName').textContent = studentName;
    document.getElementById('confirmDeleteBtn').href = 'delete.php?id=' + studentId;
    new bootstrap.Modal(document.getElementById('deleteModal')).show();
}
</script>

<?php require_once '../includes/footer.php'; ?>
