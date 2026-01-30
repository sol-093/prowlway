<?php
/**
 * Centralized Error Handling for PROWLWAY
 * Provides consistent error responses and logging
 */

/**
 * API Error Response Handler
 */
class APIError {
    /**
     * Send JSON error response
     * @param string $message Error message
     * @param int $code HTTP status code (default: 400)
     * @param array $additionalData Additional data to include
     */
    public static function json($message, $code = 400, $additionalData = []) {
        http_response_code($code);
        header('Content-Type: application/json');
        
        $response = [
            'success' => false,
            'error' => $message
        ];
        
        if (!empty($additionalData)) {
            $response = array_merge($response, $additionalData);
        }
        
        echo json_encode($response);
        exit;
    }
    
    /**
     * Log error with context
     * @param string $message Error message
     * @param array $context Additional context data
     * @param string $level Log level (error, warning, info)
     */
    public static function log($message, $context = [], $level = 'error') {
        $logMessage = sprintf(
            '[%s] [%s] %s',
            date('Y-m-d H:i:s'),
            strtoupper($level),
            $message
        );
        
        if (!empty($context)) {
            $logMessage .= ' | Context: ' . json_encode($context);
        }
        
        // Add request context
        $requestContext = [
            'method' => $_SERVER['REQUEST_METHOD'] ?? 'UNKNOWN',
            'uri' => $_SERVER['REQUEST_URI'] ?? 'UNKNOWN',
            'ip' => $_SERVER['REMOTE_ADDR'] ?? 'UNKNOWN',
            'user_agent' => substr($_SERVER['HTTP_USER_AGENT'] ?? '', 0, 200)
        ];
        
        if (isset($_SESSION['admin_id'])) {
            $requestContext['admin_id'] = $_SESSION['admin_id'];
        }
        
        $logMessage .= ' | Request: ' . json_encode($requestContext);
        
        error_log($logMessage);
    }
    
    /**
     * Handle database errors
     * @param Exception $e Exception object
     * @param bool $jsonResponse Return JSON response
     */
    public static function database($e, $jsonResponse = false) {
        $errorMessage = $e->getMessage();
        
        // Also check for last database error if available
        if (function_exists('getLastDbError')) {
            $lastDbError = getLastDbError();
            if (!empty($lastDbError)) {
                if (empty($errorMessage) || $errorMessage === 'Failed to insert announcement' || 
                    $errorMessage === 'Failed to update announcement' || 
                    strpos($errorMessage, 'Failed to') === 0) {
                    // Replace generic message with actual database error
                    $errorMessage = $lastDbError;
                } elseif ($errorMessage !== $lastDbError) {
                    // If both exist and different, combine them
                    $errorMessage = $lastDbError . ' | ' . $errorMessage;
                }
            }
        }
        
        self::log('Database error: ' . $errorMessage, [
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'trace' => $e->getTraceAsString()
        ]);
        
        if ($jsonResponse) {
            // Always show actual error message for admin panel debugging
            // This helps identify database issues quickly
            $userMessage = !empty($errorMessage) ? $errorMessage : 'Database operation failed. Please try again later.';
            self::json($userMessage, 500);
        } else {
            // For non-API requests, show user-friendly error
            die('A database error occurred. Please contact the administrator.');
        }
    }
    
    /**
     * Handle validation errors
     * @param array $errors Array of validation error messages
     * @param bool $jsonResponse Return JSON response
     */
    public static function validation($errors, $jsonResponse = false) {
        if ($jsonResponse) {
            self::json('Validation failed', 400, ['errors' => $errors]);
        } else {
            // For non-API requests, return errors array
            return $errors;
        }
    }
    
    /**
     * Handle file upload errors
     * @param string $message Error message
     * @param bool $jsonResponse Return JSON response
     */
    public static function upload($message, $jsonResponse = false) {
        self::log('File upload error: ' . $message);
        
        if ($jsonResponse) {
            self::json($message, 400);
        } else {
            return ['success' => false, 'error' => $message];
        }
    }
    
    /**
     * Handle permission errors
     * @param string $message Error message
     * @param bool $jsonResponse Return JSON response
     */
    public static function permission($message = 'You do not have permission to perform this action', $jsonResponse = false) {
        self::log('Permission denied', [
            'message' => $message,
            'admin_id' => $_SESSION['admin_id'] ?? null,
            'role' => $_SESSION['admin_role'] ?? null
        ]);
        
        if ($jsonResponse) {
            self::json($message, 403);
        } else {
            header('Location: ' . (defined('ADMIN_URL') ? ADMIN_URL : '') . '/index.php');
            exit;
        }
    }
}

/**
 * User-friendly error messages
 */
class ErrorMessages {
    const GENERIC = 'An error occurred. Please try again.';
    const DATABASE = 'Database error. Please contact the administrator.';
    const VALIDATION = 'Please check your input and try again.';
    const UPLOAD = 'File upload failed. Please check the file and try again.';
    const PERMISSION = 'You do not have permission to perform this action.';
    const NOT_FOUND = 'The requested resource was not found.';
    const SESSION_EXPIRED = 'Your session has expired. Please login again.';
    const CSRF_INVALID = 'Invalid security token. Please refresh the page and try again.';
}
