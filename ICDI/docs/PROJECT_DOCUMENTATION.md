PROWLWAY - ICDISG Archive Website
Comprehensive Project Documentation

Version: 1.0.0  
Last Updated: January 30, 2026  
Platform: PHP 8.2+ / MySQL / Tailwind CSS

---

Table of Contents

1. Project Overview
2. System Architecture
3. Directory Structure
4. Database Schema
5. Features & Functionality
6. User Roles & Permissions
7. API Endpoints
8. Security Features
9. File Management
10. Configuration
11. Deployment Guide
12. Development Guide
13. Troubleshooting

---

Project Overview

PROWLWAY is a comprehensive archive and content management system for the Institute of Computing and Data Science (ICDISG). The platform serves as a centralized repository for announcements, events, documents, student organizations, and institute information.

Key Objectives

- Centralized content management for ICDISG
- Role-based access control for administrative functions
- Public-facing archive and information portal
- Student organization directory and management
- Event calendar and announcement system
- Document repository with categorization

Target Users

- Public visitors seeking institute information
- Students accessing announcements and events
- Editors creating and submitting content
- Administrators managing and publishing content
- Super Administrators with full system access

---

System Architecture

Technology Stack

Backend:
- PHP 8.2+ (Procedural/OOP hybrid)
- MySQL/MariaDB 10.4+
- Apache web server

Frontend:
- HTML5, CSS3, JavaScript (ES6+)
- Tailwind CSS 3.4+ (utility-first CSS framework)
- Vanilla JavaScript (no frameworks)

Server Requirements:
- PHP 8.2 or higher
- MySQL/MariaDB 10.4 or higher
- Apache 2.4+ (XAMPP/LAMP/WAMP compatible)
- mod_rewrite enabled (optional)

Architecture Pattern

MVC-inspired: Separation of concerns with includes/ directory
- Models: Database abstraction layer (`includes/database.php`)
- Views: Template files (`includes/header.php`, `includes/footer.php`)
- Controllers: Handler files (`admin/*.php`, `public/*.php`)

RESTful API: JSON-based handlers for AJAX operations
- All admin handlers return JSON responses
- Standardized error handling
- CSRF protection on all endpoints

Session-based Authentication: PHP sessions with CSRF protection
- Session management via `includes/auth.php`
- Role-based authorization
- Secure session configuration

---

Directory Structure

```
ICDI/
├── admin/                    # Administrative panel
│   ├── index.php            # Main dashboard
│   ├── login.php            # Admin login page
│   ├── logout.php           # Admin logout handler
│   ├── announcements.php    # Announcements CRUD handler
│   ├── events_handler.php   # Events CRUD handler
│   ├── documents.php        # Documents CRUD handler
│   ├── batches.php          # Academic batches management
│   ├── organizations.php    # Student organizations management
│   ├── institute.php        # Institute content management
│   ├── users.php            # User/Admin management
│   ├── settings.php         # System settings
│   ├── review_queue.php     # Content review queue
│   ├── archive_management.php  # Archive management interface
│   ├── archive_handler.php     # Archive operations handler
│   ├── audit_log.php        # Audit log viewer
│   ├── inquiries_handler.php   # Contact inquiries handler
│   └── holidays_handler.php    # Calendar holidays handler
│
├── public/                   # Public-facing pages
│   ├── home.php             # Homepage with intro animation
│   ├── institute.php        # Institute information
│   ├── calendar.php         # Event calendar
│   ├── events.php           # Events listing
│   ├── event-detail.php     # Event detail page
│   ├── documents.php        # Documents listing
│   ├── batches.php          # Academic batches listing
│   ├── batch-detail.php     # Batch detail page
│   ├── organization.php     # Organization detail page
│   ├── search.php           # Search functionality
│   ├── contact.php          # Contact form
│   ├── download.php         # Document download handler
│   └── image.php            # Image display handler
│
├── includes/                 # Core PHP includes
│   ├── config.php           # Configuration & constants
│   ├── database.php         # Database connection & helpers
│   ├── auth.php             # Authentication & authorization
│   ├── errors.php           # Error handling
│   ├── upload.php           # File upload utilities
│   ├── validation.php       # Input validation functions
│   ├── header.php           # Site header template
│   └── footer.php           # Site footer template
│
├── assets/                   # Static assets
│   ├── css/
│   │   └── style.css        # Main stylesheet
│   ├── js/
│   │   └── script.js        # Main JavaScript file
│   └── IMG/
│       └── ICONS/           # Document category icons
│
├── uploads/                   # User-uploaded files
│   ├── images/              # Uploaded images
│   └── documents/           # Uploaded documents
│
├── database/                 # Database files
│   └── icdi_db.sql          # Database schema & data
│
├── docs/                     # Documentation
│   ├── PROJECT_DOCUMENTATION.md
│   └── COMPLETION_PROGRESS.md
│
├── index.php                 # Entry point (redirects to home)
├── CREDENTIALS.md            # Admin credentials reference
└── README.md                 # Project README
```

