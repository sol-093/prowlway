<?php
/**
 * File Upload Handler
 * 
 * Provides functions for handling file uploads, image optimization, thumbnail generation,
 * and URL generation for uploaded files. Supports images (JPEG, PNG, GIF, WebP) and
 * documents (PDF).
 * 
 * @package PROWLWAY
 * @subpackage Includes
 * @author ICDISG Development Team
 * @since 1.0.0
 */

require_once __DIR__ . '/config.php';

// Allowed image types
define('ALLOWED_IMAGE_TYPES', ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp']);
define('ALLOWED_DOC_TYPES', ['application/pdf']);

// Max file sizes (in bytes)
define('MAX_IMAGE_SIZE', 5 * 1024 * 1024); // 5MB
define('MAX_DOC_SIZE', 50 * 1024 * 1024); // 50MB

/**
 * Generate thumbnail from image
 * 
 * Creates a resized thumbnail image maintaining aspect ratio.
 * Supports JPEG, PNG, GIF, and WebP formats. Preserves transparency for PNG and GIF.
 * Thumbnails are stored in a 'thumbnails' subdirectory.
 * 
 * @param string $sourcePath Full path to source image file
 * @param int $maxWidth Maximum thumbnail width in pixels (default: 300)
 * @param int $maxHeight Maximum thumbnail height in pixels (default: 300)
 * @param string $subfolder Subfolder within uploads directory (default: 'images')
 * 
 * @return array Result array with keys:
 *   - 'success' (bool): True if thumbnail was created successfully
 *   - 'path' (string): Relative path to thumbnail (if success)
 *   - 'full_path' (string): Full filesystem path to thumbnail (if success)
 *   - 'error' (string): Error message (if failed)
 * 
 * @example
 * $result = generateThumbnail('/path/to/image.jpg', 200, 200, 'images');
 * if ($result['success']) {
 *     echo "Thumbnail created: " . $result['path'];
 * }
 */
function generateThumbnail($sourcePath, $maxWidth = 300, $maxHeight = 300, $subfolder = 'images') {
    if (!file_exists($sourcePath) || !is_file($sourcePath)) {
        return ['success' => false, 'error' => 'Source image not found'];
    }
    
    // Check if GD extension is available
    if (!extension_loaded('gd')) {
        return ['success' => false, 'error' => 'GD extension not available'];
    }
    
    // Get image info
    $imageInfo = getimagesize($sourcePath);
    if (!$imageInfo) {
        return ['success' => false, 'error' => 'Invalid image file'];
    }
    
    $originalWidth = $imageInfo[0];
    $originalHeight = $imageInfo[1];
    $mimeType = $imageInfo['mime'];
    
    // Calculate thumbnail dimensions maintaining aspect ratio
    $ratio = min($maxWidth / $originalWidth, $maxHeight / $originalHeight);
    $thumbWidth = (int)($originalWidth * $ratio);
    $thumbHeight = (int)($originalHeight * $ratio);
    
    // Create thumbnail directory
    $thumbDir = UPLOADS_PATH . '/' . $subfolder . '/thumbnails';
    if (!file_exists($thumbDir)) {
        mkdir($thumbDir, 0755, true);
    }
    
    // Generate thumbnail filename
    $sourceFilename = basename($sourcePath);
    $thumbFilename = 'thumb_' . $sourceFilename;
    $thumbPath = $thumbDir . '/' . $thumbFilename;
    
    // Create image resource from source
    $sourceImage = null;
    switch ($mimeType) {
        case 'image/jpeg':
        case 'image/jpg':
            $sourceImage = imagecreatefromjpeg($sourcePath);
            break;
        case 'image/png':
            $sourceImage = imagecreatefrompng($sourcePath);
            break;
        case 'image/gif':
            $sourceImage = imagecreatefromgif($sourcePath);
            break;
        case 'image/webp':
            if (function_exists('imagecreatefromwebp')) {
                $sourceImage = imagecreatefromwebp($sourcePath);
            }
            break;
    }
    
    if (!$sourceImage) {
        return ['success' => false, 'error' => 'Failed to create image resource'];
    }
    
    // Create thumbnail
    $thumbImage = imagecreatetruecolor($thumbWidth, $thumbHeight);
    
    // Preserve transparency for PNG and GIF
    if ($mimeType === 'image/png' || $mimeType === 'image/gif') {
        imagealphablending($thumbImage, false);
        imagesavealpha($thumbImage, true);
        $transparent = imagecolorallocatealpha($thumbImage, 255, 255, 255, 127);
        imagefilledrectangle($thumbImage, 0, 0, $thumbWidth, $thumbHeight, $transparent);
    }
    
    // Resize image
    imagecopyresampled($thumbImage, $sourceImage, 0, 0, 0, 0, $thumbWidth, $thumbHeight, $originalWidth, $originalHeight);
    
    // Save thumbnail
    $saved = false;
    switch ($mimeType) {
        case 'image/jpeg':
        case 'image/jpg':
            $saved = imagejpeg($thumbImage, $thumbPath, 85); // 85% quality
            break;
        case 'image/png':
            $saved = imagepng($thumbImage, $thumbPath, 6); // Compression level 6
            break;
        case 'image/gif':
            $saved = imagegif($thumbImage, $thumbPath);
            break;
        case 'image/webp':
            if (function_exists('imagewebp')) {
                $saved = imagewebp($thumbImage, $thumbPath, 85);
            }
            break;
    }
    
    // Clean up
    imagedestroy($sourceImage);
    imagedestroy($thumbImage);
    
    if ($saved) {
        $relativePath = $subfolder . '/thumbnails/' . $thumbFilename;
        return ['success' => true, 'path' => $relativePath, 'full_path' => $thumbPath];
    } else {
        return ['success' => false, 'error' => 'Failed to save thumbnail'];
    }
}

/**
 * Optimize image (compress and convert to WebP if possible)
 * 
 * Compresses an image to reduce file size. Attempts to convert to WebP format
 * for better compression if the GD extension supports it. Otherwise, re-saves
 * the image with compression settings.
 * 
 * @param string $sourcePath Full path to source image file
 * @param int $quality Quality setting: JPEG (1-100), PNG compression level (0-9), WebP (0-100) (default: 85)
 * 
 * @return bool True on success, false on failure
 * 
 * @note Requires GD extension to be loaded
 * @note WebP conversion creates a new file but doesn't replace the original by default
 */
function optimizeImage($sourcePath, $quality = 85) {
    if (!file_exists($sourcePath) || !is_file($sourcePath)) {
        return false;
    }
    
    if (!extension_loaded('gd')) {
        return false;
    }
    
    $imageInfo = getimagesize($sourcePath);
    if (!$imageInfo) {
        return false;
    }
    
    $mimeType = $imageInfo['mime'];
    $sourceImage = null;
    
    // Load source image
    switch ($mimeType) {
        case 'image/jpeg':
        case 'image/jpg':
            $sourceImage = imagecreatefromjpeg($sourcePath);
            break;
        case 'image/png':
            $sourceImage = imagecreatefrompng($sourcePath);
            break;
        case 'image/gif':
            $sourceImage = imagecreatefromgif($sourcePath);
            break;
        case 'image/webp':
            if (function_exists('imagecreatefromwebp')) {
                $sourceImage = imagecreatefromwebp($sourcePath);
            }
            break;
    }
    
    if (!$sourceImage) {
        return false;
    }
    
    // Try to convert to WebP for better compression (if supported)
    if (function_exists('imagewebp')) {
        $webpPath = preg_replace('/\.(jpg|jpeg|png|gif)$/i', '.webp', $sourcePath);
        if (imagewebp($sourceImage, $webpPath, $quality)) {
            imagedestroy($sourceImage);
            // Optionally replace original with WebP
            // unlink($sourcePath);
            // rename($webpPath, $sourcePath);
            return true;
        }
    }
    
    // Otherwise, re-save with compression
    $saved = false;
    switch ($mimeType) {
        case 'image/jpeg':
        case 'image/jpg':
            $saved = imagejpeg($sourceImage, $sourcePath, $quality);
            break;
        case 'image/png':
            $saved = imagepng($sourceImage, $sourcePath, 6);
            break;
    }
    
    imagedestroy($sourceImage);
    return $saved;
}

/**
 * Upload an image file with optimization and thumbnail generation
 * 
 * Handles image upload with validation, optimization, and optional thumbnail generation.
 * Validates file type (JPEG, PNG, GIF, WebP) and size (max 5MB).
 * Generates unique filenames to prevent conflicts.
 * 
 * @param array $file $_FILES array element containing uploaded file data
 * @param string $subfolder Subfolder within uploads directory (default: 'images')
 * @param bool $generateThumbnail Whether to automatically generate thumbnail (default: true)
 * @param bool $optimize Whether to optimize image after upload (default: true)
 * 
 * @return array Result array with keys:
 *   - 'success' (bool): True if upload was successful
 *   - 'path' (string): Relative path for database storage (if success)
 *   - 'full_path' (string): Full filesystem path (if success)
 *   - 'thumbnail' (string|null): Relative path to thumbnail if generated (if success)
 *   - 'error' (string): Error message (if failed)
 * 
 * @example
 * if (isset($_FILES['image'])) {
 *     $result = uploadImage($_FILES['image'], 'images', true, true);
 *     if ($result['success']) {
 *         // Store $result['path'] in database
 *     }
 * }
 */
function uploadImage($file, $subfolder = 'images', $generateThumbnail = true, $optimize = true) {
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
        // Optimize image if requested
        if ($optimize && extension_loaded('gd')) {
            optimizeImage($filepath, 85);
        }
        
        // Generate thumbnail if requested
        $thumbnailPath = null;
        if ($generateThumbnail && extension_loaded('gd')) {
            $thumbResult = generateThumbnail($filepath, 300, 300, $subfolder);
            if ($thumbResult['success']) {
                $thumbnailPath = $thumbResult['path'];
            }
        }
        
        // Return relative path for database storage
        $relativePath = $subfolder . '/' . $filename;
        return [
            'success' => true, 
            'path' => $relativePath, 
            'full_path' => $filepath,
            'thumbnail' => $thumbnailPath
        ];
    } else {
        return ['success' => false, 'error' => 'Failed to move uploaded file'];
    }
}

