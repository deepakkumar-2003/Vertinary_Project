# Troubleshooting: 404 Error - Page Not Found

## Problem
You see: **"This localhost page can't be found"** with **HTTP ERROR 404**

This means Apache cannot find your project folder.

---

## Solution Steps

### ✅ Step 1: Verify Apache and MySQL are Running

1. **Open XAMPP Control Panel**
   - Look for XAMPP icon on your desktop or Start menu
   - Or go to: `C:\xampp\xampp-control.exe`

2. **Check the status:**
   - **Apache** should show green "Running"
   - **MySQL** should show green "Running"

3. **If they are NOT running:**
   - Click **"Start"** button next to Apache
   - Click **"Start"** button next to MySQL
   - Wait for both to turn green

**Screenshot reference:**
```
Apache    [Running]  [Stop]  [Admin]  [Config]  [Logs]
MySQL     [Running]  [Stop]  [Admin]  [Config]  [Logs]
```

---

### ✅ Step 2: Verify Project Folder Location

1. **Open File Explorer** (Windows Key + E)

2. **Navigate to:**
   ```
   C:\xampp\htdocs\
   ```

3. **Check if you see:**
   - A folder named **`Vertinary_Project`** (or whatever you named it)

4. **If the folder is NOT there:**
   - You need to copy your project folder here!
   - Copy the entire project folder to `C:\xampp\htdocs\`

---

### ✅ Step 3: Verify Folder Name Matches Configuration

1. **In File Explorer, check the EXACT folder name:**
   - Is it `Vertinary_Project`?
   - Or `vertinary_project`?
   - Or something else?

2. **The folder name MUST match the config.php setting:**

   If your folder is: `C:\xampp\htdocs\Vertinary_Project`

   Then config.php should have:
   ```php
   define('APP_URL', 'http://localhost/Vertinary_Project');
   ```

   **Capital letters matter!**

---

### ✅ Step 4: Test if XAMPP is Working

**Test 1: Access XAMPP Dashboard**

Open browser and type:
```
http://localhost
```

**Expected Result:**
- Should show XAMPP welcome page/dashboard

**If it doesn't work:**
- Apache is not running or port 80 is blocked
- Start Apache from XAMPP Control Panel

**Test 2: List htdocs contents**

Open browser and type:
```
http://localhost/
```

You should see a directory listing or XAMPP page.

---

### ✅ Step 5: Check Folder Contents

1. **Navigate to:**
   ```
   C:\xampp\htdocs\Vertinary_Project\
   ```

2. **Make sure these folders/files exist:**
   ```
   Vertinary_Project/
   ├── config/
   ├── database/
   ├── includes/
   ├── modules/
   └── public/
       ├── login.php  ← This file MUST exist!
       ├── css/
       └── js/
   ```

3. **If public/login.php doesn't exist:**
   - Your project files didn't copy correctly
   - Copy the entire project folder again

---

### ✅ Step 6: Try Alternative URLs

Sometimes the issue is just the URL format. Try these:

**Option 1:**
```
http://localhost/Vertinary_Project/public/login.php
```

**Option 2 (if folder name is lowercase):**
```
http://localhost/vertinary_project/public/login.php
```

**Option 3 (Check exact folder name first):**
```
http://localhost/[EXACT_FOLDER_NAME]/public/login.php
```

---

### ✅ Step 7: Check for Port Issues

If Apache started on a different port:

1. **Check XAMPP Control Panel**
   - Look at Apache line
   - Does it say something like: `Port 8080 in use`?

2. **If using different port (e.g., 8080):**
   ```
   http://localhost:8080/Vertinary_Project/public/login.php
   ```

3. **Update config.php if needed:**
   ```php
   define('APP_URL', 'http://localhost:8080/Vertinary_Project');
   ```

---

## 🔧 Most Common Causes & Fixes

| Problem | Solution |
|---------|----------|
| **Apache not running** | Start Apache in XAMPP Control Panel |
| **Project not in htdocs** | Copy project to `C:\xampp\htdocs\` |
| **Folder name mismatch** | Rename folder to match config.php OR update config.php |
| **Wrong case (Capital/Small letters)** | Make folder name and config.php match exactly |
| **Files not copied properly** | Re-copy entire project folder |
| **Port 80 blocked** | Change Apache port or stop conflicting app |

---

## 📋 Quick Diagnostic Checklist

Copy this list and check each item:

- [ ] XAMPP Control Panel shows Apache is **Running** (green)
- [ ] XAMPP Control Panel shows MySQL is **Running** (green)
- [ ] Project folder exists at: `C:\xampp\htdocs\Vertinary_Project`
- [ ] File exists: `C:\xampp\htdocs\Vertinary_Project\public\login.php`
- [ ] Folder name matches config.php exactly (capital letters too)
- [ ] Can access http://localhost (XAMPP dashboard appears)
- [ ] No firewall blocking Apache
- [ ] No antivirus blocking Apache

---

## 🎯 Step-by-Step Fix (Most Likely Issue)

Based on your error, here's what to do:

### Fix 1: Verify Project Location

1. **Open File Explorer**
2. **Go to:** `C:\xampp\htdocs\`
3. **Check:** Do you see `Vertinary_Project` folder?

**If NO:**
- Your project is not in the right place!
- **Copy your project folder to:** `C:\xampp\htdocs\`

**If YES:**
- Proceed to Fix 2

### Fix 2: Check Apache Status

1. **Open XAMPP Control Panel**
2. **Look at Apache line**

**If it says "Running":**
- Apache is OK, proceed to Fix 3

**If it's NOT running:**
- Click **"Start"** button
- If it fails, check what's using port 80:
  - Close Skype
  - Stop IIS (Internet Information Services)
  - Check for other web servers

### Fix 3: Verify Exact Folder Name

1. **In `C:\xampp\htdocs\`, check the folder name**
2. **Is it exactly:** `Vertinary_Project`?

3. **Now open config.php and check line 19**
4. **Does it match EXACTLY?**

**Example:**
- Folder: `Vertinary_Project`
- Config: `define('APP_URL', 'http://localhost/Vertinary_Project');`
- ✅ **Match!**

**Another example (WRONG):**
- Folder: `vertinary_project` (all lowercase)
- Config: `define('APP_URL', 'http://localhost/Vertinary_Project');`
- ❌ **No match!** - Windows might be case-insensitive but it's safer to match exactly

### Fix 4: Test the Login Page Directly

1. **In File Explorer, navigate to:**
   ```
   C:\xampp\htdocs\Vertinary_Project\public\
   ```

2. **Find the file:** `login.php`

3. **Right-click on login.php** → **Open with** → **Chrome/Firefox**

4. **Check the URL that opens**

---

## 🚀 Quick Test Command

Do this test:

1. **Create a test file**
   - Go to: `C:\xampp\htdocs\`
   - Create a new text file
   - Name it: `test.php`

2. **Edit test.php** and add:
   ```php
   <?php
   echo "XAMPP is working!";
   phpinfo();
   ?>
   ```

3. **Save the file**

4. **Open browser and go to:**
   ```
   http://localhost/test.php
   ```

5. **Result:**
   - ✅ **If you see "XAMPP is working!" and PHP info** - Apache is working!
   - ❌ **If 404 error** - Apache is not running or not configured correctly

---

## 📸 Visual Guide: Finding Your Project

**What it should look like:**

```
This PC
└── Local Disk (C:)
    └── xampp
        └── htdocs
            ├── dashboard (XAMPP folder)
            ├── test.php (your test file)
            └── Vertinary_Project  ← YOUR PROJECT HERE!
                ├── config
                ├── database
                ├── includes
                ├── modules
                └── public
                    └── login.php  ← This is what you're accessing