---

Database Schema

Core Tables

admins
Administrative user accounts with role-based permissions.

Columns:
- id (INT UNSIGNED, PRIMARY KEY, AUTO_INCREMENT)
- email (VARCHAR 255, UNIQUE, NOT NULL)
- password (VARCHAR 255, NOT NULL) - bcrypt hashed
- name (VARCHAR 255, NULL)
- role (ENUM: 'super_admin', 'admin', 'editor', DEFAULT 'admin')
- created_at (TIMESTAMP, DEFAULT CURRENT_TIMESTAMP)
- updated_at (TIMESTAMP, DEFAULT CURRENT_TIMESTAMP ON UPDATE)

Indexes:
- PRIMARY KEY (id)
- UNIQUE KEY (email)

announcements
Announcements and meeting notifications.

Columns:
- id (INT UNSIGNED, PRIMARY KEY, AUTO_INCREMENT)
- title (VARCHAR 255, NOT NULL)
- description (TEXT, NULL)
- content (TEXT, NULL)
- category (ENUM: 'general', 'academic', 'event', 'maintenance', 'urgent', DEFAULT 'general')
- image (VARCHAR 500, NULL) - Relative path to uploaded image
- pinned (TINYINT(1), DEFAULT 0) - Boolean for pinning
- is_meeting (TINYINT(1), DEFAULT 0) - Boolean for meeting type
- meeting_date (DATETIME, NULL)
- meeting_end_date (DATETIME, NULL)
- meeting_location (VARCHAR 255, NULL)
- status (ENUM: 'draft', 'pending_review', 'approved', 'published', 'archived', DEFAULT 'draft')
- academic_year (VARCHAR 20, NULL)
- created_by (INT UNSIGNED, NULL, FK to admins.id)
- reviewed_by (INT UNSIGNED, NULL, FK to admins.id)
- reviewed_at (TIMESTAMP, NULL)
- review_notes (TEXT, NULL)
- approved_by (INT UNSIGNED, NULL, FK to admins.id)
- approved_at (TIMESTAMP, NULL)
- approval_notes (TEXT, NULL)
- created_at (TIMESTAMP, DEFAULT CURRENT_TIMESTAMP)
- updated_at (TIMESTAMP, DEFAULT CURRENT_TIMESTAMP ON UPDATE)

events
Event listings with dates and details.

Columns:
- id (INT UNSIGNED, PRIMARY KEY, AUTO_INCREMENT)
- title (VARCHAR 255, NOT NULL)
- caption (VARCHAR 500, NULL)
- description (TEXT, NULL)
- summary (TEXT, NULL)
- category (ENUM: 'workshop', 'seminar', 'service', 'celebration', 'other', DEFAULT 'other')
- schedule_type (ENUM: 'event', 'enrollment', 'school_break', 'school_end', 'start_of_classes', 'exam_period', DEFAULT 'event')
- image (VARCHAR 500, NOT NULL) - Main image path
- gallery (TEXT, NULL) - JSON array of gallery image paths
- date (DATE, NOT NULL)
- end_date (DATE, NULL)
- location (VARCHAR 255, NULL)
- display_order (INT, DEFAULT 0)
- status (ENUM: 'draft', 'pending_review', 'approved', 'published', 'archived', DEFAULT 'draft')
- academic_year (VARCHAR 20, NULL)
- created_by (INT UNSIGNED, NULL, FK to admins.id)
- reviewed_by (INT UNSIGNED, NULL, FK to admins.id)
- reviewed_at (TIMESTAMP, NULL)
- review_notes (TEXT, NULL)
- approved_by (INT UNSIGNED, NULL, FK to admins.id)
- approved_at (TIMESTAMP, NULL)
- approval_notes (TEXT, NULL)
- created_at (TIMESTAMP, DEFAULT CURRENT_TIMESTAMP)
- updated_at (TIMESTAMP, DEFAULT CURRENT_TIMESTAMP ON UPDATE)

documents
Document repository with categorization.

