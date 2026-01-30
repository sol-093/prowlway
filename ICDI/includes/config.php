<?php
/**
 * Configuration File
 * 
 * Central configuration file for PROWLWAY application.
 * Contains database credentials, site settings, URL paths, file paths,
 * and session security configuration.
 * 
 * @package PROWLWAY
 * @subpackage Includes
 * @author ICDISG Development Team
 * @since 1.0.0
 */

/**
 * Database Configuration
 * 
 * @var string DB_HOST Database server hostname
 * @var string DB_NAME Database name
 * @var string DB_USER Database username
 * @var string DB_PASS Database password
 */
define('DB_HOST', 'localhost');
define('DB_NAME', 'icdi_db');
define('DB_USER', 'root');
define('DB_PASS', '');

/**
 * Site Configuration
 * 
 * @var string SITE_NAME Application name
 * @var string SITE_DESCRIPTION Application description
 */
define('SITE_NAME', 'PROWLWAY');
define('SITE_DESCRIPTION', 'ICDISG Archive Website');

// Base URL - automatically detects project directory for XAMPP
// Simple method: use SCRIPT_NAME to determine base path
$scriptName = $_SERVER['SCRIPT_NAME'];
$scriptDir = dirname($scriptName);

// Determine base URL based on current script location
if (strpos($scriptName, '/includes/') !== false) {
    // If called from includes/, go up one level
    $baseUrl = dirname($scriptDir);
} elseif (strpos($scriptName, '/public/') !== false) {
    // If called from public/, go up one level
    $baseUrl = dirname($scriptDir);
} elseif (strpos($scriptName, '/admin/') !== false) {
    // If called from admin/, go up one level
    $baseUrl = dirname($scriptDir);
} else {
    // Root level (index.php or test files)
    $baseUrl = $scriptDir;
}

// Clean up the path
$baseUrl = str_replace('\\', '/', $baseUrl);
$baseUrl = ($baseUrl === '/' || $baseUrl === '.') ? '' : rtrim($baseUrl, '/');

// If still empty or just '/', try DOCUMENT_ROOT method
if (empty($baseUrl) || $baseUrl === '/') {
    $docRoot = str_replace('\\', '/', $_SERVER['DOCUMENT_ROOT']);
    $currentFile = str_replace('\\', '/', __FILE__);
    
    if (strpos($currentFile, $docRoot) === 0) {
        // Get project root (ICDI folder) - two levels up from includes/
        $projectRoot = dirname(dirname($currentFile));
        $baseUrl = str_replace($docRoot, '', $projectRoot);
        $baseUrl = str_replace('\\', '/', $baseUrl);
        $baseUrl = ($baseUrl === '' || $baseUrl === '/') ? '' : rtrim($baseUrl, '/');
    }
}

// Final fallback for XAMPP: detect from actual file path
if (empty($baseUrl) || $baseUrl === '/') {
    $docRoot = str_replace('\\', '/', $_SERVER['DOCUMENT_ROOT']);
    $currentFile = str_replace('\\', '/', __FILE__);
    
    if (strpos($currentFile, $docRoot) === 0) {
        // Get project root (ICDI folder) - two levels up from includes/
        $projectRoot = dirname(dirname($currentFile));
        $baseUrl = str_replace($docRoot, '', $projectRoot);
        $baseUrl = str_replace('\\', '/', $baseUrl);
        $baseUrl = ($baseUrl === '' || $baseUrl === '/') ? '' : rtrim($baseUrl, '/');
    }
    
    // If still empty, try to detect from SCRIPT_NAME
    if (empty($baseUrl) || $baseUrl === '/') {
        $scriptName = str_replace('\\', '/', $_SERVER['SCRIPT_NAME']);
        if (preg_match('~/([^/]+/ICDI)~', $scriptName, $matches)) {
            $baseUrl = '/' . $matches[1];
        } elseif (preg_match('~/(ICDI)~', $scriptName, $matches)) {
            $baseUrl = '/' . $matches[1];
        }
    }
}

/**
 * URL Paths
 * 
 * Automatically detected base URL paths for the application.
 * These are used for generating links, asset URLs, and redirects.
 * 
 * @var string BASE_URL Base URL of the application (e.g., '/prowlway/ICDI')
 * @var string ASSETS_URL URL to assets directory (BASE_URL + '/assets')
 * @var string PUBLIC_URL URL to public directory (BASE_URL + '/public')
 * @var string ADMIN_URL URL to admin directory (BASE_URL + '/admin')
 */
define('BASE_URL', $baseUrl);
define('ASSETS_URL', BASE_URL . '/assets');
define('PUBLIC_URL', BASE_URL . '/public');
define('ADMIN_URL', BASE_URL . '/admin');

/**
 * File System Paths
 * 
 * Absolute file system paths for file operations.
 * These paths are used for file uploads, includes, and file system access.
 * 
 * @var string BASE_PATH Absolute path to project root directory
 * @var string ASSETS_PATH Absolute path to assets directory
 * @var string UPLOADS_PATH Absolute path to uploads directory
 */
define('BASE_PATH', dirname(__DIR__));
define('ASSETS_PATH', BASE_PATH . '/assets');
define('UPLOADS_PATH', BASE_PATH . '/uploads');

/**
 * Session Security Configuration
 * 
 * Configures secure session settings to prevent session hijacking and fixation attacks.
 * Settings include:
 * - HttpOnly cookies (prevents JavaScript access)
 * - Strict mode (prevents session fixation)
 * - SameSite cookies (prevents CSRF attacks)
 * - Secure flag (HTTPS only, if available)
 * - Session timeout (2 hours)
 * 
 * These settings are only applied if no session is currently active.
 */
if (session_status() === PHP_SESSION_NONE) {
    // Secure session configuration
    ini_set('session.cookie_httponly', 1); // Prevent JavaScript access to session cookie
    ini_set('session.use_strict_mode', 1); // Prevent session fixation attacks
    ini_set('session.cookie_samesite', 'Strict'); // Prevent CSRF attacks
    
    // Only set secure flag if HTTPS is enabled
    if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') {
        ini_set('session.cookie_secure', 1); // Only send cookie over HTTPS
    }
    
    // Session timeout: 2 hours (7200 seconds)
    ini_set('session.gc_maxlifetime', 7200); // Server-side session lifetime
    ini_set('session.cookie_lifetime', 7200); // Client-side cookie lifetime
}

// No Node.js API - PHP only
