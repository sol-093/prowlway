<?php
// Fetch organizations for mobile dropdown if database functions are available
$mobileOrganizations = [];
if (!function_exists('dbFetchAll') && file_exists(__DIR__ . '/database.php')) {
    require_once __DIR__ . '/database.php';
}
if (function_exists('dbFetchAll')) {
    try {
        $mobileOrganizations = dbFetchAll("SELECT id, name, acronym FROM student_organizations WHERE status = 'active' ORDER BY display_order ASC");
    } catch (Exception $e) {
        $mobileOrganizations = [];
    }
}
?>
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
                <img src="<?php echo ASSETS_URL; ?>/IMG/ICONS/logo.png" alt="PROWLWAY Logo" class="logo-icon">
                <div class="logo-text">PROWLWAY</div>
            </a>
            
            <!-- Search Bar - Hidden on mobile -->
            <form method="GET" action="<?php echo PUBLIC_URL; ?>/search.php" class="search-form">
                <input 
                    type="search" 
                    name="q" 
                    placeholder="Search documents, announcements, events..." 
                    value="<?php echo isset($_GET['q']) ? htmlspecialchars($_GET['q']) : ''; ?>"
                    class="search-input"
                    aria-label="Search"
                    required
                >
                <button type="submit" class="search-button" aria-label="Submit search">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="11" cy="11" r="8"></circle>
                        <path d="m21 21-4.35-4.35"></path>
                    </svg>
                </button>
            </form>
            
            <!-- Navigation Menu - Hidden on mobile -->
            <nav class="main-nav">
                <a href="<?php echo PUBLIC_URL; ?>/home.php" class="nav-link">Home</a>
                <a href="<?php echo PUBLIC_URL; ?>/institute.php" class="nav-link">Institute</a>
                <a href="<?php echo PUBLIC_URL; ?>/calendar.php" class="nav-link">Calendar</a>
                <a href="<?php echo PUBLIC_URL; ?>/events.php" class="nav-link">Events</a>
                <a href="<?php echo PUBLIC_URL; ?>/documents.php" class="nav-link">Documents</a>
                <a href="<?php echo PUBLIC_URL; ?>/contact.php" class="nav-link nav-icon" aria-label="Get in touch">✉</a>
            </nav>
            
            <!-- Mobile Search & Menu Buttons -->
            <div class="flex items-center gap-3 lg:hidden flex-shrink-0" style="order: 999;">
                <!-- Mobile Search Icon -->
                <button class="mobile-search-toggle" id="mobileSearchToggle" aria-label="Search" onclick="document.getElementById('mobileSidebar').classList.add('active'); document.body.classList.add('sidebar-open'); setTimeout(() => { const searchInput = document.getElementById('mobileSearchInput'); if(searchInput) searchInput.focus(); }, 300);">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                    </svg>
                </button>
                <!-- Mobile Menu Toggle -->
                <button class="mobile-menu-toggle" id="mobileMenuToggle" aria-label="Toggle menu">
                    <span></span>
                    <span></span>
                    <span></span>
                </button>
            </div>
        </div>
    </header>
    
    <!-- Mobile Sidebar -->
    <div class="mobile-sidebar" id="mobileSidebar">
        <div class="mobile-sidebar-overlay" id="sidebarOverlay"></div>
        <div class="mobile-sidebar-content">
            <!-- Sidebar Header -->
            <div class="mobile-sidebar-header">
                <a href="<?php echo BASE_URL ? BASE_URL : '/'; ?>" class="flex items-center gap-3">
                    <img src="<?php echo ASSETS_URL; ?>/IMG/ICONS/logo.png" alt="PROWLWAY Logo" class="w-8 h-8">
                    <div class="text-xl font-bold text-white">PROWLWAY</div>
                </a>
                <button class="mobile-sidebar-close" id="sidebarClose" aria-label="Close sidebar">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>

            <!-- Navigation items -->
            <nav class="mobile-sidebar-nav">
                <ul class="flex flex-col gap-2">
                    <li>
                        <a href="<?php echo PUBLIC_URL; ?>/home.php" class="mobile-nav-link">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
                            </svg>
                            <span>Home</span>
                        </a>
                    </li>
                    <li class="mobile-dropdown">
                        <button class="mobile-nav-link mobile-dropdown-toggle" type="button">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                            </svg>
                            <span>Institute</span>
                            <svg class="w-5 h-5 dropdown-arrow" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                            </svg>
                        </button>
                        <ul class="mobile-dropdown-menu">
                            <li><a href="<?php echo PUBLIC_URL; ?>/institute.php?section=about" class="mobile-dropdown-link">About</a></li>
                            <li><a href="<?php echo PUBLIC_URL; ?>/institute.php?section=faculty" class="mobile-dropdown-link">Faculty Unit</a></li>
                            <li><a href="<?php echo PUBLIC_URL; ?>/institute.php?section=admin" class="mobile-dropdown-link">Admin Representative</a></li>
                            <li><a href="<?php echo PUBLIC_URL; ?>/institute.php?section=program" class="mobile-dropdown-link">Program</a></li>
                        </ul>
                    </li>
                    <li class="mobile-dropdown">
                        <button class="mobile-nav-link mobile-dropdown-toggle" type="button">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                            </svg>
                            <span>Student Organizations</span>
                            <svg class="w-5 h-5 dropdown-arrow" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                            </svg>
                        </button>
                        <ul class="mobile-dropdown-menu">
                            <?php if (!empty($mobileOrganizations)): ?>
                                <?php foreach ($mobileOrganizations as $org): ?>
                                    <li><a href="<?php echo PUBLIC_URL; ?>/organization.php?id=<?php echo $org['id']; ?>" class="mobile-dropdown-link"><?php echo htmlspecialchars($org['acronym'] ? $org['acronym'] . ' - ' . $org['name'] : $org['name']); ?></a></li>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <li><a href="<?php echo PUBLIC_URL; ?>/institute.php" class="mobile-dropdown-link">View Organizations</a></li>
                            <?php endif; ?>
                        </ul>
                    </li>
                    <li>
                        <a href="<?php echo PUBLIC_URL; ?>/calendar.php" class="mobile-nav-link">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                            </svg>
                            <span>Calendar</span>
                        </a>
                    </li>
                    <li>
                        <a href="<?php echo PUBLIC_URL; ?>/events.php" class="mobile-nav-link">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                            </svg>
                            <span>Events</span>
                        </a>
                    </li>
                    <li>
                        <a href="<?php echo PUBLIC_URL; ?>/documents.php" class="mobile-nav-link">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                            </svg>
                            <span>Documents</span>
                        </a>
                    </li>
                    <li>
                        <a href="<?php echo PUBLIC_URL; ?>/contact.php" class="mobile-nav-link">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                            </svg>
                            <span>Contact</span>
                        </a>
                    </li>
                </ul>
            </nav>
            
            <!-- Sidebar Footer -->
            <div class="mobile-sidebar-footer">
                <form method="GET" action="<?php echo PUBLIC_URL; ?>/search.php" class="mobile-search-form">
                    <div class="mobile-search-wrapper">
                        <input 
                            type="search" 
                            name="q" 
                            id="mobileSearchInput"
                            placeholder="Search..." 
                            value="<?php echo isset($_GET['q']) ? htmlspecialchars($_GET['q']) : ''; ?>"
                            class="mobile-search-input"
                            required
                        >
                        <button type="submit" class="mobile-search-submit" aria-label="Submit search">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                            </svg>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <!-- Mobile Sidebar Styles -->
    <style>
        /* Mobile Sidebar - Slide in from right */
        .mobile-sidebar {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: 9999;
            display: none;
        }

        .mobile-sidebar.active {
            display: block;
        }

        .mobile-sidebar-overlay {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.6);
            opacity: 0;
            transition: opacity 0.3s ease;
        }

        .mobile-sidebar.active .mobile-sidebar-overlay {
            opacity: 1;
        }

        .mobile-sidebar-content {
            position: absolute;
            top: 0;
            right: -100%;
            width: 85%;
            max-width: 400px;
            height: 100%;
            background: linear-gradient(180deg, #1a1d1f 0%, #141517 100%);
            box-shadow: -2px 0 20px rgba(0, 0, 0, 0.5);
            overflow-y: auto;
            transition: right 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            z-index: 10000;
        }

        .mobile-sidebar.active .mobile-sidebar-content {
            right: 0;
        }

        /* Sidebar Header */
        .mobile-sidebar-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1.5rem;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            background: linear-gradient(135deg, #1a1d1f 0%, #23272a 100%);
        }

        .mobile-sidebar-close {
            background: rgba(255, 255, 255, 0.1);
            border: none;
            font-size: 1.5rem;
            color: #e6e9eb;
            cursor: pointer;
            padding: 0.5rem;
            border-radius: 8px;
            transition: all 0.3s ease;
            width: 40px;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .mobile-sidebar-close:hover {
            background: rgba(255, 255, 255, 0.2);
            transform: rotate(90deg);
        }

        /* Sidebar Navigation */
        .mobile-sidebar-nav {
            padding: 1.5rem;
        }

        .mobile-sidebar-nav ul {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .mobile-nav-link {
            padding: 1rem 1.25rem;
            border-radius: 12px;
            background: rgba(255, 255, 255, 0.05);
            color: #e6e9eb;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 0.75rem;
            transition: all 0.3s ease;
            text-decoration: none;
            margin-bottom: 0.5rem;
            width: 100%;
        }

        .mobile-nav-link span {
            flex: 0 1 auto;
        }

        .mobile-nav-link:hover {
            background: rgba(255, 255, 255, 0.1);
            color: rgba(255, 255, 255, 0.9);
            transform: translateX(5px);
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.2);
        }

        .mobile-nav-link svg {
            flex-shrink: 0;
            color: rgba(255, 255, 255, 0.7);
            transition: color 0.3s ease;
        }

        .mobile-nav-link:hover svg {
            color: rgba(255, 255, 255, 0.9);
        }

        /* Mobile Dropdown Menu */
        .mobile-dropdown {
            position: relative;
        }

        .mobile-dropdown-toggle {
            width: 100%;
            justify-content: flex-start;
            gap: 0.75rem;
            position: relative;
        }

        .mobile-dropdown-toggle .dropdown-arrow {
            transition: transform 0.3s ease;
            flex-shrink: 0;
            margin-left: auto;
            width: 16px;
            height: 16px;
        }

        .mobile-dropdown-toggle span {
            flex: 0 1 auto;
        }

        .mobile-dropdown.active .mobile-dropdown-toggle .dropdown-arrow {
            transform: rotate(180deg);
        }

        .mobile-dropdown-menu {
            list-style: none;
            padding: 0;
            margin: 0;
            max-height: 0;
            overflow: hidden;
            transition: max-height 0.3s ease;
            padding-left: 2rem;
        }

        .mobile-dropdown.active .mobile-dropdown-menu {
            max-height: 500px;
        }

        .mobile-dropdown-link {
            display: block;
            padding: 0.75rem 1.25rem;
            color: rgba(255, 255, 255, 0.7);
            text-decoration: none;
            font-size: 14px;
            transition: all 0.3s ease;
        }

        .mobile-dropdown-link:hover {
            color: rgba(255, 255, 255, 0.9);
            padding-left: 1.5rem;
        }

        /* Mobile Search Toggle Button */
        .mobile-search-toggle {
            background: transparent;
            border: none;
            color: #e6e9eb;
            cursor: pointer;
            padding: 0.5rem;
            border-radius: 8px;
            transition: all 0.3s ease;
            width: 40px;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .mobile-search-toggle:hover {
            background: rgba(255, 255, 255, 0.1);
            color: rgba(255, 255, 255, 0.9);
            transform: scale(1.1);
        }

        .mobile-search-toggle:active {
            transform: scale(0.95);
        }

        .mobile-search-toggle svg {
            width: 20px;
            height: 20px;
        }

        /* Sidebar Footer */
        .mobile-sidebar-footer {
            padding: 1.5rem;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
            margin-top: auto;
        }

        .mobile-search-form {
            width: 100%;
        }

        .mobile-search-wrapper {
            position: relative;
            display: flex;
            align-items: center;
            width: 100%;
        }

        .mobile-search-input {
            width: 100%;
            padding: 0.75rem 3rem 0.75rem 1rem;
            background: rgba(255, 255, 255, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: 8px;
            color: #e6e9eb;
            font-size: 0.95rem;
            transition: all 0.3s ease;
        }

        .mobile-search-input::placeholder {
            color: rgba(255, 255, 255, 0.5);
        }

        .mobile-search-input:focus {
            outline: none;
            border-color: rgba(255, 255, 255, 0.4);
            background: rgba(255, 255, 255, 0.15);
            box-shadow: 0 0 0 3px rgba(255, 255, 255, 0.1);
        }

        .mobile-search-submit {
            position: absolute;
            right: 0.5rem;
            background: transparent;
            border: none;
            color: rgba(255, 255, 255, 0.7);
            cursor: pointer;
            padding: 0.5rem;
            border-radius: 6px;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            width: 36px;
            height: 36px;
        }

        .mobile-search-submit:hover {
            background: rgba(255, 255, 255, 0.1);
            color: rgba(255, 255, 255, 0.9);
            transform: scale(1.1);
        }

        .mobile-search-submit:active {
            transform: scale(0.95);
        }

        .mobile-search-submit svg {
            width: 20px;
            height: 20px;
        }

        /* Prevent body scroll when sidebar is open */
        body.sidebar-open {
            overflow: hidden;
        }

        /* Hide main nav dropdown on mobile to prevent duplicate */
        @media (max-width: 1023px) {
            .main-nav.active {
                display: none !important;
            }
            
            /* Hide desktop search and nav on mobile */
            .search-form {
                display: none !important;
            }
            
            .main-nav {
                display: none !important;
            }
            
            /* Ensure header layout is correct on mobile */
            .header-container {
                display: flex;
                align-items: center;
                justify-content: space-between;
                flex-wrap: nowrap;
            }
            
            /* Ensure mobile buttons container is visible and positioned correctly */
            .header-container > div[style*="order: 999"] {
                display: flex !important;
                order: 999;
                margin-left: auto;
                flex-shrink: 0;
            }
            
            .mobile-menu-toggle {
                display: flex !important;
            }
            
            .mobile-search-toggle {
                display: flex !important;
            }
        }
        
        /* Show desktop elements on large screens */
        @media (min-width: 1024px) {
            .search-form {
                display: flex !important;
            }
            
            .main-nav {
                display: flex !important;
            }
        }

        @media (min-width: 1024px) {
            .mobile-sidebar {
                display: none !important;
            }
            
            .mobile-menu-toggle,
            .mobile-search-toggle {
                display: none !important;
            }
            
            .header-container > div[style*="order: 999"] {
                display: none !important;
            }
        }
    </style>
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const sidebar = document.getElementById('mobileSidebar');
            const sidebarToggle = document.getElementById('mobileMenuToggle');
            const sidebarClose = document.getElementById('sidebarClose');
            const sidebarOverlay = document.getElementById('sidebarOverlay');

            // Open sidebar
            if (sidebarToggle) {
                sidebarToggle.addEventListener('click', function() {
                    sidebar.classList.add('active');
                    document.body.classList.add('sidebar-open');
                });
            }

            // Close sidebar
            function closeSidebar() {
                sidebar.classList.remove('active');
                document.body.classList.remove('sidebar-open');
            }

            if (sidebarClose) {
                sidebarClose.addEventListener('click', closeSidebar);
            }

            if (sidebarOverlay) {
                sidebarOverlay.addEventListener('click', closeSidebar);
            }

            // Close sidebar when clicking navigation links (but not dropdown toggles)
            const sidebarLinks = document.querySelectorAll('.mobile-sidebar-nav .mobile-nav-link:not(.mobile-dropdown-toggle)');
            sidebarLinks.forEach(link => {
                link.addEventListener('click', function() {
                    setTimeout(closeSidebar, 300);
                });
            });

            // Close sidebar on escape key
            document.addEventListener('keydown', function(e) {
                if (e.key === 'Escape' && sidebar.classList.contains('active')) {
                    closeSidebar();
                }
            });

            // Mobile dropdown toggle
            const dropdownToggles = document.querySelectorAll('.mobile-dropdown-toggle');
            dropdownToggles.forEach(toggle => {
                toggle.addEventListener('click', function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    const dropdown = this.closest('.mobile-dropdown');
                    dropdown.classList.toggle('active');
                });
            });

            // Close dropdown and sidebar when clicking dropdown links
            const dropdownLinks = document.querySelectorAll('.mobile-dropdown-link');
            dropdownLinks.forEach(link => {
                link.addEventListener('click', function() {
                    // Close the dropdown menu
                    const dropdown = this.closest('.mobile-dropdown');
                    if (dropdown) {
                        dropdown.classList.remove('active');
                    }
                    // Close the sidebar
                    setTimeout(closeSidebar, 300);
                });
            });
        });
    </script>
    <?php endif; ?>

