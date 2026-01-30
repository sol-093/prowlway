<?php
session_start();
require_once '../includes/config.php';
require_once '../includes/database.php';
require_once '../includes/auth.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate CSRF token for login
    $token = $_POST['csrf_token'] ?? '';
    if (!validateCSRFToken($token)) {
        $_SESSION['login_error'] = 'Invalid security token. Please refresh the page and try again.';
        header('Location: ' . ADMIN_URL . '/index.php');
        exit;
    }
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    
    // Check database for admin
    $admin = dbFetchOne("SELECT * FROM admins WHERE email = ?", [$email]);
    
    if ($admin && password_verify($password, $admin['password'])) {
        // Regenerate session ID on successful login (prevents session fixation)
        session_regenerate_id(true);
        
        // Login successful
        $_SESSION['admin_logged_in'] = true;
        $_SESSION['admin_email'] = $email;
        $_SESSION['admin_id'] = $admin['id'];
        $_SESSION['admin_name'] = $admin['name'];
        $_SESSION['admin_role'] = $admin['role'];
        $_SESSION['last_activity'] = time(); // Track last activity for timeout
        require_once __DIR__ . '/../includes/auth.php';
        auditLog('login', 'Login successful', 'admin', $admin['id']);
        header('Location: ' . ADMIN_URL . '/index.php');
        exit;
    } else {
        // Fallback to simple auth for initial setup
        $valid_credentials = [
            'admin@icdisg.ph' => 'admin123'
        ];
        
        if (isset($valid_credentials[$email]) && $valid_credentials[$email] === $password) {
            // Regenerate session ID on successful login (prevents session fixation)
            session_regenerate_id(true);
            
            $_SESSION['admin_logged_in'] = true;
            $_SESSION['admin_email'] = $email;
            $_SESSION['admin_id'] = 1;
            $_SESSION['admin_name'] = 'Admin User';
            $_SESSION['admin_role'] = 'super_admin';
            $_SESSION['last_activity'] = time(); // Track last activity for timeout
            require_once __DIR__ . '/../includes/auth.php';
            auditLog('login', 'Login (fallback)', 'admin', 1);
            header('Location: ' . ADMIN_URL . '/index.php');
            exit;
        } else {
            // Login failed
            $_SESSION['login_error'] = 'Invalid email or password';
            header('Location: ' . ADMIN_URL . '/index.php');
            exit;
        }
    }
} else {
    header('Location: ' . ADMIN_URL . '/index.php');
    exit;
}
?>