Columns:
- id (INT UNSIGNED, PRIMARY KEY, AUTO_INCREMENT)
- title (VARCHAR 255, NOT NULL)
- description (TEXT, NULL)
- category (ENUM: '01', '02', '03', '04', '05', NOT NULL)
  - '01': OFFICES REPORT
  - '02': EXECUTIVE ORDER
  - '03': ORDINANCE
  - '04': RESOLUTION
  - '05': OTHER
- subcategory (VARCHAR 100, NULL)
- series_year (VARCHAR 20, NULL)
- document_type (ENUM: 'executive_order', 'administrative_order', 'memorandum', NULL)
- academic_year (VARCHAR 20, NULL)
- file_path (VARCHAR 500, NULL) - Relative path to uploaded file
- file_size (BIGINT, NULL) - File size in bytes
- file_type (VARCHAR 50, NULL)
- status (ENUM: 'draft', 'pending_review', 'approved', 'published', 'archived', DEFAULT 'draft')
- created_by (INT UNSIGNED, NULL, FK to admins.id)
- reviewed_by (INT UNSIGNED, NULL, FK to admins.id)
- reviewed_at (TIMESTAMP, NULL)
- review_notes (TEXT, NULL)
- approved_by (INT UNSIGNED, NULL, FK to admins.id)
- approved_at (TIMESTAMP, NULL)
- approval_notes (TEXT, NULL)
- created_at (TIMESTAMP, DEFAULT CURRENT_TIMESTAMP)
- updated_at (TIMESTAMP, DEFAULT CURRENT_TIMESTAMP ON UPDATE)

student_organizations
Student organization profiles.

Columns:
- id (INT UNSIGNED, PRIMARY KEY, AUTO_INCREMENT)
- name (VARCHAR 255, NOT NULL)
- description (TEXT, NULL)
- mission (TEXT, NULL)
- vision (TEXT, NULL)
- logo (VARCHAR 500, NULL) - Logo image path
- banner (VARCHAR 500, NULL) - Banner image path
- core_values (TEXT, NULL) - JSON array of core values
- status (ENUM: 'active', 'inactive', 'archived', DEFAULT 'active')
- display_order (INT, DEFAULT 0)
- created_at (TIMESTAMP, DEFAULT CURRENT_TIMESTAMP)
- updated_at (TIMESTAMP, DEFAULT CURRENT_TIMESTAMP ON UPDATE)

batches
Academic batch information.

Columns:
- id (INT UNSIGNED, PRIMARY KEY, AUTO_INCREMENT)
- organization_id (INT UNSIGNED, NULL, FK to student_organizations.id)
- academic_year (VARCHAR 50, NOT NULL)
- start_year (YEAR(4), NOT NULL)
- end_year (YEAR(4), NOT NULL)
- image (VARCHAR 500, NULL)
- description (TEXT, NULL)
- target_group (ENUM: 'all', 'adviser', 'executive_officer', 'executive_associate', DEFAULT 'all')
- status (ENUM: 'draft', 'active', 'archived', DEFAULT 'active')
- display_order (INT, DEFAULT 0)
- created_by (INT UNSIGNED, NULL, FK to admins.id)
- created_at (TIMESTAMP, DEFAULT CURRENT_TIMESTAMP)
- updated_at (TIMESTAMP, DEFAULT CURRENT_TIMESTAMP ON UPDATE)

batch_members
Members of academic batches.

Columns:
- id (INT UNSIGNED, PRIMARY KEY, AUTO_INCREMENT)
- batch_id (INT UNSIGNED, NOT NULL, FK to batches.id)
- group_type (ENUM: 'adviser', 'executive_officer', 'executive_associate', NOT NULL)
- name (VARCHAR 255, NOT NULL)
- position_title (VARCHAR 255, NOT NULL)
- image (VARCHAR 500, NULL)
- display_order (INT, DEFAULT 0)
- created_at (TIMESTAMP, DEFAULT CURRENT_TIMESTAMP)
- updated_at (TIMESTAMP, DEFAULT CURRENT_TIMESTAMP ON UPDATE)

institute_info
Institute information sections.

Columns:
- id (INT UNSIGNED, PRIMARY KEY, AUTO_INCREMENT)
- section (VARCHAR 100, NOT NULL) - 'about', 'mission', 'vision', 'logo', 'banner'
- title (VARCHAR 255, NULL)
- content (TEXT, NULL)
- image (VARCHAR 500, NULL)
- display_order (INT, DEFAULT 0)
- status (ENUM: 'draft', 'published', DEFAULT 'published')
- updated_by (INT UNSIGNED, NULL, FK to admins.id)
- created_at (TIMESTAMP, DEFAULT CURRENT_TIMESTAMP)
- updated_at (TIMESTAMP, DEFAULT CURRENT_TIMESTAMP ON UPDATE)