/**
 * Upload a document file (PDF)
 * 
 * Handles PDF document upload with validation. Validates file type (PDF only)
 * and size (max 50MB). Generates unique filenames to prevent conflicts.
 * 
 * @param array $file $_FILES array element containing uploaded file data
 * 
 * @return array Result array with keys:
 *   - 'success' (bool): True if upload was successful
 *   - 'path' (string): Relative path for database storage (if success)
 *   - 'full_path' (string): Full filesystem path (if success)
 *   - 'size' (int): File size in bytes (if success)
 *   - 'type' (string): MIME type (if success)
 *   - 'error' (string): Error message (if failed)
 * 
 * @example
 * if (isset($_FILES['document'])) {
 *     $result = uploadDocument($_FILES['document']);
 *     if ($result['success']) {
 *         // Store $result['path'] and $result['size'] in database
 *     }
 * }
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
 * 
 * Handles multiple image uploads in a single call. Processes each file
 * individually and returns an array of results.
 * 
 * @param array $files $_FILES array (can be single file or multiple files)
 * @param string $subfolder Subfolder within uploads directory (default: 'images')
 * 
 * @return array Array of result arrays, each with same structure as uploadImage()
 * 
 * @example
 * if (isset($_FILES['gallery'])) {
 *     $results = uploadMultipleImages($_FILES['gallery'], 'images');
 *     foreach ($results as $result) {
 *         if ($result['success']) {
 *             // Process each uploaded image
 *         }
 *     }
 * }
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
 * 
 * Safely deletes a file from the uploads directory. Also attempts to delete
 * associated thumbnail if it exists.
 * 
 * @param string $filepath Relative path from uploads directory (as stored in database)
 * 
 * @return bool True on success, false on failure
 * 
 * @example
 * if (deleteUploadedFile('images/photo.jpg')) {
 *     echo "File deleted successfully";
 * }
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
 * 
 * Generates a full URL for displaying an image. Uses the secure image.php endpoint
 * to serve images, ensuring proper access control and path handling.
 * Optionally returns thumbnail URL if available.
 * 
 * @param string $filepath Relative path stored in database (e.g., 'images/photo.jpg')
 * @param bool $useThumbnail Whether to use thumbnail if available (default: false)
 * 
 * @return string Full URL to the image, or empty string if filepath is invalid
 * 
 * @example
 * $imageUrl = getImageUrl('images/photo.jpg', false);
 * echo "<img src='$imageUrl' alt='Photo'>";
 * 
 * $thumbUrl = getImageUrl('images/photo.jpg', true);
 * echo "<img src='$thumbUrl' alt='Thumbnail'>";
 */
