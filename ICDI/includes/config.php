<?php
// Database Configuration
define('DB_HOST', 'localhost');
define('DB_NAME', 'icdi_db');
define('DB_USER', 'root');
define('DB_PASS', '');

// Site Configuration
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

define('BASE_URL', $baseUrl);
define('ASSETS_URL', BASE_URL . '/assets');
define('PUBLIC_URL', BASE_URL . '/public');
define('ADMIN_URL', BASE_URL . '/admin');

// File Paths
define('BASE_PATH', dirname(__DIR__));
define('ASSETS_PATH', BASE_PATH . '/assets');
define('UPLOADS_PATH', BASE_PATH . '/uploads');

// No Node.js API - PHP only
