<?php
/**
 * Input Validation and Sanitization Functions
 * Centralized validation helpers for PROWLWAY
 */

/**
 * Validate email address
 * @param string $email Email to validate
 * @return bool True if valid, false otherwise
 */
function validateEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

/**
 * Sanitize string input
 * @param string $input Input to sanitize
 * @param int|null $maxLength Maximum length (null for no limit)
 * @return string Sanitized string
 */
function sanitizeString($input, $maxLength = null) {
    if (!is_string($input)) {
        $input = (string) $input;
    }
    $cleaned = trim(strip_tags($input));
    if ($maxLength !== null && $maxLength > 0) {
        $cleaned = substr($cleaned, 0, $maxLength);
    }
    return htmlspecialchars($cleaned, ENT_QUOTES, 'UTF-8');
}

/**
 * Validate academic year format (YYYY-YYYY)
 * @param string $year Academic year string
 * @return bool True if valid format
 */
function validateAcademicYear($year) {
    return preg_match('/^\d{4}-\d{4}$/', $year) === 1;
}

/**
 * Validate date string
 * @param string $date Date string
 * @param string $format Date format (default: Y-m-d)
 * @return bool True if valid date
 */
function validateDate($date, $format = 'Y-m-d') {
    $d = DateTime::createFromFormat($format, $date);
    return $d && $d->format($format) === $date;
}

/**
 * Validate integer within range
 * @param mixed $value Value to validate
 * @param int|null $min Minimum value (null for no minimum)
 * @param int|null $max Maximum value (null for no maximum)
 * @return bool True if valid integer in range
 */
function validateInteger($value, $min = null, $max = null) {
    if (!is_numeric($value)) {
        return false;
    }
    $int = (int) $value;
    if ($min !== null && $int < $min) {
        return false;
    }
    if ($max !== null && $int > $max) {
        return false;
    }
    return true;
}

/**
 * Validate file upload
 * @param array $file $_FILES array element
 * @param array $allowedTypes Allowed MIME types
 * @param int $maxSize Maximum file size in bytes
 * @return array ['valid' => bool, 'error' => string]
 */
function validateFileUpload($file, $allowedTypes = [], $maxSize = 5242880) {
    if (!isset($file) || $file['error'] !== UPLOAD_ERR_OK) {
        return ['valid' => false, 'error' => 'No file uploaded or upload error'];
    }
    
    if ($file['size'] > $maxSize) {
        return ['valid' => false, 'error' => 'File size exceeds ' . round($maxSize / 1024 / 1024, 1) . 'MB limit'];
    }
    
    if (!empty($allowedTypes)) {
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);
        
        if (!in_array($mimeType, $allowedTypes, true)) {
            return ['valid' => false, 'error' => 'Invalid file type. Allowed types: ' . implode(', ', $allowedTypes)];
        }
    }
    
    return ['valid' => true, 'error' => ''];
}

/**
 * Validate URL
 * @param string $url URL to validate
 * @return bool True if valid URL
 */
function validateUrl($url) {
    return filter_var($url, FILTER_VALIDATE_URL) !== false;
}

/**
 * Sanitize HTML content (allows some HTML tags)
 * @param string $html HTML content
 * @param array $allowedTags Allowed HTML tags (default: basic formatting)
 * @return string Sanitized HTML
 */
function sanitizeHtml($html, $allowedTags = ['p', 'br', 'strong', 'em', 'u', 'ul', 'ol', 'li', 'a', 'h1', 'h2', 'h3', 'h4', 'h5', 'h6']) {
    $allowed = '<' . implode('><', $allowedTags) . '>';
    return strip_tags($html, $allowed);
}

/**
 * Validate password strength
 * @param string $password Password to validate
 * @return array ['valid' => bool, 'error' => string]
 */
function validatePasswordStrength($password) {
    if (strlen($password) < 8) {
        return ['valid' => false, 'error' => 'Password must be at least 8 characters long'];
    }
    
    if (!preg_match('/[a-z]/', $password)) {
        return ['valid' => false, 'error' => 'Password must contain at least one lowercase letter'];
    }
    
    if (!preg_match('/[A-Z]/', $password)) {
        return ['valid' => false, 'error' => 'Password must contain at least one uppercase letter'];
    }
    
    if (!preg_match('/[0-9]/', $password)) {
        return ['valid' => false, 'error' => 'Password must contain at least one number'];
    }
    
    return ['valid' => true, 'error' => ''];
}
