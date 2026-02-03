PROWLWAY - ICDISG Archive Website

A comprehensive content management and archive system for the Institute of Computing and Data Science (ICDISG).

Version: 1.0.0  
Last Updated: February 3, 2026

Recent Updates:
- Responsive homepage banners (desktop and mobile)
- Institute sections with inner border panels
- Faculty and Admin sections using batch-detail style layout
- Institute About supports dynamic title + numbered goals list formatting
- Program pages: hover menu + dedicated program detail pages (IS, CS, DS)
- Footer is static via `ICDI/includes/footer_section.php`
- Events page styling matching documents page
- Mobile header improvements
- Various UI/UX enhancements

---

Table of Contents

1. Overview
2. Quick Start
3. Installation
4. Configuration
5. Default Credentials
6. Project Structure
7. Features
8. User Roles
9. Technology Stack
10. Database Setup
11. Security Features
12. API Endpoints
13. File Management
14. Development Guide
15. Troubleshooting
16. Documentation
17. Support

---

Overview

PROWLWAY is a centralized archive and content management platform designed specifically for ICDISG. It provides a comprehensive solution for managing announcements, events, documents, student organizations, and institute information.

Key Objectives

- Centralized content management for ICDISG
- Role-based access control for administrative functions
- Public-facing archive and information portal
- Student organization directory and management
- Event calendar and announcement system
- Document repository with categorization

---

Quick Start

Prerequisites

- PHP 8.2 or higher
- MySQL/MariaDB 10.4 or higher
- Apache web server (XAMPP/LAMP/WAMP)
- Modern web browser

Minimum Requirements

- PHP: 8.2+
- MySQL: 10.4+
- Apache: 2.4+
- Memory: 128MB PHP memory limit
- Disk Space: 100MB minimum

---

Installation

Step 1: Download/Clone Project

```bash
cd C:\xampp\htdocs\prowlway
```

Or clone from repository:
```bash
git clone [repository-url] prowlway
```

Step 2: Database Setup

1. Open phpMyAdmin or MySQL client
2. Create a new database named `icdi_db`
3. Import the database schema:
   - Navigate to `ICDI/database/icdi_db.sql`
   - Import the file into `icdi_db` database

Step 3: Configure Database Connection

Edit `ICDI/includes/config.php` and update database credentials if needed:

```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'icdi_db');
define('DB_USER', 'root');
define('DB_PASS', '');
```

Step 4: Set File Permissions (Linux/Mac)

```bash
chmod 755 ICDI/uploads/
chmod 755 ICDI/uploads/images/
chmod 755 ICDI/uploads/documents/
```

For Windows, ensure the uploads directories are writable.

Step 5: Access the Application

- Public Site: `http://localhost/prowlway/ICDI/public/home.php`
- Admin Panel: `http://localhost/prowlway/ICDI/admin/`
- Login Page: `http://localhost/prowlway/ICDI/admin/login.php`

---

Configuration

Database Configuration

All database settings are in `ICDI/includes/config.php`:

```php
define('DB_HOST', 'localhost');    // Database host
define('DB_NAME', 'icdi_db');      // Database name
define('DB_USER', 'root');         // Database username
define('DB_PASS', '');             // Database password
```

URL Configuration

URLs are automatically detected based on the script location. No manual configuration needed for:
- BASE_URL
- PUBLIC_URL
- ADMIN_URL
- ASSETS_URL

PHP Configuration

Recommended PHP settings in `php.ini`:

```ini
upload_max_filesize = 10M
post_max_size = 10M
max_execution_time = 300
memory_limit = 128M
```

---

Default Credentials

See `ICDI/CREDENTIALS.md` for complete admin login credentials.

Super Administrator

- Email: `superadmin@icdisg.ph`
- Password: `password123`
- Role: Super Admin
- Access: Full system access

Administrator

- Email: `icdi.admin@kld.edu.ph`
- Password: `password123`
- Role: Admin
- Access: Content management, publishing, archiving

Editor

- Email: `gitcub.editor@kld.edu.ph`
- Password: `password123`
- Role: Editor
- Access: Create drafts, submit for review

Important: Change all default passwords after first login!

---

Project Structure

