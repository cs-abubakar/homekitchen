<?php
/**
 * Helper Functions
 * Canteen Management System - Yangtze University
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/config.php';

/**
 * Sanitize input data
 * @param string $data Input data
 * @return string Sanitized data
 */
function sanitize($data) {
    return htmlspecialchars(strip_tags(trim($data)), ENT_QUOTES, 'UTF-8');
}

/**
 * Validate email address
 * @param string $email Email address
 * @return bool
 */
function isValidEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

/**
 * Format date for display
 * @param string $date Date string
 * @param string $format Format string
 * @return string Formatted date
 */
function formatDate($date, $format = DISPLAY_DATE_FORMAT) {
    if (empty($date) || $date === '0000-00-00' || $date === '0000-00-00 00:00:00') {
        return 'N/A';
    }
    try {
        $dateObj = new DateTime($date);
        return $dateObj->format($format);
    } catch (Exception $e) {
        return $date;
    }
}

/**
 * Format currency
 * @param float $amount Amount
 * @return string Formatted currency
 */
function formatCurrency($amount) {
    return CURRENCY_SYMBOL . ' ' . number_format($amount, 2);
}

/**
 * Get status badge HTML
 * @param string $status Status string
 * @return string HTML badge
 */
function getStatusBadge($status) {
    $badges = [
        'active' => '<span class="badge bg-success">Active</span>',
        'inactive' => '<span class="badge bg-secondary">Inactive</span>',
        'expired' => '<span class="badge bg-danger">Expired</span>',
        'cancelled' => '<span class="badge bg-warning">Cancelled</span>',
        'expiring' => '<span class="badge bg-warning">Expiring Soon</span>'
    ];

    return $badges[strtolower($status)] ?? '<span class="badge bg-secondary">' . ucfirst($status) . '</span>';
}

/**
 * Log activity
 * @param int $userId User ID
 * @param string $action Action performed
 * @param string $tableName Table name
 * @param int $recordId Record ID
 * @param string $description Description
 */
function logActivity($userId, $action, $tableName = null, $recordId = null, $description = null) {
    try {
        $db = getDB();
        $ip = getUserIP();

        $stmt = $db->prepare("
            INSERT INTO activity_logs (user_id, action, table_name, record_id, description, ip_address)
            VALUES (:user_id, :action, :table_name, :record_id, :description, :ip_address)
        ");

        $stmt->execute([
            ':user_id' => $userId,
            ':action' => $action,
            ':table_name' => $tableName,
            ':record_id' => $recordId,
            ':description' => $description,
            ':ip_address' => $ip
        ]);
    } catch (PDOException $e) {
        error_log("Activity log error: " . $e->getMessage());
    }
}

/**
 * Generate unique filename for uploaded files
 * @param string $originalName Original filename
 * @return string Unique filename
 */
function generateUniqueFilename($originalName) {
    $extension = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
    return uniqid('img_', true) . '.' . $extension;
}

/**
 * Handle file upload
 * @param array $file File array from $_FILES
 * @param string $destination Destination directory
 * @return array Result array with 'success' and 'filename' or 'error'
 */
function handleFileUpload($file, $destination) {
    // Check if file was uploaded
    if (!isset($file['error']) || is_array($file['error'])) {
        return ['success' => false, 'error' => 'Invalid file upload'];
    }

    // Check for upload errors
    switch ($file['error']) {
        case UPLOAD_ERR_OK:
            break;
        case UPLOAD_ERR_NO_FILE:
            return ['success' => false, 'error' => 'No file uploaded'];
        case UPLOAD_ERR_INI_SIZE:
        case UPLOAD_ERR_FORM_SIZE:
            return ['success' => false, 'error' => 'File size exceeds limit'];
        default:
            return ['success' => false, 'error' => 'Unknown upload error'];
    }

    // Check file size
    if ($file['size'] > MAX_FILE_SIZE) {
        return ['success' => false, 'error' => 'File size exceeds ' . (MAX_FILE_SIZE / 1024 / 1024) . 'MB'];
    }

    // Check file type
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mimeType = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);

    if (!in_array($mimeType, ALLOWED_IMAGE_TYPES)) {
        return ['success' => false, 'error' => 'Invalid file type. Only JPG and PNG allowed'];
    }

    // Check file extension
    $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($extension, ALLOWED_IMAGE_EXTENSIONS)) {
        return ['success' => false, 'error' => 'Invalid file extension'];
    }

    // Generate unique filename
    $filename = generateUniqueFilename($file['name']);
    $filepath = $destination . $filename;

    // Move uploaded file
    if (!move_uploaded_file($file['tmp_name'], $filepath)) {
        return ['success' => false, 'error' => 'Failed to move uploaded file'];
    }

    // Set proper permissions
    chmod($filepath, 0644);

    return ['success' => true, 'filename' => $filename];
}

/**
 * Delete file
 * @param string $filepath Full path to file
 * @return bool
 */
function deleteFile($filepath) {
    if (file_exists($filepath) && is_file($filepath)) {
        return unlink($filepath);
    }
    return false;
}

/**
 * Generate next package number
 * @return string Package number
 */
