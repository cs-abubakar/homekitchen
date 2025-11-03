# Canteen Management System - macOS Setup Guide

## 🎯 Quick Start (TL;DR)

```bash
cd canteen-management
./START.sh
```

Then open: **http://localhost:8000**
Login: **admin** / **admin123**

---

## 📋 Prerequisites

### 1. Install PHP 8+ (if not installed)

```bash
# Check if PHP is installed
php -v

# If not installed, install via Homebrew
brew install php
```

### 2. Install MySQL 8+ (if not installed)

```bash
# Install MySQL via Homebrew
brew install mysql

# Start MySQL service
brew services start mysql

# Or start manually
mysql.server start

# Secure your installation (optional)
mysql_secure_installation
```

### 3. Verify Installation

```bash
php -v        # Should show PHP 8.0+
mysql --version  # Should show MySQL 8.0+
```

---

## 🚀 Installation Steps

### Method 1: Automatic (Recommended)

1. **Navigate to the project directory:**
   ```bash
   cd /path/to/canteen-management
   ```

2. **Run the START script:**
   ```bash
   chmod +x START.sh  # Make executable (first time only)
   ./START.sh
   ```

3. **Access the system:**
   - Open browser: `http://localhost:8000`
   - Login: `admin` / `admin123`

### Method 2: Manual Setup

1. **Import the database:**
   ```bash
   # Create database
   mysql -u root -e "CREATE DATABASE canteen_management CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"

   # Import schema
   mysql -u root canteen_management < sql/database.sql

   # Verify import
   mysql -u root -e "USE canteen_management; SHOW TABLES;"
   ```

2. **Create directories:**
   ```bash
   mkdir -p tmp/sessions uploads/students logs
   chmod -R 777 tmp uploads logs
   ```

3. **Start PHP server:**
   ```bash
   php -S localhost:8000 router.php
   ```

4. **Access the system:**
   - URL: `http://localhost:8000`
   - Login: `admin` / `admin123`

---

## ⚙️ Configuration

### Database Settings (if needed)

Edit `init.php` to change database credentials:

```php
// Around line 80
define('DB_HOST', '127.0.0.1');  // Use 127.0.0.1 for macOS
define('DB_NAME', 'canteen_management');
define('DB_USER', 'root');
define('DB_PASS', '');  // Add your MySQL password here
```

### Change Port (if 8000 is in use)

```bash
# Use a different port
php -S localhost:8080 router.php
```

---

## 🐛 Troubleshooting

### Issue 1: "Database connection failed"

**Solution:**

1. **Check if MySQL is running:**
   ```bash
   pgrep -x "mysqld"  # Should return a process ID
   ```

2. **Start MySQL:**
   ```bash
   sudo mysql.server start
   # OR
   brew services start mysql
   ```

3. **Verify database exists:**
   ```bash
   mysql -u root -e "SHOW DATABASES LIKE 'canteen_management';"
   ```

4. **Re-import database if needed:**
   ```bash
   mysql -u root canteen_management < sql/database.sql
   ```

### Issue 2: "Permission denied" or "Cannot write to directory"

**Solution:**

```bash
# Fix permissions
chmod -R 777 tmp uploads logs

# Recreate directories
rm -rf tmp uploads logs
mkdir -p tmp/sessions uploads/students logs
chmod -R 777 tmp uploads logs
```

### Issue 3: "Parse error" in PHP files

**Solution:**

Ensure you're using PHP 8.0+:

```bash
php -v  # Check version

# If PHP 7.x, upgrade:
brew upgrade php
```

### Issue 4: "Port already in use"

**Solution:**

```bash
# Find what's using port 8000
lsof -i :8000

# Kill the process or use a different port
php -S localhost:8080 router.php
```

### Issue 5: "Session expired" immediately after login

**Solution:**

```bash
# Ensure session directory is writable
chmod -R 777 tmp/sessions

# Clear existing sessions
rm -rf tmp/sessions/*
```

### Issue 6: MySQL connection using 'localhost' fails on macOS

**Solution:**

The system automatically uses `127.0.0.1` instead of `localhost` on macOS. This is configured in `init.php`.

If issues persist, manually edit `init.php`:

```php
define('DB_HOST', '127.0.0.1');  // Force IP instead of hostname
```

---

## 📁 Project Structure

