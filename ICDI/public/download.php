<?php
/**
 * Document Download Endpoint
 * Serves uploaded documents securely
 */

require_once '../includes/config.php';

// Get document path from query parameter
$docPath = $_GET['path'] ?? '';

if (empty($docPath)) {
    http_response_code(404);
    die('Document not found');
}

// Security: Prevent directory traversal
$docPath = str_replace(['../', '..\\'], '', $docPath);
$docPath = ltrim($docPath, '/\\');

// Full file path
$fullPath = UPLOADS_PATH . '/' . $docPath;

// Check if file exists
if (!file_exists($fullPath) || !is_file($fullPath)) {
    http_response_code(404);
    die('Document not found');
}

// Check if it's a PDF file
$extension = strtolower(pathinfo($fullPath, PATHINFO_EXTENSION));

if ($extension !== 'pdf') {
    http_response_code(403);
    die('Forbidden file type');
}

// Get filename for download
$filename = basename($fullPath);

// Set headers for download
header('Content-Type: application/pdf');
header('Content-Disposition: inline; filename="' . $filename . '"');
header('Content-Length: ' . filesize($fullPath));
header('Cache-Control: public, max-age=3600');

// Output file
readfile($fullPath);
exit;

?>

