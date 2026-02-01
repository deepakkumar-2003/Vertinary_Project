# Veterinary Management System - Installation Guide

## Prerequisites

- **Web Server**: Apache or Nginx
- **PHP**: Version 7.4 or higher
- **MySQL**: Version 5.7 or higher
- **PHP Extensions Required**:
  - mysqli
  - pdo_mysql
  - mbstring
  - json
  - fileinfo

## Installation Steps

### 1. Database Setup

1. Open phpMyAdmin or MySQL command line
2. Import the database schema:
   ```sql
   mysql -u root -p < database/schema.sql
   ```
   Or manually execute the SQL file in phpMyAdmin

3. The default admin credentials will be created automatically:
   - **Username**: admin
   - **Password**: admin123

### 2. Configure Database Connection

1. Open `config/database.php`
2. Update the database credentials:
   ```php
   define('DB_HOST', 'localhost');
   define('DB_USER', 'your_db_user');
   define('DB_PASS', 'your_db_password');
   define('DB_NAME', 'vet_management_system');
   ```

### 3. Configure Application URL

1. Open `config/config.php`
2. Update the `APP_URL` constant to match your installation:
   ```php
   define('APP_URL', 'http://localhost/vasanth_project');
   ```
   or
   ```php
   define('APP_URL', 'http://yourdomain.com');
   ```

### 4. Set File Permissions

Set proper permissions for upload directories:

**On Linux/Mac:**
```bash
chmod 755 public/uploads
chmod 755 public/uploads/diseases
chmod 755 public/uploads/insurance
```

**On Windows:**
- Right-click on the `public/uploads` folder
- Properties → Security → Edit
- Ensure the web server user has write permissions

### 5. Configure PHP Settings

Update your `php.ini` file:

```ini
upload_max_filesize = 10M
post_max_size = 10M
max_execution_time = 300
memory_limit = 256M
```

Restart your web server after making changes.

### 6. Access the Application

1. Open your web browser
2. Navigate to: `http://localhost/vasanth_project/public/login.php`
3. Login with default credentials:
   - Username: `admin`
   - Password: `admin123`

### 7. Change Default Password

**IMPORTANT**: After first login, change the default admin password:
1. Go to User Management
2. Edit the admin user
3. Set a strong password

## Directory Structure

```
vasanth_project/
├── config/
│   ├── config.php          # Main configuration
│   └── database.php        # Database connection
├── database/
│   └── schema.sql          # Database schema
├── includes/
│   ├── functions.php       # Helper functions
│   ├── session.php         # Session management
│   ├── header.php          # Common header
│   └── footer.php          # Common footer
├── modules/
│   ├── auth/               # Authentication
│   ├── customers/          # Customer management
│   ├── animals/            # Animal registration
│   ├── diseases/           # Disease tracking
│   ├── treatments/         # Treatment management
│   ├── vaccinations/       # Vaccination management
│   ├── ai/                 # AI records
│   ├── dairy/              # Milk dairy management
│   ├── loans/              # Loan management
│   ├── insurance/          # Insurance & claims
│   └── reports/            # Reporting module
├── public/
│   ├── css/
│   │   └── style.css       # Main stylesheet
│   ├── js/
│   │   └── main.js         # Main JavaScript
│   ├── images/             # Static images
│   ├── uploads/            # User uploads
│   ├── login.php           # Login page
│   └── dashboard.php       # Dashboard
└── prd.md                  # Product requirements
```

## Module Completion Status

### ✅ Completed Modules
- Authentication & Session Management
- Customer Management (List, Add, Edit, View, Delete)
- Dashboard with Statistics
- Database Schema with all tables

### 🚧 To Be Completed

Follow the same pattern as the Customer module to create:

1. **Animals Module** (`modules/animals/`)
   - list.php
   - add.php
   - edit.php
   - view.php

2. **Diseases Module** (`modules/diseases/`)
   - list.php
   - add.php (with image upload)
   - edit.php
   - view.php

3. **Treatments Module** (`modules/treatments/`)
   - list.php
   - add.php
   - edit.php
   - view.php

4. **Vaccinations Module** (`modules/vaccinations/`)
   - list.php
   - add.php
   - edit.php
   - view.php
   - reminders.php (for overdue vaccinations)

5. **AI Records Module** (`modules/ai/`)
   - list.php
   - add.php
   - edit.php
   - view.php

6. **Dairy Module** (`modules/dairy/`)
   - list.php
   - add.php
   - reports.php

7. **Loans Module** (`modules/loans/`)
   - list.php
   - add.php
   - edit.php
   - view.php
   - payments.php

8. **Insurance Module** (`modules/insurance/`)
   - policies/
     - list.php
     - add.php
     - edit.php
     - view.php
   - claims/
     - list.php
     - add.php
     - edit.php
     - view.php

9. **Reports Module** (`modules/reports/`)
   - index.php
   - health_report.php
   - dairy_report.php
   - financial_report.php
   - vaccination_report.php

10. **User Management** (`modules/auth/`)
    - users.php
    - add_user.php
    - edit_user.php

## Development Guidelines

### Creating New CRUD Pages

Each module follows a consistent pattern:

1. **List Page**: Display records with search, pagination
2. **Add Page**: Form to create new records
3. **Edit Page**: Form to update existing records
4. **View Page**: Display detailed information

### Code Pattern Example

```php
<?php
require_once '../../config/config.php';
requireLogin();

$pageTitle = 'Module Name';

// Your logic here

include_once INCLUDES_PATH . '/header.php';
?>

<!-- Your HTML here -->

<?php include_once INCLUDES_PATH . '/footer.php'; ?>
```

### Security Best Practices

1. Always use `requireLogin()` at the top of protected pages
2. Use prepared statements for all database queries
3. Sanitize user inputs with `sanitize()` function
4. Validate file uploads before processing
5. Log important activities with `logActivity()`

## Troubleshooting

### Common Issues

1. **"Cannot connect to database"**
   - Check database credentials in `config/database.php`
   - Ensure MySQL service is running
   - Verify database exists

2. **"Permission denied" on uploads**
   - Check folder permissions on `public/uploads`
   - Ensure web server user has write access

3. **Session issues / Auto-logout**
   - Check PHP session configuration
   - Verify `session_start()` is called
   - Check `SESSION_TIMEOUT` in config

4. **CSS/JS not loading**
   - Verify `APP_URL` in `config/config.php`
   - Check file paths in header.php
   - Clear browser cache

5. **SQL errors**
   - Check database schema is imported correctly
   - Verify table names match code
   - Check MySQL error logs

## Production Deployment

Before deploying to production:

1. **Disable error display**:
   ```php
   error_reporting(0);
   ini_set('display_errors', 0);
   ```

2. **Enable HTTPS**
3. **Change default credentials**
4. **Set up regular database backups**
5. **Configure proper file permissions**
6. **Update `APP_URL` to production domain**
7. **Review and update password hashing algorithm if needed**

## Support

For issues or questions:
- Review this installation guide
- Check the PRD document for feature requirements
- Review code comments and function documentation

## License

This project is developed for educational and commercial use.
