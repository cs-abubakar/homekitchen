-- ============================================
-- Canteen Management System Database Schema
-- Yangtze University
-- ============================================

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

-- Create database
CREATE DATABASE IF NOT EXISTS canteen_management CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE canteen_management;

-- ============================================
-- Table: users (Admin/Staff accounts)
-- ============================================
CREATE TABLE `users` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `username` VARCHAR(50) UNIQUE NOT NULL,
    `password` VARCHAR(255) NOT NULL,
    `full_name` VARCHAR(100) NOT NULL,
    `email` VARCHAR(100),
    `role` ENUM('admin', 'staff') DEFAULT 'staff',
    `is_active` TINYINT(1) DEFAULT 1,
    `last_login` DATETIME,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_username` (`username`),
    INDEX `idx_is_active` (`is_active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- Table: students
-- ============================================
CREATE TABLE `students` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `passport_number` VARCHAR(50) UNIQUE NOT NULL,
    `full_name` VARCHAR(100) NOT NULL,
    `email` VARCHAR(100),
    `phone` VARCHAR(20),
    `wechat_id` VARCHAR(50),
    `photo` VARCHAR(255),
    `batch_year` VARCHAR(10),
    `major` VARCHAR(100),
    `home_address` TEXT,
    `china_address` TEXT,
    `emergency_contact` VARCHAR(100),
    `emergency_phone` VARCHAR(20),
    `is_active` TINYINT(1) DEFAULT 1,
    `created_by` INT,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (`created_by`) REFERENCES `users`(`id`) ON DELETE SET NULL,
    INDEX `idx_passport` (`passport_number`),
    INDEX `idx_full_name` (`full_name`),
    INDEX `idx_batch_year` (`batch_year`),
    INDEX `idx_is_active` (`is_active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- Table: package_types
-- ============================================
CREATE TABLE `package_types` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(50) NOT NULL,
    `price` DECIMAL(10,2) NOT NULL,
    `meals_per_day` INT NOT NULL,
    `meal_times` VARCHAR(100), -- 'Brunch,Dinner' or 'Brunch' or 'Dinner'
    `duration_days` INT DEFAULT 30,
    `description` TEXT,
    `is_active` TINYINT(1) DEFAULT 1,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_is_active` (`is_active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- Table: packages
-- ============================================
CREATE TABLE `packages` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `package_number` VARCHAR(20) UNIQUE NOT NULL, -- Auto-generated: PKG-YYYYMMDD-001
    `student_id` INT NOT NULL,
    `package_type_id` INT NOT NULL,
    `start_date` DATE NOT NULL,
    `end_date` DATE NOT NULL,
    `amount_paid` DECIMAL(10,2) NOT NULL,
    `payment_date` DATE NOT NULL,
    `status` ENUM('active', 'expired', 'cancelled') DEFAULT 'active',
    `remaining_days` INT,
    `notes` TEXT,
    `created_by` INT,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (`student_id`) REFERENCES `students`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`package_type_id`) REFERENCES `package_types`(`id`) ON DELETE RESTRICT,
    FOREIGN KEY (`created_by`) REFERENCES `users`(`id`) ON DELETE SET NULL,
    INDEX `idx_package_number` (`package_number`),
    INDEX `idx_student_id` (`student_id`),
    INDEX `idx_status` (`status`),
    INDEX `idx_end_date` (`end_date`),
    INDEX `idx_remaining_days` (`remaining_days`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- Table: payments
-- ============================================
CREATE TABLE `payments` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `receipt_number` VARCHAR(20) UNIQUE NOT NULL, -- Auto: RCP-YYYYMMDD-001
    `package_id` INT NOT NULL,
    `student_id` INT NOT NULL,
    `amount` DECIMAL(10,2) NOT NULL,
    `payment_date` DATE NOT NULL,
    `payment_method` VARCHAR(20) DEFAULT 'Cash',
    `received_by` INT,
    `notes` TEXT,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`package_id`) REFERENCES `packages`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`student_id`) REFERENCES `students`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`received_by`) REFERENCES `users`(`id`) ON DELETE SET NULL,
    INDEX `idx_receipt_number` (`receipt_number`),
    INDEX `idx_package_id` (`package_id`),
    INDEX `idx_student_id` (`student_id`),
    INDEX `idx_payment_date` (`payment_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- Table: activity_logs
-- ============================================
CREATE TABLE `activity_logs` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT,
    `action` VARCHAR(100) NOT NULL,
    `table_name` VARCHAR(50),
    `record_id` INT,
    `description` TEXT,
    `ip_address` VARCHAR(45),
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE SET NULL,
    INDEX `idx_user_id` (`user_id`),
    INDEX `idx_created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- Table: system_settings
-- ============================================
CREATE TABLE `system_settings` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `setting_key` VARCHAR(50) UNIQUE NOT NULL,
    `setting_value` TEXT,
    `description` VARCHAR(255),
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_setting_key` (`setting_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- Insert Default Admin User
-- Password: admin123 (hashed with PASSWORD_DEFAULT)
-- ============================================
INSERT INTO `users` (`username`, `password`, `full_name`, `email`, `role`, `is_active`) VALUES
('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'System Administrator', 'admin@yangtze.edu.cn', 'admin', 1);

-- ============================================
-- Insert Default Package Types
-- ============================================
INSERT INTO `package_types` (`name`, `price`, `meals_per_day`, `meal_times`, `duration_days`, `description`, `is_active`) VALUES
('Full Package', 500.00, 2, 'Brunch,Dinner', 30, '2 meals per day - Brunch and Dinner', 1),
('Single Package (Brunch)', 280.00, 1, 'Brunch', 30, '1 meal per day - Brunch only', 1),
('Single Package (Dinner)', 280.00, 1, 'Dinner', 30, '1 meal per day - Dinner only', 1);

-- ============================================
-- Insert System Settings
-- ============================================
INSERT INTO `system_settings` (`setting_key`, `setting_value`, `description`) VALUES
('app_name', 'Yangtze University Canteen Management System', 'Application name'),
('app_short_name', 'YUCMS', 'Short application name'),
('university_name', 'Yangtze University', 'University name'),
('contact_email', 'canteen@yangtze.edu.cn', 'Contact email address'),
('contact_phone', '+86 123 4567 8900', 'Contact phone number'),
('currency', 'RMB', 'Currency symbol'),
('date_format', 'Y-m-d', 'Date format for display'),
('items_per_page', '50', 'Number of items per page in tables'),
('session_timeout', '1800', 'Session timeout in seconds (30 minutes)'),
('expiry_warning_days', '7', 'Days before expiry to show warning'),
('enable_email_notifications', '0', 'Enable email notifications'),
('timezone', 'Asia/Shanghai', 'System timezone');

-- ============================================
-- Insert Sample Students for Testing
-- ============================================
INSERT INTO `students` (`passport_number`, `full_name`, `email`, `phone`, `wechat_id`, `batch_year`, `major`, `home_address`, `china_address`, `emergency_contact`, `emergency_phone`, `is_active`, `created_by`) VALUES
('P12345678', 'John Smith', 'john.smith@student.yangtze.edu.cn', '+1234567890', 'johnsmith_wx', '2024', 'Computer Science', '123 Main St, New York, USA', 'Dorm Building 5, Room 301, Yangtze University', 'Mary Smith', '+1234567891', 1, 1),
('P23456789', 'Maria Garcia', 'maria.garcia@student.yangtze.edu.cn', '+3456789012', 'mariagarcia_wx', '2024', 'Business Administration', '456 Oak Ave, Madrid, Spain', 'International Student Apartment 2, Room 205', 'Carlos Garcia', '+3456789013', 1, 1),
('P34567890', 'Ahmed Hassan', 'ahmed.hassan@student.yangtze.edu.cn', '+2345678901', 'ahmedhassan_wx', '2023', 'Mechanical Engineering', '789 Palm St, Cairo, Egypt', 'Dorm Building 8, Room 102', 'Fatima Hassan', '+2345678902', 1, 1),
('P45678901', 'Yuki Tanaka', 'yuki.tanaka@student.yangtze.edu.cn', '+8198765432', 'yukitanaka_wx', '2024', 'International Relations', '321 Sakura St, Tokyo, Japan', 'International Student Apartment 1, Room 401', 'Hiroshi Tanaka', '+8198765433', 1, 1),
('P56789012', 'Sophie Dubois', 'sophie.dubois@student.yangtze.edu.cn', '+3312345678', 'sophiedubois_wx', '2023', 'Medicine', '654 Rue de Paris, Lyon, France', 'Dorm Building 3, Room 505', 'Pierre Dubois', '+3312345679', 1, 1);

-- ============================================
-- Insert Sample Packages for Testing
-- ============================================
-- Active packages
INSERT INTO `packages` (`package_number`, `student_id`, `package_type_id`, `start_date`, `end_date`, `amount_paid`, `payment_date`, `status`, `remaining_days`, `notes`, `created_by`) VALUES
('PKG-20251001-001', 1, 1, '2025-10-01', '2025-10-31', 500.00, '2025-10-01', 'active', NULL, 'Initial package', 1),
('PKG-20251001-002', 2, 2, '2025-10-01', '2025-10-31', 280.00, '2025-10-01', 'active', NULL, 'Brunch only package', 1),
('PKG-20251005-001', 3, 1, '2025-10-05', '2025-11-04', 500.00, '2025-10-05', 'active', NULL, 'Regular subscription', 1),

-- Expiring soon packages (for testing alerts)
('PKG-20251001-003', 4, 1, '2025-10-01', '2025-11-05', 500.00, '2025-10-01', 'active', NULL, 'Expiring soon', 1),
('PKG-20251002-001', 5, 3, '2025-10-02', '2025-11-06', 280.00, '2025-10-02', 'active', NULL, 'Dinner only - expiring soon', 1),

-- Expired packages
('PKG-20250901-001', 1, 1, '2025-09-01', '2025-09-30', 500.00, '2025-09-01', 'expired', 0, 'Previous month package', 1),
('PKG-20250901-002', 2, 2, '2025-09-01', '2025-09-30', 280.00, '2025-09-01', 'expired', 0, 'Previous month package', 1),
('PKG-20250801-001', 3, 1, '2025-08-01', '2025-08-31', 500.00, '2025-08-01', 'expired', 0, 'Two months ago', 1);

-- ============================================
-- Insert Sample Payments for Testing
-- ============================================
INSERT INTO `payments` (`receipt_number`, `package_id`, `student_id`, `amount`, `payment_date`, `payment_method`, `received_by`, `notes`) VALUES
('RCP-20251001-001', 1, 1, 500.00, '2025-10-01', 'Cash', 1, 'Payment received in full'),
('RCP-20251001-002', 2, 2, 280.00, '2025-10-01', 'Cash', 1, 'Payment received in full'),
('RCP-20251005-001', 3, 3, 500.00, '2025-10-05', 'Cash', 1, 'Payment received in full'),
('RCP-20251001-003', 4, 4, 500.00, '2025-10-01', 'Cash', 1, 'Payment received in full'),
('RCP-20251002-001', 5, 5, 280.00, '2025-10-02', 'Cash', 1, 'Payment received in full'),
('RCP-20250901-001', 6, 1, 500.00, '2025-09-01', 'Cash', 1, 'Previous month payment'),
('RCP-20250901-002', 7, 2, 280.00, '2025-09-01', 'Cash', 1, 'Previous month payment'),
('RCP-20250801-001', 8, 3, 500.00, '2025-08-01', 'Cash', 1, 'Two months ago payment');

-- ============================================
-- Insert Sample Activity Logs
-- ============================================
INSERT INTO `activity_logs` (`user_id`, `action`, `table_name`, `record_id`, `description`, `ip_address`) VALUES
(1, 'CREATE', 'students', 1, 'Added new student: John Smith', '127.0.0.1'),
(1, 'CREATE', 'students', 2, 'Added new student: Maria Garcia', '127.0.0.1'),
(1, 'CREATE', 'students', 3, 'Added new student: Ahmed Hassan', '127.0.0.1'),
(1, 'CREATE', 'packages', 1, 'Created package PKG-20251001-001 for student John Smith', '127.0.0.1'),
(1, 'CREATE', 'packages', 2, 'Created package PKG-20251001-002 for student Maria Garcia', '127.0.0.1'),
(1, 'CREATE', 'payments', 1, 'Recorded payment RCP-20251001-001 - 500.00 RMB', '127.0.0.1');

-- ============================================
-- Create Views for Easy Reporting
-- ============================================

-- View: Active packages with student details
CREATE OR REPLACE VIEW `v_active_packages` AS
SELECT
    p.id,
    p.package_number,
    s.id AS student_id,
    s.passport_number,
    s.full_name AS student_name,
    s.phone,
    s.wechat_id,
    s.batch_year,
    s.major,
    pt.name AS package_type,
    pt.price AS package_price,
    pt.meals_per_day,
    p.start_date,
    p.end_date,
    p.amount_paid,
    p.status,
    p.remaining_days,
    DATEDIFF(p.end_date, CURDATE()) AS days_until_expiry
FROM packages p
JOIN students s ON p.student_id = s.id
JOIN package_types pt ON p.package_type_id = pt.id
WHERE p.status = 'active' AND s.is_active = 1;

-- View: Expiring packages (within 7 days)
CREATE OR REPLACE VIEW `v_expiring_packages` AS
SELECT
    p.id,
    p.package_number,
    s.id AS student_id,
    s.passport_number,
    s.full_name AS student_name,
    s.phone,
    s.wechat_id,
    pt.name AS package_type,
    p.end_date,
    DATEDIFF(p.end_date, CURDATE()) AS days_remaining
FROM packages p
JOIN students s ON p.student_id = s.id
JOIN package_types pt ON p.package_type_id = pt.id
WHERE p.status = 'active'
    AND s.is_active = 1
    AND DATEDIFF(p.end_date, CURDATE()) BETWEEN 0 AND 7
ORDER BY days_remaining ASC;

-- View: Monthly revenue summary
CREATE OR REPLACE VIEW `v_monthly_revenue` AS
SELECT
    DATE_FORMAT(payment_date, '%Y-%m') AS month,
    COUNT(*) AS payment_count,
    SUM(amount) AS total_revenue
FROM payments
GROUP BY DATE_FORMAT(payment_date, '%Y-%m')
ORDER BY month DESC;

-- ============================================
-- Stored Procedures
-- ============================================

-- Procedure to update package statuses (run daily)
DELIMITER $$
CREATE PROCEDURE `update_package_statuses`()
BEGIN
    -- Update remaining days for all active packages
    UPDATE packages
    SET remaining_days = DATEDIFF(end_date, CURDATE())
    WHERE status = 'active';

    -- Mark packages as expired if end_date has passed
    UPDATE packages
    SET status = 'expired', remaining_days = 0
    WHERE status = 'active' AND end_date < CURDATE();

    SELECT ROW_COUNT() AS packages_updated;
END$$
DELIMITER ;

-- Procedure to generate next package number
DELIMITER $$
CREATE PROCEDURE `get_next_package_number`(OUT next_number VARCHAR(20))
BEGIN
    DECLARE today_prefix VARCHAR(15);
    DECLARE today_count INT;

    SET today_prefix = CONCAT('PKG-', DATE_FORMAT(CURDATE(), '%Y%m%d'), '-');

    SELECT COUNT(*) INTO today_count
    FROM packages
    WHERE package_number LIKE CONCAT(today_prefix, '%');

    SET next_number = CONCAT(today_prefix, LPAD(today_count + 1, 3, '0'));
END$$
DELIMITER ;

-- Procedure to generate next receipt number
DELIMITER $$
CREATE PROCEDURE `get_next_receipt_number`(OUT next_number VARCHAR(20))
BEGIN
    DECLARE today_prefix VARCHAR(15);
    DECLARE today_count INT;

    SET today_prefix = CONCAT('RCP-', DATE_FORMAT(CURDATE(), '%Y%m%d'), '-');

    SELECT COUNT(*) INTO today_count
    FROM payments
    WHERE receipt_number LIKE CONCAT(today_prefix, '%');

    SET next_number = CONCAT(today_prefix, LPAD(today_count + 1, 3, '0'));
END$$
DELIMITER ;

COMMIT;

-- ============================================
-- End of Database Schema
-- ============================================
