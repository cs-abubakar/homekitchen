<?php
/**
 * Demo Database Configuration
 * For testing without MySQL
 */

// Mock database class that simulates database operations
class DemoDatabase {
    private $mockData = [];

    public function __construct() {
        $this->initializeMockData();
    }

    public function getConnection() {
        return $this;
    }

    public function prepare($sql) {
        return new DemoStatement($sql, $this->mockData);
    }

    public function query($sql) {
        return new DemoStatement($sql, $this->mockData);
    }

    public function beginTransaction() { return true; }
    public function commit() { return true; }
    public function rollBack() { return true; }
    public function lastInsertId() { return rand(100, 999); }

    private function initializeMockData() {
        $this->mockData = [
            'users' => [
                ['id' => 1, 'username' => 'admin', 'password' => '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'full_name' => 'System Administrator', 'email' => 'admin@yangtze.edu.cn', 'role' => 'admin', 'is_active' => 1]
            ],
            'students' => [
                ['id' => 1, 'passport_number' => 'P12345678', 'full_name' => 'John Smith', 'email' => 'john@student.edu.cn', 'phone' => '+1234567890', 'wechat_id' => 'johnsmith_wx', 'photo' => null, 'batch_year' => '2024', 'major' => 'Computer Science', 'is_active' => 1],
                ['id' => 2, 'passport_number' => 'P23456789', 'full_name' => 'Maria Garcia', 'email' => 'maria@student.edu.cn', 'phone' => '+3456789012', 'wechat_id' => 'maria_wx', 'photo' => null, 'batch_year' => '2024', 'major' => 'Business', 'is_active' => 1],
                ['id' => 3, 'passport_number' => 'P34567890', 'full_name' => 'Ahmed Hassan', 'email' => 'ahmed@student.edu.cn', 'phone' => '+2345678901', 'wechat_id' => 'ahmed_wx', 'photo' => null, 'batch_year' => '2023', 'major' => 'Engineering', 'is_active' => 1],
            ],
            'packages' => [
                ['id' => 1, 'package_number' => 'PKG-20251101-001', 'student_id' => 1, 'package_type_id' => 1, 'start_date' => '2025-11-01', 'end_date' => '2025-12-01', 'amount_paid' => 500.00, 'status' => 'active', 'remaining_days' => 30],
                ['id' => 2, 'package_number' => 'PKG-20251101-002', 'student_id' => 2, 'package_type_id' => 2, 'start_date' => '2025-10-28', 'end_date' => '2025-11-05', 'amount_paid' => 280.00, 'status' => 'active', 'remaining_days' => 4],
            ],
            'payments' => [
                ['id' => 1, 'receipt_number' => 'RCP-20251101-001', 'package_id' => 1, 'student_id' => 1, 'amount' => 500.00, 'payment_date' => '2025-11-01'],
                ['id' => 2, 'receipt_number' => 'RCP-20251028-001', 'package_id' => 2, 'student_id' => 2, 'amount' => 280.00, 'payment_date' => '2025-10-28'],
            ],
            'package_types' => [
                ['id' => 1, 'name' => 'Full Package', 'price' => 500.00, 'meals_per_day' => 2, 'meal_times' => 'Brunch,Dinner', 'duration_days' => 30, 'is_active' => 1],
                ['id' => 2, 'name' => 'Single Package (Brunch)', 'price' => 280.00, 'meals_per_day' => 1, 'meal_times' => 'Brunch', 'duration_days' => 30, 'is_active' => 1],
            ]
        ];
    }
}

class DemoStatement {
    private $sql;
    private $mockData;
    private $results = [];

    public function __construct($sql, $mockData) {
        $this->sql = $sql;
        $this->mockData = $mockData;
        $this->executeMockQuery();
    }

    public function execute($params = []) {
        return true;
    }

    public function fetch() {
        if (empty($this->results)) return false;
        return array_shift($this->results);
    }

    public function fetchAll() {
        return $this->results;
    }

    private function executeMockQuery() {
        // Simple mock logic - return data based on keywords in query
        if (stripos($this->sql, 'SELECT') !== false) {
            if (stripos($this->sql, 'users') !== false) {
                $this->results = $this->mockData['users'];
            } elseif (stripos($this->sql, 'students') !== false) {
                $this->results = $this->mockData['students'];
            } elseif (stripos($this->sql, 'packages') !== false && stripos($this->sql, 'package_types') === false) {
                $this->results = $this->mockData['packages'];
            } elseif (stripos($this->sql, 'package_types') !== false) {
                $this->results = $this->mockData['package_types'];
            } elseif (stripos($this->sql, 'payments') !== false) {
                $this->results = $this->mockData['payments'];
            } elseif (stripos($this->sql, 'COUNT') !== false) {
                $this->results = [['count' => count($this->mockData['students'])]];
            } elseif (stripos($this->sql, 'SUM') !== false) {
                $this->results = [['total' => 1560.00, 'total_revenue' => 1560.00]];
            } else {
                $this->results = [];
            }
        }

        // For views
        if (stripos($this->sql, 'v_expiring_packages') !== false) {
            $this->results = [
                ['id' => 2, 'package_number' => 'PKG-20251101-002', 'student_id' => 2, 'student_name' => 'Maria Garcia', 'passport_number' => 'P23456789', 'wechat_id' => 'maria_wx', 'phone' => '+3456789012', 'package_type' => 'Single Package (Brunch)', 'end_date' => '2025-11-05', 'days_remaining' => 4]
            ];
        }
    }
}

function getDB() {
    static $db = null;
    if ($db === null) {
        $db = new DemoDatabase();
    }
    return $db->getConnection();
}
?>
