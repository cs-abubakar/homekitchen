<?php
/**
 * Login Page
 * Canteen Management System - Yangtze University
 */

require_once 'config/config.php';
require_once 'config/session.php';

// If user is already logged in, redirect to dashboard
if (isLoggedIn()) {
    header('Location: ' . BASE_URL . 'dashboard/');
    exit;
}

// Get error message if any
$error = $_GET['error'] ?? '';
$errorMessage = '';

switch ($error) {
    case 'invalid_credentials':
        $errorMessage = 'Invalid username or password';
        break;
    case 'session_expired':
        $errorMessage = 'Your session has expired. Please login again.';
        break;
    case 'unauthorized':
        $errorMessage = 'Please login to access this page';
        break;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - <?php echo APP_SHORT_NAME; ?></title>

    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">

    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
        }
        .login-container {
            max-width: 450px;
            margin: auto;
        }
        .login-card {
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
        }
        .login-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 15px 15px 0 0;
            padding: 30px;
            text-align: center;
        }
        .login-header h3 {
            margin: 0;
            font-weight: bold;
        }
        .login-body {
            padding: 40px;
        }
        .btn-login {
            padding: 12px;
            font-size: 16px;
            font-weight: 600;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="login-container">
            <div class="card login-card">
                <div class="login-header">
                    <i class="bi bi-building" style="font-size: 48px;"></i>
                    <h3 class="mt-3"><?php echo UNIVERSITY_NAME; ?></h3>
                    <p class="mb-0">Canteen Management System</p>
                </div>

                <div class="login-body">
                    <?php if ($errorMessage): ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <i class="bi bi-exclamation-triangle-fill"></i>
                            <?php echo htmlspecialchars($errorMessage); ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>

                    <form action="auth/login.php" method="POST" id="loginForm">
                        <div class="mb-3">
                            <label for="username" class="form-label">
                                <i class="bi bi-person"></i> Username
                            </label>
                            <input type="text" class="form-control form-control-lg" id="username" name="username" required autofocus>
                        </div>

                        <div class="mb-3">
                            <label for="password" class="form-label">
                                <i class="bi bi-lock"></i> Password
                            </label>
                            <input type="password" class="form-control form-control-lg" id="password" name="password" required>
                        </div>

                        <div class="mb-3 form-check">
                            <input type="checkbox" class="form-check-input" id="remember" name="remember">
                            <label class="form-check-label" for="remember">
                                Remember me
                            </label>
                        </div>

                        <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">

                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary btn-login">
                                <i class="bi bi-box-arrow-in-right"></i> Login
                            </button>
                        </div>
                    </form>

                    <div class="text-center mt-4">
                        <small class="text-muted">
                            Default credentials: <strong>admin</strong> / <strong>admin123</strong>
                        </small>
                    </div>
                </div>
            </div>

            <div class="text-center mt-4 text-white">
                <p>&copy; <?php echo date('Y'); ?> <?php echo UNIVERSITY_NAME; ?>. All rights reserved.</p>
            </div>
        </div>
    </div>

    <!-- Bootstrap 5 JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