```

**URL Mapping:**
```
http://localhost/Vertinary_Project/public/login.php

localhost          = C:\xampp\htdocs\
Vertinary_Project  = C:\xampp\htdocs\Vertinary_Project\
public             = C:\xampp\htdocs\Vertinary_Project\public\
login.php          = C:\xampp\htdocs\Vertinary_Project\public\login.php
```

---

## 🆘 Still Not Working?

### Last Resort Checks:

1. **Restart Everything:**
   - Stop Apache in XAMPP
   - Stop MySQL in XAMPP
   - Close XAMPP Control Panel
   - Restart your computer
   - Open XAMPP Control Panel
   - Start Apache and MySQL

2. **Check Windows Firewall:**
   - Search for "Windows Defender Firewall"
   - Click "Allow an app through firewall"
   - Find "Apache HTTP Server"
   - Make sure it's checked for Private and Public

3. **Reinstall XAMPP (if desperate):**
   - Backup your project folder first!
   - Uninstall XAMPP
   - Download fresh XAMPP
   - Install it
   - Copy project back to htdocs

---

## ✅ Success Checklist

When everything is working, you should:

- [x] See Apache **Running** in XAMPP
- [x] See MySQL **Running** in XAMPP
- [x] Be able to access http://localhost (XAMPP page)
- [x] Be able to access http://localhost/Vertinary_Project/public/login.php
- [x] See the login page with username/password fields
- [x] No 404 errors

---

## 💡 Pro Tip

Add a bookmark in your browser for:
```
http://localhost/Vertinary_Project/public/login.php
```

This way you don't have to type it every time!

---

**Need more help? Check these in order:**

1. Is XAMPP Control Panel showing green "Running" for Apache?
2. Does `C:\xampp\htdocs\Vertinary_Project` folder exist?
3. Does `C:\xampp\htdocs\Vertinary_Project\public\login.php` file exist?
4. Does config.php line 19 match your folder name exactly?

If all 4 are YES, it should work! 🎉