```
prowlway/
├── ICDI/
│   ├── admin/                    # Administrative panel
│   │   ├── index.php             # Main dashboard
│   │   ├── login.php             # Admin login
│   │   ├── logout.php            # Admin logout
│   │   ├── announcements.php     # Announcements CRUD
│   │   ├── events_handler.php    # Events CRUD
│   │   ├── documents.php         # Documents CRUD
│   │   ├── batches.php           # Batches management
│   │   ├── organizations.php    # Organizations management
│   │   ├── institute.php         # Institute content
│   │   ├── users.php             # User management
│   │   ├── settings.php          # System settings
│   │   ├── review_queue.php      # Review queue
│   │   ├── archive_management.php # Archive management
│   │   ├── archive_handler.php   # Archive operations
│   │   ├── audit_log.php         # Audit log viewer
│   │   ├── inquiries_handler.php # Contact inquiries
│   │   └── holidays_handler.php  # Calendar holidays
│   │
│   ├── public/                    # Public-facing pages
│   │   ├── home.php              # Homepage
│   │   ├── institute.php         # Institute information
│   │   ├── faculty-detail.php    # Faculty subcategory detail page
│   │   ├── admin-representative-detail.php # Admin representative detail page
│   │   ├── program-detail-1.php  # Program detail (Computer Science)
│   │   ├── program-detail-2.php  # Program detail (Information Systems)
│   │   ├── program-detail-3.php  # Program detail (Data Science)
│   │   ├── calendar.php          # Event calendar
│   │   ├── events.php            # Events listing
│   │   ├── event-detail.php      # Event detail page
│   │   ├── documents.php         # Documents listing
│   │   ├── batches.php           # Batches listing
│   │   ├── batch-detail.php      # Batch detail page
│   │   ├── organization.php      # Organization detail
│   │   ├── search.php            # Search functionality
│   │   ├── contact.php           # Contact form
│   │   ├── announcement-detail.php # Announcement detail
│   │   ├── download.php          # Document download
│   │   └── image.php             # Image display
│   │
│   ├── includes/                  # Core PHP includes
│   │   ├── config.php            # Configuration
│   │   ├── database.php          # Database helpers
│   │   ├── auth.php              # Authentication
│   │   ├── errors.php            # Error handling
│   │   ├── upload.php            # File upload utilities
│   │   ├── validation.php       # Input validation
│   │   ├── header.php            # Site header
│   │   ├── footer.php            # Footer wrapper (includes footer section + scripts)
│   │   └── footer_section.php    # Footer markup (static)
│   │
│   ├── assets/                    # Static assets
│   │   ├── css/
│   │   │   └── style.css         # Main stylesheet
│   │   ├── js/
│   │   │   └── script.js         # Main JavaScript
│   │   └── IMG/
│   │       └── ICONS/            # Document icons
│   │
│   ├── uploads/                   # User-uploaded files
│   │   ├── images/               # Uploaded images
│   │   └── documents/            # Uploaded documents
│   │
│   ├── database/                  # Database files
│   │   └── icdi_db.sql           # Database schema
│   │
│   ├── docs/                      # Documentation
│   │   ├── PROJECT_DOCUMENTATION.md
│   │   └── FUNCTION_ANALYSIS.md
│   │
│   ├── index.php                  # Entry point
│   ├── CREDENTIALS.md             # Admin credentials
│   └── README.md                  # Project README
│
└── README.md                      # Main README
```

---

Features

Public Features

Homepage
- Intro animation (skippable)
- Featured announcements display
- Upcoming events carousel
- Quick access to documents
- Search functionality

Institute Information
- About section
- Mission & Vision
- Goals (numbered list formatting supported)
- Faculty Unit information
- Admin Representative details
- Program information
- Student organization directory

Event Calendar
- Monthly calendar view
- Event markers and highlights
- Meeting announcements integration
- Holiday display
- Day detail modal
- Click to view full event/announcement details

Events
- Event listing with filters
- Event detail pages
- Image galleries
- Date range filtering

Documents
- Categorized document listing
- Download functionality
- Category filtering (5 categories)
- Search integration

Batches
- Academic batch listing
- Batch detail pages
- Member listings

Organizations
- Organization profile pages
- Mission, Vision, Core Values
- Logo and banner display
- Member listings

Search
- Full-text search across:
  - Announcements
  - Events
  - Documents
  - Student Organizations
- Search result categorization

Contact
- Contact form submission
- Inquiry management

Administrative Features

Dashboard
- Role-based card visibility
- Quick statistics
- Content management tabs
- Review queue access
- Archive management

Content Management
- Announcements: CRUD with meeting fields, pinning, categories
- Events: CRUD with date ranges, locations, galleries
- Documents: CRUD with file uploads, categorization
- Batches: Academic batch management
- Organizations: Student organization profiles
- Institute Content: About, Mission, Vision, Faculty, Admin, Program

Review Queue
- Pending submissions list
- Approve/Reject functionality
- Review notes
- Bulk actions

