# 🚀 Platform-Specific Deployment Guide

This guide covers automatic deployment to Railway, Render, Heroku, and other platforms.

---

## ✅ **CONFIGURATION FILES INCLUDED:**

All necessary files for auto-detection:
- ✅ `composer.json` - PHP app detection
- ✅ `Procfile` - Process management
- ✅ `shipit.yml` - Shipit configuration
- ✅ `railway.json` - Railway configuration
- ✅ `render.yaml` - Render configuration
- ✅ `nixpacks.toml` - Nixpacks (Railway) configuration
- ✅ `config/database-env.php` - Environment-based DB config
- ✅ `.env.example` - Example environment variables

---

## 🔧 **BEFORE DEPLOYMENT:**

### **1. Update Database Configuration**

Your app now supports environment variables! Update your `config/database.php`:

```php
// OLD (hardcoded):
require_once 'database.php';

// NEW (environment-based):
require_once 'database-env.php';
```

Or rename files:
```bash
mv config/database.php config/database-local.php
mv config/database-env.php config/database.php
```

### **2. Set Environment Variables**

On your deployment platform, set these variables:

**Required:**
```
DB_HOST=your-mysql-host
DB_NAME=canteen_management
DB_USER=your-mysql-user
DB_PASS=your-mysql-password
DB_PORT=3306
```

**Optional:**
```
APP_ENV=production
APP_NAME=Yangtze University Canteen Management System
SESSION_TIMEOUT=1800
```

---

## 📦 **PLATFORM-SPECIFIC INSTRUCTIONS:**

### **A) Railway** (Recommended - Free Tier)

**Why Railway:**
- ✅ Free $5/month credit
- ✅ MySQL database included
- ✅ Automatic deployments from GitHub
- ✅ Easy setup

**Steps:**

1. **Sign up:** https://railway.app

2. **Create New Project:**
   - Click "New Project"
   - Select "Deploy from GitHub repo"
   - Connect your GitHub account
   - Select your repository
   - Select branch: `claude/canteen-management-system-...`

3. **Add MySQL Database:**
   - In your project, click "+ New"
   - Select "Database" → "Add MySQL"
   - Railway will auto-provision a MySQL database

4. **Set Environment Variables:**
   Railway auto-sets `DATABASE_URL`, but you can also set:
   ```
   Click "Variables" tab:
   APP_ENV=production
   BASE_URL=https://your-app.railway.app/
   ```

5. **Deploy Path (if needed):**
   - Go to Settings
   - Set "Root Directory" to: `canteen-management`

6. **Import Database:**
   - Connect to Railway MySQL using provided credentials
   - Import `sql/database.sql`

7. **Access Your App:**
   - Railway provides: `https://your-app.railway.app`

**Deploy Command:**
```bash
# Push to GitHub and Railway auto-deploys
git push origin your-branch
```

---

### **B) Render**

**Why Render:**
- ✅ Free tier available
- ✅ PostgreSQL free (MySQL paid)
- ✅ Auto-deploy from Git

**Steps:**

1. **Sign up:** https://render.com

2. **Create Web Service:**
   - Click "New +" → "Web Service"
   - Connect GitHub repository
   - Select your branch
   - Render detects `render.yaml` automatically

3. **Create Database:**
   - Create new PostgreSQL database (free)
   - Or connect external MySQL

4. **Environment Variables:**
   ```
   DB_HOST=<from database>
   DB_NAME=canteen_management
   DB_USER=<from database>
   DB_PASS=<from database>
   APP_ENV=production
   ```

5. **Deploy:**
   - Render auto-deploys on git push

---

### **C) Heroku**

**Why Heroku:**
- ✅ Well-established platform
- ✅ Free tier (with limits)
- ✅ Easy scaling

**Steps:**

1. **Install Heroku CLI:**
   ```bash
   # macOS
   brew tap heroku/brew && brew install heroku

   # Ubuntu/Debian
   curl https://cli-assets.heroku.com/install.sh | sh
   ```

2. **Login:**
   ```bash
   heroku login
   ```

3. **Create App:**
   ```bash
   cd canteen-management
   heroku create your-app-name
   ```

4. **Add MySQL Database:**
   ```bash
   heroku addons:create jawsdb:kitefin
   # or
   heroku addons:create cleardb:ignite
   ```

5. **Get Database Credentials:**
   ```bash
   heroku config:get JAWSDB_URL
   # or
   heroku config:get CLEARDB_DATABASE_URL
   ```

