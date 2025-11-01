<?php
/**
 * Database Configuration with Environment Variables
 * Use this file for cloud deployments (Railway, Render, etc.)
 */

// Get database credentials from environment variables with fallbacks
define('DB_HOST', getenv('DB_HOST') ?: getenv('MYSQL_HOST') ?: 'localhost');
define('DB_NAME', getenv('DB_NAME') ?: getenv('MYSQL_DATABASE') ?: 'canteen_management');
define('DB_USER', getenv('DB_USER') ?: getenv('MYSQL_USER') ?: 'root');
define('DB_PASS', getenv('DB_PASS') ?: getenv('MYSQL_PASSWORD') ?: '');
define('DB_PORT', getenv('DB_PORT') ?: getenv('MYSQL_PORT') ?: '3306');
define('DB_CHARSET', 'utf8mb4');

// For platforms that provide DATABASE_URL (like Railway, Heroku)
if (getenv('DATABASE_URL')) {
    $databaseUrl = parse_url(getenv('DATABASE_URL'));
    define('DB_HOST', $databaseUrl['host']);
    define('DB_NAME', ltrim($databaseUrl['path'], '/'));
    define('DB_USER', $databaseUrl['user']);
    define('DB_PASS', $databaseUrl['pass']);
    define('DB_PORT', $databaseUrl['port'] ?? '3306');
}

// Create database connection class
class Database {
    private $host = DB_HOST;
    private $db_name = DB_NAME;
    private $username = DB_USER;
    private $password = DB_PASS;
    private $port = DB_PORT;
    private $charset = DB_CHARSET;
    private $conn = null;

    /**
     * Get database connection
     * @return PDO|null
     */
    public function getConnection() {
        if ($this->conn === null) {
            try {
                $dsn = "mysql:host={$this->host};port={$this->port};dbname={$this->db_name};charset={$this->charset}";
                $options = [
                    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES   => false,
                    PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES {$this->charset}"
                ];

                $this->conn = new PDO($dsn, $this->username, $this->password, $options);
            } catch (PDOException $e) {
                error_log("Connection Error: " . $e->getMessage());

                // In production, show generic error
                if (getenv('APP_ENV') === 'production') {
                    die("Database connection failed. Please contact system administrator.");
                } else {
                    die("Database connection failed: " . $e->getMessage());
                }
            }
        }

        return $this->conn;
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

?>
