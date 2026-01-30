<?php
// Main entry point - redirect to home page
require_once __DIR__ . '/includes/config.php';
$redirectUrl = PUBLIC_URL . '/home.php';
header('Location: ' . $redirectUrl);
exit;
?>

