<?php
/**
 * Database Configuration
 * Canteen Management System - Yangtze University
 *
 * This file uses constants from init.php
 */

// Database connection class
class Database {
    private $host;
    private $db_name;
    private $username;
    private $password;
    private $charset;
    private $conn = null;
    private $error = null;

    public function __construct() {
        $this->host = DB_HOST;
        $this->db_name = DB_NAME;
        $this->username = DB_USER;
        $this->password = DB_PASS;
        $this->charset = DB_CHARSET;
    }

    /**
     * Get database connection
     * @return PDO|null
     */
    public function getConnection() {
        if ($this->conn === null) {
            try {
                $dsn = "mysql:host={$this->host};dbname={$this->db_name};charset={$this->charset}";
                $options = [
                    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES   => false,
                    PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES {$this->charset}",
                    PDO::ATTR_TIMEOUT            => 5, // 5 second connection timeout
                ];

                $this->conn = new PDO($dsn, $this->username, $this->password, $options);

                // Test the connection
                $this->conn->query("SELECT 1");

            } catch (PDOException $e) {
                $this->error = $e->getMessage();

                // Log the error
                error_log("=== DATABASE CONNECTION ERROR ===");
                error_log("Error: " . $this->error);
                error_log("Host: " . $this->host);
                error_log("Database: " . $this->db_name);
                error_log("User: " . $this->username);
                error_log("================================");

                // Display user-friendly error
                $this->displayConnectionError();
                exit;
            }
        }

        return $this->conn;
    }

    /**
     * Display connection error page
     */
    private function displayConnectionError() {
        http_response_code(500);
        ?>
        <!DOCTYPE html>
        <html lang="en">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Database Connection Error</title>
            <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
            <style>
                body {
                    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                    min-height: 100vh;
                    display: flex;
                    align-items: center;
                }
                .error-container { max-width: 600px; margin: auto; }
                .error-card { border-radius: 15px; box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3); }
            </style>
        </head>
        <body>
            <div class="container">
                <div class="error-container">
                    <div class="card error-card">
                        <div class="card-body p-5 text-center">
                            <div class="text-danger mb-4">
                                <svg xmlns="http://www.w3.org/2000/svg" width="64" height="64" fill="currentColor" class="bi bi-exclamation-triangle" viewBox="0 0 16 16">
                                    <path d="M7.938 2.016A.13.13 0 0 1 8.002 2a.13.13 0 0 1 .063.016.146.146 0 0 1 .054.057l6.857 11.667c.036.06.035.124.002.183a.163.163 0 0 1-.054.06.116.116 0 0 1-.066.017H1.146a.115.115 0 0 1-.066-.017.163.163 0 0 1-.054-.06.176.176 0 0 1 .002-.183L7.884 2.073a.147.147 0 0 1 .054-.057zm1.044-.45a1.13 1.13 0 0 0-1.96 0L.165 13.233c-.457.778.091 1.767.98 1.767h13.713c.889 0 1.438-.99.98-1.767L8.982 1.566z"/>
                                    <path d="M7.002 12a1 1 0 1 1 2 0 1 1 0 0 1-2 0zM7.1 5.995a.905.905 0 1 1 1.8 0l-.35 3.507a.552.552 0 0 1-1.1 0L7.1 5.995z"/>
                                </svg>
                            </div>

                            <h2 class="text-danger mb-3">Database Connection Failed</h2>

                            <div class="alert alert-danger text-start">
                                <strong>Connection Details:</strong>
                                <ul class="mb-0 mt-2">
                                    <li><strong>Host:</strong> <?php echo htmlspecialchars($this->host); ?></li>
                                    <li><strong>Database:</strong> <?php echo htmlspecialchars($this->db_name); ?></li>
                                    <li><strong>User:</strong> <?php echo htmlspecialchars($this->username); ?></li>
                                </ul>
                            </div>

                            <div class="alert alert-info text-start">
                                <strong>⚠️ Troubleshooting Steps:</strong>
                                <ol class="mb-0 mt-2">
                                    <li>Ensure MySQL is running:
                                        <br><code class="text-dark">sudo mysql.server start</code> (macOS)
                                    </li>
                                    <li>Verify database exists:
                                        <br><code class="text-dark">mysql -u root -e "SHOW DATABASES;"</code>
                                    </li>
                                    <li>Import database schema:
                                        <br><code class="text-dark">mysql -u root canteen_management < sql/database.sql</code>
                                    </li>
                                    <li>Check MySQL credentials in <code>init.php</code></li>
                                </ol>
                            </div>

                            <?php if (ENVIRONMENT === 'development'): ?>
                            <div class="alert alert-warning text-start">
                                <strong>🔍 Technical Error:</strong>
                                <pre class="mb-0 mt-2" style="font-size: 12px; text-align: left;"><?php echo htmlspecialchars($this->error); ?></pre>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </body>
        </html>
        <?php
    }

    /**
     * Get last error
     */
    public function getError() {
        return $this->error;
    }

    /**
     * Close database connection
     */
    public function closeConnection() {
        $this->conn = null;
    }

    /**
     * Begin transaction
     */
    public function beginTransaction() {
        return $this->conn->beginTransaction();
    }

    /**
     * Commit transaction
     */
    public function commit() {
        return $this->conn->commit();
    }

    /**
     * Rollback transaction
     */
    public function rollback() {
        return $this->conn->rollBack();
    }
}

// Create a global database instance function
function getDB() {
    static $database = null;
    if ($database === null) {
        $database = new Database();
    }
    return $database->getConnection();
}

// Test database connection on first load (only in development)
if (ENVIRONMENT === 'development' && !isset($_SESSION['db_test_done'])) {
    try {
        $testDb = getDB();
        $_SESSION['db_test_done'] = true;
        error_log("✓ Database connection successful!");
    } catch (Exception $e) {
        // Error already handled in getConnection()
    }
}