institute_sections
Institute section content (Faculty, Admin, Program).

Columns:
- id (INT UNSIGNED, PRIMARY KEY, AUTO_INCREMENT)
- type (ENUM: 'faculty_unit', 'admin_representative', 'program', NOT NULL)
- title (VARCHAR 255, NOT NULL)
- description (TEXT, NULL)
- content (TEXT, NULL)
- image (VARCHAR 500, NULL)
- display_order (INT, DEFAULT 0)
- status (ENUM: 'draft', 'published', DEFAULT 'published')
- created_by (INT UNSIGNED, NULL, FK to admins.id)
- created_at (TIMESTAMP, DEFAULT CURRENT_TIMESTAMP)
- updated_at (TIMESTAMP, DEFAULT CURRENT_TIMESTAMP ON UPDATE)

organization_core_values
Core values for student organizations.

Columns:
- id (INT UNSIGNED, PRIMARY KEY, AUTO_INCREMENT)
- organization_id (INT UNSIGNED, NOT NULL, FK to student_organizations.id)
- icon (VARCHAR 500, NULL)
- title (VARCHAR 255, NOT NULL)
- description (TEXT, NULL)
- display_order (INT, NOT NULL, DEFAULT 0)
- created_at (TIMESTAMP, DEFAULT CURRENT_TIMESTAMP)
- updated_at (TIMESTAMP, DEFAULT CURRENT_TIMESTAMP ON UPDATE)

holidays
Calendar holidays and special dates.

Columns:
- id (INT UNSIGNED, PRIMARY KEY, AUTO_INCREMENT)
- date (DATE, NOT NULL)
- end_date (DATE, NULL) - For multi-day holidays
- name (VARCHAR 255, NOT NULL)
- description (TEXT, NULL)
- type (ENUM: 'regular', 'special_non_working', 'special_working', 'dasma', 'enrollment', 'wellness_break', 'christmas_break', 'year_end', 'school_end', 'start_of_school', 'school', DEFAULT 'regular')
- type_label (VARCHAR 255, NULL) - Custom label override
- region (VARCHAR 20, NOT NULL, DEFAULT 'PH') - 'PH' or 'Dasma'
- created_at (TIMESTAMP, DEFAULT CURRENT_TIMESTAMP)

contact_inquiries
Contact form submissions.

Columns:
- id (INT UNSIGNED, PRIMARY KEY, AUTO_INCREMENT)
- name (VARCHAR 255, NOT NULL)
- email (VARCHAR 255, NOT NULL)
- subject (VARCHAR 255, NULL)
- message (TEXT, NOT NULL)
- status (ENUM: 'open', 'closed', 'archived', DEFAULT 'open')
- response_text (TEXT, NULL)
- responded_at (TIMESTAMP, NULL)
- responded_by (INT UNSIGNED, NULL, FK to admins.id)
- created_at (TIMESTAMP, DEFAULT CURRENT_TIMESTAMP)
- updated_at (TIMESTAMP, DEFAULT CURRENT_TIMESTAMP ON UPDATE)

audit_log
Administrative action audit trail.

Columns:
- id (INT UNSIGNED, PRIMARY KEY, AUTO_INCREMENT)
- admin_id (INT UNSIGNED, NULL, FK to admins.id)
- action (VARCHAR 100, NOT NULL) - 'login', 'logout', 'publish', 'archive', etc.
- entity_type (VARCHAR 50, NULL) - 'announcement', 'document', 'event', etc.
- entity_id (INT UNSIGNED, NULL)
- details (TEXT, NULL) - JSON or text details
- ip_address (VARCHAR 45, NULL)
- user_agent (VARCHAR 500, NULL)
- created_at (TIMESTAMP, DEFAULT CURRENT_TIMESTAMP)

site_settings
System configuration settings.

Columns:
- id (INT UNSIGNED, PRIMARY KEY, AUTO_INCREMENT)
- key (VARCHAR 255, UNIQUE, NOT NULL)
- value (TEXT, NULL)
- type (VARCHAR 50, NULL) - 'string', 'number', 'boolean', 'json'
- updated_at (TIMESTAMP, DEFAULT CURRENT_TIMESTAMP ON UPDATE)

Foreign Key Relationships

