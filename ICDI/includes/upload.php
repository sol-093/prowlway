<?php
/**
 * File Upload Handler
 */

require_once __DIR__ . '/config.php';

// Allowed image types
define('ALLOWED_IMAGE_TYPES', ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp']);
define('ALLOWED_DOC_TYPES', ['application/pdf']);

// Max file sizes (in bytes)
define('MAX_IMAGE_SIZE', 5 * 1024 * 1024); // 5MB
define('MAX_DOC_SIZE', 50 * 1024 * 1024); // 50MB

/**
 * Upload an image file
 * @param array $file $_FILES array element
 * @param string $subfolder Subfolder within uploads (e.g., 'images', 'documents')
 * @return array ['success' => bool, 'path' => string, 'error' => string]
 */
function uploadImage($file, $subfolder = 'images') {
    if (!isset($file) || $file['error'] !== UPLOAD_ERR_OK) {
        return ['success' => false, 'error' => 'No file uploaded or upload error'];
    }
    
    // Check file size
    if ($file['size'] > MAX_IMAGE_SIZE) {
        return ['success' => false, 'error' => 'File size exceeds 5MB limit'];
    }
    
    // Check file type
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mimeType = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);
    
    if (!in_array($mimeType, ALLOWED_IMAGE_TYPES)) {
        return ['success' => false, 'error' => 'Invalid file type. Only JPEG, PNG, GIF, and WebP are allowed'];
    }
    
    // Create upload directory if it doesn't exist
    $uploadDir = UPLOADS_PATH . '/' . $subfolder;
    if (!file_exists($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }
    
    // Generate unique filename
    $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = uniqid('img_', true) . '_' . time() . '.' . $extension;
    $filepath = $uploadDir . '/' . $filename;
    
    // Move uploaded file
    if (move_uploaded_file($file['tmp_name'], $filepath)) {
        // Return relative path for database storage
        $relativePath = $subfolder . '/' . $filename;
        return ['success' => true, 'path' => $relativePath, 'full_path' => $filepath];
    } else {
        return ['success' => false, 'error' => 'Failed to move uploaded file'];
    }
}

/**
 * Upload a document file (PDF)
 * @param array $file $_FILES array element
 * @return array ['success' => bool, 'path' => string, 'error' => string]
 */
function uploadDocument($file) {
    if (!isset($file) || $file['error'] !== UPLOAD_ERR_OK) {
        return ['success' => false, 'error' => 'No file uploaded or upload error'];
    }
    
    // Check file size
    if ($file['size'] > MAX_DOC_SIZE) {
        return ['success' => false, 'error' => 'File size exceeds 50MB limit'];
    }
    
    // Check file type
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mimeType = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);
    
    if (!in_array($mimeType, ALLOWED_DOC_TYPES)) {
        return ['success' => false, 'error' => 'Invalid file type. Only PDF files are allowed'];
    }
    
    // Create upload directory if it doesn't exist
    $uploadDir = UPLOADS_PATH . '/documents';
    if (!file_exists($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }
    
    // Generate unique filename
    $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = uniqid('doc_', true) . '_' . time() . '.' . $extension;
    $filepath = $uploadDir . '/' . $filename;
    
    // Move uploaded file
    if (move_uploaded_file($file['tmp_name'], $filepath)) {
        // Return relative path for database storage
        $relativePath = 'documents/' . $filename;
        return [
            'success' => true, 
            'path' => $relativePath, 
            'full_path' => $filepath,
            'size' => $file['size'],
            'type' => $mimeType
        ];
    } else {
        return ['success' => false, 'error' => 'Failed to move uploaded file'];
    }
}

/**
 * Upload multiple images (for gallery)
 * @param array $files $_FILES array
 * @return array Array of upload results
 */
function uploadMultipleImages($files, $subfolder = 'images') {
    $results = [];
    
    if (!is_array($files['name'])) {
        // Single file
        return [uploadImage($files, $subfolder)];
    }
    
    // Multiple files
    $count = count($files['name']);
    for ($i = 0; $i < $count; $i++) {
        if ($files['error'][$i] === UPLOAD_ERR_OK) {
            $file = [
                'name' => $files['name'][$i],
                'type' => $files['type'][$i],
                'tmp_name' => $files['tmp_name'][$i],
                'error' => $files['error'][$i],
                'size' => $files['size'][$i]
            ];
            $results[] = uploadImage($file, $subfolder);
        }
    }
    
    return $results;
}

/**
 * Delete an uploaded file
 * @param string $filepath Relative path from uploads directory
 * @return bool Success status
 */
function deleteUploadedFile($filepath) {
    if (empty($filepath)) {
        return false;
    }
    
    $fullPath = UPLOADS_PATH . '/' . $filepath;
    
    if (file_exists($fullPath) && is_file($fullPath)) {
        return unlink($fullPath);
    }
    
    return false;
}

/**
 * Get image URL for display
 * @param string $filepath Relative path stored in database
 * @return string Full URL to the image
 */
function getImageUrl($filepath) {
    if (empty($filepath)) {
        return '';
    }
    
    // If it's already a full URL, return as is
    if (filter_var($filepath, FILTER_VALIDATE_URL)) {
        return $filepath;
    }
    
    // Return relative URL for serving
    return BASE_URL . '/uploads/' . $filepath;
}

/**
 * Get document URL for download
 * @param string $filepath Relative path stored in database
 * @return string Full URL to the document
 */
function getDocumentUrl($filepath) {
    if (empty($filepath)) {
        return '';
    }
    
    // If it's already a full URL, return as is
    if (filter_var($filepath, FILTER_VALIDATE_URL)) {
        return $filepath;
    }
    
    // Return URL through download endpoint for security
    return BASE_URL . '/public/download.php?path=' . urlencode($filepath);
}

?>

