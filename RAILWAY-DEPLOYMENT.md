# 🚂 Railway Deployment Fix

## ❌ **Original Error:**
```
Error creating build plan with Railpack
```

## 🔍 **Root Cause:**
Railway was building from repository root (`/homekitchen/`) but the PHP application is located in subdirectory (`/canteen-management/`). Railway couldn't find `composer.json` or `index.php` at root level to detect PHP runtime.

---

## ✅ **Solution Applied:**

### **Files Created at Root:**

1. **`railway.toml`** - Tells Railway where the app is
2. **`composer.json`** - PHP runtime detection
3. **`Procfile`** - Start command specification
4. **`nixpacks.toml`** - Nixpacks build configuration
5. **`index.php`** - Redirect helper

### **Corrected Project Structure:**

```
homekitchen/                          <- Repository root
├── .git/
├── railway.toml                      <- ✅ NEW: Railway config
├── composer.json                     <- ✅ NEW: PHP detection
├── Procfile                          <- ✅ NEW: Start command
├── nixpacks.toml                     <- ✅ NEW: Build config
├── index.php                         <- ✅ NEW: Redirect to app
└── canteen-management/               <- Actual application
    ├── composer.json                 <- App-specific deps
    ├── Procfile                      <- App start command
    ├── index.php                     <- Login page
    ├── config/
    │   ├── database.php
    │   └── database-env.php          <- ✅ Uses env vars
    ├── dashboard/
    ├── students/
    ├── packages/
    └── [rest of app files]
```

---

## 🚀 **How to Deploy on Railway:**

### **Option 1: Deploy via GitHub (Recommended)**

1. **Push these fixes to GitHub:**
   ```bash
   git add .
   git commit -m "Fix Railway deployment structure"
   git push origin your-branch
   ```

2. **In Railway Dashboard:**
   - Go to: https://railway.app
   - Click "New Project"
   - Choose "Deploy from GitHub repo"
   - Select your repository
   - Select branch: `claude/canteen-management-system-011CUhUxymtV4iYArv2HexyL`
   - Railway will now auto-detect PHP! ✅

3. **Add MySQL Database:**
   - Click "+ New" in your project
   - Select "Database" → "Add MySQL"
   - Railway provisions MySQL automatically

4. **Set Environment Variables:**
   Railway auto-sets `DATABASE_URL`, but verify these are set:
   ```
   DB_HOST=<from Railway MySQL>
   DB_NAME=railway
   DB_USER=root
   DB_PASS=<from Railway MySQL>
   PORT=<Railway sets this>
   ```

5. **Import Database Schema:**
   - Get MySQL connection string from Railway
   - Connect using MySQL client:
     ```bash
     mysql -h <host> -u root -p <database> < canteen-management/sql/database.sql
     ```
   - Or use Railway's web console

6. **Deploy!**
   - Railway automatically deploys
   - You'll get a URL like: `https://your-app.railway.app`

---

### **Option 2: Deploy via Railway CLI**

1. **Install Railway CLI:**
   ```bash
   npm install -g @railway/cli
   ```

2. **Login:**
   ```bash
   railway login
   ```

3. **Initialize Project:**
   ```bash
   railway init
   ```

4. **Link to your project (or create new):**
   ```bash
   railway link
   # or
   railway link <project-id>
   ```

5. **Deploy:**
   ```bash
   railway up
   ```

6. **Add MySQL:**
   ```bash
   railway add
   # Select: MySQL
   ```

7. **Open your app:**
   ```bash
   railway open
   ```

---

## 🔧 **Configuration Details:**

### **railway.toml** (Root Level)
```toml
[build]
builder = "NIXPACKS"
buildCommand = "cd canteen-management && composer install"

[deploy]
startCommand = "cd canteen-management && php -S 0.0.0.0:$PORT -t ."
```

### **nixpacks.toml** (Root Level)
```toml
[phases.setup]
nixPkgs = ["php82", "php82Extensions.pdo", ...]

[start]
cmd = "cd canteen-management && php -S 0.0.0.0:$PORT -t ."
```

### **Start Command:**
```bash
cd canteen-management && php -S 0.0.0.0:$PORT -t .
```

This ensures:
- Change to app directory
- Start PHP built-in server
- Listen on Railway's `$PORT` variable
- Serve from current directory (`.`)

---

## 🗄️ **Database Configuration:**

The app uses **environment-based database config** (`config/database-env.php`):

### **Supported Environment Variables:**

```env
# Standard format
DB_HOST=monorail.proxy.rlwy.net
DB_PORT=12345
DB_NAME=railway
DB_USER=root
DB_PASS=your-password

# Or DATABASE_URL (Railway format)
DATABASE_URL=mysql://root:password@monorail.proxy.rlwy.net:12345/railway
```

Railway auto-provides `DATABASE_URL` when you add MySQL service. The app automatically parses it!

---

## ✅ **Verification Checklist:**

After deployment:

- [ ] Railway build completes successfully (no "Railpack" error)
- [ ] App shows as "Active" in Railway dashboard
- [ ] Visit provided URL - login page loads
- [ ] Can login with: `admin` / `admin123`
- [ ] Dashboard displays (even if empty initially)
- [ ] No database connection errors

---

## 🐛 **Troubleshooting:**

### **Build fails with "No buildpack detected"**

**Solution:** The root-level files should fix this. If still failing:
```bash
# In Railway dashboard, manually set:
Build Command: cd canteen-management && composer install
Start Command: cd canteen-management && php -S 0.0.0.0:$PORT -t .
```

### **"Application failed to respond"**

**Check:**
1. Ensure `$PORT` is being used (not hardcoded port)
2. Verify start command includes `cd canteen-management`
3. Check logs: `railway logs`

### **Database connection error**

**Check Railway variables:**
```bash
railway variables
```

Ensure `DATABASE_URL` or individual `DB_*` variables are set.

### **"Permission denied" errors**

**Solution:** Ensure `uploads/` and `logs/` directories exist:
```bash
# In build command:
mkdir -p canteen-management/uploads canteen-management/logs
```

---

## 📊 **Expected Build Output:**

```
╔═══════════════════════════════════════╗
║ Nixpacks build                        ║
╚═══════════════════════════════════════╝

→ Detecting PHP application
→ Found composer.json
→ Installing PHP 8.2
→ Running: cd canteen-management && composer install
→ Build complete!

╔═══════════════════════════════════════╗
║ Starting application                  ║
╚═══════════════════════════════════════╝

→ Running: cd canteen-management && php -S 0.0.0.0:8080 -t .
→ PHP 8.2.0 Development Server started
→ Listening on http://0.0.0.0:8080
→ Document root is /app/canteen-management
```

---

## 🎯 **Quick Deploy Command:**

```bash
# 1. Commit fixes
git add railway.toml composer.json Procfile nixpacks.toml index.php
git commit -m "Fix Railway deployment - add root config files"
git push origin your-branch

# 2. Deploy on Railway
railway up
# or connect via GitHub dashboard
```

---

## 📞 **Need Help?**

- **Railway Docs:** https://docs.railway.app
- **Railway Discord:** https://discord.gg/railway
- **Check Logs:** `railway logs` or view in dashboard

---

## 🎉 **Success!**

Once deployed, your Canteen Management System will be accessible at:
```
https://your-app-name.railway.app
```

**Default Login:**
- Username: `admin`
- Password: `admin123`

**⚠️ Remember to change the password immediately after first login!**

---

**Deployment Fixed! Your app is now Railway-ready!** 🚀
