# Step-by-Step Guide: How to Update Configuration Files

This guide will show you **exactly** how to edit the configuration files on your new laptop.

---

## 📍 Step 1: Locate Your Project Folder

First, find where you placed your project:

**Typical location for XAMPP on Windows:**
```
C:\xampp\htdocs\Vertinary_Project
```

**How to get there:**
1. Open **File Explorer** (Windows Key + E)
2. Click on **This PC** or **Computer**
3. Double-click **Local Disk (C:)**
4. Double-click **xampp** folder
5. Double-click **htdocs** folder
6. You should see **Vertinary_Project** folder

---

## 📝 Step 2: Choose a Text Editor

You need a text editor to edit the files. Choose ONE of these options:

### Option A: Notepad (Built-in Windows - Simplest)
✅ Already installed on Windows
✅ Easy to use

### Option B: Notepad++ (Recommended - Better)
✅ Free download: https://notepad-plus-plus.org/
✅ Shows line numbers
✅ Color codes PHP syntax

### Option C: VS Code (Advanced)
✅ Professional editor
✅ Best if you plan to do more coding

**For this guide, I'll use Notepad++ (but Notepad works the same way)**

---

## 🔧 PART A: Update Database Configuration File

### Step 1: Navigate to the Config Folder

In File Explorer:
```
C:\xampp\htdocs\Vertinary_Project\config\
```

You should see these files:
- config.php
- database.php

### Step 2: Open database.php

**Method 1 - Using Right-Click:**
1. Right-click on **database.php**
2. Select **"Edit with Notepad++"** (or "Open with" → "Notepad")
3. The file will open

**Method 2 - Using Notepad++ Directly:**
1. Open Notepad++
2. Click **File** → **Open**
3. Navigate to `C:\xampp\htdocs\Vertinary_Project\config\`
4. Click on **database.php**
5. Click **Open**

### Step 3: Find the Lines to Change

You will see something like this:

```php
<?php
// Database Configuration

define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'vet_management_system');

// Create database connection
class Database {
    // ... more code below
```

**These are lines 4-7** in the file.

### Step 4: Check What to Change

#### Current Values (from old laptop):
```php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'vet_management_system');
```

#### What You Need to Update:

**For standard XAMPP installation, you typically DON'T need to change anything!**

But verify these values match your setup:

| Setting | Value | When to Change |
|---------|-------|----------------|
| `DB_HOST` | `'localhost'` | Keep as is (unless MySQL is on different server) |
| `DB_USER` | `'root'` | Keep as is (default XAMPP username) |
| `DB_PASS` | `''` | Keep empty for XAMPP default (or add your password if you set one) |
| `DB_NAME` | `'vet_management_system'` | Keep as is (this is your database name) |

### Step 5: Make Changes (If Needed)

**Scenario 1: Using Default XAMPP (Most Common)**
- ✅ **NO CHANGES NEEDED** - The default values are correct!
- Skip to Step 6

**Scenario 2: You Set a MySQL Password**

If you created a password for MySQL root user, update line 6:

**Change FROM:**
```php
define('DB_PASS', '');
```

**Change TO:**
```php
define('DB_PASS', 'your_password_here');
```

**Example:**
```php
define('DB_PASS', 'MySecurePass123');
```

**Scenario 3: You Created a Different Database User**

If you created a user called `vet_admin` with password `vetpass123`:

```php
define('DB_HOST', 'localhost');
define('DB_USER', 'vet_admin');          // Changed from 'root'
define('DB_PASS', 'vetpass123');         // Changed from ''
define('DB_NAME', 'vet_management_system');
```

### Step 6: Save the File

**In Notepad++:**
1. Click **File** → **Save** (or press Ctrl + S)
2. Close the file

**In Notepad:**
1. Click **File** → **Save** (or press Ctrl + S)
2. Close the file

✅ **database.php is now configured!**

---

## 🌐 PART B: Update Application URL in config.php

### Step 1: Open config.php

In the same `config` folder:
```
C:\xampp\htdocs\Vertinary_Project\config\
```

**Right-click on config.php** → **Edit with Notepad++** (or Notepad)

### Step 2: Find Line 19

Scroll down until you see:

```php
// Application Settings
define('APP_NAME', 'Veterinary Management System');
define('APP_VERSION', '1.0.0');
define('APP_URL', 'http://localhost/vasanth_project');
```

**Line 19** is the one with `APP_URL`

In Notepad++, you can see line numbers on the left side.

### Step 3: Update the APP_URL

**Find this line:**
```php
define('APP_URL', 'http://localhost/vasanth_project');
```

**Change it to:**
```php
define('APP_URL', 'http://localhost/Vertinary_Project');
```

### ⚠️ Important Notes:

1. **Capital letters matter!**
   - If your folder is `Vertinary_Project`, use exactly that
   - If it's `vertinary_project`, use that
   - Must match EXACTLY

2. **No trailing slash!**
   - ✅ Correct: `'http://localhost/Vertinary_Project'`
   - ❌ Wrong: `'http://localhost/Vertinary_Project/'`

3. **Check your port (for MAMP users):**
   - XAMPP/WAMP: `http://localhost/Vertinary_Project`
   - MAMP (Mac): `http://localhost:8888/Vertinary_Project`
   - If you changed Apache port to 8080: `http://localhost:8080/Vertinary_Project`

