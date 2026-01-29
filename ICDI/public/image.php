<?php
/**
 * Image Serving Endpoint
 * Serves uploaded images securely
 */

// Start output buffering immediately
ob_start();

// Suppress display of errors (but log them)
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

require_once '../includes/config.php';

// Get image path from query parameter
$imagePath = $_GET['path'] ?? '';

if (empty($imagePath)) {
    ob_end_clean();
    http_response_code(404);
    die('Image not found');
}

// Decode in case it was URL-encoded
$imagePath = urldecode($imagePath);

// Security: Prevent directory traversal
$imagePath = str_replace(['../', '..\\'], '', $imagePath);
$imagePath = ltrim($imagePath, '/\\');

// Normalize various possible stored formats to a path relative to the uploads/ directory.
if (preg_match('~uploads[\\\\/](.*)$~i', $imagePath, $m)) {
    $imagePath = $m[1];
}

// Ensure we have a valid path
if (empty($imagePath)) {
    ob_end_clean();
    http_response_code(404);
    die('Image not found');
}

// Build absolute path to uploads directory
$uploadsDir1 = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'uploads';
$uploadsDir2 = UPLOADS_PATH;
$uploadsDir = file_exists($uploadsDir1) ? $uploadsDir1 : $uploadsDir2;

// Normalize the image path
$normalizedPath = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $imagePath);
$fullPath = $uploadsDir . DIRECTORY_SEPARATOR . $normalizedPath;

// Check if file exists
if (!file_exists($fullPath) || !is_file($fullPath)) {
    error_log("Image not found - Path: " . $fullPath);
    error_log("Uploads dir: " . $uploadsDir);
    error_log("Image path from DB: " . ($_GET['path'] ?? 'N/A'));
    ob_end_clean();
    http_response_code(404);
    die('Image not found');
}

// Security: Ensure the resolved path is within uploads directory
$fullPathReal = realpath($fullPath);
$uploadsDirReal = realpath($uploadsDir);

if (!$fullPathReal || !$uploadsDirReal) {
    error_log("Realpath failed - fullPath: " . $fullPath . ", uploadsDir: " . $uploadsDir);
    ob_end_clean();
    http_response_code(403);
    die('Access denied');
}

// Use case-insensitive comparison for Windows compatibility
$fullPathRealLower = strtolower(str_replace('\\', '/', $fullPathReal));
$uploadsDirRealLower = strtolower(str_replace('\\', '/', $uploadsDirReal));

if (strpos($fullPathRealLower, $uploadsDirRealLower) !== 0) {
    error_log("Path outside uploads directory - fullPath: " . $fullPathReal . ", uploadsDir: " . $uploadsDirReal);
    ob_end_clean();
    http_response_code(403);
    die('Access denied');
}

// Use the real path for file operations
$fullPath = $fullPathReal;

// Check if it's an image file
$allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
$extension = strtolower(pathinfo($fullPath, PATHINFO_EXTENSION));

if (!in_array($extension, $allowedExtensions)) {
    ob_end_clean();
    http_response_code(403);
    die('Forbidden file type');
}

// Get MIME type
$mimeTypes = [
    'jpg' => 'image/jpeg',
    'jpeg' => 'image/jpeg',
    'png' => 'image/png',
    'gif' => 'image/gif',
    'webp' => 'image/webp'
];

$mimeType = $mimeTypes[$extension] ?? 'image/jpeg';

// Clear ALL output buffers before sending headers
while (ob_get_level()) {
    ob_end_clean();
}

// Set headers
header('Content-Type: ' . $mimeType);
header('Content-Length: ' . filesize($fullPath));
header('Cache-Control: public, max-age=31536000');
header('Expires: ' . gmdate('D, d M Y H:i:s', time() + 31536000) . ' GMT');

// Output image
$bytesRead = readfile($fullPath);
if ($bytesRead === false) {
    error_log("Failed to read file: " . $fullPath);
    http_response_code(500);
    die('Error reading image file');
}

exit;