function generatePackageNumber() {
    try {
        $db = getDB();
        $stmt = $db->prepare("CALL get_next_package_number(@next_number)");
        $stmt->execute();

        $result = $db->query("SELECT @next_number AS next_number")->fetch();
        return $result['next_number'];
    } catch (PDOException $e) {
        error_log("Package number generation error: " . $e->getMessage());
        // Fallback method
        $prefix = 'PKG-' . date('Ymd') . '-';
        return $prefix . str_pad(rand(1, 999), 3, '0', STR_PAD_LEFT);
    }
}

/**
 * Generate next receipt number
 * @return string Receipt number
 */
function generateReceiptNumber() {
    try {
        $db = getDB();
        $stmt = $db->prepare("CALL get_next_receipt_number(@next_number)");
        $stmt->execute();

        $result = $db->query("SELECT @next_number AS next_number")->fetch();
        return $result['next_number'];
    } catch (PDOException $e) {
        error_log("Receipt number generation error: " . $e->getMessage());
        // Fallback method
        $prefix = 'RCP-' . date('Ymd') . '-';
        return $prefix . str_pad(rand(1, 999), 3, '0', STR_PAD_LEFT);
    }
}

/**
 * Calculate remaining days
 * @param string $endDate End date
 * @return int Remaining days
 */
function calculateRemainingDays($endDate) {
    $today = new DateTime();
    $end = new DateTime($endDate);
    $interval = $today->diff($end);

    if ($end < $today) {
        return 0;
    }

    return $interval->days;
}

/**
 * Get package type details
 * @param int $packageTypeId Package type ID
 * @return array|null Package type details
 */
function getPackageType($packageTypeId) {
    try {
        $db = getDB();
        $stmt = $db->prepare("SELECT * FROM package_types WHERE id = :id");
        $stmt->execute([':id' => $packageTypeId]);
        return $stmt->fetch();
    } catch (PDOException $e) {
        error_log("Get package type error: " . $e->getMessage());
        return null;
    }
}

/**
 * Get all active package types
 * @return array Package types
 */
function getActivePackageTypes() {
    try {
        $db = getDB();
        $stmt = $db->query("SELECT * FROM package_types WHERE is_active = 1 ORDER BY meals_per_day DESC, price DESC");
        return $stmt->fetchAll();
    } catch (PDOException $e) {
        error_log("Get package types error: " . $e->getMessage());
        return [];
    }
}

/**
 * Get student by ID
 * @param int $studentId Student ID
 * @return array|null Student details
 */
function getStudentById($studentId) {
    try {
        $db = getDB();
        $stmt = $db->prepare("SELECT * FROM students WHERE id = :id");
        $stmt->execute([':id' => $studentId]);
        return $stmt->fetch();
    } catch (PDOException $e) {
        error_log("Get student error: " . $e->getMessage());
        return null;
    }
}

/**
 * Get student's active package
 * @param int $studentId Student ID
 * @return array|null Active package details
 */
function getStudentActivePackage($studentId) {
    try {
        $db = getDB();
        $stmt = $db->prepare("
            SELECT p.*, pt.name as package_name, pt.price, pt.meals_per_day, pt.meal_times
            FROM packages p
            JOIN package_types pt ON p.package_type_id = pt.id
            WHERE p.student_id = :student_id AND p.status = 'active'
            ORDER BY p.end_date DESC
            LIMIT 1
        ");
        $stmt->execute([':student_id' => $studentId]);
        return $stmt->fetch();
    } catch (PDOException $e) {
        error_log("Get active package error: " . $e->getMessage());
        return null;
    }
}

/**
 * Update package statuses (run daily or on page load)
 */
function updatePackageStatuses() {
    try {
        $db = getDB();
        $stmt = $db->prepare("CALL update_package_statuses()");
        $stmt->execute();
    } catch (PDOException $e) {
        error_log("Update package statuses error: " . $e->getMessage());
    }
}

/**
 * Get system setting
 * @param string $key Setting key
 * @param mixed $default Default value if not found
 * @return mixed Setting value
 */
function getSetting($key, $default = null) {
    try {
        $db = getDB();
        $stmt = $db->prepare("SELECT setting_value FROM system_settings WHERE setting_key = :key");
        $stmt->execute([':key' => $key]);
        $result = $stmt->fetch();

        return $result ? $result['setting_value'] : $default;
    } catch (PDOException $e) {
        error_log("Get setting error: " . $e->getMessage());
        return $default;
    }
}

/**
 * Redirect with message
 * @param string $url URL to redirect to
 * @param string $type Message type (success, error, warning, info)
 * @param string $message Message text
 */
function redirectWithMessage($url, $type, $message) {
    setFlashMessage($type, $message);
    header('Location: ' . $url);
    exit;
}

/**
 * Get pagination data
 * @param int $totalItems Total number of items
 * @param int $currentPage Current page number
 * @param int $itemsPerPage Items per page
 * @return array Pagination data
 */
function getPagination($totalItems, $currentPage, $itemsPerPage = ITEMS_PER_PAGE) {
    $totalPages = ceil($totalItems / $itemsPerPage);
    $currentPage = max(1, min($currentPage, $totalPages));
    $offset = ($currentPage - 1) * $itemsPerPage;

    return [
        'total_items' => $totalItems,
        'total_pages' => $totalPages,
        'current_page' => $currentPage,
        'items_per_page' => $itemsPerPage,
        'offset' => $offset,
        'has_prev' => $currentPage > 1,
        'has_next' => $currentPage < $totalPages
    ];
}

?>
