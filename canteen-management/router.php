<?php
/**
 * Router for PHP Built-in Web Server
 * Canteen Management System - Yangtze University
 *
 * This file handles routing when using: php -S localhost:8000 router.php
 */

// Get the requested URI
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$uri = urldecode($uri);

// Serve static files directly
$filePath = __DIR__ . $uri;
if ($uri !== '/' && file_exists($filePath) && is_file($filePath)) {
    // Serve static files with proper mime types
    $extension = pathinfo($filePath, PATHINFO_EXTENSION);
    $mimeTypes = [
        'css'  => 'text/css',
        'js'   => 'application/javascript',
        'json' => 'application/json',
        'png'  => 'image/png',
        'jpg'  => 'image/jpeg',
        'jpeg' => 'image/jpeg',
        'gif'  => 'image/gif',
        'svg'  => 'image/svg+xml',
        'ico'  => 'image/x-icon',
        'woff' => 'font/woff',
        'woff2'=> 'font/woff2',
        'ttf'  => 'font/ttf',
        'eot'  => 'application/vnd.ms-fontobject',
    ];

    if (isset($mimeTypes[$extension])) {
        header('Content-Type: ' . $mimeTypes[$extension]);
    }

    return false; // Let PHP serve the file
}

// Handle directory access (add trailing slash)
if ($uri !== '/' && is_dir($filePath) && substr($uri, -1) !== '/') {
    header("Location: $uri/");
    exit;
}

// Handle PHP files
if ($uri !== '/') {
    $phpFile = __DIR__ . $uri;

    // If URI ends with /, look for index.php
    if (substr($uri, -1) === '/') {
        $phpFile = __DIR__ . $uri . 'index.php';
    }

    // If the PHP file exists, serve it
    if (file_exists($phpFile) && is_file($phpFile) && pathinfo($phpFile, PATHINFO_EXTENSION) === 'php') {
        require $phpFile;
        exit;
    }
}

// Default: serve index.php for root
if ($uri === '/') {
    require __DIR__ . '/index.php';
    exit;
}

// 404 Not Found
http_response_code(404);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>404 - Page Not Found</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
        }
        .error-container { max-width: 500px; margin: auto; }
        .error-card { border-radius: 15px; box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3); }
    </style>
</head>
<body>
    <div class="container">
        <div class="error-container">
            <div class="card error-card">
                <div class="card-body p-5 text-center">
                    <h1 class="display-1 text-muted">404</h1>
                    <h3 class="mb-3">Page Not Found</h3>
                    <p class="text-muted mb-4">
                        The page <code><?php echo htmlspecialchars($uri); ?></code> could not be found.
                    </p>
                    <a href="/" class="btn btn-primary">
                        <i class="bi bi-house"></i> Go to Home
                    </a>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
