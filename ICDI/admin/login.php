<?php
session_start();
require_once '../includes/config.php';
require_once '../includes/database.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    
    // Check database for admin
    $admin = dbFetchOne("SELECT * FROM admins WHERE email = ?", [$email]);
    
    if ($admin && password_verify($password, $admin['password'])) {
        // Login successful
        $_SESSION['admin_logged_in'] = true;
        $_SESSION['admin_email'] = $email;
        $_SESSION['admin_id'] = $admin['id'];
        $_SESSION['admin_name'] = $admin['name'];
        $_SESSION['admin_role'] = $admin['role'];
        header('Location: ' . ADMIN_URL . '/index.php');
        exit;
    } else {
        // Fallback to simple auth for initial setup
        $valid_credentials = [
            'admin@icdisg.ph' => 'admin123'
        ];
        
        if (isset($valid_credentials[$email]) && $valid_credentials[$email] === $password) {
            $_SESSION['admin_logged_in'] = true;
            $_SESSION['admin_email'] = $email;
            $_SESSION['admin_id'] = 1;
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

