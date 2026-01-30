<?php
/**
 * Role-based access control helpers for PROWLWAY admin.
 * Roles: editor (draft only), admin (approve/publish/archive), super_admin (full + config/users/audit).
 */

if (!function_exists('getAdminRole')) {
    function getAdminRole() {
        return $_SESSION['admin_role'] ?? null;
    }
}

/**
 * Check if current user can publish/archive/approve content (admin or super_admin).
 */
function canPublish() {
    $role = getAdminRole();
    return in_array($role, ['admin', 'super_admin'], true);
}

/**
 * Check if current user is Super Administrator.
 */
function isSuperAdmin() {
    return getAdminRole() === 'super_admin';
}

/**
 * Check if current user is Editor (draft only).
 */
function isEditor() {
    return getAdminRole() === 'editor';
}

/**
 * Require admin to be logged in. Optionally require one of the given roles.
 * @param array|null $allowedRoles e.g. ['admin','super_admin']. If null, any logged-in admin is OK.
 * @param bool $jsonResponse If true, output JSON and exit; otherwise redirect.
 */
function requireAdmin($allowedRoles = null, $jsonResponse = false) {
    // Check session timeout (2 hours)
    if (isset($_SESSION['last_activity'])) {
        $timeout = 7200; // 2 hours in seconds
        if (time() - $_SESSION['last_activity'] > $timeout) {
            // Session expired
            $_SESSION = array();
            session_destroy();
            if ($jsonResponse) {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'error' => 'Session expired. Please login again.']);
                exit;
            }
            header('Location: ' . (defined('ADMIN_URL') ? ADMIN_URL : '') . '/index.php');
            exit;
        }
    }
    
    // Update last activity time
    $_SESSION['last_activity'] = time();
    
    if (!isset($_SESSION['admin_logged_in']) || !$_SESSION['admin_logged_in']) {
        if ($jsonResponse) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'error' => 'Unauthorized']);
            exit;
        }
        header('Location: ' . (defined('ADMIN_URL') ? ADMIN_URL : '') . '/index.php');
        exit;
    }
    if ($allowedRoles !== null) {
        $role = getAdminRole();
        if (!in_array($role, $allowedRoles, true)) {
            if ($jsonResponse) {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'error' => 'Forbidden: insufficient role']);
                exit;
            }
            header('Location: ' . (defined('ADMIN_URL') ? ADMIN_URL : '') . '/index.php');
            exit;
        }
    }
}

/**
 * Require Super Administrator (for settings, user management, audit).
 */
function requireSuperAdmin($jsonResponse = false) {
    requireAdmin(['super_admin'], $jsonResponse);
}

/**
 * Normalize status for current role: Editors can only use 'draft' or 'pending_review'.
 * Returns the status to use (draft if editor tried to set a restricted status).
 * @param string $requestedStatus
 * @param array $restrictedStatuses Statuses only admin/super_admin can set (default: pending_review, approved, published, archived). For org/batch use ['active','archived'].
 */
function normalizeStatusByRole($requestedStatus, $restrictedStatuses = ['pending_review', 'approved', 'published', 'archived']) {
    if (canPublish()) {
        return $requestedStatus;
    }
    // Editors can only set draft or pending_review
    if (in_array($requestedStatus, $restrictedStatuses, true)) {
        // Allow editors to submit for review
        if ($requestedStatus === 'pending_review') {
            return 'pending_review';
        }
        return 'draft';
    }
    return $requestedStatus;
}

/**
 * Check if current user can review content (admin or super_admin).
 */
function canReview() {
    return canPublish(); // Same as canPublish for now
}

/**
 * Check if current user can approve content (admin or super_admin).
 */
function canApprove() {
    return canPublish(); // Same as canPublish for now
}

/**
 * Generate CSRF token and store in session.
 * @return string CSRF token
 */
function generateCSRFToken() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Validate CSRF token.
 * @param string $token Token to validate
 * @return bool True if valid, false otherwise
 */
function validateCSRFToken($token) {
    if (!isset($_SESSION['csrf_token'])) {
        return false;
    }
    return hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Require valid CSRF token for POST requests.
 * @param bool $jsonResponse If true, output JSON and exit; otherwise redirect.
 */
function requireCSRFToken($jsonResponse = false) {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        return; // Only check POST requests
    }
    
    $token = $_POST['csrf_token'] ?? '';
    if (!validateCSRFToken($token)) {
        if ($jsonResponse) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'error' => 'Invalid CSRF token. Please refresh the page and try again.']);
            exit;
        }
        $_SESSION['csrf_error'] = 'Invalid security token. Please try again.';
        header('Location: ' . (defined('ADMIN_URL') ? ADMIN_URL : '') . '/index.php');
        exit;
    }
}

/**
 * Log an action to audit_log. Call after config and database are loaded.
 */
function auditLog($action, $details = '', $entityType = null, $entityId = null) {
    if (!function_exists('dbInsert')) {
        return;
    }
    $adminId = $_SESSION['admin_id'] ?? null;
    $detailsStr = is_array($details) ? json_encode($details) : (string) $details;
    try {
        dbInsert('audit_log', [
            'admin_id' => $adminId,
            'action' => $action,
            'entity_type' => $entityType,
            'entity_id' => $entityId,
            'details' => $detailsStr,
            'ip_address' => $_SERVER['REMOTE_ADDR'] ?? '',
            'user_agent' => substr($_SERVER['HTTP_USER_AGENT'] ?? '', 0, 500),
        ]);
    } catch (Exception $e) {
        error_log('Audit log failed: ' . $e->getMessage());
    }
}