### Step 4: Verify Your Changes

After editing, line 19 should look exactly like this:

```php
define('APP_URL', 'http://localhost/Vertinary_Project');
```

### Step 5: Save the File

1. Click **File** → **Save** (or press Ctrl + S)
2. Close the file

✅ **config.php is now configured!**

---

## 🎯 Visual Summary - What Changed

### File 1: database.php
```
Location: C:\xampp\htdocs\Vertinary_Project\config\database.php

Lines 4-7:
┌─────────────────────────────────────────────────┐
│ define('DB_HOST', 'localhost');                 │ ← Usually NO change needed
│ define('DB_USER', 'root');                      │ ← Usually NO change needed
│ define('DB_PASS', '');                          │ ← Add password if you set one
│ define('DB_NAME', 'vet_management_system');     │ ← Usually NO change needed
└─────────────────────────────────────────────────┘
```

### File 2: config.php
```
Location: C:\xampp\htdocs\Vertinary_Project\config\config.php

Line 19:
┌─────────────────────────────────────────────────────────┐
│ define('APP_URL', 'http://localhost/Vertinary_Project'); │
│                                    ^^^^^^^^^^^^^^^^^^^^  │
│                                    Change this part only │
└─────────────────────────────────────────────────────────┘

OLD: http://localhost/vasanth_project
NEW: http://localhost/Vertinary_Project
```

---

## ✅ Verification Checklist

After making changes, verify:

- [ ] Both files saved successfully (no * in title bar)
- [ ] Folder name in APP_URL matches actual folder name
- [ ] Folder name spelling is correct (Vertinary_Project)
- [ ] No extra spaces in the values
- [ ] Quotes are properly closed `'...'`
- [ ] Semicolons `;` are at the end of each line

---

## 🧪 Test Your Configuration

### Step 1: Open XAMPP Control Panel
- Make sure **Apache** is running (green)
- Make sure **MySQL** is running (green)

### Step 2: Open Your Browser
Type this URL:
```
http://localhost/Vertinary_Project/public/login.php
```

### Step 3: Expected Results

✅ **Success - You should see:**
- Login page with username and password fields
- Nice styling and colors
- "Veterinary Management System" heading

❌ **If you see errors:**

**Error: "Cannot connect to database"**
- Problem: database.php settings are wrong
- Solution: Double-check username, password, database name
- Test: Open phpMyAdmin and verify database exists

**Error: "Page not found" or 404**
- Problem: APP_URL is wrong or folder name doesn't match
- Solution: Check folder name spelling matches APP_URL exactly

**Error: CSS not loading (plain text page)**
- Problem: APP_URL path is incorrect
- Solution: Verify APP_URL points to correct folder

---

## 🎬 Quick Video-Style Walkthrough

**Imagine following these exact clicks:**

1. **Windows Key + E** (opens File Explorer)
2. Click **This PC**
3. Double-click **C:** drive
4. Double-click **xampp**
5. Double-click **htdocs**
6. Double-click **Vertinary_Project**
7. Double-click **config** folder
8. Right-click **database.php**
9. Click **Edit with Notepad++**
10. Look at lines 4-7 (usually no changes needed)
11. Press **Ctrl + S** to save
12. Close the file
13. Right-click **config.php**
14. Click **Edit with Notepad++**
15. Find line 19 (APP_URL)
16. Change `vasanth_project` to `Vertinary_Project`
17. Press **Ctrl + S** to save
18. Close the file
19. Open browser
20. Type: `http://localhost/Vertinary_Project/public/login.php`
21. Press Enter
22. **Done! Login page should appear** ✅

---

## 🔍 Common Mistakes to Avoid

| Mistake | Problem | Solution |
|---------|---------|----------|
| Editing with Microsoft Word | Adds formatting | Use Notepad or Notepad++ only |
| Missing quotes | Syntax error | Keep quotes: `'value'` |
| Extra spaces | Won't match | `'localhost'` not `' localhost '` |
| Wrong folder name | 404 error | Match exactly: `Vertinary_Project` |
| Adding trailing slash | Path issues | No slash: `/Vertinary_Project` not `/Vertinary_Project/` |
| Not saving file | Changes not applied | Always press Ctrl + S |

---

## 📱 Alternative: Edit Files in VS Code (If Installed)

If you have VS Code:

1. Open VS Code
2. Click **File** → **Open Folder**
3. Select `C:\xampp\htdocs\Vertinary_Project`
4. In the left sidebar, navigate to **config** folder
5. Click **database.php** to open
6. Make changes
7. Press **Ctrl + S** to save
8. Click **config.php** to open
9. Make changes to line 19
10. Press **Ctrl + S** to save

---

## 🆘 Need Help?

**Can't find the files?**
- Make sure you copied the project to `C:\xampp\htdocs\`
- Check the folder is named `Vertinary_Project`

**Notepad won't let you save?**
- Close XAMPP completely
- Edit the files
- Save them
- Restart XAMPP

**Still confused?**
- Take a screenshot of your folder structure
- Take a screenshot of the file contents
- Compare with this guide

---

## 🎉 Success!

Once you've updated both files and can see the login page in your browser, you're done!

**Next steps:**
1. Login with: admin / admin123
2. Change the default password
3. Start using the system

**Configuration is complete!** ✨