```
canteen-management/
├── START.sh                    # One-click startup script
├── router.php                  # Router for PHP built-in server
├── init.php                    # Auto-configuration & environment detection
├── index.php                   # Login page
├── config/
│   ├── config.php             # Application config (uses init.php)
│   ├── database.php           # Database connection with error handling
│   └── session.php            # Session management functions
├── auth/
│   ├── login.php              # Login processing
│   ├── logout.php             # Logout handling
│   └── check_auth.php         # Authentication middleware
├── dashboard/
│   └── index.php              # Main dashboard
├── students/
│   ├── list.php               # Student listing
│   ├── add.php                # Add student
│   ├── edit.php               # Edit student
│   └── view.php               # View student details
├── packages/
│   ├── list.php               # Package listing
│   ├── add.php                # Add package
│   ├── renew.php              # Renew package
│   └── expiring.php           # Expiring packages
├── payments/
│   └── history.php            # Payment history
├── reports/
│   └── financial.php          # Financial reports
├── sql/
│   └── database.sql           # Database schema & sample data
├── tmp/                        # Auto-created session storage
├── uploads/                    # Auto-created file uploads
└── logs/                       # Auto-created error logs
```

---

## 🔐 Default Credentials

After installation, login with:

- **Username:** `admin`
- **Password:** `admin123`

**⚠️ Change the password immediately after first login!**

---

## ✨ Features

- ✅ **Auto-detection** of macOS environment
- ✅ **Automatic directory creation** (tmp, uploads, logs)
- ✅ **127.0.0.1 database host** for macOS compatibility
- ✅ **Clear error messages** with troubleshooting steps
- ✅ **PHP 8.4 compatible** with full error reporting in development
- ✅ **One-command startup** with START.sh
- ✅ **No manual configuration** required
- ✅ **Session storage** in local tmp/ directory
- ✅ **Works offline** - no internet required after setup

---

## 🎯 System Requirements

| Requirement | Version | Status |
|------------|---------|--------|
| PHP | 8.0+ | ✅ Required |
| MySQL | 8.0+ | ✅ Required |
| macOS | 10.15+ | ✅ Recommended |
| Browser | Modern | ✅ Required |

---

## 🔧 Advanced Configuration

### Enable Production Mode

Edit `init.php` and change:

```php
define('ENVIRONMENT', 'development');  // Change to 'production'
```

### Change Session Timeout

Edit `init.php`:

```php
define('SESSION_TIMEOUT', 1800);  // 30 minutes (in seconds)
```

### Custom MySQL Password

Edit `init.php`:

```php
define('DB_PASS', 'your_password_here');
```

---

## 📊 Database Schema

The system includes:

- **7 Tables:** users, students, package_types, packages, payments, activity_logs, system_settings
- **3 Views:** v_active_packages, v_expiring_packages, v_revenue_summary
- **3 Stored Procedures:** update_package_statuses, get_monthly_revenue, cleanup_old_logs

Sample data includes:
- 1 admin user (admin/admin123)
- 5 sample students
- 8 sample packages
- 8 sample payments

---

## 🛠 Maintenance

### Clear Sessions

```bash
rm -rf tmp/sessions/*
```

### Clear Logs

```bash
rm -rf logs/*.log
```

### Reset Database

```bash
mysql -u root -e "DROP DATABASE canteen_management;"
mysql -u root -e "CREATE DATABASE canteen_management CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
mysql -u root canteen_management < sql/database.sql
```

### Update System

```bash
git pull origin main
# Restart server
./START.sh
```

---

## 📝 Development Notes

### Running Tests

```bash
# No automated tests yet - manual testing required
```

### Debug Mode

Already enabled in development mode. Check logs:

```bash
tail -f logs/php_errors.log
```

### Adding New Modules

1. Create new directory under root (e.g., `inventory/`)
2. Add `index.php` with authentication:
   ```php
   <?php
   require_once '../auth/check_auth.php';
   // Your code here
   ?>
   ```

---

## 🤝 Support

For issues or questions:

1. Check this README's **Troubleshooting** section
2. Check `logs/php_errors.log` for errors
3. Verify database connection on login page
4. Ensure MySQL is running: `pgrep -x "mysqld"`

---

## 📄 License

Proprietary - Yangtze University
© 2024 All Rights Reserved

---

## 🎓 Credits

**Developed for:** Yangtze University
**System:** Canteen Management System
**Version:** 2.0.0 (macOS Optimized)
**PHP:** 8.0+ Compatible
**Database:** MySQL 8.0+

---

## ✅ Checklist Before First Run

- [ ] PHP 8+ installed (`php -v`)
- [ ] MySQL 8+ installed (`mysql --version`)
- [ ] MySQL service running (`pgrep -x "mysqld"`)
- [ ] Database imported (`mysql -u root canteen_management < sql/database.sql`)
- [ ] Permissions set (`chmod -R 777 tmp uploads logs`)
- [ ] Server started (`./START.sh`)
- [ ] Browser opened (`http://localhost:8000`)
- [ ] Logged in successfully (`admin / admin123`)

---

**Ready to start? Run:**

```bash
./START.sh
```

**🚀 Happy Managing!**