6. **Deploy:**
   ```bash
   git push heroku your-branch:main
   ```

7. **Import Database:**
   ```bash
   # Get MySQL URL from Heroku config
   heroku config

   # Connect and import
   mysql -h host -u user -p database < sql/database.sql
   ```

8. **Open App:**
   ```bash
   heroku open
   ```

---

### **D) DigitalOcean App Platform**

**Steps:**

1. **Sign up:** https://cloud.digitalocean.com/apps

2. **Create App:**
   - Click "Create" → "Apps"
   - Connect GitHub
   - Select repository and branch

3. **Configure:**
   - Detected as: PHP
   - Root Directory: `canteen-management`
   - Build Command: `composer install || echo ok`
   - Run Command: `php -S 0.0.0.0:$PORT -t .`

4. **Add Database:**
   - Add "Database" component
   - Select MySQL
   - DigitalOcean provisions it

5. **Environment Variables:**
   - Auto-added from database connection

---

### **E) Fly.io**

**Steps:**

1. **Install Fly CLI:**
   ```bash
   curl -L https://fly.io/install.sh | sh
   ```

2. **Login:**
   ```bash
   fly auth login
   ```

3. **Launch App:**
   ```bash
   cd canteen-management
   fly launch
   ```

4. **Create Database:**
   ```bash
   fly postgres create
   ```

5. **Deploy:**
   ```bash
   fly deploy
   ```

---

## 🗄️ **DATABASE SETUP:**

### **Option 1: Import via Command Line**

```bash
# Connect to your database
mysql -h YOUR_HOST -u YOUR_USER -p YOUR_DATABASE

# Import schema
mysql -h YOUR_HOST -u YOUR_USER -p YOUR_DATABASE < sql/database.sql
```

### **Option 2: Import via phpMyAdmin**

1. Access phpMyAdmin (provided by host)
2. Select your database
3. Click "Import"
4. Upload `sql/database.sql`
5. Click "Go"

### **Option 3: Import via Platform UI**

Some platforms (Railway, Render) provide GUI for database import.

---

## ✅ **VERIFICATION CHECKLIST:**

After deployment, verify:

- [ ] App loads at provided URL
- [ ] Login page appears
- [ ] Can login with: `admin` / `admin123`
- [ ] Dashboard shows data
- [ ] No database connection errors

---

## 🔍 **TROUBLESHOOTING:**

### **"Database connection failed"**

**Check:**
1. Environment variables are set correctly
2. Database host is accessible
3. Database exists and schema is imported
4. User has proper permissions

**Debug:**
```bash
# View environment variables
echo $DB_HOST
echo $DB_NAME

# Test database connection
php -r "new PDO('mysql:host=$DB_HOST;dbname=$DB_NAME', '$DB_USER', '$DB_PASS');"
```

### **"Shipit could not detect provider"**

**Solution:** Now fixed with `composer.json` and `Procfile`

### **"Port binding failed"**

**Check:** Using `$PORT` environment variable:
```bash
php -S 0.0.0.0:$PORT -t .
```

### **"502 Bad Gateway"**

**Causes:**
- App crashed on startup
- Wrong start command
- Port not accessible

**Check logs:**
```bash
# Railway
railway logs

# Heroku
heroku logs --tail

# Render
(check dashboard logs)
```

---

## 🎯 **RECOMMENDED PLATFORM:**

**For Production:** Railway or DigitalOcean
- Reliable
- Good free tier / affordable
- MySQL support
- Easy to use

**For Testing:** Render or Heroku
- Quick setup
- Free tier
- Good for demos

---

## 📞 **QUICK START COMMAND:**

**Railway (Easiest):**
```bash
# Install Railway CLI
npm install -g @railway/cli

# Login
railway login

# Deploy
cd canteen-management
railway init
railway up
```

**That's it!** ✅

---

## 🔐 **SECURITY NOTES:**

1. **Change default admin password** immediately after first login
2. **Set strong DB passwords** in production
3. **Use HTTPS** (most platforms provide this automatically)
4. **Set `APP_ENV=production`** to hide errors
5. **Keep credentials secure** - never commit to Git

---

## 📚 **ADDITIONAL RESOURCES:**

- Railway Docs: https://docs.railway.app
- Render Docs: https://render.com/docs
- Heroku PHP: https://devcenter.heroku.com/articles/getting-started-with-php
- DigitalOcean: https://docs.digitalocean.com/products/app-platform/

---

**Need help?** Check the platform's specific documentation or contact support.

**Your app is now deployment-ready!** 🚀
