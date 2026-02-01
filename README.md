# Advanced Veterinary Clinic, Dairy & Farm Management System

A comprehensive web-based application for managing veterinary clinics, animal healthcare records, dairy production, and farm operations.

## 🎯 Features

- **User Management**: Role-based access control (Admin, Veterinarian, Staff, Farm Owner)
- **Customer Management**: Store and manage customer details with emergency contacts
- **Animal Registration**: Track animals with unique IDs and complete history
- **Disease & Diagnosis**: Record diseases, symptoms, and upload images
- **Treatment Management**: Manage prescriptions, medicines, and dosages
- **Vaccination Management**: Schedule tracking with automatic reminders
- **AI Module**: Artificial Insemination records and pregnancy tracking
- **Dairy Management**: Daily milk production tracking and reports
- **Loan Management**: Track loans with due dates and payment history
- **Insurance & Claims**: Manage insurance policies and claim records
- **Reports**: Health, dairy, and financial reports with PDF/Excel export

## 🛠️ Tech Stack

### Backend
- **PHP** 7.4+
- **MySQL** 5.7+
- Object-oriented database class
- Prepared statements for security

### Frontend
- **HTML5**
- **CSS3** (Custom responsive design)
- **Vanilla JavaScript** (No frameworks)
- Mobile-first responsive design

### Security
- Password hashing with `password_hash()`
- SQL injection protection with prepared statements
- XSS protection with input sanitization
- Session management with timeout
- Role-based access control
- Activity logging

## 📁 Project Structure

```
vasanth_project/
├── config/
│   ├── config.php              # Application configuration
│   └── database.php            # Database connection class
├── database/
│   └── schema.sql              # Complete database schema
├── includes/
│   ├── functions.php           # Helper functions
│   ├── session.php             # Session management
│   ├── header.php              # Common header
│   └── footer.php              # Common footer
├── modules/
│   ├── auth/                   # Authentication
│   │   ├── logout.php
│   │   └── users.php (To be implemented)
│   ├── customers/              # ✅ Customer management (Complete)
│   │   ├── list.php
│   │   ├── add.php
│   │   ├── edit.php
│   │   └── view.php
│   ├── animals/                # ✅ Animal registration (Partial)
│   │   ├── list.php
│   │   ├── add.php
│   │   ├── view.php
│   │   └── edit.php (To be implemented)
│   ├── diseases/               # 🚧 To be implemented
│   ├── treatments/             # 🚧 To be implemented
│   ├── vaccinations/           # 🚧 Placeholder created
│   ├── ai/                     # 🚧 To be implemented
│   ├── dairy/                  # 🚧 Placeholder created
│   ├── loans/                  # 🚧 To be implemented
│   ├── insurance/              # 🚧 To be implemented
│   └── reports/                # 🚧 Placeholder created
├── public/
│   ├── css/
│   │   └── style.css           # Complete responsive styles
│   ├── js/
│   │   └── main.js             # JavaScript utilities
│   ├── images/                 # Static images
│   ├── uploads/                # User uploads
│   │   ├── diseases/
│   │   └── insurance/
│   ├── login.php               # ✅ Login page
│   └── dashboard.php           # ✅ Dashboard with statistics
├── prd.md                      # Product Requirements Document
├── INSTALLATION_GUIDE.md       # Detailed installation instructions
├── PROJECT_COMPLETION_GUIDE.md # Development guide
└── README.md                   # This file
```

## 🚀 Installation

### Prerequisites
- **Web Server**: Apache or Nginx
- **PHP**: Version 7.4 or higher
- **MySQL**: Version 5.7 or higher
- **PHP Extensions**: mysqli, pdo_mysql, mbstring, json, fileinfo

### Quick Setup

1. **Clone or extract the project**
   ```bash
   cd d:/Deepakkumar/vasanth_project
   ```

2. **Import database schema**
   ```bash
   mysql -u root -p < database/schema.sql
   ```
   Or use phpMyAdmin to import `database/schema.sql`

3. **Configure database connection**

   Edit [config/database.php](config/database.php):
   ```php
   define('DB_HOST', 'localhost');
   define('DB_USER', 'root');
   define('DB_PASS', '');
   define('DB_NAME', 'vet_management_system');
   ```

4. **Configure application URL**

   Edit [config/config.php](config/config.php):
   ```php
   define('APP_URL', 'http://localhost/vasanth_project');
   ```

5. **Set file permissions**

   Ensure the web server has write access to:
   ```bash
   chmod 755 public/uploads
   chmod 755 public/uploads/diseases
   chmod 755 public/uploads/insurance
   ```

6. **Access the application**

   Open your browser and navigate to:
   ```
   http://localhost/vasanth_project/public/login.php
   ```

For detailed installation instructions, see [INSTALLATION_GUIDE.md](INSTALLATION_GUIDE.md)

## 🔐 Default Login Credentials

After importing the database, use these credentials:

**Admin:**
- Username: `admin`
- Password: `admin123`

