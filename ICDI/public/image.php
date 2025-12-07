<?php
/**
 * Image Serving Endpoint
 * Serves uploaded images securely
 */

require_once '../includes/config.php';

// Get image path from query parameter
$imagePath = $_GET['path'] ?? '';

if (empty($imagePath)) {
    http_response_code(404);
    die('Image not found');
}

// Security: Prevent directory traversal
$imagePath = str_replace(['../', '..\\'], '', $imagePath);
$imagePath = ltrim($imagePath, '/\\');

// Full file path
$fullPath = UPLOADS_PATH . '/' . $imagePath;

// Check if file exists
if (!file_exists($fullPath) || !is_file($fullPath)) {
    http_response_code(404);
    die('Image not found');
}

// Check if it's an image file
$allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
$extension = strtolower(pathinfo($fullPath, PATHINFO_EXTENSION));

if (!in_array($extension, $allowedExtensions)) {
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

// Set headers
header('Content-Type: ' . $mimeType);
header('Content-Length: ' . filesize($fullPath));
header('Cache-Control: public, max-age=31536000'); // Cache for 1 year
header('Expires: ' . gmdate('D, d M Y H:i:s', time() + 31536000) . ' GMT');

// Output image
readfile($fullPath);
exit;

?>

