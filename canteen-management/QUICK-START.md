# 🚀 Quick Start Guide - Canteen Management System

## ✅ System is Ready for macOS!

The system has been completely revamped for **macOS compatibility** and **zero-configuration setup**.

---

## 📦 What You Need

1. **PHP 8.0+** - Check: `php -v`
2. **MySQL 8.0+** - Check: `mysql --version`
3. **MySQL Running** - Check: `pgrep -x "mysqld"`

---

## 🎯 Start the System (3 Simple Steps)

### Step 1: Import Database (First Time Only)

```bash
cd canteen-management

# Create and import database
mysql -u root -e "CREATE DATABASE canteen_management CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
mysql -u root canteen_management < sql/database.sql
```

### Step 2: Start Server

```bash
# Option A: Use the automatic script (Recommended)
./START.sh

# Option B: Manual start
php -S localhost:8000 router.php
```

### Step 3: Login

- **URL:** http://localhost:8000
- **Username:** `admin`
- **Password:** `admin123`

---

## 🎉 That's It!

The system will automatically:
- ✅ Detect macOS environment
- ✅ Use `127.0.0.1` for MySQL (macOS compatibility)
- ✅ Create required directories (tmp, uploads, logs)
- ✅ Set proper permissions
- ✅ Configure sessions
- ✅ Display helpful error messages

---

## 🐛 Troubleshooting

### Problem: "Database connection failed"

**Solution:**
```bash
# Start MySQL
sudo mysql.server start

# Verify it's running
pgrep -x "mysqld"

# Re-import database if needed
mysql -u root canteen_management < sql/database.sql
```

### Problem: "Permission denied"

**Solution:**
```bash
# Fix permissions
chmod -R 777 tmp uploads logs

# Or recreate directories
rm -rf tmp uploads logs
mkdir -p tmp/sessions uploads/students logs
chmod -R 777 tmp uploads logs
```

### Problem: "Port 8000 already in use"

**Solution:**
```bash
# Use a different port
php -S localhost:8080 router.php

# Or find what's using port 8000
lsof -i :8000
```

---

## 📚 Full Documentation

See **README-MACOS.md** for:
- Detailed troubleshooting guide
- Advanced configuration options
- Project structure
- Maintenance tasks
- Development notes

---

## 🔗 Key Files

| File | Purpose |
|------|---------|
| `START.sh` | One-click startup script |
| `init.php` | Auto-configuration engine |
| `router.php` | PHP server router |
| `README-MACOS.md` | Complete documentation |

---

## ⚡ Pro Tips

1. **Always use `./START.sh`** - It handles everything automatically
2. **Keep MySQL running** - Start with `sudo mysql.server start`
3. **Check logs** if issues occur - `tail -f logs/php_errors.log`
4. **Use 127.0.0.1** for database host on macOS (already configured)

---

## 🎓 What Changed?

This is **version 2.0.0** with major improvements:

- ✨ **Zero-configuration** - Works immediately after extraction
- 🍎 **macOS native** - Automatic environment detection
- 🚀 **One-command start** - Just run `./START.sh`
- 📊 **Better errors** - Clear messages with solutions
- 🔒 **Secure sessions** - Local tmp/ directory storage
- 🎯 **PHP 8.4 compatible** - Latest PHP features supported

---

## 💡 Need Help?

1. Check **README-MACOS.md** troubleshooting section
2. Verify MySQL is running: `pgrep -x "mysqld"`
3. Check error logs: `logs/php_errors.log`
4. Ensure database exists: `mysql -u root -e "SHOW DATABASES;"`

---

**Ready? Let's start!**

```bash
cd canteen-management
./START.sh
```

**Happy managing! 🎉**