- announcements.created_by -> admins.id
- announcements.reviewed_by -> admins.id
- announcements.approved_by -> admins.id
- events.created_by -> admins.id
- events.reviewed_by -> admins.id
- events.approved_by -> admins.id
- documents.created_by -> admins.id
- documents.reviewed_by -> admins.id
- documents.approved_by -> admins.id
- batches.organization_id -> student_organizations.id
- batches.created_by -> admins.id
- batch_members.batch_id -> batches.id
- organization_core_values.organization_id -> student_organizations.id
- contact_inquiries.responded_by -> admins.id
- audit_log.admin_id -> admins.id
- institute_info.updated_by -> admins.id
- institute_sections.created_by -> admins.id

---

Features & Functionality

Public Features

Homepage (public/home.php)
- Intro animation (skippable with ?skip_intro=1)
- Featured announcements display (pinned first, then recent)
- Upcoming events carousel
- Quick access to documents
- Search functionality in header
- Responsive design

Institute Page (public/institute.php)
- About section with mission and vision
- Faculty Unit information
- Admin Representative details
- Program information
- Student organization directory sidebar
- Dynamic section switching via URL parameter (?section=about|faculty|admin|program)

Calendar (public/calendar.php)
- Monthly calendar view with navigation
- Event markers on calendar days
- Meeting announcements integration
- Holiday display with different types
- Day detail modal showing all events/holidays
- Click to view full event/announcement details in modal
- Year and month picker

Events (public/events.php)
- Event listing with filters
- Event detail pages (public/event-detail.php)
- Image galleries for events
- Date range filtering
- Category filtering
- Search integration

Documents (public/documents.php)
- Categorized document listing
- Download functionality (public/download.php)
- Category filtering (5 categories)
- Search integration
- Document icons by category

Batches (public/batches.php)
- Academic batch listing
- Batch detail pages (public/batch-detail.php)
- Member listings by group type
- Organization association

Organizations (public/organization.php)
- Organization profile pages
- Mission, Vision, Core Values display
- Logo and banner display
- Member listings
- Batch history

Search (public/search.php)
- Full-text search across:
  - Announcements
  - Events
  - Documents
  - Student Organizations
- Search result categorization
- Highlighted search terms

Contact (public/contact.php)
- Contact form submission
- Inquiry management in admin panel
- Email validation
- Status tracking

Administrative Features

Dashboard (admin/index.php)
- Role-based card visibility
- Quick statistics
- Content management tabs (Announcements, Events, Documents)
- Review queue access
- Archive management access
- User management (Super Admin only)
- Settings access (Super Admin only)
- Audit log access (Super Admin only)
- Institute content management (Super Admin only)

Content Management

Announcements (admin/announcements.php):
- Create, Read, Update, Archive operations
- Meeting fields (date, time, location)
- Pinning functionality
- Category selection
- Image upload
- Status workflow (draft -> pending_review -> approved -> published)
- Bulk operations (publish, archive)
- Archive and restore functionality

Events (admin/events_handler.php):
- Create, Read, Update, Archive operations
- Date ranges (start and end dates)
- Location field
- Image gallery upload
- Category selection
- Schedule type selection
- Status workflow
- Bulk operations

Documents (admin/documents.php):
- Create, Read, Update, Archive operations
- File upload (PDF, DOC, DOCX, XLS, XLSX)
- Category selection (5 categories)
- Subcategory field
- Series year and academic year
- Document type (for Orders category)
- Status workflow
- Bulk operations

Batches (admin/batches.php):
- Create, Read, Update, Archive operations
- Academic year management
- Organization association
- Image upload
- Target group selection
- Member management
- Display order

Organizations (admin/organizations.php):
- Create, Read, Update, Archive operations
- Mission, Vision, Core Values
- Logo and banner upload
- Core values management
- Display order
- Status management

Institute Content (admin/institute.php):
- About, Mission, Vision, Logo, Banner management
- Faculty Unit management
- Admin Representative management
- Program management
- Image uploads
- Content editing

Review Queue (admin/review_queue.php)
- Pending submissions list
- Approve/Reject functionality
- Review notes
- Bulk actions
- Filter by content type

Archive Management (admin/archive_management.php)
- View all archived content
- Filter by content type
- Restore functionality
- Permanent delete
- Bulk operations
- Statistics display

User Management (admin/users.php)
- Create/edit admin accounts
- Role assignment (Super Admin, Admin, Editor)
- Password change functionality
- Archive users (redirects to archive management)
- User listing with roles

Settings (admin/settings.php)
- Site configuration
- System preferences
- Key-value storage

Audit Log (admin/audit_log.php)
- Administrative action history
- Filtering and search
- IP address tracking
- User agent tracking
- Action details

Contact Inquiries (admin/inquiries_handler.php)
- List all inquiries
- Update inquiry status
- Archive inquiries
- Response functionality

