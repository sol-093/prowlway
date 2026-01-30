<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
    <meta http-equiv="Pragma" content="no-cache">
    <meta http-equiv="Expires" content="0">
    <title><?php echo isset($pageTitle) ? $pageTitle : 'PROWLWAY - ICDISG Archive Website'; ?></title>
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=Sora:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@3.4.1/dist/tailwind.min.css" rel="stylesheet">
    
    <!-- Custom Stylesheet -->
    <link rel="stylesheet" href="<?php echo ASSETS_URL; ?>/css/style.css?v=<?php echo time(); ?>">
    
    <!-- Base URL for JavaScript -->
    <script>
        window.BASE_URL = '<?php echo BASE_URL; ?>';
        window.ASSETS_URL = '<?php echo ASSETS_URL; ?>';
        window.PUBLIC_URL = '<?php echo PUBLIC_URL; ?>';
    </script>
</head>
<body class="<?php echo isset($bodyClass) ? $bodyClass : ''; ?>">
    
    <?php if (!isset($hideHeader) || !$hideHeader): ?>
    <!-- ========================================
         HEADER / NAVIGATION BAR
         ======================================== -->
    <header class="site-header">
        <div class="header-container">
            <!-- Logo Section -->
            <a href="<?php echo BASE_URL ? BASE_URL : '/'; ?>" class="brand" aria-label="PROWLWAY home">
                <div class="logo-icon">🐾</div>
                <div class="logo-text">PROWLWAY</div>
            </a>
            
            <!-- Navigation Menu -->
            <nav class="main-nav">
                <a href="<?php echo PUBLIC_URL; ?>/home.php" class="nav-link">Home</a>
                <a href="<?php echo PUBLIC_URL; ?>/institute.php" class="nav-link">Institute</a>
                <a href="<?php echo PUBLIC_URL; ?>/calendar.php" class="nav-link">Calendar</a>
                <a href="<?php echo PUBLIC_URL; ?>/events.php" class="nav-link">Events</a>
                <a href="<?php echo PUBLIC_URL; ?>/documents.php" class="nav-link">Documents</a>
                <a href="<?php echo PUBLIC_URL; ?>/home.php#contact" class="nav-link nav-icon">✉</a>
            </nav>
            
            <!-- Mobile Menu Toggle -->
            <button class="mobile-menu-toggle" aria-label="Toggle menu">
                <span></span>
                <span></span>
                <span></span>
            </button>
        </div>
    </header>
    <?php endif; ?>