function getImageUrl($filepath, $useThumbnail = false) {
    if (empty($filepath)) {
        return '';
    }
    
    // If it's already a full URL, return as is
    if (filter_var($filepath, FILTER_VALIDATE_URL)) {
        return $filepath;
    }
    
    // Use thumbnail if requested and available
    if ($useThumbnail) {
        $thumbPath = getThumbnailPath($filepath);
        if ($thumbPath && file_exists(UPLOADS_PATH . '/' . $thumbPath)) {
            $filepath = $thumbPath;
        }
    }
    
    // Serve via secure image endpoint so paths work everywhere (admin + public)
    return PUBLIC_URL . '/image.php?path=' . urlencode($filepath);
}

/**
 * Get thumbnail path for an image
 * 
 * Checks if a thumbnail exists for the given image and returns its path.
 * Thumbnails are stored in a 'thumbnails' subdirectory with 'thumb_' prefix.
 * 
 * @param string $filepath Relative path stored in database (e.g., 'images/photo.jpg')
 * 
 * @return string|null Relative path to thumbnail if exists, null otherwise
 * 
 * @example
 * $thumbPath = getThumbnailPath('images/photo.jpg');
 * if ($thumbPath) {
 *     echo "Thumbnail: $thumbPath";
 * }
 */
function getThumbnailPath($filepath) {
    if (empty($filepath)) {
        return null;
    }
    
    // Extract subfolder and filename
    $parts = explode('/', $filepath, 2);
    if (count($parts) === 2) {
        $subfolder = $parts[0];
        $filename = $parts[1];
        $thumbPath = $subfolder . '/thumbnails/thumb_' . $filename;
        
        // Check if thumbnail exists
        if (file_exists(UPLOADS_PATH . '/' . $thumbPath)) {
            return $thumbPath;
        }
    }
    
    return null;
}

/**
 * Get document URL for download
 * 
 * Generates a full URL for downloading a document. Uses the secure download.php
 * endpoint to serve documents with proper access control.
 * 
 * @param string $filepath Relative path stored in database (e.g., 'documents/file.pdf')
 * 
 * @return string Full URL to the document, or empty string if filepath is invalid
 * 
 * @example
 * $docUrl = getDocumentUrl('documents/report.pdf');
 * echo "<a href='$docUrl'>Download Report</a>";
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

