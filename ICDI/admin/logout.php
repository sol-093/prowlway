<?php
session_start();
require_once '../includes/config.php';

// Destroy session
$_SESSION = array();
session_destroy();

// Redirect to login
header('Location: ' . ADMIN_URL . '/index.php');
exit;
?>

