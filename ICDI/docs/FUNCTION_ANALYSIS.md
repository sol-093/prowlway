# ICDI Project - Complete Function Analysis

**Version:** 1.0.0  
**Date:** January 31, 2026  
**Project:** PROWLWAY - ICDISG Archive Website

---

## Table of Contents

1. [Database Functions](#database-functions)
2. [Authentication & Authorization Functions](#authentication--authorization-functions)
3. [File Upload Functions](#file-upload-functions)
4. [Validation Functions](#validation-functions)
5. [Error Handling Functions](#error-handling-functions)
6. [Configuration & Constants](#configuration--constants)
7. [Function Dependencies Map](#function-dependencies-map)
8. [Security Analysis](#security-analysis)
9. [Performance Considerations](#performance-considerations)
10. [Recommendations & Improvements](#recommendations--improvements)

---

## Database Functions

### Class: Database (Singleton Pattern)

#### `Database::__construct()`
**Purpose:** Private constructor that creates a PDO database connection  
**Access:** Private (only called internally)  
**Parameters:** None  
**Returns:** void  
**Throws:** PDOException on connection failure

**Analysis:**
- Implements singleton pattern to ensure single database connection
- Uses UTF-8 charset (utf8mb4) for full Unicode support
- Sets PDO error mode to exception for better error handling
- Disables prepared statement emulation for security
- Logs errors but dies on failure (could be improved to throw exception)

**Security:**
- ✅ Uses prepared statements (emulation disabled)
- ✅ UTF-8 charset prevents encoding issues
- ⚠️ Dies on error - should throw exception for better error handling

**Dependencies:**
- `DB_HOST`, `DB_NAME`, `DB_USER`, `DB_PASS` constants from config.php

---

#### `Database::getInstance()`
**Purpose:** Returns singleton instance of Database class  
**Access:** Public static  
**Parameters:** None  
**Returns:** Database instance  
**Throws:** Exception on failure

**Analysis:**
- Thread-safe singleton implementation
- Creates instance only if null
- Logs errors before throwing exception
- Returns existing instance if already created

**Usage Pattern:**
```php
$db = Database::getInstance()->getConnection();
```

---

#### `Database::getConnection()`
**Purpose:** Returns PDO connection object  
**Access:** Public  
**Parameters:** None  
**Returns:** PDO connection object

**Analysis:**
- Simple getter method
- Returns the PDO instance stored in private property
- Used internally by helper functions

---

#### `Database::__clone()` & `Database::__wakeup()`
**Purpose:** Prevent cloning and unserialization of singleton  
**Access:** Private / Public  
**Analysis:**
- Prevents singleton pattern violation
- `__wakeup()` throws exception to prevent unserialization attacks

---

### Helper Functions

#### `getDB()`
**Purpose:** Convenience function to get PDO connection  
**Parameters:** None  
**Returns:** PDO|null (null on failure)

**Analysis:**
- Wraps Database::getInstance()->getConnection()
- Returns null on failure instead of throwing exception
- Allows graceful error handling in calling code
- Logs errors to error log

**Usage:**
```php
$db = getDB();
if ($db === null) {
    // Handle connection failure
    return;
}
```

**Security:**
- ✅ Returns null instead of exposing connection details
- ✅ Logs errors for debugging

---

#### `dbQuery($sql, $params = [])`
**Purpose:** Execute prepared SQL query  
**Parameters:**
- `$sql` (string): SQL query with placeholders
- `$params` (array): Associative array of parameters

**Returns:** PDOStatement|false

**Analysis:**
- Uses prepared statements (SQL injection prevention)
- Handles both named and positional placeholders
- Returns false on failure (allows checking)
- Logs errors with query preview (first 100 chars)

**Security:**
- ✅ Uses prepared statements
- ✅ Parameter binding prevents SQL injection
- ⚠️ Returns false on error - should consider throwing exception

**Usage:**
```php
$stmt = dbQuery("SELECT * FROM announcements WHERE status = :status", 
    ['status' => 'published']);
if ($stmt !== false) {
    $results = $stmt->fetchAll();
}
```

---

#### `dbFetchAll($sql, $params = [])`
**Purpose:** Fetch all rows from query result  
**Parameters:**
- `$sql` (string): SQL query
- `$params` (array): Query parameters

**Returns:** array (empty array on failure/no results)

**Analysis:**
- Wraps dbQuery() and fetches all results
- Returns empty array on failure (safe default)
- Uses FETCH_ASSOC mode (associative arrays)
- Logs errors for debugging

**Usage:**
```php
$announcements = dbFetchAll(
    "SELECT * FROM announcements WHERE status = :status",
    ['status' => 'published']
);
```

**Security:**
- ✅ Returns empty array on failure (no null checks needed)
- ✅ Uses prepared statements via dbQuery()

---

#### `dbFetchOne($sql, $params = [])`
**Purpose:** Fetch single row from query result  
**Parameters:**
- `$sql` (string): SQL query
- `$params` (array): Query parameters

**Returns:** array|null (null if not found/failed)

**Analysis:**
- Returns first row only
- Returns null on failure or no results
- Requires null check in calling code
- Logs errors for debugging

**Usage:**
```php
$admin = dbFetchOne(
    "SELECT * FROM admins WHERE email = :email",
    ['email' => 'admin@example.com']
);
if ($admin === null) {
    // Not found or error
}
```

**Security:**
- ✅ Uses prepared statements
- ✅ Returns null (safe default)

---

#### `dbInsert($table, $data)`
**Purpose:** Insert new record into table  
**Parameters:**
- `$table` (string): Table name (escaped with backticks)
- `$data` (array): Associative array of column => value pairs

**Returns:** int|false (last insert ID on success, false on failure)

**Analysis:**
- Dynamically builds INSERT statement
- Uses named placeholders for all values
- Escapes table name with backticks
- Stores error in global `$lastDbError` variable
- Returns last insert ID on success

**Security:**
- ✅ Uses prepared statements
- ✅ Table name escaped with backticks
- ✅ All values parameterized
- ⚠️ Global variable for error storage (could use exception)

**Usage:**
```php
$id = dbInsert('announcements', [
    'title' => 'New Announcement',
    'description' => 'Description',
    'status' => 'draft',
    'created_by' => 1
]);
if ($id !== false) {
    echo "Inserted with ID: $id";
} else {
    $error = getLastDbError();
}
```

**Error Handling:**
- Stores error in global variable
- Logs detailed error information
- Returns false on failure

---

#### `dbUpdate($table, $data, $where, $whereParams = [])`
**Purpose:** Update records matching WHERE clause  
**Parameters:**
- `$table` (string): Table name
- `$data` (array): Column => value pairs to update
- `$where` (string): WHERE clause with placeholders
- `$whereParams` (array): Parameters for WHERE clause

**Returns:** bool (true on success, false on failure)

**Analysis:**
- Dynamically builds UPDATE statement
- Uses prepared statements for both SET and WHERE clauses
- Escapes table and column names
- Stores error in global variable
- Returns boolean (not affected row count)

**Security:**
- ✅ Uses prepared statements for SET clause
- ✅ Uses prepared statements for WHERE clause
- ⚠️ WHERE clause is string - must be carefully constructed
- ⚠️ No validation that WHERE clause is safe

**Usage:**
```php
$success = dbUpdate(
    'announcements',
    ['status' => 'published'],
    'id = :id',
    ['id' => 5]
);
```

**Warning:**
- WHERE clause must be manually constructed with placeholders
- No automatic escaping of WHERE clause
- Developer must ensure WHERE clause is safe

---

#### `dbDelete($table, $where, $whereParams = [])`
**Purpose:** Delete records matching WHERE clause  
**Parameters:**
- `$table` (string): Table name
- `$where` (string): WHERE clause with placeholders
- `$whereParams` (array): Parameters for WHERE clause

**Returns:** bool (true on success, false on failure)

**Analysis:**
- Permanently deletes records (no soft delete)
- Uses prepared statements for WHERE clause
- Stores error in global variable
- Returns boolean

**Security:**
- ✅ Uses prepared statements
- ⚠️ Permanent deletion (no recovery)
- ⚠️ WHERE clause must be manually constructed

**Usage:**
```php
$success = dbDelete('announcements', 'id = :id', ['id' => 5]);
```

**Warning:**
- Permanently deletes data
- No soft delete mechanism
- Should be used with caution

---

#### `getLastDbError()`
**Purpose:** Get last database error message  
**Parameters:** None  
**Returns:** string|null

**Analysis:**
- Accesses global `$lastDbError` variable
- Returns null if no error stored
- Used for error reporting after failed operations

**Usage:**
```php
$result = dbInsert('table', $data);
if ($result === false) {
    $error = getLastDbError();
    // Handle error
}
```

---

## Authentication & Authorization Functions

### Role Checking Functions

#### `getAdminRole()`
**Purpose:** Get current admin's role from session  
**Parameters:** None  
**Returns:** string|null (role or null if not logged in)

**Analysis:**
- Simple session accessor
- Returns null if role not set
- Used by all authorization functions

**Security:**
- ✅ Checks session variable
- ⚠️ No validation of role value
- ⚠️ Relies on session security

---

#### `canPublish()`
**Purpose:** Check if user can publish/archive/approve content  
**Parameters:** None  
**Returns:** bool

**Analysis:**
- Returns true for 'admin' or 'super_admin' roles
- Returns false for 'editor' or null
- Used throughout admin handlers

**Usage:**
```php
if (canPublish()) {
    // Allow publish/archive operations
}
```

**Security:**
- ✅ Role-based access control
- ✅ Strict role checking (in_array with strict comparison)

---

#### `isSuperAdmin()`
**Purpose:** Check if user is Super Administrator  
**Parameters:** None  
**Returns:** bool

**Analysis:**
- Returns true only for 'super_admin' role
- Used for sensitive operations (user management, settings)

**Security:**
- ✅ Strict role comparison
- ✅ Used for highest privilege operations

---

#### `isEditor()`
**Purpose:** Check if user is Editor  
**Parameters:** None  
**Returns:** bool

**Analysis:**
- Returns true only for 'editor' role
- Editors have limited permissions (draft only)

---

### Authorization Functions

#### `requireAdmin($allowedRoles = null, $jsonResponse = false)`
**Purpose:** Require admin to be logged in, optionally with specific roles  
**Parameters:**
- `$allowedRoles` (array|null): Allowed roles (null = any logged-in admin)
- `$jsonResponse` (bool): Return JSON error if true, redirect if false

**Returns:** void (exits on failure)

**Analysis:**
- Checks session timeout (2 hours)
- Updates last activity timestamp
- Validates login status
- Validates role if specified
- Handles both JSON API and HTML redirects

**Security:**
- ✅ Session timeout check (2 hours)
- ✅ Last activity tracking
- ✅ Role-based access control
- ✅ CSRF-safe redirects

**Session Timeout:**
- 7200 seconds (2 hours)
- Destroys session on timeout
- Updates timestamp on each request

**Usage:**
```php
// Require any logged-in admin
requireAdmin();

// Require admin or super_admin
requireAdmin(['admin', 'super_admin'], true);
```

---

#### `requireSuperAdmin($jsonResponse = false)`
**Purpose:** Require Super Administrator role  
**Parameters:**
- `$jsonResponse` (bool): Return JSON error if true

**Returns:** void (exits on failure)

**Analysis:**
- Wrapper around requireAdmin(['super_admin'])
- Used for sensitive operations

---

#### `normalizeStatusByRole($requestedStatus, $restrictedStatuses = [...])`
**Purpose:** Normalize status based on user role  
**Parameters:**
- `$requestedStatus` (string): Requested status
- `$restrictedStatuses` (array): Statuses restricted to admin/super_admin

**Returns:** string (normalized status)

**Analysis:**
- Editors can only set 'draft' or 'pending_review'
- Admins can set any status
- Prevents privilege escalation
- Allows editors to submit for review

**Security:**
- ✅ Prevents privilege escalation
- ✅ Allows editors to submit for review
- ✅ Defaults to 'draft' for restricted statuses

**Usage:**
```php
$status = normalizeStatusByRole($_POST['status']);
// Editors: 'published' -> 'draft', 'pending_review' -> 'pending_review'
// Admins: Any status -> unchanged
```

---

#### `canReview()` & `canApprove()`
**Purpose:** Check if user can review/approve content  
**Parameters:** None  
**Returns:** bool

**Analysis:**
- Currently same as canPublish()
- Returns true for admin/super_admin
- May be separated in future for more granular control

---

### CSRF Protection Functions

#### `generateCSRFToken()`
**Purpose:** Generate and store CSRF token in session  
**Parameters:** None  
**Returns:** string (CSRF token)

**Analysis:**
- Generates 64-character hex token (32 bytes)
- Stores in session
- Returns existing token if already generated
- Uses cryptographically secure random_bytes()

**Security:**
- ✅ Cryptographically secure random generation
- ✅ Stored in session (server-side)
- ✅ 64-character token (strong)

**Usage:**
```php
$token = generateCSRFToken();
// Include in forms: <input type="hidden" name="csrf_token" value="<?= $token ?>">
```

---

#### `validateCSRFToken($token)`
**Purpose:** Validate CSRF token  
**Parameters:**
- `$token` (string): Token to validate

**Returns:** bool (true if valid)

**Analysis:**
- Uses hash_equals() for timing-safe comparison
- Checks session token exists
- Prevents timing attacks

**Security:**
- ✅ Timing-safe comparison (hash_equals)
- ✅ Checks session token exists
- ✅ Prevents CSRF attacks

**Usage:**
```php
if (!validateCSRFToken($_POST['csrf_token'])) {
    // Invalid token
}
```

---

#### `requireCSRFToken($jsonResponse = false)`
**Purpose:** Require valid CSRF token for POST requests  
**Parameters:**
- `$jsonResponse` (bool): Return JSON error if true

**Returns:** void (exits on failure)

**Analysis:**
- Only checks POST requests
- Validates token from $_POST['csrf_token']
- Returns JSON error or redirects on failure
- Used in all admin handlers

**Security:**
- ✅ Only validates POST requests (GET safe)
- ✅ JSON or redirect error handling
- ✅ Prevents CSRF attacks

**Usage:**
```php
requireCSRFToken(true); // For JSON API endpoints
```

---

### Audit Logging

#### `auditLog($action, $details = '', $entityType = null, $entityId = null)`
**Purpose:** Log administrative action to audit_log table  
**Parameters:**
- `$action` (string): Action name (e.g., 'login', 'publish', 'archive')
- `$details` (string|array): Action details (converted to JSON if array)
- `$entityType` (string|null): Entity type (e.g., 'announcement', 'event')
- `entityId` (int|null): Entity ID

**Returns:** void

**Analysis:**
- Logs admin actions for audit trail
- Captures IP address and user agent
- Stores admin ID from session
- Converts arrays to JSON
- Silently fails if database unavailable

**Security:**
- ✅ Comprehensive audit trail
- ✅ IP address tracking
- ✅ User agent tracking
- ✅ Admin ID tracking
- ⚠️ Silently fails on error (logs to error_log)

**Usage:**
```php
auditLog('publish', 'Announcement published', 'announcement', $id);
auditLog('login', ['ip' => $_SERVER['REMOTE_ADDR']]);
```

**Logged Information:**
- Admin ID
- Action name
- Entity type and ID
- Details (JSON or string)
- IP address
- User agent (truncated to 500 chars)
- Timestamp (automatic)

---

## File Upload Functions

### Image Functions

#### `generateThumbnail($sourcePath, $maxWidth = 300, $maxHeight = 300, $subfolder = 'images')`
**Purpose:** Generate thumbnail from image  
**Parameters:**
- `$sourcePath` (string): Full path to source image
- `$maxWidth` (int): Maximum thumbnail width (default: 300)
- `$maxHeight` (int): Maximum thumbnail height (default: 300)
- `$subfolder` (string): Subfolder within uploads (default: 'images')

**Returns:** array with keys:
- `success` (bool): True if successful
- `path` (string): Relative path to thumbnail (if success)
- `full_path` (string): Full filesystem path (if success)
- `error` (string): Error message (if failed)

**Analysis:**
- Creates resized thumbnail maintaining aspect ratio
- Supports JPEG, PNG, GIF, WebP
- Preserves transparency for PNG/GIF
- Stores in 'thumbnails' subdirectory
- Uses GD library

**Security:**
- ✅ Validates file exists
- ✅ Checks GD extension available
- ✅ Validates image file
- ✅ Creates secure directory structure

**Supported Formats:**
- JPEG/JPG
- PNG (with transparency)
- GIF (with transparency)
- WebP (if supported)

**Usage:**
```php
$result = generateThumbnail('/path/to/image.jpg', 200, 200);
if ($result['success']) {
    echo "Thumbnail: " . $result['path'];
}
```

**Thumbnail Quality:**
- JPEG: 85% quality
- PNG: Compression level 6
- GIF: Original quality
- WebP: 85% quality (if supported)

---

#### `optimizeImage($sourcePath, $quality = 85)`
**Purpose:** Optimize image (compress and optionally convert to WebP)  
**Parameters:**
- `$sourcePath` (string): Full path to source image
- `$quality` (int): Quality setting (default: 85)

**Returns:** bool (true on success)

**Analysis:**
- Compresses image to reduce file size
- Attempts WebP conversion if supported
- Re-saves with compression settings
- Requires GD extension

**Security:**
- ✅ Validates file exists
- ✅ Checks GD extension
- ✅ Validates image file

**Note:**
- WebP conversion creates new file (doesn't replace original by default)
- Can be modified to replace original if desired

---

#### `uploadImage($file, $subfolder = 'images', $generateThumbnail = true, $optimize = true)`
**Purpose:** Upload image file with validation and processing  
**Parameters:**
- `$file` (array): $_FILES array element
- `$subfolder` (string): Subfolder within uploads (default: 'images')
- `$generateThumbnail` (bool): Generate thumbnail (default: true)
- `$optimize` (bool): Optimize image (default: true)

**Returns:** array with keys:
- `success` (bool): True if successful
- `path` (string): Relative path for database (if success)
- `full_path` (string): Full filesystem path (if success)
- `thumbnail` (string|null): Thumbnail path if generated (if success)
- `error` (string): Error message (if failed)

**Analysis:**
- Validates file upload (error, size, type)
- Checks file size (max 5MB)
- Validates MIME type (JPEG, PNG, GIF, WebP)
- Generates unique filename
- Optionally optimizes and generates thumbnail
- Returns relative path for database storage

**Security:**
- ✅ Validates upload error
- ✅ Checks file size (5MB limit)
- ✅ Validates MIME type (not just extension)
- ✅ Generates unique filename (prevents overwrites)
- ✅ Creates directory if needed

**File Naming:**
- Format: `img_[uniqid]_[timestamp].[ext]`
- Example: `img_697b5a022cfad0.14140578_1769691650.png`
- Prevents filename collisions

**Usage:**
```php
if (isset($_FILES['image'])) {
    $result = uploadImage($_FILES['image'], 'images', true, true);
    if ($result['success']) {
        // Store $result['path'] in database
    }
}
```

**Validation:**
- Upload error check (UPLOAD_ERR_OK)
- File size: Max 5MB (MAX_IMAGE_SIZE)
- MIME type: JPEG, PNG, GIF, WebP only
- File type validation using finfo

---

#### `uploadMultipleImages($files, $subfolder = 'images')`
**Purpose:** Upload multiple images (for gallery)  
**Parameters:**
- `$files` (array): $_FILES array (single or multiple)
- `$subfolder` (string): Subfolder within uploads (default: 'images')

**Returns:** array of result arrays (same structure as uploadImage())

**Analysis:**
- Handles both single and multiple file uploads
- Processes each file individually
- Returns array of results
- Used for event galleries

**Usage:**
```php
if (isset($_FILES['gallery'])) {
    $results = uploadMultipleImages($_FILES['gallery'], 'images');
    foreach ($results as $result) {
        if ($result['success']) {
            // Process each image
        }
    }
}
```

---

### Document Functions

#### `uploadDocument($file)`
**Purpose:** Upload PDF document file  
**Parameters:**
- `$file` (array): $_FILES array element

**Returns:** array with keys:
- `success` (bool): True if successful
- `path` (string): Relative path for database (if success)
- `full_path` (string): Full filesystem path (if success)
- `size` (int): File size in bytes (if success)
- `type` (string): MIME type (if success)
- `error` (string): Error message (if failed)

**Analysis:**
- Validates PDF file upload
- Checks file size (max 50MB)
- Validates MIME type (PDF only)
- Generates unique filename
- Returns file metadata

**Security:**
- ✅ Validates upload error
- ✅ Checks file size (50MB limit)
- ✅ Validates MIME type (PDF only)
- ✅ Generates unique filename

**File Naming:**
- Format: `doc_[uniqid]_[timestamp].pdf`
- Example: `doc_697b5cf47aca74.10396727_1769692404.pdf`

**Usage:**
```php
if (isset($_FILES['document'])) {
    $result = uploadDocument($_FILES['document']);
    if ($result['success']) {
        // Store $result['path'] and $result['size'] in database
    }
}
```

**Note:**
- Currently only supports PDF
- Could be extended for DOC, DOCX, XLS, XLSX (as mentioned in docs)

---

### File Management Functions

#### `deleteUploadedFile($filepath)`
**Purpose:** Delete uploaded file from filesystem  
**Parameters:**
- `$filepath` (string): Relative path from uploads directory

**Returns:** bool (true on success)

**Analysis:**
- Safely deletes file from uploads directory
- Validates file exists before deletion
- Returns false on failure
- Does not delete associated thumbnails

**Security:**
- ✅ Validates file exists
- ✅ Only deletes from uploads directory
- ⚠️ Does not delete thumbnails (potential orphaned files)

**Usage:**
```php
if (deleteUploadedFile('images/photo.jpg')) {
    echo "File deleted";
}
```

**Note:**
- Should be extended to delete thumbnails automatically

---

#### `getImageUrl($filepath, $useThumbnail = false)`
**Purpose:** Generate URL for displaying image  
**Parameters:**
- `$filepath` (string): Relative path stored in database
- `$useThumbnail` (bool): Use thumbnail if available (default: false)

**Returns:** string (URL or empty string)

**Analysis:**
- Generates URL through secure image.php endpoint
- Optionally uses thumbnail if available
- Handles full URLs (returns as-is)
- Uses PUBLIC_URL constant

**Security:**
- ✅ Uses secure endpoint (image.php) for access control
- ✅ URL encoding for special characters
- ✅ Validates filepath not empty

**Usage:**
```php
$imageUrl = getImageUrl('images/photo.jpg', false);
echo "<img src='$imageUrl' alt='Photo'>";
```

**URL Format:**
- `/public/image.php?path=images/photo.jpg`
- Secure endpoint prevents direct file access

---

#### `getThumbnailPath($filepath)`
**Purpose:** Get thumbnail path for image if exists  
**Parameters:**
- `$filepath` (string): Relative path stored in database

**Returns:** string|null (thumbnail path if exists, null otherwise)

**Analysis:**
- Checks if thumbnail exists for given image
- Returns relative path if found
- Returns null if not found
- Used by getImageUrl()

**Thumbnail Path Format:**
- `images/thumbnails/thumb_[filename]`
- Example: `images/thumbnails/thumb_photo.jpg`

---

#### `getDocumentUrl($filepath)`
**Purpose:** Generate URL for downloading document  
**Parameters:**
- `$filepath` (string): Relative path stored in database

**Returns:** string (URL or empty string)

**Analysis:**
- Generates URL through secure download.php endpoint
- Handles full URLs (returns as-is)
- Uses BASE_URL constant

**Security:**
- ✅ Uses secure endpoint (download.php) for access control
- ✅ URL encoding for special characters

**Usage:**
```php
$docUrl = getDocumentUrl('documents/report.pdf');
echo "<a href='$docUrl'>Download Report</a>";
```

---

## Validation Functions

#### `validateEmail($email)`
**Purpose:** Validate email address format  
**Parameters:**
- `$email` (string): Email to validate

**Returns:** bool (true if valid)

**Analysis:**
- Uses PHP's filter_var() with FILTER_VALIDATE_EMAIL
- Returns false for invalid emails
- Simple wrapper function

**Usage:**
```php
if (validateEmail($_POST['email'])) {
    // Valid email
}
```

---

#### `sanitizeString($input, $maxLength = null)`
**Purpose:** Sanitize string input  
**Parameters:**
- `$input` (string): Input to sanitize
- `$maxLength` (int|null): Maximum length (null for no limit)

**Returns:** string (sanitized string)

**Analysis:**
- Strips HTML tags
- Trims whitespace
- Truncates to max length if specified
- Escapes HTML special characters
- Converts non-strings to string

**Security:**
- ✅ Strips HTML tags (prevents XSS)
- ✅ Escapes HTML entities
- ✅ UTF-8 encoding
- ✅ Length limiting

**Usage:**
```php
$clean = sanitizeString($_POST['title'], 255);
```

**Note:**
- Uses ENT_QUOTES flag (escapes single and double quotes)

---

#### `validateAcademicYear($year)`
**Purpose:** Validate academic year format (YYYY-YYYY)  
**Parameters:**
- `$year` (string): Academic year string

**Returns:** bool (true if valid format)

**Analysis:**
- Validates format: YYYY-YYYY
- Uses regex: `/^\d{4}-\d{4}$/`
- Does not validate year ranges

**Usage:**
```php
if (validateAcademicYear('2024-2025')) {
    // Valid format
}
```

**Note:**
- Does not validate that years are sequential or reasonable

---

#### `validateDate($date, $format = 'Y-m-d')`
**Purpose:** Validate date string format  
**Parameters:**
- `$date` (string): Date string
- `$format` (string): Date format (default: 'Y-m-d')

**Returns:** bool (true if valid date)

**Analysis:**
- Uses DateTime::createFromFormat()
- Validates both format and actual date validity
- Returns false for invalid dates

**Usage:**
```php
if (validateDate('2024-01-15', 'Y-m-d')) {
    // Valid date
}
```

---

#### `validateInteger($value, $min = null, $max = null)`
**Purpose:** Validate integer within range  
**Parameters:**
- `$value` (mixed): Value to validate
- `$min` (int|null): Minimum value (null for no minimum)
- `$max` (int|null): Maximum value (null for no maximum)

**Returns:** bool (true if valid integer in range)

**Analysis:**
- Checks if value is numeric
- Converts to integer
- Validates range if specified
- Returns false for non-numeric or out-of-range values

**Usage:**
```php
if (validateInteger($_POST['age'], 0, 120)) {
    // Valid age
}
```

---

#### `validateFileUpload($file, $allowedTypes = [], $maxSize = 5242880)`
**Purpose:** Validate file upload  
**Parameters:**
- `$file` (array): $_FILES array element
- `$allowedTypes` (array): Allowed MIME types
- `$maxSize` (int): Maximum file size in bytes (default: 5MB)

**Returns:** array with keys:
- `valid` (bool): True if valid
- `error` (string): Error message if invalid

**Analysis:**
- Validates upload error
- Checks file size
- Validates MIME type if allowedTypes specified
- Returns structured result array

**Usage:**
```php
$result = validateFileUpload($_FILES['file'], ['image/jpeg', 'image/png'], 10485760);
if ($result['valid']) {
    // File is valid
} else {
    echo $result['error'];
}
```

---

#### `validateUrl($url)`
**Purpose:** Validate URL format  
**Parameters:**
- `$url` (string): URL to validate

**Returns:** bool (true if valid URL)

**Analysis:**
- Uses PHP's filter_var() with FILTER_VALIDATE_URL
- Returns false for invalid URLs

**Usage:**
```php
if (validateUrl($_POST['website'])) {
    // Valid URL
}
```

---

#### `sanitizeHtml($html, $allowedTags = [...])`
**Purpose:** Sanitize HTML content (allows some HTML tags)  
**Parameters:**
- `$html` (string): HTML content
- `$allowedTags` (array): Allowed HTML tags

**Returns:** string (sanitized HTML)

**Analysis:**
- Strips all tags except allowed ones
- Default allowed tags: p, br, strong, em, u, ul, ol, li, a, h1-h6
- Uses strip_tags()

**Security:**
- ✅ Strips dangerous tags (script, iframe, etc.)
- ✅ Allows safe formatting tags
- ⚠️ Does not validate attributes (could allow malicious attributes)

**Usage:**
```php
$clean = sanitizeHtml($_POST['content'], ['p', 'br', 'strong']);
```

**Note:**
- Should consider HTMLPurifier for more robust sanitization

---

#### `validatePasswordStrength($password)`
**Purpose:** Validate password strength  
**Parameters:**
- `$password` (string): Password to validate

**Returns:** array with keys:
- `valid` (bool): True if valid
- `error` (string): Error message if invalid

**Analysis:**
- Validates minimum length (8 characters)
- Requires lowercase letter
- Requires uppercase letter
- Requires number
- Does not require special characters

**Requirements:**
- Minimum 8 characters
- At least one lowercase letter
- At least one uppercase letter
- At least one number

**Usage:**
```php
$result = validatePasswordStrength($_POST['password']);
if ($result['valid']) {
    // Password is strong enough
} else {
    echo $result['error'];
}
```

**Note:**
- Does not require special characters
- Could be enhanced with more requirements

---

## Error Handling Functions

### Class: APIError

#### `APIError::json($message, $code = 400, $additionalData = [])`
**Purpose:** Send JSON error response  
**Parameters:**
- `$message` (string): Error message
- `$code` (int): HTTP status code (default: 400)
- `$additionalData` (array): Additional data to include

**Returns:** void (exits)

**Analysis:**
- Sets HTTP response code
- Sets Content-Type header
- Returns JSON error response
- Exits script execution
- Merges additional data into response

**Usage:**
```php
APIError::json('Invalid input', 400, ['field' => 'email']);
// Outputs: {"success":false,"error":"Invalid input","field":"email"}
// Exits with HTTP 400
```

**Response Format:**
```json
{
    "success": false,
    "error": "Error message",
    ...additionalData
}
```

---

#### `APIError::log($message, $context = [], $level = 'error')`
**Purpose:** Log error with context  
**Parameters:**
- `$message` (string): Error message
- `$context` (array): Additional context data
- `$level` (string): Log level (error, warning, info)

**Returns:** void

**Analysis:**
- Formats log message with timestamp and level
- Includes context data as JSON
- Includes request context (method, URI, IP, user agent)
- Includes admin ID if available
- Logs to error_log

**Logged Information:**
- Timestamp
- Log level
- Error message
- Context data (JSON)
- Request method
- Request URI
- IP address
- User agent
- Admin ID (if available)

**Usage:**
```php
APIError::log('Database error', ['table' => 'announcements', 'id' => 5], 'error');
```

---

#### `APIError::database($e, $jsonResponse = false)`
**Purpose:** Handle database errors  
**Parameters:**
- `$e` (Exception): Exception object
- `$jsonResponse` (bool): Return JSON response

**Returns:** void (exits if jsonResponse)

**Analysis:**
- Extracts error message from exception
- Checks getLastDbError() for additional details
- Combines error messages if different
- Logs detailed error information
- Returns JSON error or dies with message

**Error Message Handling:**
- Uses exception message
- Checks getLastDbError() for database-specific errors
- Combines messages if both exist and different
- Shows actual error message for admin debugging

**Usage:**
```php
try {
    dbInsert('table', $data);
} catch (Exception $e) {
    APIError::database($e, true); // Returns JSON error
}
```

**Security:**
- ✅ Shows actual error for admin debugging
- ✅ Logs detailed error information
- ⚠️ Exposes database errors (could be security risk in production)

---

#### `APIError::validation($errors, $jsonResponse = false)`
**Purpose:** Handle validation errors  
**Parameters:**
- `$errors` (array): Array of validation error messages
- `$jsonResponse` (bool): Return JSON response

**Returns:** void (exits if jsonResponse) or array

**Analysis:**
- Returns JSON error with errors array if jsonResponse
- Returns errors array if not jsonResponse
- Used for form validation

**Usage:**
```php
$errors = [];
if (empty($_POST['title'])) {
    $errors[] = 'Title is required';
}
if (!empty($errors)) {
    APIError::validation($errors, true); // Returns JSON
}
```

**Response Format:**
```json
{
    "success": false,
    "error": "Validation failed",
    "errors": ["Title is required", "Email is invalid"]
}
```

---

#### `APIError::upload($message, $jsonResponse = false)`
**Purpose:** Handle file upload errors  
**Parameters:**
- `$message` (string): Error message
- `$jsonResponse` (bool): Return JSON response

**Returns:** void (exits if jsonResponse) or array

**Analysis:**
- Logs upload error
- Returns JSON error or error array
- Used for file upload validation

**Usage:**
```php
if ($uploadResult['success'] === false) {
    APIError::upload($uploadResult['error'], true);
}
```

---

#### `APIError::permission($message = '...', $jsonResponse = false)`
**Purpose:** Handle permission errors  
**Parameters:**
- `$message` (string): Error message
- `$jsonResponse` (bool): Return JSON response

**Returns:** void (exits)

**Analysis:**
- Logs permission denial with admin context
- Returns JSON error or redirects
- Used for authorization failures

**Usage:**
```php
if (!canPublish()) {
    APIError::permission('You cannot publish content', true);
}
```

**Security:**
- ✅ Logs permission denials
- ✅ Includes admin ID and role in log
- ✅ Returns 403 status code

---

### Class: ErrorMessages

**Purpose:** User-friendly error message constants  
**Constants:**
- `GENERIC`: 'An error occurred. Please try again.'
- `DATABASE`: 'Database error. Please contact the administrator.'
- `VALIDATION`: 'Please check your input and try again.'
- `UPLOAD`: 'File upload failed. Please check the file and try again.'
- `PERMISSION`: 'You do not have permission to perform this action.'
- `NOT_FOUND`: 'The requested resource was not found.'
- `SESSION_EXPIRED`: 'Your session has expired. Please login again.'
- `CSRF_INVALID`: 'Invalid security token. Please refresh the page and try again.'

**Usage:**
```php
echo ErrorMessages::DATABASE;
```

---

## Configuration & Constants

### Database Constants
- `DB_HOST`: Database server hostname (default: 'localhost')
- `DB_NAME`: Database name (default: 'icdi_db')
- `DB_USER`: Database username (default: 'root')
- `DB_PASS`: Database password (default: '')

### Site Constants
- `SITE_NAME`: Application name (default: 'PROWLWAY')
- `SITE_DESCRIPTION`: Application description (default: 'ICDISG Archive Website')

### URL Constants (Auto-detected)
- `BASE_URL`: Base URL of application (e.g., '/prowlway/ICDI')
- `ASSETS_URL`: URL to assets directory (BASE_URL + '/assets')
- `PUBLIC_URL`: URL to public directory (BASE_URL + '/public')
- `ADMIN_URL`: URL to admin directory (BASE_URL + '/admin')

### Path Constants
- `BASE_PATH`: Absolute path to project root
- `ASSETS_PATH`: Absolute path to assets directory
- `UPLOADS_PATH`: Absolute path to uploads directory

### File Upload Constants
- `ALLOWED_IMAGE_TYPES`: ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp']
- `ALLOWED_DOC_TYPES`: ['application/pdf']
- `MAX_IMAGE_SIZE`: 5 * 1024 * 1024 (5MB)
- `MAX_DOC_SIZE`: 50 * 1024 * 1024 (50MB)

### Session Security Configuration
- `session.cookie_httponly`: 1 (prevents JavaScript access)
- `session.use_strict_mode`: 1 (prevents session fixation)
- `session.cookie_samesite`: 'Strict' (prevents CSRF)
- `session.cookie_secure`: 1 (if HTTPS enabled)
- `session.gc_maxlifetime`: 7200 (2 hours)
- `session.cookie_lifetime`: 7200 (2 hours)

---

## Function Dependencies Map

### Database Layer
```
Database (Singleton)
├── getDB()
│   ├── dbQuery()
│   │   ├── dbFetchAll()
│   │   └── dbFetchOne()
│   ├── dbInsert()
│   ├── dbUpdate()
│   └── dbDelete()
└── getLastDbError()
```

### Authentication Layer
```
Session Management
├── getAdminRole()
│   ├── canPublish()
│   ├── isSuperAdmin()
│   └── isEditor()
├── requireAdmin()
│   └── requireSuperAdmin()
├── normalizeStatusByRole()
├── generateCSRFToken()
├── validateCSRFToken()
└── requireCSRFToken()
```

### File Upload Layer
```
uploadImage()
├── validateFileUpload() [validation.php]
├── optimizeImage()
│   └── GD Extension
└── generateThumbnail()
    └── GD Extension

uploadDocument()
└── validateFileUpload() [validation.php]

uploadMultipleImages()
└── uploadImage()

deleteUploadedFile()
getImageUrl()
├── getThumbnailPath()
└── image.php endpoint

getDocumentUrl()
└── download.php endpoint
```

### Validation Layer
```
validateEmail()
validateDate()
validateInteger()
validateAcademicYear()
validateUrl()
validateFileUpload()
validatePasswordStrength()
sanitizeString()
sanitizeHtml()
```

### Error Handling Layer
```
APIError::json()
APIError::log()
APIError::database()
├── getLastDbError()
└── error_log()
APIError::validation()
APIError::upload()
APIError::permission()
```

### Audit Logging
```
auditLog()
└── dbInsert('audit_log', ...)
```

---

## Security Analysis

### Strengths

1. **SQL Injection Prevention**
   - ✅ All queries use prepared statements
   - ✅ Parameter binding for all values
   - ✅ Table names escaped with backticks
   - ✅ No string concatenation in queries

2. **XSS Prevention**
   - ✅ htmlspecialchars() for output
   - ✅ strip_tags() for input sanitization
   - ✅ ENT_QUOTES flag for quotes
   - ✅ UTF-8 encoding

3. **CSRF Protection**
   - ✅ CSRF tokens on all POST requests
   - ✅ Timing-safe comparison (hash_equals)
   - ✅ Token stored in session
   - ✅ Cryptographically secure token generation

4. **File Upload Security**
   - ✅ MIME type validation (not just extension)
   - ✅ File size limits
   - ✅ Unique filename generation
   - ✅ Secure file paths
   - ✅ No executable files allowed

5. **Session Security**
   - ✅ HttpOnly cookies
   - ✅ Strict mode (prevents fixation)
   - ✅ SameSite cookies
   - ✅ Secure flag (HTTPS)
   - ✅ Session timeout (2 hours)

6. **Authentication**
   - ✅ Bcrypt password hashing
   - ✅ Role-based access control
   - ✅ Session timeout handling
   - ✅ Last activity tracking

7. **Audit Logging**
   - ✅ Comprehensive action logging
   - ✅ IP address tracking
   - ✅ User agent tracking
   - ✅ Admin ID tracking

### Weaknesses & Recommendations

1. **Error Handling**
   - ⚠️ Database errors exposed to admins (could be security risk)
   - **Recommendation:** Hide detailed errors in production, show generic messages

2. **File Upload**
   - ⚠️ Thumbnails not deleted when main image deleted
   - **Recommendation:** Auto-delete thumbnails in deleteUploadedFile()

3. **HTML Sanitization**
   - ⚠️ strip_tags() doesn't validate attributes
   - **Recommendation:** Use HTMLPurifier for robust sanitization

4. **Password Strength**
   - ⚠️ No special character requirement
   - **Recommendation:** Add special character requirement

5. **WHERE Clause Construction**
   - ⚠️ dbUpdate() and dbDelete() require manual WHERE clause construction
   - **Recommendation:** Add helper function for safe WHERE clause building

6. **Global Error Variable**
   - ⚠️ Uses global $lastDbError variable
   - **Recommendation:** Use exception handling instead

7. **File Type Validation**
   - ⚠️ Documents only support PDF (docs mention DOC, DOCX, XLS, XLSX)
   - **Recommendation:** Add support for additional document types

8. **Image Optimization**
   - ⚠️ WebP conversion creates new file but doesn't replace original
   - **Recommendation:** Add option to replace original with WebP

---

## Performance Considerations

### Database

1. **Connection Pooling**
   - ✅ Singleton pattern reduces connection overhead
   - ✅ Single connection reused throughout request

2. **Query Optimization**
   - ✅ Prepared statements cached by MySQL
   - ✅ No N+1 query problems (queries are explicit)

3. **Index Usage**
   - ⚠️ No explicit index hints in queries
   - **Recommendation:** Ensure database indexes are properly set

### File Operations

1. **Image Processing**
   - ⚠️ GD library operations can be memory-intensive
   - **Recommendation:** Consider using Imagick for better performance

2. **Thumbnail Generation**
   - ✅ Thumbnails generated once and cached
   - ✅ Reduces bandwidth for image display

3. **File Size Limits**
   - ✅ Reasonable limits (5MB images, 50MB documents)
   - ✅ Prevents memory exhaustion

### Session Management

1. **Session Storage**
   - ⚠️ Default file-based sessions (could be slow on high traffic)
   - **Recommendation:** Consider Redis/Memcached for session storage

2. **Session Timeout**
   - ✅ 2-hour timeout balances security and usability
   - ✅ Last activity tracking prevents premature timeouts

---

## Recommendations & Improvements

### High Priority

1. **Exception Handling**
   - Replace global error variables with exceptions
   - Implement try-catch blocks consistently
   - Create custom exception classes

2. **Error Messages in Production**
   - Hide detailed database errors in production
   - Show generic error messages to users
   - Log detailed errors server-side only

3. **Thumbnail Cleanup**
   - Auto-delete thumbnails when main image deleted
   - Add cleanup function for orphaned thumbnails

4. **HTML Sanitization**
   - Implement HTMLPurifier for robust sanitization
   - Validate HTML attributes
   - Whitelist allowed attributes

### Medium Priority

1. **Document Type Support**
   - Add support for DOC, DOCX, XLS, XLSX
   - Update validation functions
   - Update upload functions

2. **Password Strength**
   - Add special character requirement
   - Add password history (prevent reuse)
   - Add password expiration

3. **WHERE Clause Builder**
   - Create helper function for safe WHERE clause construction
   - Support multiple conditions
   - Support operators (AND, OR)

4. **Image Optimization**
   - Add option to replace original with WebP
   - Implement progressive JPEG loading
   - Add lazy loading support

### Low Priority

1. **Caching**
   - Implement query result caching
   - Cache frequently accessed data
   - Use Redis/Memcached

2. **Logging**
   - Implement structured logging
   - Add log rotation
   - Add log levels (DEBUG, INFO, WARN, ERROR)

3. **Testing**
   - Add unit tests for functions
   - Add integration tests for handlers
   - Add PHPUnit test suite

4. **Documentation**
   - Add PHPDoc comments to all functions
   - Generate API documentation
   - Add code examples

---

## Conclusion

The ICDI project demonstrates a well-structured PHP application with comprehensive functionality for content management. The codebase follows security best practices with prepared statements, CSRF protection, and proper input validation. The function architecture is modular and reusable, with clear separation of concerns.

**Key Strengths:**
- Strong security implementation
- Comprehensive error handling
- Well-organized function structure
- Good documentation

**Areas for Improvement:**
- Exception handling instead of global variables
- Enhanced HTML sanitization
- Better error message handling in production
- Additional document type support

Overall, the function implementation is solid and production-ready with minor improvements recommended for enhanced security and maintainability.

---

**Document Version:** 1.0.0  
**Last Updated:** January 31, 2026  
**Author:** AI Analysis  
**Status:** Complete
