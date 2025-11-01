<?php
/**
 * Database Configuration
 * Canteen Management System - Yangtze University
 */

// Database configuration constants
define('DB_HOST', 'localhost');           // Database host
define('DB_NAME', 'canteen_management');  // Database name
define('DB_USER', 'root');                // Database username (change for production)
define('DB_PASS', '');                    // Database password (change for production)
define('DB_CHARSET', 'utf8mb4');          // Character set

// Create database connection class
class Database {
    private $host = DB_HOST;
    private $db_name = DB_NAME;
    private $username = DB_USER;
    private $password = DB_PASS;
    private $charset = DB_CHARSET;
    private $conn = null;

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
                    PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES {$this->charset}"
                ];

                $this->conn = new PDO($dsn, $this->username, $this->password, $options);
            } catch (PDOException $e) {
                error_log("Connection Error: " . $e->getMessage());
                die("Database connection failed. Please contact system administrator.");
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
