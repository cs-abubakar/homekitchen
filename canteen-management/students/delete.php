<?php
/**
 * Delete Student (Soft Delete)
 * Canteen Management System - Yangtze University
 */

require_once '../auth/check_auth.php';

// Only admins can delete students
requireAdmin();

// Get student ID
$studentId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($studentId === 0) {
    redirectWithMessage('list.php', 'error', 'Invalid student ID');
}

try {
    $db = getDB();

    // Get student details
    $student = getStudentById($studentId);

    if (!$student) {
        redirectWithMessage('list.php', 'error', 'Student not found');
    }

    // Perform soft delete (set is_active to 0)
    $stmt = $db->prepare("UPDATE students SET is_active = 0 WHERE id = :id");
    $stmt->execute([':id' => $studentId]);

    // Log activity
    logActivity(
        getCurrentUserId(),
        'DELETE',
        'students',
        $studentId,
        "Deleted (soft) student: {$student['full_name']}"
    );

    redirectWithMessage('list.php', 'success', 'Student deactivated successfully');

} catch (PDOException $e) {
    error_log("Delete student error: " . $e->getMessage());
    redirectWithMessage('list.php', 'error', 'Failed to delete student');
}
?>
