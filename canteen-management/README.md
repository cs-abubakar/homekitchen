# Yangtze University Canteen Management System

A comprehensive web-based canteen management system designed for Yangtze University to manage meal package subscriptions for international students. Built with PHP, MySQL, and Bootstrap 5.

![Version](https://img.shields.io/badge/version-1.0.0-blue)
![PHP](https://img.shields.io/badge/PHP-8.x-777BB4?logo=php)
![MySQL](https://img.shields.io/badge/MySQL-8.0-4479A1?logo=mysql)
![Bootstrap](https://img.shields.io/badge/Bootstrap-5.3-7952B3?logo=bootstrap)

## 📋 Table of Contents

- [Features](#features)
- [Requirements](#requirements)
- [Installation](#installation)
- [Database Setup](#database-setup)
- [Configuration](#configuration)
- [Default Credentials](#default-credentials)
- [Folder Structure](#folder-structure)
- [Usage Guide](#usage-guide)
- [Deployment](#deployment)
- [Cron Job Setup](#cron-job-setup)
- [Troubleshooting](#troubleshooting)
- [Security](#security)
- [License](#license)

## ✨ Features

### Student Management
- ✅ Complete student registration with profiles
- ✅ Photo upload and management
- ✅ Academic information tracking (batch, major)
- ✅ Emergency contact information
- ✅ Address management (home and China)
- ✅ Search, filter, and export functionality

### Package Management
- ✅ Multiple package types:
  - Full Package (500 RMB) - 2 meals/day
  - Single Package (280 RMB) - 1 meal/day
- ✅ Auto-calculated package duration (30 days)
- ✅ Package renewal system
- ✅ Expiry tracking with 7-day advance warnings
- ✅ Package history for each student

### Payment Management
- ✅ Cash payment tracking
- ✅ Auto-generated receipt numbers
- ✅ Payment history with filters
- ✅ Search by receipt, student, or package

### Dashboard & Reports
- ✅ Real-time metrics and KPIs
- ✅ Interactive charts (Chart.js):
  - Monthly revenue trends
  - Package distribution
  - Students by batch
- ✅ Expiring packages alerts
- ✅ Recent activity logs
- ✅ Financial reports with monthly/yearly breakdowns
- ✅ Export to Excel functionality

### Security Features
- ✅ Secure authentication with session management
- ✅ Password hashing (bcrypt)
- ✅ CSRF protection
- ✅ SQL injection prevention (PDO prepared statements)
- ✅ XSS protection
- ✅ Role-based access control (Admin/Staff)
- ✅ File upload validation
- ✅ Activity logging

### User Interface
- ✅ Responsive Bootstrap 5 design
- ✅ Mobile-friendly
- ✅ Clean and intuitive navigation
- ✅ DataTables for sortable/searchable tables
- ✅ Select2 for enhanced dropdowns
- ✅ Print-friendly pages
- ✅ Toast notifications

## 🔧 Requirements

- **Web Server:** Apache 2.4+ or Nginx
- **PHP:** 8.0 or higher
- **Database:** MySQL 5.7+ or MariaDB 10.3+
- **PHP Extensions:**
  - PDO
  - pdo_mysql
  - mbstring
  - gd (for image processing)
  - fileinfo

## 📦 Installation

### Step 1: Download and Extract

1. Download the project files
2. Extract to your web server directory:
   ```
   /var/www/html/canteen-management/  (Linux)
   C:\xampp\htdocs\canteen-management\  (Windows XAMPP)
   ```

### Step 2: Set Permissions

For Linux/Unix servers:
```bash
cd /path/to/canteen-management
chmod 755 -R .
chmod 777 -R uploads/
chmod 777 -R logs/
```

For shared hosting, use your hosting control panel to set permissions.

## 🗄️ Database Setup

### Method 1: Using phpMyAdmin

1. Open phpMyAdmin
2. Click "New" to create a database
3. Name it `canteen_management`
4. Select `utf8mb4_unicode_ci` collation
5. Click "Import" tab
6. Choose `sql/database.sql` file
7. Click "Go" to import

### Method 2: Using MySQL Command Line

```bash
mysql -u root -p
CREATE DATABASE canteen_management CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE canteen_management;
SOURCE /path/to/canteen-management/sql/database.sql;
EXIT;
```

### Method 3: Using cPanel

1. Login to cPanel
2. Open "MySQL Databases"
3. Create new database: `canteen_management`
4. Create MySQL user and set password
5. Add user to database with ALL PRIVILEGES
6. Open phpMyAdmin
7. Select the database
8. Import `sql/database.sql`

## ⚙️ Configuration

### Database Configuration

Edit `config/database.php`:

```php
define('DB_HOST', 'localhost');        // Database host
define('DB_NAME', 'canteen_management'); // Database name
define('DB_USER', 'your_username');     // Database username
define('DB_PASS', 'your_password');     // Database password
```

### Application Configuration

Edit `config/config.php`:

```php
// Update BASE_URL to match your installation
define('BASE_URL', 'http://localhost/canteen-management/');

// For production:
define('BASE_URL', 'https://yourdomain.com/canteen-management/');
```

### File Upload Directory

Ensure these directories exist and are writable:
- `uploads/students/` - for student photos
- `logs/` - for error logs

## 🔑 Default Credentials

**Administrator Account:**
- **Username:** `admin`
- **Password:** `admin123`

⚠️ **IMPORTANT:** Change the default password immediately after first login!

## 📁 Folder Structure

```
canteen-management/
├── assets/
│   ├── css/
│   │   └── style.css          # Custom styles
│   ├── js/
│   │   └── script.js          # Custom JavaScript
│   └── images/                # Application images
├── auth/
│   ├── login.php              # Login processing
│   ├── logout.php             # Logout processing
│   └── check_auth.php         # Authentication check
├── config/
│   ├── database.php           # Database connection
│   ├── config.php             # App configuration
│   └── session.php            # Session management
├── cron/
│   └── update_status.php      # Automated status updates
├── dashboard/
│   └── index.php              # Main dashboard
├── includes/
│   ├── header.php             # Header template
│   ├── footer.php             # Footer template
│   ├── navbar.php             # Navigation bar
│   └── functions.php          # Helper functions
├── packages/
│   ├── list.php               # All packages
│   ├── add.php                # Add new package
│   ├── renew.php              # Renew package
│   └── expiring.php           # Expiring packages
├── payments/
│   └── history.php            # Payment history
├── reports/
│   └── financial.php          # Financial reports
├── settings/
│   └── (admin settings)       # System settings
├── students/
│   ├── list.php               # All students
│   ├── add.php                # Add new student
│   ├── edit.php               # Edit student
│   ├── view.php               # View student profile
│   └── delete.php             # Delete student
├── sql/
│   └── database.sql           # Database schema
├── uploads/
│   └── students/              # Student photos
├── .htaccess                  # Apache configuration
├── index.php                  # Login page
└── README.md                  # This file
```

## 📖 Usage Guide

### Managing Students

1. **Add New Student:**
   - Navigate to Students → Add New
   - Fill in required fields (Passport, Name)
   - Upload photo (optional)
   - Save

2. **View Student Profile:**
   - Click on student name from list
   - View complete profile with packages and payments
   - Quick actions: Edit, Add Package, Renew

3. **Edit Student:**
   - Open student profile
   - Click Edit button
   - Update information
   - Save changes

### Managing Packages

1. **Create New Package:**
   - Navigate to Packages → Add New
   - Select student
   - Choose package type
   - Set start date (auto-calculates end date)
   - Enter payment details
   - System auto-generates package # and receipt #

2. **Renew Package:**
   - From expiring packages list or student profile
   - Click Renew
   - Confirm/adjust package details
   - System creates new package and expires old one

3. **Monitor Expiring Packages:**
   - Dashboard shows count of packages expiring in 7 days
   - Navigate to Packages → Expiring Soon
   - Contact students via WeChat/Phone
   - Renew packages directly from list

### Viewing Reports

1. **Financial Reports:**
   - Navigate to Reports → Financial
   - Select month/year
   - View revenue breakdown
   - Export or print report

2. **Dashboard Metrics:**
   - Real-time KPIs
   - Interactive charts
   - Recent activities
   - Quick actions

## 🚀 Deployment

### Hostinger Shared Hosting

1. **Upload Files:**
   - Use FTP/SFTP or File Manager
   - Upload to `public_html/canteen-management/`

2. **Create Database:**
   - Open MySQL Databases in cPanel
   - Create database and user
   - Import `sql/database.sql`

3. **Configure:**
   - Update `config/database.php` with DB credentials
   - Update `config/config.php` with correct BASE_URL

4. **Set Permissions:**
   - `uploads/` → 755 or 777
   - `logs/` → 755 or 777

5. **Test:**
   - Visit `https://yourdomain.com/canteen-management/`
   - Login with default credentials
   - Change admin password

### Security Checklist for Production

- [ ] Change default admin password
- [ ] Update database credentials
- [ ] Enable HTTPS/SSL
- [ ] Set proper file permissions
- [ ] Disable error display (set in `config/config.php`)
- [ ] Update `.htaccess` rules
- [ ] Set up regular database backups
- [ ] Change cron security token

## ⏰ Cron Job Setup

The system needs to run a daily script to update package statuses.

### Option 1: Cron Job (Linux Hosting)

Add to crontab:
```bash
0 0 * * * /usr/bin/php /path/to/canteen-management/cron/update_status.php
```

### Option 2: HTTP Request (Shared Hosting)

Set up a cron job to visit:
```
https://yourdomain.com/canteen-management/cron/update_status.php?token=YOUR_SECURITY_TOKEN
```

**Security:** Update the token in `cron/update_status.php`:
```php
$expectedToken = 'CHANGE_THIS_TO_RANDOM_STRING';
```

### Option 3: Manual (Not Recommended)

The script also runs automatically on page loads, but this is not ideal for production.

## 🔍 Troubleshooting

### Database Connection Error

**Problem:** "Database connection failed"

**Solution:**
1. Check database credentials in `config/database.php`
2. Verify database exists
3. Check database user has proper privileges
4. Test connection: `mysql -u username -p -h localhost database_name`

### Upload Permission Error

**Problem:** "Failed to move uploaded file"

**Solution:**
```bash
chmod 777 uploads/students/
# Or via FTP: Set folder permissions to 777
```

### Session Expired Too Quickly

**Solution:** Edit `config/config.php`:
```php
define('SESSION_TIMEOUT', 3600); // 1 hour instead of 30 minutes
```

### Blank Page or 500 Error

**Solution:**
1. Enable error display temporarily:
   ```php
   // In config/config.php
   error_reporting(E_ALL);
   ini_set('display_errors', 1);
   ```
2. Check error logs
3. Verify PHP version (8.0+)
4. Check file permissions

### DataTables Not Working

**Solution:**
1. Check browser console for JavaScript errors
2. Ensure jQuery loads before DataTables
3. Clear browser cache
4. Check CDN links are accessible

## 🔒 Security

### Built-in Security Features

1. **Authentication:** Secure session-based authentication
2. **Password Hashing:** bcrypt with PASSWORD_DEFAULT
3. **SQL Injection:** PDO prepared statements throughout
4. **XSS Protection:** htmlspecialchars() on all output
5. **CSRF Protection:** Token validation on forms
6. **File Upload Security:** Type and size validation
7. **Access Control:** Role-based permissions
8. **Activity Logging:** All important actions logged

### Security Best Practices

1. Always use HTTPS in production
2. Keep PHP and database updated
3. Regular security audits
4. Strong passwords policy
5. Regular backups
6. Monitor error logs
7. Limit file upload sizes
8. Use prepared statements for all queries

## 📞 Support

For issues, questions, or contributions:

- **Email:** canteen@yangtze.edu.cn
- **Documentation:** See inline code comments
- **University IT Support:** Contact IT department

## 📄 License

This system is proprietary software developed for Yangtze University.

© 2025 Yangtze University. All rights reserved.

---

## 🎯 Quick Start Guide

1. **Install:** Extract files to web server
2. **Database:** Import `sql/database.sql`
3. **Configure:** Update `config/database.php` and `config/config.php`
4. **Login:** Use `admin` / `admin123`
5. **Change Password:** Update admin password immediately
6. **Add Students:** Start adding student records
7. **Create Packages:** Assign meal packages
8. **Monitor:** Check dashboard for expiring packages

## 📊 System Requirements Summary

| Component | Requirement |
|-----------|-------------|
| PHP | 8.0+ |
| MySQL | 5.7+ / MariaDB 10.3+ |
| Web Server | Apache 2.4+ / Nginx |
| Disk Space | 100MB minimum |
| RAM | 256MB minimum |
| Browser | Modern browser (Chrome, Firefox, Edge, Safari) |

---

**Thank you for using the Yangtze University Canteen Management System!**