Holidays (admin/holidays_handler.php)
- Create, Read, Update, Delete holidays
- Holiday type selection
- Multi-day holiday support
- Region selection (PH, Dasma)

---

User Roles & Permissions

Super Admin

Full System Access:
- User management (create, edit, archive admins)
- System settings
- Audit log access
- Publish, approve, archive content
- Review and approve submissions
- Institute content management
- All dashboard cards visible

Dashboard Cards:
- Review Queue
- Archive Management
- Audit Log
- Institute Content
- Settings
- User Roles

Admin

Content Management:
- Publish, approve, archive content
- Review and approve submissions
- Create and edit content
- Archive management
- Cannot manage users
- Cannot access system settings
- Cannot access audit log
- Cannot manage institute content

Dashboard Cards:
- Review Queue
- Archive Management

Editor

Limited Access:
- Create drafts
- Submit content for review
- Edit own content (draft status only)
- Cannot publish content
- Cannot approve content
- Cannot archive content
- Cannot access user management
- Cannot access system settings
- Cannot access review queue
- Cannot access archive management

Dashboard Cards:
- None (content creation only)

---

API Endpoints

Admin Handlers (JSON API)

All admin handlers return JSON responses with the following structure:

Success Response:
```json
{
  "success": true,
  "data": [...],
  "message": "Operation successful"
}
```

Error Response:
```json
{
  "success": false,
  "error": "Error message"
}
```

Announcements (admin/announcements.php)

GET - List Announcements:
- URL: `admin/announcements.php?page=1&per_page=20&status=published&category=general&search=keyword`
- Parameters:
  - page (int): Page number
  - per_page (int): Items per page
  - status (string): Filter by status
  - category (string): Filter by category
  - search (string): Search term
- Returns: JSON with announcements array and pagination info

POST - Create Announcement:
- Action: `action=create`
- Required: title, description, category
- Optional: content, image, pinned, is_meeting, meeting_date, meeting_end_date, meeting_location
- Returns: Success/error JSON

POST - Update Announcement:
- Action: `action=update`
- Required: id, title, description, category
- Optional: All announcement fields
- Returns: Success/error JSON

POST - Archive Announcement:
- Action: `action=archive`
- Required: id
- Returns: Success/error JSON

POST - Restore Announcement:
- Action: `action=restore`
- Required: id
- Returns: Success/error JSON

POST - Bulk Action:
- Action: `action=bulk_action`
- Required: bulk_action (publish|archive), ids[] (array)
- Returns: Success/error JSON

Events (admin/events_handler.php)

GET - List Events:
- URL: `admin/events_handler.php?page=1&per_page=20&status=published&category=workshop`
- Returns: JSON with events array and pagination info

POST - Create Event:
- Action: `action=create`
- Required: title, date, image
- Optional: caption, description, summary, category, schedule_type, gallery, end_date, location

POST - Update Event:
- Action: `action=update`
- Required: id, title, date, image
- Optional: All event fields

POST - Archive Event:
- Action: `action=archive`
- Required: id

POST - Restore Event:
- Action: `action=restore`
- Required: id

POST - Bulk Action:
- Action: `action=bulk_action`
- Required: bulk_action, ids[]

Documents (admin/documents.php)

GET - List Documents:
- URL: `admin/documents.php?page=1&per_page=20&status=published&category=01`
- Returns: JSON with documents array and pagination info

POST - Create Document:
- Action: `action=create`
- Required: title, category, file (upload)
- Optional: description, subcategory, series_year, document_type, academic_year

POST - Update Document:
- Action: `action=update`
- Required: id, title, category
- Optional: All document fields, file (to replace)

POST - Archive Document:
- Action: `action=archive`
- Required: id

POST - Restore Document:
- Action: `action=restore`
- Required: id

POST - Bulk Action:
- Action: `action=bulk_action`
- Required: bulk_action, ids[]

Archive (admin/archive_handler.php)

POST - Bulk Archive:
- Action: `action=bulk_archive`
- Required: itemType, ids[] (array)
- Returns: Success/error JSON

POST - Restore Item:
- Action: `action=restore`
- Required: itemType, id
- Returns: Success/error JSON

POST - Permanent Delete:
- Action: `action=delete`
- Required: itemType, id
- Returns: Success/error JSON

Inquiries (admin/inquiries_handler.php)

GET - List Inquiries:
- URL: `admin/inquiries_handler.php`
- Returns: JSON with inquiries array

POST - Update Status:
- Action: `action=update_status`
- Required: id, status
- Optional: response_text

