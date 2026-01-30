<?php
session_start();
require_once '../includes/config.php';
require_once '../includes/database.php';
require_once '../includes/auth.php';

$adminId = $_SESSION['admin_id'] ?? null;
auditLog('logout', 'Logout', 'admin', $adminId);

// Destroy session
$_SESSION = array();
session_destroy();

// Redirect to login
header('Location: ' . ADMIN_URL . '/index.php');
exit;
?>