**⚠️ IMPORTANT**: Change the default password after first login!

## 📊 Project Status

### ✅ Completed (Ready to Use)
- [x] Database schema with all 15+ tables
- [x] Core configuration and database connection
- [x] Authentication system (login/logout)
- [x] Session management with timeout
- [x] Dashboard with statistics
- [x] Customer Management (CRUD complete)
- [x] Animal Registration (List, Add, View)
- [x] Complete UI/UX framework
- [x] Helper functions library
- [x] Role-based access control

### 🚧 To Be Completed
- [ ] Animal edit page
- [ ] Disease & Diagnosis module
- [ ] Treatment Management module
- [ ] Vaccination Management module
- [ ] AI Records module
- [ ] Milk Dairy Management module
- [ ] Loan Management module
- [ ] Insurance & Claims module
- [ ] Reports module with PDF/Excel export
- [ ] User Management (Admin)

**Completion Status**: ~40% (Core foundation complete)

For detailed implementation instructions, see [PROJECT_COMPLETION_GUIDE.md](PROJECT_COMPLETION_GUIDE.md)

## 📖 Documentation

- **[prd.md](prd.md)** - Product Requirements Document
- **[INSTALLATION_GUIDE.md](INSTALLATION_GUIDE.md)** - Detailed installation steps
- **[PROJECT_COMPLETION_GUIDE.md](PROJECT_COMPLETION_GUIDE.md)** - Development guide for completing remaining modules

## 🎨 Key Features

### Dashboard
- Total customers, animals, and vaccinations statistics
- Today's milk production tracking
- Active pregnancies count
- Overdue vaccinations alerts
- Recent activity logs

### Customer Management
- Add, edit, view, and delete customers
- Search and pagination
- Emergency contact information
- Associated animals tracking

### Animal Registration
- Unique animal codes auto-generation
- Multiple species support (cattle, buffalo, goat, sheep, pig, poultry)
- Age calculation from date of birth
- Customer association
- Status tracking (active, sold, deceased)

### Security Features
- Secure password hashing
- SQL injection protection with prepared statements
- XSS protection via input sanitization
- Session timeout management
- Activity logging for audit trails
- Role-based page access

## 🛡️ Security Best Practices

The system implements:
- `requireLogin()` - Protects all pages
- `sanitize()` - Cleans user inputs
- Prepared statements for all database queries
- Password hashing with bcrypt
- Session timeout (1 hour)
- Activity logging for all critical actions

## 🎯 Development Guidelines

### Creating New Modules

Follow the established pattern from Customer module:

1. **Copy structure** from `modules/customers/`
2. **Update database table** references
3. **Modify form fields** according to schema
4. **Update navigation** in header.php
5. **Test CRUD operations**

See [PROJECT_COMPLETION_GUIDE.md](PROJECT_COMPLETION_GUIDE.md) for detailed instructions.

## 📱 Browser Support

- Chrome (Latest)
- Firefox (Latest)
- Safari (Latest)
- Edge (Latest)
- Mobile browsers (iOS Safari, Chrome Mobile)

## 🔧 Troubleshooting

### Common Issues

1. **"Cannot connect to database"**
   - Verify MySQL is running
   - Check credentials in `config/database.php`
   - Ensure database exists

2. **"Permission denied" on uploads**
   - Check folder permissions: `chmod 755 public/uploads`
   - Ensure web server user has write access

3. **CSS/JS not loading**
   - Verify `APP_URL` in `config/config.php`
   - Clear browser cache

For more troubleshooting tips, see [INSTALLATION_GUIDE.md](INSTALLATION_GUIDE.md)

## 📈 Roadmap

### Phase 1 (Complete)
- ✅ Core infrastructure
- ✅ Authentication
- ✅ Customer management
- ✅ Basic animal registration

### Phase 2 (In Progress)
- 🚧 Complete all CRUD modules
- 🚧 Vaccination reminders
- 🚧 Basic reporting

### Phase 3 (Future)
- ⏳ Mobile application
- ⏳ SMS/WhatsApp notifications
- ⏳ AI-based disease prediction
- ⏳ Advanced analytics dashboard

## 🤝 Contributing

1. Follow the existing code structure
2. Use prepared statements for database queries
3. Sanitize all user inputs
4. Test on multiple browsers
5. Add comments for complex logic

## 📄 License

This project is developed for educational and commercial use.

## 💬 Support

- Review the [INSTALLATION_GUIDE.md](INSTALLATION_GUIDE.md)
- Check the [PROJECT_COMPLETION_GUIDE.md](PROJECT_COMPLETION_GUIDE.md)
- Review code comments in helper functions

## 🙏 Acknowledgments

Built with:
- PHP for robust backend processing
- MySQL for reliable data storage
- Vanilla JavaScript for lightweight frontend
- Custom CSS for responsive design

---

**Version**: 1.0.0
**Status**: In Development (Core Complete)
**Last Updated**: January 2026
