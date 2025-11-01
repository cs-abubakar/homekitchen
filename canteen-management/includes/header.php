<?php
/**
 * Header Template
 * Canteen Management System - Yangtze University
 */

if (!isset($pageTitle)) {
    $pageTitle = APP_NAME;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title><?php echo htmlspecialchars($pageTitle); ?> - <?php echo APP_SHORT_NAME; ?></title>

    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">

    <!-- DataTables CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css">

    <!-- Select2 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet" />

    <!-- Custom CSS -->
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/style.css">

    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="<?php echo BASE_URL; ?>assets/images/favicon.ico">
</head>
<body>

<?php
// Update package statuses on page load (alternative to cron job)
if (function_exists('updatePackageStatuses')) {
    updatePackageStatuses();
}

// Display flash messages if any
$flashMessage = getFlashMessage();
if ($flashMessage): ?>
    <div class="position-fixed top-0 end-0 p-3" style="z-index: 9999;">
        <div class="toast show align-items-center text-white bg-<?php echo $flashMessage['type'] === 'error' ? 'danger' : $flashMessage['type']; ?> border-0" role="alert">
            <div class="d-flex">
                <div class="toast-body">
                    <?php echo htmlspecialchars($flashMessage['message']); ?>
                </div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
            </div>
        </div>
    </div>
<?php endif; ?>
