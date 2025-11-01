# 🚀 Deployment Guide - Canteen Management System

This guide provides multiple deployment options for the Canteen Management System.

---

## ⚠️ IMPORTANT: Demo vs Production

### **Demo Version (Static HTML)**
- **What it is:** A preview of the interface with sample data
- **What works:** Viewing dashboard, UI, sample data, charts
- **What doesn't work:** Add/Edit/Delete operations, database persistence
- **Best for:** Testing UI, showing to stakeholders, previewing design
- **Platforms:** Vercel, Netlify, GitHub Pages, Surge, wasmr.io

### **Production Version (Full PHP App)**
- **What it is:** Complete functional system with database
- **What works:** EVERYTHING - full CRUD, authentication, reports, etc.
- **Requirements:** PHP 8+, MySQL, web server (Apache/Nginx)
- **Best for:** Actual production use
- **Platforms:** Hostinger, InfinityFree, 000webhost, cPanel hosting

---

## 🎯 Option 1: Deploy DEMO to Free Static Hosts

### A) **Vercel** (Recommended for Demo)

1. Install Vercel CLI:
   ```bash
   npm install -g vercel
   ```

2. Deploy:
   ```bash
   cd canteen-management
   vercel
   ```

3. Follow prompts and your demo will be live!

**URL:** `https://your-project.vercel.app`

---

### B) **Netlify** (Easy Drag & Drop)

1. Go to: https://app.netlify.com/drop
2. Drag the `canteen-management` folder
3. Done! Your demo is live instantly

**URL:** `https://random-name.netlify.app`

---

### C) **GitHub Pages**

1. Push code to GitHub
2. Go to **Settings** → **Pages**
3. Select your branch
4. Enable Pages
5. Access at: `https://username.github.io/repo-name/canteen-management/`

---

### D) **Surge.sh** (Fastest)

1. Install Surge:
   ```bash
   npm install -g surge
   ```

2. Deploy:
   ```bash
   cd canteen-management
   surge
   ```

3. Your demo is live!

**URL:** `https://random-name.surge.sh`

---

### E) **wasmr.io**

Since wasmr.io is looking for a specific project structure:

1. Create a `wasm.toml` or similar config (check wasmr.io docs)
2. Or deploy just the `DEMO-STANDALONE.html` file
3. The demo should work as it's pure HTML/CSS/JS

---

## 🏢 Option 2: Deploy PRODUCTION to PHP Hosting

### A) **Hostinger** (Recommended - As Designed)

**Step 1: Upload Files**
1. Login to Hostinger cPanel
2. Go to **File Manager**
3. Navigate to `public_html`
4. Upload entire `canteen-management` folder

**Step 2: Create Database**
1. Go to **MySQL Databases**
2. Create database: `canteen_management`
3. Create user with password
4. Assign user to database with ALL PRIVILEGES
5. Note down: database name, username, password

**Step 3: Import Database**
1. Open **phpMyAdmin**
2. Select your database
3. Click **Import**
4. Upload `sql/database.sql`
5. Click **Go**

**Step 4: Configure**
1. Edit `config/database.php`:
   ```php
   define('DB_HOST', 'localhost');
   define('DB_NAME', 'your_database_name');
   define('DB_USER', 'your_username');
   define('DB_PASS', 'your_password');
   ```

2. Edit `config/config.php`:
   ```php
   define('BASE_URL', 'https://yourdomain.com/canteen-management/');
   ```

**Step 5: Test**
1. Visit: `https://yourdomain.com/canteen-management/`
2. Login: `admin` / `admin123`
3. **CHANGE PASSWORD IMMEDIATELY!**

**Step 6: Set Up Cron Job**
1. Go to **Cron Jobs** in cPanel
2. Add new cron job:
   ```
   0 0 * * * /usr/bin/php /path/to/canteen-management/cron/update_status.php
   ```

---

### B) **InfinityFree** (Free Alternative)

Same steps as Hostinger, but:
1. Sign up at: https://infinityfree.net
2. Get free hosting with MySQL
3. Follow same upload/import/configure steps
4. URL: `http://yourusername.infinityfreeapp.com/canteen-management/`

---

### C) **000webhost** (Another Free Option)

1. Sign up: https://www.000webhost.com
2. Get free hosting
3. Upload via File Manager
4. Create database
5. Import SQL
6. Configure
7. Access

---

## 📱 Quick Comparison

| Platform | Type | Cost | Database | Best For |
|----------|------|------|----------|----------|
| **Vercel** | Static | Free | ❌ No | Demo Only |
| **Netlify** | Static | Free | ❌ No | Demo Only |
| **GitHub Pages** | Static | Free | ❌ No | Demo Only |
| **Surge** | Static | Free | ❌ No | Demo Only |
| **wasmr.io** | Static | Free | ❌ No | Demo Only |
| **Hostinger** | PHP + MySQL | Paid | ✅ Yes | **Production** ⭐ |
| **InfinityFree** | PHP + MySQL | Free | ✅ Yes | Production/Testing |
| **000webhost** | PHP + MySQL | Free | ✅ Yes | Production/Testing |

---

## 🎬 Quick Start Commands

### Deploy Demo to Vercel:
```bash
cd canteen-management
npx vercel --prod
```

### Deploy Demo to Netlify:
```bash
cd canteen-management
npx netlify-cli deploy --prod
```

### Deploy Demo to Surge:
```bash
cd canteen-management
npx surge
```

---

## 🆘 Troubleshooting wasmr.io

If wasmr.io still doesn't work:

1. **Check their documentation** for required config files
2. **Try deploying just the HTML file:**
   - Upload only `DEMO-STANDALONE.html`
   - Rename it to `index.html`
3. **Contact wasmr.io support** - they can tell you exact requirements
4. **Use alternative:** Vercel/Netlify work perfectly for static demos

---

## 💡 Recommendation

**For Demo/Preview:**
→ Use **Vercel** or **Netlify** (easiest, instant)

**For Production:**
→ Use **Hostinger** or **InfinityFree** (actual working system)

---

## 📞 Need Help?

- **Demo Issues:** Check that HTML file opens in browser first
- **Production Issues:** Verify PHP version (8+) and MySQL availability
- **Database Issues:** Ensure correct credentials in `config/database.php`
- **Cron Issues:** Use HTTP trigger as alternative for shared hosting

---

**Created for Yangtze University Canteen Management System**