POST - Archive Inquiry:
- Action: `action=archive`
- Required: id

Holidays (admin/holidays_handler.php)

GET - List Holidays:
- URL: `admin/holidays_handler.php`
- Returns: JSON with holidays array

POST - Create Holiday:
- Action: `action=create`
- Required: name, date, type, region
- Optional: end_date, description, type_label

POST - Update Holiday:
- Action: `action=update`
- Required: id, name, date, type, region
- Optional: All holiday fields

POST - Delete Holiday:
- Action: `action=delete`
- Required: id

CSRF Protection

All POST requests require CSRF token:
- Token name: `csrf_token`
- Token value: Generated via `generateCSRFToken()` function
- Validation: `requireCSRFToken()` function

---

Security Features

Authentication

Session-based Authentication:
- PHP sessions for user state
- Session regeneration on login
- Secure cookie configuration
- Session timeout handling

Password Security:
- Bcrypt hashing via `password_hash()`
- Password verification via `password_verify()`
- Password change functionality
- No password recovery (manual admin reset)

CSRF Protection

Token Generation:
- `generateCSRFToken()` function in `includes/auth.php`
- Stored in session
- Included in all forms and AJAX requests

Token Validation:
- `requireCSRFToken()` function validates tokens
- All POST requests validated
- Invalid token returns error response

Input Validation

Server-side Validation:
- `includes/validation.php` provides validation functions
- Email validation: `validateEmail()`
- String sanitization: `sanitizeString()`
- Input length limits
- Type checking

SQL Injection Prevention:
- Prepared statements for all queries
- Parameter binding via PDO
- No direct string concatenation in queries

XSS Prevention:
- `htmlspecialchars()` for output
- `strip_tags()` for input sanitization
- ENT_QUOTES flag for quotes
- UTF-8 encoding

File Upload Security

Validation:
- File type checking (whitelist)
- File size limits
- File extension validation
- MIME type checking

Storage:
- Unique filename generation
- Secure file paths
- No executable files allowed
- Separate directories for images and documents

Authorization

Role-based Access Control:
- Function-level checks: `canPublish()`, `isSuperAdmin()`, `requireAdmin()`
- Page-level restrictions
- Feature-level permissions
- Dynamic UI based on role

Audit Logging

Comprehensive Logging:
- All administrative actions logged
- IP address tracking
- User agent tracking
- Action details in JSON format
- Timestamp for all actions

---

File Management

Upload Directories

Structure:
- `uploads/images/` - Image files (announcements, events, organizations, batches)
- `uploads/documents/` - Document files (PDFs, DOC, DOCX, XLS, XLSX)

File Naming Convention

Images:
- Format: `img_[hash].[ext]`
- Hash: MD5 hash of filename + timestamp
- Example: `img_697b5a022cfad0.14140578_1769691650.png`

Documents:
- Format: `doc_[hash].[ext]`
- Hash: MD5 hash of filename + timestamp
- Example: `doc_697b5cf47aca74.10396727_1769692404.pdf`

File Utilities (includes/upload.php)

uploadImage():
- Validates image file type
- Checks file size
- Generates unique filename
- Moves file to uploads/images/
- Returns relative path

uploadDocument():
- Validates document file type
- Checks file size
- Generates unique filename
- Moves file to uploads/documents/
- Returns relative path and file info

getImageUrl():
- Generates full URL for image
- Handles relative paths
- Returns public-accessible URL

deleteUploadedFile():
- Deletes file from filesystem
- Validates file path
- Handles errors gracefully

Supported Formats

Images:
- JPG, JPEG
- PNG
- GIF
- WebP

Documents:
- PDF
- DOC, DOCX
- XLS, XLSX

File Size Limits

- Default PHP limits apply
- Recommended: 10MB max file size
- Configurable via PHP ini settings

---

Configuration

Database Configuration (includes/config.php)

```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'icdi_db');
define('DB_USER', 'root');
define('DB_PASS', '');
```

URL Configuration

URLs are automatically detected based on script location:
- BASE_URL: Base path to ICDI directory
- PUBLIC_URL: Path to public directory
- ADMIN_URL: Path to admin directory
- ASSETS_URL: Path to assets directory

Site Configuration

```php
define('SITE_NAME', 'PROWLWAY');
define('SITE_DESCRIPTION', 'ICDISG Archive Website');
```

PHP Configuration

Recommended settings in `php.ini`:

```ini
upload_max_filesize = 10M
post_max_size = 10M
max_execution_time = 300
memory_limit = 128M
session.cookie_httponly = 1
session.cookie_secure = 0  # Set to 1 for HTTPS
```

