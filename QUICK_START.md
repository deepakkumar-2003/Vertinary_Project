# Quick Start Guide

Get the Veterinary Management System running in 5 minutes!

## 🚀 Prerequisites

- Web Server (Apache/Nginx with PHP support)
- PHP 7.4+
- MySQL 5.7+

## Steps

### 1. Import Database
```bash
mysql -u root -p < database/schema.sql
```
Or use phpMyAdmin to import `database/schema.sql`

### 2. Configure Database Connection

Edit [config/database.php](config/database.php):
```php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'vet_management_system');
```

### 3. Configure Application URL

Edit [config/config.php](config/config.php):
```php
define('APP_URL', 'http://localhost/vasanth_project');
```

### 4. Set Permissions

Ensure write access to uploads folder:
```bash
chmod 755 public/uploads
chmod 755 public/uploads/diseases
chmod 755 public/uploads/insurance
```

### 5. Access Application

- Open browser: `http://localhost/vasanth_project/public/login.php`
- Login with: **admin** / **admin123**

## That's it! 🎉

For detailed setup, see [INSTALLATION_GUIDE.md](INSTALLATION_GUIDE.md)

## 📂 What's Working Now

✅ **Fully Functional:**
- Login/Logout system
- Dashboard with statistics
- Customer Management (Complete CRUD)
- Animal Registration (List, Add, View)
- Responsive UI/UX
- Role-based access control

## 🔨 What to Build Next

Follow the Customer module pattern to complete:

1. Animal edit page
2. Treatments module
3. Vaccinations module
4. Dairy records module
5. Reports module

See [PROJECT_COMPLETION_GUIDE.md](PROJECT_COMPLETION_GUIDE.md) for detailed instructions.

## 📚 Key Files

- [database/schema.sql](database/schema.sql) - Complete database schema
- [includes/functions.php](includes/functions.php) - Helper functions
- [modules/customers/](modules/customers/) - Reference implementation
- [PROJECT_COMPLETION_GUIDE.md](PROJECT_COMPLETION_GUIDE.md) - Step-by-step guide

## 💡 Development Tips

- Use `requireLogin()` at the top of every protected page
- Use `sanitize()` for all user inputs
- Use prepared statements for SQL queries
- Test on Chrome, Firefox, and mobile browsers

## Common Issues

**Database connection error?**
- Check MySQL is running
- Verify credentials in `config/database.php`
- Ensure database exists: `SHOW DATABASES;`

**CSS/JS not loading?**
- Verify `APP_URL` in `config/config.php`
- Clear browser cache
- Check file paths in header.php

**Permission denied on uploads?**
- Check folder permissions: `ls -la public/uploads`
- Ensure web server user has write access

## Next Steps

1. ✅ Login and explore the dashboard
2. ✅ Add some customers
3. ✅ Register animals
4. 🚧 Complete remaining modules
5. 🚧 Add PDF/Excel reporting

For detailed instructions, see [PROJECT_COMPLETION_GUIDE.md](PROJECT_COMPLETION_GUIDE.md)
