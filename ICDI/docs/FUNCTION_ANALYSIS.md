PROWLWAY (ICDI) - Function Analysis
Last Updated: February 3, 2026

---

This document lists the key reusable functions/classes in the `ICDI/includes/` layer and explains what they do and where they are used.

## Core Architecture Notes

- Public pages live in `ICDI/public/` and include `../includes/config.php`, `database.php`, and sometimes `upload.php`.
- Admin pages live in `ICDI/admin/` and use `../includes/auth.php` for role checks and CSRF protection.
- Uploaded media is stored in `ICDI/uploads/` and served via the public `image.php` endpoint through `getImageUrl()`.

---

## `ICDI/includes/config.php`

- **BASE_URL / PUBLIC_URL / ADMIN_URL / ASSETS_URL**: URL constants used to build links reliably under `http://localhost/...`.
- **BASE_PATH / UPLOADS_PATH / ASSETS_PATH**: filesystem constants used for upload/delete/file checks.
- **Session security defaults**: secure cookie settings (HttpOnly/SameSite) when a session is started.

---

## `ICDI/includes/database.php`

### Class
- **`Database`**: PDO singleton connection (MySQL, utf8mb4). Provides `Database::getInstance()->getConnection()`.

### Helpers
- **`getDB()`**: returns PDO connection or `null` (logs errors).
- **`dbQuery($sql, $params = [])`**: prepares + executes a statement safely; returns `PDOStatement|false`.
- **`dbFetchAll($sql, $params = [])`**: returns all rows as array; returns `[]` on failure.
- **`dbFetchOne($sql, $params = [])`**: returns a single row as array or `null`.
- **`dbInsert($table, $data)`**: inserts row and returns last insert id or `false`.
- **`dbUpdate($table, $data, $where, $whereParams)`**: updates rows; returns affected rows / bool (implementation below in file).
- **`dbDelete($table, $where, $whereParams)`**: deletes rows.
- **`getLastDbError()`**: returns last stored DB error message (used by error handler).

Used across **public** and **admin** pages for all content (announcements, events, documents, institute info, organizations, batches, etc.).

---

## `ICDI/includes/auth.php`

### Roles
- **editor**: draft / submit for review
- **admin**: publish/archive/approve/review
- **super_admin**: full access (users/settings/audit)

### Helpers
- **`getAdminRole()`**: returns role from session.
- **`canPublish()` / `canReview()` / `canApprove()`**: capability checks.
- **`isSuperAdmin()` / `isEditor()`**: convenience role checks.
- **`requireAdmin($allowedRoles = null, $jsonResponse = false)`**: enforces login (+ optional role restriction). Also enforces session timeout.
- **`requireSuperAdmin($jsonResponse = false)`**: super-admin-only gate.
- **`normalizeStatusByRole($requestedStatus, $restrictedStatuses = [...])`**: prevents editors from setting restricted statuses.
- **CSRF**
  - **`generateCSRFToken()`**
  - **`validateCSRFToken($token)`**
  - **`requireCSRFToken($jsonResponse = false)`**
- **Auditing**
  - **`auditLog($action, $details = '', $entityType = null, $entityId = null)`**: writes to `audit_log`.

Used by admin pages like `admin/institute.php`, `admin/documents.php`, `admin/review_queue.php`, etc.

---

## `ICDI/includes/upload.php`

### Upload + Media Handling
- **`uploadImage($file, $subfolder = 'images', $generateThumbnail = true, $optimize = true)`**
  - validates MIME + size, saves under `uploads/images/`, optionally generates thumbnail + optimizes via GD.
- **`uploadDocument($file)`**
  - validates PDF, saves under `uploads/documents/`.
- **`uploadMultipleImages($files, $subfolder = 'images')`**
  - batch upload helper.
- **`deleteUploadedFile($filepath)`**
  - deletes a stored upload by relative path.
- **`getImageUrl($filepath, $useThumbnail = false)`**
  - returns URL using `PUBLIC_URL . '/image.php?path=...'` to serve uploads consistently.

---

## `ICDI/includes/validation.php`

- **`validateEmail($email)`**
- **`sanitizeString($input, $maxLength = null)`**
- **`validateAcademicYear($year)`** (format `YYYY-YYYY`)
- **`validateDate($date, $format = 'Y-m-d')`**
- **`validateInteger($value, $min = null, $max = null)`**
- **`validateFileUpload($file, $allowedTypes = [], $maxSize = 5242880)`**
- **`validateUrl($url)`**
- **`sanitizeHtml($html, $allowedTags = [...])`**
- **`validatePasswordStrength($password)`**

---

## `ICDI/includes/errors.php`

### Classes
- **`APIError::json($message, $code = 400, $additionalData = [])`**: standard JSON error response + exit.
- **`APIError::log($message, $context = [], $level = 'error')`**: structured server-side logging with request context.
- **`APIError::database($e, $jsonResponse = false)`**: DB error handler (uses `getLastDbError()` when available).
- **`APIError::validation($errors, $jsonResponse = false)`**
- **`APIError::upload($message, $jsonResponse = false)`**
- **`APIError::permission($message = ..., $jsonResponse = false)`**

- **`ErrorMessages`**: constants for user-friendly default messages.

---

## Public “helper functions” implemented inside pages

Some public pages define small, page-scoped helpers (not shared includes), e.g.:

- **`renderInstituteInfoContent()`** in `public/institute.php`
  - renders institute content safely and converts `1. ...` lines into an `<ol>` numbered list.

If you want these to be reusable across pages, they can be moved into `includes/` later.