Archive Management
- View all archived content
- Restore functionality
- Permanent delete
- Filter by content type
- Bulk operations

User Management
- Create/edit admin accounts
- Role assignment
- Password change
- Archive users

Settings
- Site configuration
- System preferences

Audit Log
- Administrative action history
- Filtering and search
- IP address tracking

---

User Roles

Super Admin

Permissions:
- Full system access
- User management (create, edit, archive)
- System settings
- Audit log access
- Publish, approve, archive content
- Review and approve submissions
- Institute content management

Admin

Permissions:
- Publish, approve, archive content
- Review and approve submissions
- Create and edit content
- Archive management
- User management (not allowed)
- System settings (not allowed)
- Audit log access (not allowed)
- Institute content management (not allowed)

Editor

Permissions:
- Create drafts
- Submit content for review
- Edit own content (draft status only)
- Publish content (not allowed)
- Approve content (not allowed)
- Archive content (not allowed)
- User management (not allowed)
- System settings (not allowed)
- Review queue access (not allowed)

---

Technology Stack

Backend
- PHP: 8.2+ (Procedural/OOP hybrid)
- Database: MySQL/MariaDB 10.4+
- Server: Apache 2.4+

Frontend
- HTML: HTML5
- CSS: CSS3, Tailwind CSS 3.4+
- JavaScript: ES6+
- Framework: None (Vanilla JavaScript)

Architecture
- Pattern: MVC-inspired
- API: RESTful JSON-based handlers
- Authentication: Session-based with CSRF protection

---

Database Setup

Database Schema

The database includes the following main tables:

- `admins` - Administrative user accounts
- `announcements` - Announcements and meeting notifications
- `events` - Event listings
- `documents` - Document repository
- `student_organizations` - Student organization profiles
- `batches` - Academic batch information
- `batch_members` - Batch members
- `institute_info` - Institute information sections
- `holidays` - Calendar holidays
- `contact_inquiries` - Contact form submissions
- `audit_log` - Administrative action audit trail
- `site_settings` - System configuration settings

Import Database

1. Create database: `icdi_db`
2. Import file: `ICDI/database/icdi_db.sql`
3. Verify tables are created
4. Check default admin accounts exist

---

Security Features

Authentication
- Session-based authentication
- Password hashing (bcrypt via `password_hash()`)
- Session regeneration on login
- Secure cookie configuration

CSRF Protection
- CSRF token generation (`generateCSRFToken()`)
- Token validation (`requireCSRFToken()`)
- All forms and AJAX requests protected

Input Validation
- Server-side validation (`includes/validation.php`)
- SQL injection prevention (prepared statements)
- XSS prevention (htmlspecialchars, strip_tags)
- File upload validation (type, size limits)

Authorization
- Role-based access control (RBAC)
- Function-level permission checks
- Page-level access restrictions

Audit Logging
- All administrative actions logged
- IP address tracking
- Action details stored in JSON format

---

API Endpoints

Admin Handlers (JSON API)

Announcements (`admin/announcements.php`)
- `GET ?page=1&per_page=20` - List announcements
- `POST action=create` - Create announcement
- `POST action=update` - Update announcement
- `POST action=archive` - Archive announcement
- `POST action=restore` - Restore archived announcement
- `POST action=bulk_action` - Bulk operations

Events (`admin/events_handler.php`)
- `GET ?page=1&per_page=20` - List events
- `POST action=create` - Create event
- `POST action=update` - Update event
- `POST action=archive` - Archive event
- `POST action=restore` - Restore archived event
- `POST action=bulk_action` - Bulk operations

Documents (`admin/documents.php`)
- `GET ?page=1&per_page=20` - List documents
- `POST action=create` - Create document
- `POST action=update` - Update document
- `POST action=archive` - Archive document
- `POST action=restore` - Restore archived document
- `POST action=bulk_action` - Bulk operations

Archive (`admin/archive_handler.php`)
- `POST action=bulk_archive` - Bulk archive items
- `POST action=restore` - Restore item
- `POST action=delete` - Permanent delete

Inquiries (`admin/inquiries_handler.php`)
- `GET` - List inquiries
- `POST action=update_status` - Update inquiry status
- `POST action=archive` - Archive inquiry

Holidays (`admin/holidays_handler.php`)
- `GET` - List holidays
- `POST action=create` - Create holiday
- `POST action=update` - Update holiday
- `POST action=delete` - Delete holiday

---

File Management

Upload Directories
- `uploads/images/` - Image files (announcements, events, organizations)
- `uploads/documents/` - Document files (PDFs, etc.)