---

Deployment Guide

Prerequisites

- PHP 8.2 or higher
- MySQL/MariaDB 10.4 or higher
- Apache web server
- mod_rewrite enabled (optional)

Installation Steps

1. Clone/Download Project
   ```bash
   cd /path/to/webroot
   git clone [repository-url] prowlway
   ```

2. Database Setup
   - Create database: `icdi_db`
   - Import schema: `database/icdi_db.sql`
   - Verify tables created

3. Configuration
   - Edit `includes/config.php`:
     - Update database credentials
     - Verify URL paths (auto-detected)
     - Check site settings

4. File Permissions
   ```bash
   chmod 755 uploads/
   chmod 755 uploads/images/
   chmod 755 uploads/documents/
   ```

5. Access Admin Panel
   - URL: `http://your-domain/prowlway/ICDI/admin/`
   - Default credentials: See `CREDENTIALS.md`
   - Change passwords immediately

Production Checklist

- Change all default admin passwords
- Set `error_reporting(0)` in production
- Enable HTTPS
- Configure proper file permissions
- Set up database backups
- Review and test CSRF protection
- Test all user roles and permissions
- Verify file upload security
- Configure session security
- Set up error logging

---

Development Guide

Database Helpers (includes/database.php)

getDB():
- Returns PDO database connection
- Singleton pattern
- Error handling for connection failures

dbFetchOne($query, $params = []):
- Fetches single row
- Returns associative array or null
- Uses prepared statements

dbFetchAll($query, $params = []):
- Fetches multiple rows
- Returns array of associative arrays
- Uses prepared statements

dbInsert($table, $data):
- Inserts record into table
- Returns last insert ID
- Uses prepared statements

dbUpdate($table, $data, $where, $whereParams = []):
- Updates records matching where clause
- Returns affected row count
- Uses prepared statements

dbDelete($table, $where, $whereParams = []):
- Deletes records matching where clause
- Returns affected row count
- Uses prepared statements

getLastDbError():
- Returns last database error message
- Useful for debugging

Error Handling (includes/errors.php)

APIError::json($message, $code = 400):
- Returns JSON error response
- Sets HTTP status code
- Logs error

APIError::database($exception, $logDetails = false):
- Handles database errors
- Returns user-friendly message
- Logs detailed error if requested

Code Style

PHP:
- PSR-12 inspired
- Mixed procedural/OOP approach
- Descriptive function names
- PHPDoc comments for functions

JavaScript:
- ES6+ features
- Modern syntax
- Descriptive variable names
- Function documentation

CSS:
- Tailwind utility classes
- Custom styles in style.css
- Consistent naming
- Responsive design

Adding New Features

1. Database:
   - Create table if needed
   - Add to schema documentation
   - Create migration if needed

2. Backend:
   - Create handler file in `admin/`
   - Add CRUD operations
   - Implement validation
   - Add audit logging

3. Frontend:
   - Create public page in `public/`
   - Add JavaScript functions in `script.js`
   - Add styles in `style.css`
   - Update navigation if needed

4. Testing:
   - Test all CRUD operations
   - Test permissions
   - Test validation
   - Test error handling

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
- Verify database exists

File Upload Issues

Symptoms: Files not uploading or upload errors

Solutions:
- Verify `uploads/` directory permissions (755)
- Check PHP `upload_max_filesize` and `post_max_size`
- Ensure directory exists and is writable
- Check disk space availability
- Verify file type is allowed
- Check PHP error logs

Admin Login Issues

Symptoms: Cannot login or session errors

Solutions:
- Clear browser cookies/session
- Verify admin account exists in database
- Check `CREDENTIALS.md` for correct credentials
- Verify session configuration in PHP
- Check PHP error logs
- Verify password hash matches

Page Not Loading

Symptoms: Blank page or 500 error

Solutions:
- Check PHP error logs
- Verify all includes are present
- Check file permissions
- Verify database connection
- Check PHP version compatibility
- Verify syntax errors

CSRF Token Errors

Symptoms: "Invalid CSRF Token" errors

Solutions:
- Clear browser cache
- Ensure JavaScript is enabled
- Check session is active
- Verify CSRF token is being sent in forms
- Check session configuration
- Verify token generation function

Archive/Restore Not Working

Symptoms: Archive or restore operations fail

Solutions:
- Verify item exists in database
- Check status field values
- Verify permissions
- Check database constraints
- Review error logs
- Verify handler file exists

---

Document Version: 1.0.0  
Last Updated: January 30, 2026  