File Naming Convention
- Images: `img_[hash].[ext]`
- Documents: `doc_[hash].[ext]`

File Utilities (`includes/upload.php`)
- `uploadImage()` - Image upload with validation
- `uploadDocument()` - Document upload with validation
- `getImageUrl()` - Generate image URL
- `deleteUploadedFile()` - Delete uploaded file

Supported Formats
- Images: JPG, JPEG, PNG, GIF, WebP
- Documents: PDF, DOC, DOCX, XLS, XLSX

File Size Limits
- Default PHP limits apply
- Recommended: 10MB max file size

---

Development Guide

Database Helpers (`includes/database.php`)

```php
getDB()              // Get database connection
dbFetchOne()         // Fetch single row
dbFetchAll()         // Fetch multiple rows
dbInsert()           // Insert record
dbUpdate()           // Update record
dbDelete()           // Delete record
getLastDbError()     // Get last database error
```

Error Handling (`includes/errors.php`)

```php
APIError::json()         // JSON error response
APIError::database()     // Database error handler
```

Code Style
- PHP: PSR-12 inspired (mixed procedural/OOP)
- JavaScript: ES6+ with modern features
- CSS: Tailwind utility classes + custom styles

Adding New Features

1. Create database table if needed
2. Add CRUD handler in `admin/` directory
3. Create public page in `public/` directory
4. Add JavaScript functions in `assets/js/script.js`
5. Add styles in `assets/css/style.css`
6. Update navigation if needed
7. Add to audit logging

---

Troubleshooting

Database Connection Error

Symptoms: "Database connection failed" or similar errors

Solutions:
- Verify database credentials in `includes/config.php`
- Ensure MySQL service is running
- Check database name matches (`icdi_db`)
- Verify database user has proper permissions
- Check firewall settings

File Upload Issues

Symptoms: Files not uploading or upload errors

Solutions:
- Verify `uploads/` directory permissions (755)
- Check PHP `upload_max_filesize` and `post_max_size`
- Ensure directory exists and is writable
- Check disk space availability
- Verify file type is allowed

Admin Login Issues

Symptoms: Cannot login or session errors

Solutions:
- Clear browser cookies/session
- Verify admin account exists in database
- Check `CREDENTIALS.md` for correct credentials
- Verify session configuration in PHP
- Check PHP error logs

Page Not Loading

Symptoms: Blank page or 500 error

Solutions:
- Check PHP error logs
- Verify all includes are present
- Check file permissions
- Verify database connection
- Check PHP version compatibility

CSRF Token Errors

Symptoms: "Invalid CSRF Token" errors

Solutions:
- Clear browser cache
- Ensure JavaScript is enabled
- Check session is active
- Verify CSRF token is being sent in forms

---

Documentation

Available Documentation

- Project Documentation: `ICDI/docs/PROJECT_DOCUMENTATION.md`
  - Complete system architecture
  - Database schema details
  - API documentation
  - Security features
  - Deployment guide

- Function Analysis: `ICDI/docs/FUNCTION_ANALYSIS.md`
  - Key PHP helper functions/classes
  - DB/Auth/Upload/Error handling references

- Credentials: `ICDI/CREDENTIALS.md`
  - Admin login credentials
  - Role permissions
  - Access URLs

Documentation Structure

All documentation files are located in:
- `ICDI/docs/` - Technical documentation
- `ICDI/CREDENTIALS.md` - Login credentials
- `README.md` - This file

---

Support

Getting Help

For issues or questions:

1. Check `ICDI/docs/PROJECT_DOCUMENTATION.md` for detailed information
2. Review `ICDI/docs/COMPLETION_PROGRESS.md` for known issues
3. Check PHP error logs in server error log
4. Review `ICDI/CREDENTIALS.md` for login issues

Error Logs

- PHP Error Log: Check server error log location
- Application Errors: Logged via `error_log()` function
- Database Errors: Check `getLastDbError()` function output

Backup Recommendations

- Database: Daily backups recommended
- Files: Weekly backups of `uploads/` directory
- Code: Version control (Git) recommended

Update Procedure

1. Backup database and files
2. Pull latest code changes
3. Run database migrations (if any)
4. Clear browser cache
5. Test functionality
6. Verify all features work

---

License

Internal use - ICDISG Development Team

---

Version History

Version 1.0.0 (January 30, 2026)
- Initial release
- Complete admin panel
- Public-facing pages
- Role-based access control
- Content management system
- Archive management
- Audit logging

---

Last Updated: February 3, 2026  
