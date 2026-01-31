<?php
session_start();
require_once '../includes/config.php';
require_once __DIR__ . '/../includes/database.php';
require_once __DIR__ . '/../includes/auth.php';
$pageTitle = 'PROWLWAY Admin Panel';
$hideHeader = true;
$bodyClass = 'admin-panel-page';
include '../includes/header.php';

// Check if logged in
$isLoggedIn = isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'];
$adminRole = $isLoggedIn ? (getAdminRole() ?? '') : '';
$canPublish = $isLoggedIn && canPublish();
$isSuperAdmin = $isLoggedIn && isSuperAdmin();
?>
<script>
    // Make ADMIN_URL and role available to JavaScript
    window.ADMIN_URL = '<?php echo ADMIN_URL; ?>';
    window.ADMIN_ROLE = '<?php echo htmlspecialchars($adminRole); ?>';
    window.ADMIN_CAN_PUBLISH = <?php echo $canPublish ? 'true' : 'false'; ?>;
    window.ADMIN_IS_SUPER = <?php echo $isSuperAdmin ? 'true' : 'false'; ?>;
    window.CSRF_TOKEN = '<?php echo function_exists("generateCSRFToken") ? generateCSRFToken() : ""; ?>';
    
    // Force light theme on page load
    document.addEventListener('DOMContentLoaded', function() {
        // Ensure body has admin class
        document.body.classList.add('admin-panel-page');
        
        // Force light background
        document.body.style.background = 'linear-gradient(to bottom right, #f8fafc, #e0e7ff, #e9d5ff)';
        document.body.style.color = '#1f2937';
        document.documentElement.style.background = 'linear-gradient(to bottom right, #f8fafc, #e0e7ff, #e9d5ff)';
        
        // Hide any dark containers
        const darkContainers = document.querySelectorAll('.admin-container, .admin-header:not(.dashboard-header), .admin-content, .container:not(.admin-panel-wrapper):not(.max-w-7xl):not(.max-w-md):not(.w-full)');
        darkContainers.forEach(el => {
            el.style.display = 'none';
            el.style.visibility = 'hidden';
        });
        
        // Hide site header if present
        const siteHeader = document.querySelector('.site-header');
        if (siteHeader) {
            siteHeader.style.display = 'none';
        }
    });
</script>

<div class="admin-panel-wrapper min-h-screen bg-gradient-to-br from-slate-50 via-blue-50 to-indigo-50">
    <!-- Login Page -->
    <div class="login-page <?php echo (!$isLoggedIn) ? 'flex' : 'hidden'; ?> min-h-screen items-center justify-center p-4" id="loginPage">
        <div class="w-full max-w-md">
            <!-- Logo & Branding -->
            <div class="text-center mb-8">
                <div class="inline-flex items-center justify-center w-20 h-20 bg-gradient-to-br from-indigo-600 via-purple-600 to-pink-600 rounded-2xl shadow-2xl mb-6 transform hover:scale-105 transition-transform duration-300">
                    <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2.5">
                        <path d="M12 2L2 7l10 5 10-5-10-5z"></path>
                        <path d="M2 17l10 5 10-5"></path>
                        <path d="M2 12l10 5 10-5"></path>
                    </svg>
                </div>
                <h1 class="text-4xl font-bold bg-gradient-to-r from-indigo-600 via-purple-600 to-pink-600 bg-clip-text text-transparent mb-2">
                    PROWLWAY
                </h1>
                <p class="text-gray-600 text-lg font-medium">Admin Control Panel</p>
                <p class="text-gray-500 text-sm mt-1">Manage your ICDISG Archive Website</p>
            </div>

            <!-- Login Card -->
            <div class="bg-white rounded-2xl shadow-2xl p-8 border border-gray-100">
                <div class="mb-6">
                    <h2 class="text-2xl font-bold text-gray-900 mb-2">Welcome Back</h2>
                    <p class="text-gray-600">Sign in to access the admin panel</p>
                </div>
                
                <!-- Alert -->
                <div id="loginAlert" class="mb-6">
                    <?php if (isset($_SESSION['login_error'])): ?>
                        <div class="flex items-center gap-3 p-4 bg-red-50 border-l-4 border-red-500 rounded-lg animate-slide-in">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="text-red-600 flex-shrink-0">
                                <circle cx="12" cy="12" r="10"></circle>
                                <line x1="12" y1="8" x2="12" y2="12"></line>
                                <line x1="12" y1="16" x2="12.01" y2="16"></line>
                            </svg>
                            <span class="font-medium text-red-800"><?php echo $_SESSION['login_error']; unset($_SESSION['login_error']); ?></span>
                        </div>
                    <?php endif; ?>
                </div>
                
                <!-- Login Form -->
                <form id="loginForm" method="POST" action="<?php echo ADMIN_URL; ?>/login.php" class="space-y-5">
                    <?php if (function_exists('generateCSRFToken')): ?>
                    <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                    <?php endif; ?>
                    <div>
                        <label for="email" class="block text-sm font-semibold text-gray-700 mb-2">
                            Email Address
                        </label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                <svg class="h-5 w-5 text-gray-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path>
                                    <polyline points="22,6 12,13 2,6"></polyline>
                                </svg>
                            </div>
                            <input 
                                type="email" 
                                id="email" 
                                name="email" 
                                value="admin@icdisg.ph" 
                                required 
                                class="block w-full pl-12 pr-4 py-3.5 border-2 border-gray-200 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all duration-200 bg-gray-50 focus:bg-white"
                                placeholder="admin@icdisg.ph"
                            >
                        </div>
                    </div>
                    
                    <div>
                        <label for="password" class="block text-sm font-semibold text-gray-700 mb-2">
                            Password
                        </label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                <svg class="h-5 w-5 text-gray-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect>
                                    <path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
                                </svg>
                            </div>
                            <input 
                                type="password" 
                                id="password" 
                                name="password" 
                                required 
                                class="block w-full pl-12 pr-4 py-3.5 border-2 border-gray-200 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all duration-200 bg-gray-50 focus:bg-white"
                                placeholder="Enter your password"
                            >
                        </div>
                    </div>
                    
                    <button 
                        type="submit" 
                        class="w-full flex items-center justify-center gap-3 bg-gradient-to-r from-indigo-600 via-purple-600 to-pink-600 text-white font-bold py-4 px-6 rounded-xl hover:from-indigo-700 hover:via-purple-700 hover:to-pink-700 focus:outline-none focus:ring-4 focus:ring-indigo-300 transition-all duration-200 shadow-lg hover:shadow-xl transform hover:-translate-y-0.5"
                    >
                        <span>Sign In</span>
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                            <path d="M5 12h14M12 5l7 7-7 7"></path>
                        </svg>
                    </button>
                </form>

                <!-- Footer -->
                <div class="mt-6 pt-6 border-t border-gray-200">
                    <p class="text-center text-xs text-gray-500">
                        © <?php echo date('Y'); ?> PROWLWAY. All rights reserved.
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- Dashboard Page -->
    <div class="dashboard-page <?php echo $isLoggedIn ? 'block' : 'hidden'; ?>" id="dashboardPage">
        <!-- Top Navigation Bar -->
        <div class="bg-white border-b border-gray-200 shadow-sm sticky top-0 z-50">
            <div class="max-w-7xl mx-auto px-6 py-4">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-4">
                        <div class="w-10 h-10 bg-gradient-to-br from-indigo-600 via-purple-600 to-pink-600 rounded-xl flex items-center justify-center shadow-lg">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2.5">
                                <path d="M12 2L2 7l10 5 10-5-10-5z"></path>
                                <path d="M2 17l10 5 10-5"></path>
                                <path d="M2 12l10 5 10-5"></path>
                            </svg>
                        </div>
                        <div>
                            <h1 class="text-xl font-bold text-gray-900">PROWLWAY Admin</h1>
                            <p class="text-xs text-gray-500">Control Panel</p>
                        </div>
                    </div>
                    
                    <div class="flex items-center gap-4">
                        <a 
                            href="<?php echo PUBLIC_URL; ?>/home.php" 
                            target="_blank" 
                            class="flex items-center gap-2 px-4 py-2 text-gray-700 hover:text-indigo-600 font-medium transition-colors"
                        >
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6M15 3h6v6M10 14L21 3"></path>
                            </svg>
                            <span>View Site</span>
                        </a>
                        
                        <div class="h-8 w-px bg-gray-300"></div>
                        
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 bg-gradient-to-br from-indigo-500 to-purple-600 rounded-full flex items-center justify-center shadow-md">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2">
                                    <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                                    <circle cx="12" cy="7" r="4"></circle>
                                </svg>
                            </div>
                            <div>
                                <p class="text-sm font-semibold text-gray-900"><?php echo isset($_SESSION['admin_name']) ? htmlspecialchars($_SESSION['admin_name']) : 'Admin'; ?></p>
                                <p class="text-xs text-gray-500" id="userEmail"><?php echo isset($_SESSION['admin_email']) ? htmlspecialchars($_SESSION['admin_email']) : ''; ?></p>
                            </div>
                        </div>
                        
                        <a 
                            href="<?php echo ADMIN_URL; ?>/logout.php" 
                            class="flex items-center gap-2 px-4 py-2 bg-red-50 text-red-600 rounded-lg hover:bg-red-100 font-medium transition-colors"
                        >
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4M16 17l5-5-5-5M21 12H9"></path>
                            </svg>
                            <span>Logout</span>
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Content -->
        <div class="max-w-7xl mx-auto px-6 py-8">
            <!-- Alert Container -->
            <div id="dashboardAlert" class="mb-6"></div>

            <!-- Quick Stats / Quick Links -->
            <?php
            // Calculate pending count for Review Queue (only for admins/super admins)
            $pendingCount = 0;
            if ($canPublish) {
                $pendingCount = dbFetchOne("SELECT COUNT(*) as count FROM documents WHERE status = 'pending_review'")['count'] ?? 0;
                $pendingCount += dbFetchOne("SELECT COUNT(*) as count FROM announcements WHERE status = 'pending_review'")['count'] ?? 0;
                $pendingCount += dbFetchOne("SELECT COUNT(*) as count FROM events WHERE status = 'pending_review'")['count'] ?? 0;
            }
            
            ?>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                <!-- Review Queue - Admin and Super Admin only -->
                <?php if ($canPublish): ?>
                <a href="<?php echo ADMIN_URL; ?>/review_queue.php" class="group bg-white rounded-xl p-6 border-2 border-gray-200 hover:border-indigo-500 hover:shadow-lg transition-all duration-200 relative">
                    <?php if ($pendingCount > 0): ?>
                    <span class="absolute top-2 right-2 bg-yellow-500 text-white text-xs font-bold rounded-full w-6 h-6 flex items-center justify-center"><?php echo $pendingCount; ?></span>
                    <?php endif; ?>
                    <div class="flex items-center gap-4">
                        <div class="w-14 h-14 bg-gradient-to-br from-yellow-500 to-yellow-600 rounded-xl flex items-center justify-center group-hover:scale-110 transition-transform duration-200 shadow-md">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2">
                                <path d="M9 11l3 3L22 4"></path>
                                <path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"></path>
                            </svg>
                        </div>
                        <div class="flex-1">
                            <h3 class="font-bold text-gray-900 group-hover:text-indigo-600 transition-colors">Review Queue</h3>
                            <p class="text-sm text-gray-500"><?php echo $pendingCount; ?> pending</p>
                        </div>
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="text-gray-400 group-hover:text-indigo-600 transition-colors">
                            <path d="M5 12h14M12 5l7 7-7 7"></path>
                        </svg>
                    </div>
                </a>
                <?php endif; ?>
                <a href="<?php echo ADMIN_URL; ?>/batches.php" class="group bg-white rounded-xl p-6 border-2 border-gray-200 hover:border-indigo-500 hover:shadow-lg transition-all duration-200">
                    <div class="flex items-center gap-4">
                        <div class="w-14 h-14 bg-gradient-to-br from-blue-500 to-blue-600 rounded-xl flex items-center justify-center group-hover:scale-110 transition-transform duration-200 shadow-md">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2">
                                <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                                <circle cx="9" cy="7" r="4"></circle>
                                <path d="M23 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75"></path>
                            </svg>
                        </div>
                        <div class="flex-1">
                            <h3 class="font-bold text-gray-900 group-hover:text-indigo-600 transition-colors">Manage Batches</h3>
                            <p class="text-sm text-gray-500">Student batches</p>
                        </div>
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="text-gray-400 group-hover:text-indigo-600 transition-colors">
                            <path d="M5 12h14M12 5l7 7-7 7"></path>
                        </svg>
                    </div>
                </a>
                
                <a href="<?php echo ADMIN_URL; ?>/organizations.php" class="group bg-white rounded-xl p-6 border-2 border-gray-200 hover:border-indigo-500 hover:shadow-lg transition-all duration-200">
                    <div class="flex items-center gap-4">
                        <div class="w-14 h-14 bg-gradient-to-br from-pink-500 to-pink-600 rounded-xl flex items-center justify-center group-hover:scale-110 transition-transform duration-200 shadow-md">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2">
                                <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                                <circle cx="9" cy="7" r="4"></circle>
                                <path d="M23 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75"></path>
                            </svg>
                        </div>
                        <div class="flex-1">
                            <h3 class="font-bold text-gray-900 group-hover:text-indigo-600 transition-colors">Manage Organizations</h3>
                            <p class="text-sm text-gray-500">Student orgs</p>
                        </div>
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="text-gray-400 group-hover:text-indigo-600 transition-colors">
                            <path d="M5 12h14M12 5l7 7-7 7"></path>
                        </svg>
                    </div>
                </a>
                
                <!-- Institute Content - Admin and Super Admin only -->
                <?php if ($canPublish): ?>
                <a href="<?php echo ADMIN_URL; ?>/institute.php" class="group bg-white rounded-xl p-6 border-2 border-gray-200 hover:border-indigo-500 hover:shadow-lg transition-all duration-200">
                    <div class="flex items-center gap-4">
                        <div class="w-14 h-14 bg-gradient-to-br from-teal-500 to-teal-600 rounded-xl flex items-center justify-center group-hover:scale-110 transition-transform duration-200 shadow-md">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2">
                                <path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"></path>
                                <path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"></path>
                            </svg>
                        </div>
                        <div class="flex-1">
                            <h3 class="font-bold text-gray-900 group-hover:text-indigo-600 transition-colors">Institute Content</h3>
                            <p class="text-sm text-gray-500">About, Mission, Vision, etc.</p>
                        </div>
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="text-gray-400 group-hover:text-indigo-600 transition-colors">
                            <path d="M5 12h14M12 5l7 7-7 7"></path>
                        </svg>
                    </div>
                </a>
                <?php endif; ?>
                <!-- Archive Management - Admin and Super Admin only -->
                <?php if ($canPublish): ?>
                <a href="<?php echo ADMIN_URL; ?>/archive_management.php" class="group bg-white rounded-xl p-6 border-2 border-gray-200 hover:border-indigo-500 hover:shadow-lg transition-all duration-200">
                    <div class="flex items-center gap-4">
                        <div class="w-14 h-14 bg-gradient-to-br from-gray-500 to-gray-600 rounded-xl flex items-center justify-center group-hover:scale-110 transition-transform duration-200 shadow-md">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2">
                                <path d="M5 8h14M5 8a2 2 0 1 0 0-4h14a2 2 0 1 0 0 4M5 8v10a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V8m-9 4h4"></path>
                            </svg>
                        </div>
                        <div class="flex-1">
                            <h3 class="font-bold text-gray-900 group-hover:text-indigo-600 transition-colors">Archive Management</h3>
                            <p class="text-sm text-gray-500">Bulk archive & restore</p>
                        </div>
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="text-gray-400 group-hover:text-indigo-600 transition-colors">
                            <path d="M5 12h14M12 5l7 7-7 7"></path>
                        </svg>
                    </div>
                </a>
                <!-- Audit Log - Admin and Super Admin only -->
                <a href="<?php echo ADMIN_URL; ?>/audit_log.php" class="group bg-white rounded-xl p-6 border-2 border-gray-200 hover:border-indigo-500 hover:shadow-lg transition-all duration-200">
                    <div class="flex items-center gap-4">
                        <div class="w-14 h-14 bg-gradient-to-br from-slate-500 to-slate-600 rounded-xl flex items-center justify-center group-hover:scale-110 transition-transform duration-200 shadow-md">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2">
                                <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                                <polyline points="14 2 14 8 20 8"></polyline>
                                <line x1="16" y1="13" x2="8" y2="13"></line>
                                <line x1="16" y1="17" x2="8" y2="17"></line>
                            </svg>
                        </div>
                        <div class="flex-1">
                            <h3 class="font-bold text-gray-900 group-hover:text-indigo-600 transition-colors">Audit Log</h3>
                            <p class="text-sm text-gray-500">Security &amp; activity</p>
                        </div>
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="text-gray-400 group-hover:text-indigo-600 transition-colors">
                            <path d="M5 12h14M12 5l7 7-7 7"></path>
                        </svg>
                    </div>
                </a>
                <?php endif; ?>
                <!-- Settings - Super Admin only -->
                <?php if ($isSuperAdmin): ?>
                <a href="<?php echo ADMIN_URL; ?>/settings.php" class="group bg-white rounded-xl p-6 border-2 border-gray-200 hover:border-indigo-500 hover:shadow-lg transition-all duration-200">
                    <div class="flex items-center gap-4">
                        <div class="w-14 h-14 bg-gradient-to-br from-amber-500 to-amber-600 rounded-xl flex items-center justify-center group-hover:scale-110 transition-transform duration-200 shadow-md">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2">
                                <circle cx="12" cy="12" r="3"></circle>
                                <path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z"></path>
                            </svg>
                        </div>
                        <div class="flex-1">
                            <h3 class="font-bold text-gray-900 group-hover:text-indigo-600 transition-colors">Settings</h3>
                            <p class="text-sm text-gray-500">Site config (Super Admin)</p>
                        </div>
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="text-gray-400 group-hover:text-indigo-600 transition-colors">
                            <path d="M5 12h14M12 5l7 7-7 7"></path>
                        </svg>
                    </div>
                </a>
                <!-- User roles - Super Admin only -->
                <a href="<?php echo ADMIN_URL; ?>/users.php" class="group bg-white rounded-xl p-6 border-2 border-gray-200 hover:border-indigo-500 hover:shadow-lg transition-all duration-200">
                    <div class="flex items-center gap-4">
                        <div class="w-14 h-14 bg-gradient-to-br from-violet-500 to-violet-600 rounded-xl flex items-center justify-center group-hover:scale-110 transition-transform duration-200 shadow-md">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2">
                                <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                                <circle cx="9" cy="7" r="4"></circle>
                                <path d="M23 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75"></path>
                            </svg>
                        </div>
                        <div class="flex-1">
                            <h3 class="font-bold text-gray-900 group-hover:text-indigo-600 transition-colors">User roles</h3>
                            <p class="text-sm text-gray-500">Admins &amp; roles (Super Admin)</p>
                        </div>
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="text-gray-400 group-hover:text-indigo-600 transition-colors">
                            <path d="M5 12h14M12 5l7 7-7 7"></path>
                        </svg>
                    </div>
                </a>
                <?php endif; ?>
            </div>

            <!-- Tabs Navigation -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 mb-6">
                <div class="border-b border-gray-200">
                    <nav class="flex" aria-label="Tabs">
                        <button 
                            onclick="switchTab('announcements')" 
                            class="tab-button active px-8 py-4 text-sm font-bold text-indigo-600 border-b-3 border-indigo-600 hover:text-indigo-700 transition-colors relative"
                        >
                            <span class="flex items-center gap-2">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                                    <polyline points="14 2 14 8 20 8"></polyline>
                                    <line x1="16" y1="13" x2="8" y2="13"></line>
                                    <line x1="16" y1="17" x2="8" y2="17"></line>
                                </svg>
                                Announcements
                            </span>
                        </button>
                        <button 
                            onclick="switchTab('events')" 
                            class="tab-button px-8 py-4 text-sm font-bold text-gray-600 border-b-3 border-transparent hover:text-gray-900 hover:border-gray-300 transition-colors"
                        >
                            <span class="flex items-center gap-2">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
                                    <line x1="16" y1="2" x2="16" y2="6"></line>
                                    <line x1="8" y1="2" x2="8" y2="6"></line>
                                    <line x1="3" y1="10" x2="21" y2="10"></line>
                                </svg>
                                Events
                            </span>
                        </button>
                        <button 
                            onclick="switchTab('calendar')" 
                            class="tab-button px-8 py-4 text-sm font-bold text-gray-600 border-b-3 border-transparent hover:text-gray-900 hover:border-gray-300 transition-colors"
                        >
                            <span class="flex items-center gap-2">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
                                    <line x1="16" y1="2" x2="16" y2="6"></line>
                                    <line x1="8" y1="2" x2="8" y2="6"></line>
                                    <line x1="3" y1="10" x2="21" y2="10"></line>
                                </svg>
                                Calendar
                            </span>
                        </button>
                        <button 
                            onclick="switchTab('documents')" 
                            id="documentsTabBtn" 
                            class="tab-button px-8 py-4 text-sm font-bold text-gray-600 border-b-3 border-transparent hover:text-gray-900 hover:border-gray-300 transition-colors"
                        >
                            <span class="flex items-center gap-2">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                                    <polyline points="14 2 14 8 20 8"></polyline>
                                </svg>
                                Documents
                            </span>
                        </button>
                        <button 
                            onclick="switchTab('inquiries')" 
                            class="tab-button px-8 py-4 text-sm font-bold text-gray-600 border-b-3 border-transparent hover:text-gray-900 hover:border-gray-300 transition-colors"
                        >
                            <span class="flex items-center gap-2">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path>
                                    <polyline points="22,6 12,13 2,6"></polyline>
                                </svg>
                                Inquiries
                            </span>
                        </button>
                        <?php if ($canPublish): ?>
                        <button 
                            onclick="switchTab('sponsors')" 
                            class="tab-button px-8 py-4 text-sm font-bold text-gray-600 border-b-3 border-transparent hover:text-gray-900 hover:border-gray-300 transition-colors"
                        >
                            <span class="flex items-center gap-2">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"></path>
                                    <polyline points="3.27 6.96 12 12.01 20.73 6.96"></polyline>
                                    <line x1="12" y1="22.08" x2="12" y2="12"></line>
                                </svg>
                                Sponsors
                            </span>
                        </button>
                        <?php endif; ?>
                    </nav>
                </div>

                <!-- Tab Content Container -->
                <div class="p-6">
                    <!-- Announcements Tab -->
                    <div class="tab-content block" id="announcements-tab">
                        <div class="flex justify-between items-center mb-6">
                            <div>
                                <h2 class="text-2xl font-bold text-gray-900">Announcements</h2>
                                <p class="text-sm text-gray-500 mt-1">Manage site announcements and updates</p>
                            </div>
                            <button 
                                onclick="resetAndShowAnnouncementForm()" 
                                class="flex items-center gap-2 px-5 py-3 bg-gradient-to-r from-indigo-600 to-purple-600 text-white font-bold rounded-xl hover:from-indigo-700 hover:to-purple-700 transition-all duration-200 shadow-lg hover:shadow-xl transform hover:-translate-y-0.5"
                            >
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                                    <line x1="12" y1="5" x2="12" y2="19"></line>
                                    <line x1="5" y1="12" x2="19" y2="12"></line>
                                </svg>
                                <span>Create New</span>
                            </button>
                        </div>

                        <!-- Announcement Form -->
                        <div id="announcement-form" class="hidden bg-gradient-to-br from-gray-50 to-white border-2 border-gray-200 rounded-2xl p-8 mb-6 shadow-lg">
                            <h3 id="announcement-form-title" class="text-xl font-bold text-gray-900 mb-6 flex items-center gap-3">
                                <span class="w-1.5 h-8 bg-gradient-to-b from-indigo-500 via-purple-500 to-pink-500 rounded-full"></span>
                                Create Announcement
                            </h3>
                            <form id="announcementForm" method="POST" action="<?php echo ADMIN_URL; ?>/announcements.php" enctype="multipart/form-data" class="space-y-6">
                                <input type="hidden" id="ann-id" name="id">
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div>
                                        <label for="ann-title" class="block text-sm font-bold text-gray-700 mb-2">Title *</label>
                                        <input type="text" id="ann-title" name="title" required class="w-full px-4 py-3.5 border-2 border-gray-200 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all duration-200 bg-white">
                                    </div>
                                    <div>
                                        <label for="ann-category" class="block text-sm font-bold text-gray-700 mb-2">Category *</label>
                                        <select id="ann-category" name="category" required class="w-full px-4 py-3.5 border-2 border-gray-200 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all duration-200 bg-white">
                                            <option value="general">General</option>
                                            <option value="academic">Academic</option>
                                            <option value="event">Event</option>
                                            <option value="maintenance">Maintenance</option>
                                            <option value="urgent">Urgent</option>
                                        </select>
                                    </div>
                                </div>
                                <div>
                                    <label for="ann-description" class="block text-sm font-bold text-gray-700 mb-2">Short Description *</label>
                                    <textarea id="ann-description" name="description" required rows="3" class="w-full px-4 py-3.5 border-2 border-gray-200 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all duration-200 bg-white resize-y"></textarea>
                                </div>
                                <div>
                                    <label for="ann-content" class="block text-sm font-bold text-gray-700 mb-2">Full Content *</label>
                                    <textarea id="ann-content" name="content" required rows="6" class="w-full px-4 py-3.5 border-2 border-gray-200 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all duration-200 bg-white resize-y"></textarea>
                                </div>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div>
                                        <label for="ann-image" class="block text-sm font-bold text-gray-700 mb-2">
                                            Image <span id="ann-image-required-indicator" class="text-red-600 hidden">*</span>
                                            <span class="text-gray-500 font-normal" id="ann-image-optional-text">(optional)</span>
                                        </label>
                                        <div id="ann-image-preview" class="hidden mb-3">
                                            <img id="ann-image-preview-img" src="" alt="Preview" class="max-w-xs max-h-48 rounded-xl border-2 border-gray-200 shadow-md">
                                            <input type="hidden" id="ann-old-image" name="old_image">
                                        </div>
                                        <input type="file" id="ann-image" name="image" accept="image/jpeg,image/jpg,image/png,image/gif,image/webp" class="block w-full text-sm text-gray-500 file:mr-4 file:py-2.5 file:px-4 file:rounded-xl file:border-0 file:text-sm file:font-bold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100 transition-colors">
                                        <p class="mt-2 text-xs text-gray-500">
                                            <span id="ann-image-help-text">Max size: 5MB. Formats: JPEG, PNG, GIF, WebP</span>
                                            <span id="ann-image-required-text" class="text-red-600 font-semibold hidden"> • Required for published or pinned announcements</span>
                                        </p>
                                    </div>
                                    <div>
                                        <label for="ann-pdf" class="block text-sm font-bold text-gray-700 mb-2">
                                            PDF File <span class="text-gray-500 font-normal">(optional)</span>
                                        </label>
                                        <div id="ann-pdf-preview" class="hidden mb-3">
                                            <div class="flex items-center gap-2 p-3 bg-gray-50 rounded-xl border-2 border-gray-200">
                                                <svg class="w-8 h-8 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                                                </svg>
                                                <div class="flex-1">
                                                    <p id="ann-pdf-filename" class="text-sm font-semibold text-gray-700"></p>
                                                    <p class="text-xs text-gray-500">PDF Document</p>
                                                </div>
                                            </div>
                                            <input type="hidden" id="ann-old-pdf" name="old_pdf">
                                        </div>
                                        <input type="file" id="ann-pdf" name="pdf_file" accept=".pdf,application/pdf" class="block w-full text-sm text-gray-500 file:mr-4 file:py-2.5 file:px-4 file:rounded-xl file:border-0 file:text-sm file:font-bold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100 transition-colors">
                                        <p class="mt-2 text-xs text-gray-500">Max size: 50MB. Format: PDF</p>
                                    </div>
                                    <div class="flex items-center">
                                        <label class="flex items-center gap-3 cursor-pointer">
                                            <input type="checkbox" id="ann-pinned" name="pinned" class="w-5 h-5 text-indigo-600 border-2 border-gray-300 rounded focus:ring-indigo-500">
                                            <span class="text-sm font-semibold text-gray-700">Pin this announcement</span>
                                        </label>
                                    </div>
                                    <div id="ann-status-wrap">
                                        <label for="ann-status" class="block text-sm font-bold text-gray-700 mb-2">Status</label>
                                        <select id="ann-status" name="status" class="w-full px-4 py-3.5 border-2 border-gray-200 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all duration-200 bg-white">
                                            <option value="draft">Draft</option>
                                            <option value="pending_review">Pending Review</option>
                                            <option value="approved">Approved</option>
                                            <option value="published">Published</option>
                                            <option value="archived">Archived</option>
                                        </select>
                                        <p class="mt-1 text-xs text-gray-500" id="ann-status-hint">Editors can save as Draft or submit for Review. Administrators can approve and publish.</p>
                                    </div>
                                </div>
                                
                                <!-- Meeting Section -->
                                <div class="border-t-2 border-gray-200 pt-6 mt-6">
                                    <div class="flex items-center mb-4">
                                        <label class="flex items-center gap-3 cursor-pointer">
                                            <input type="checkbox" id="ann-is-meeting" name="is_meeting" class="w-5 h-5 text-indigo-600 border-2 border-gray-300 rounded focus:ring-indigo-500" onchange="toggleMeetingFields()">
                                            <span class="text-sm font-bold text-gray-700">This is a meeting that everyone needs to attend</span>
                                        </label>
                                    </div>
                                    <p class="text-xs text-gray-500 mb-4">If checked, this announcement will appear in the calendar and be highlighted as a required meeting.</p>
                                    
                                    <div id="ann-meeting-fields" class="hidden grid grid-cols-1 md:grid-cols-2 gap-6">
                                        <div>
                                            <label for="ann-meeting-date" class="block text-sm font-bold text-gray-700 mb-2">Meeting Date & Time *</label>
                                            <input type="datetime-local" id="ann-meeting-date" name="meeting_date" class="w-full px-4 py-3.5 border-2 border-gray-200 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all duration-200 bg-white">
                                        </div>
                                        <div>
                                            <label for="ann-meeting-end-date" class="block text-sm font-bold text-gray-700 mb-2">End Date & Time (optional)</label>
                                            <input type="datetime-local" id="ann-meeting-end-date" name="meeting_end_date" class="w-full px-4 py-3.5 border-2 border-gray-200 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all duration-200 bg-white">
                                            <p class="mt-1 text-xs text-gray-500">For multi-day or extended meetings</p>
                                        </div>
                                        <div class="md:col-span-2">
                                            <label for="ann-meeting-location" class="block text-sm font-bold text-gray-700 mb-2">Meeting Location</label>
                                            <input type="text" id="ann-meeting-location" name="meeting_location" placeholder="e.g., Main Auditorium, Room 201, Online via Zoom" class="w-full px-4 py-3.5 border-2 border-gray-200 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all duration-200 bg-white">
                                        </div>
                                    </div>
                                </div>
                                <div class="flex gap-3 pt-4">
                                    <button type="submit" class="px-8 py-3.5 bg-gradient-to-r from-indigo-600 to-purple-600 text-white font-bold rounded-xl hover:from-indigo-700 hover:to-purple-700 transition-all duration-200 shadow-lg hover:shadow-xl transform hover:-translate-y-0.5" id="ann-submit-btn">Create Announcement</button>
                                    <button type="button" onclick="cancelAnnouncementEdit()" class="px-8 py-3.5 bg-gray-100 text-gray-700 font-bold rounded-xl hover:bg-gray-200 transition-colors">Cancel</button>
                                </div>
                            </form>
                        </div>

                        <!-- Announcements List -->
                        <div id="announcements-list" class="bg-white border-2 border-gray-200 rounded-xl p-6 min-h-[200px]">
                            <p class="text-gray-500 text-center py-8">Loading announcements...</p>
                        </div>
                    </div>

                    <!-- Events Tab -->
                    <div class="tab-content hidden" id="events-tab">
                        <div class="flex justify-between items-center mb-6">
                            <div>
                                <h2 class="text-2xl font-bold text-gray-900">Events</h2>
                                <p class="text-sm text-gray-500 mt-1">Manage events and galleries</p>
                            </div>
                            <button 
                                onclick="toggleForm('event-form')" 
                                class="flex items-center gap-2 px-5 py-3 bg-gradient-to-r from-indigo-600 to-purple-600 text-white font-bold rounded-xl hover:from-indigo-700 hover:to-purple-700 transition-all duration-200 shadow-lg hover:shadow-xl transform hover:-translate-y-0.5"
                            >
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                                    <line x1="12" y1="5" x2="12" y2="19"></line>
                                    <line x1="5" y1="12" x2="19" y2="12"></line>
                                </svg>
                                <span>Create New</span>
                            </button>
                        </div>

                        <!-- Event Form -->
                        <div id="event-form" class="hidden bg-gradient-to-br from-gray-50 to-white border-2 border-gray-200 rounded-2xl p-8 mb-6 shadow-lg">
                            <h3 id="event-form-title" class="text-xl font-bold text-gray-900 mb-6 flex items-center gap-3">
                                <span class="w-1.5 h-8 bg-gradient-to-b from-indigo-500 via-purple-500 to-pink-500 rounded-full"></span>
                                Create Event
                            </h3>
                            <form id="eventForm" method="POST" action="<?php echo ADMIN_URL; ?>/events_handler.php" enctype="multipart/form-data" class="space-y-6">
                                <?php if (function_exists('generateCSRFToken')): ?>
                                <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                                <?php endif; ?>
                                <input type="hidden" id="evt-id" name="id">
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div>
                                        <label for="evt-title" class="block text-sm font-bold text-gray-700 mb-2">Title *</label>
                                        <input type="text" id="evt-title" name="title" required class="w-full px-4 py-3.5 border-2 border-gray-200 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all duration-200 bg-white" onblur="clearFieldError('evt-title')">
                                    </div>
                                    <div>
                                        <label for="evt-category" class="block text-sm font-bold text-gray-700 mb-2">Category *</label>
                                        <select id="evt-category" name="category" required class="w-full px-4 py-3.5 border-2 border-gray-200 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all duration-200 bg-white">
                                            <option value="workshop">Workshop</option>
                                            <option value="seminar">Seminar</option>
                                            <option value="service">Service</option>
                                            <option value="celebration">Celebration</option>
                                            <option value="other">Other</option>
                                        </select>
                                    </div>
                                    <div>
                                        <label for="evt-schedule-type" class="block text-sm font-bold text-gray-700 mb-2">What happens this day</label>
                                        <select id="evt-schedule-type" name="schedule_type" class="w-full px-4 py-3.5 border-2 border-gray-200 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all duration-200 bg-white">
                                            <option value="event">Event / Activity</option>
                                            <option value="enrollment">Enrollment</option>
                                            <option value="school_break">School Break</option>
                                            <option value="school_end">School End</option>
                                            <option value="start_of_classes">Start of Classes</option>
                                            <option value="exam_period">Exam Period</option>
                                        </select>
                                        <p class="mt-1 text-xs text-gray-500">Shows on Institute Calendar (e.g. &quot;Enrollment&quot;, &quot;School Break&quot;)</p>
                                    </div>
                                </div>
                                <div>
                                    <label for="evt-caption" class="block text-sm font-bold text-gray-700 mb-2">Caption *</label>
                                    <input type="text" id="evt-caption" name="caption" required class="w-full px-4 py-3.5 border-2 border-gray-200 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all duration-200 bg-white" onblur="clearFieldError('evt-caption')">
                                </div>
                                <div>
                                    <label for="evt-description" class="block text-sm font-bold text-gray-700 mb-2">Description *</label>
                                    <textarea id="evt-description" name="description" required rows="5" class="w-full px-4 py-3.5 border-2 border-gray-200 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all duration-200 bg-white resize-y"></textarea>
                                </div>
                                <div>
                                    <label for="evt-summary" class="block text-sm font-bold text-gray-700 mb-2">Event Summary</label>
                                    <textarea id="evt-summary" name="summary" placeholder="Short highlight that appears in the event details modal" rows="3" class="w-full px-4 py-3.5 border-2 border-gray-200 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all duration-200 bg-white resize-y"></textarea>
                                    <p class="mt-2 text-xs text-gray-500">Optional. Keep it to 1-2 concise sentences.</p>
                                </div>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div>
                                        <label for="evt-image" class="block text-sm font-bold text-gray-700 mb-2">Image *</label>
                                        <div id="evt-image-preview" class="hidden mb-3">
                                            <img id="evt-image-preview-img" src="" alt="Preview" class="max-w-xs max-h-48 rounded-xl border-2 border-gray-200 shadow-md">
                                            <input type="hidden" id="evt-old-image" name="old_image">
                                        </div>
                                        <input type="file" id="evt-image" name="image" accept="image/jpeg,image/jpg,image/png,image/gif,image/webp" class="block w-full text-sm text-gray-500 file:mr-4 file:py-2.5 file:px-4 file:rounded-xl file:border-0 file:text-sm file:font-bold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100 transition-colors" onchange="clearFieldError('evt-image')">
                                        <p class="mt-2 text-xs text-gray-500">Max size: 5MB. Formats: JPEG, PNG, GIF, WebP</p>
                                    </div>
                                    <div>
                                        <label for="evt-location" class="block text-sm font-bold text-gray-700 mb-2">Location</label>
                                        <input type="text" id="evt-location" name="location" class="w-full px-4 py-3.5 border-2 border-gray-200 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all duration-200 bg-white">
                                    </div>
                                </div>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div>
                                        <label for="evt-date" class="block text-sm font-bold text-gray-700 mb-2">Start Date *</label>
                                        <input type="date" id="evt-date" name="date" required class="w-full px-4 py-3.5 border-2 border-gray-200 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all duration-200 bg-white" onblur="clearFieldError('evt-date')">
                                    </div>
                                    <div>
                                        <label for="evt-end-date" class="block text-sm font-bold text-gray-700 mb-2">End Date (Optional)</label>
                                        <input type="date" id="evt-end-date" name="end_date" class="w-full px-4 py-3.5 border-2 border-gray-200 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all duration-200 bg-white" onblur="clearFieldError('evt-end-date')">
                                        <p class="mt-1 text-xs text-gray-500">Leave empty for single-day events</p>
                                    </div>
                                </div>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div>
                                        <label for="evt-order" class="block text-sm font-bold text-gray-700 mb-2">Display Order</label>
                                        <input type="number" id="evt-order" name="order" value="0" class="w-full px-4 py-3.5 border-2 border-gray-200 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all duration-200 bg-white">
                                    </div>
                                </div>
                                <div>
                                    <label for="evt-gallery" class="block text-sm font-bold text-gray-700 mb-2">Gallery Images (multiple)</label>
                                    <input type="file" id="evt-gallery" name="gallery[]" accept="image/jpeg,image/jpg,image/png,image/gif,image/webp" multiple class="block w-full text-sm text-gray-500 file:mr-4 file:py-2.5 file:px-4 file:rounded-xl file:border-0 file:text-sm file:font-bold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100 transition-colors">
                                    <p class="mt-2 text-xs text-gray-500">Select multiple images. Existing gallery images are kept when you add more. Max 5MB per image.</p>
                                    <div id="evt-gallery-preview" class="mt-4 grid grid-cols-2 md:grid-cols-4 lg:grid-cols-6 gap-3"></div>
                                    <input type="hidden" id="evt-old-gallery" name="old_gallery">
                                </div>
                                <div id="evt-status-wrap">
                                    <label for="evt-status" class="block text-sm font-bold text-gray-700 mb-2">Status</label>
                                    <select id="evt-status" name="status" class="w-full px-4 py-3.5 border-2 border-gray-200 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all duration-200 bg-white">
                                        <option value="draft">Draft</option>
                                        <option value="pending_review">Pending Review</option>
                                        <option value="approved">Approved</option>
                                        <option value="published">Published</option>
                                        <option value="archived">Archived</option>
                                    </select>
                                    <p class="mt-1 text-xs text-gray-500" id="evt-status-hint">Editors can save as Draft or submit for Review. Administrators can approve and publish.</p>
                                </div>
                                <div class="flex gap-3 pt-4">
                                    <button type="submit" class="px-8 py-3.5 bg-gradient-to-r from-indigo-600 to-purple-600 text-white font-bold rounded-xl hover:from-indigo-700 hover:to-purple-700 transition-all duration-200 shadow-lg hover:shadow-xl transform hover:-translate-y-0.5" id="evt-submit-btn">Create Event</button>
                                    <button type="button" onclick="cancelEventEdit()" class="px-8 py-3.5 bg-gray-100 text-gray-700 font-bold rounded-xl hover:bg-gray-200 transition-colors">Cancel</button>
                                </div>
                            </form>
                        </div>

                        <!-- Events List -->
                        <div id="events-list" class="bg-white border-2 border-gray-200 rounded-xl p-6 min-h-[200px]">
                            <p class="text-gray-500 text-center py-8">Loading events...</p>
                        </div>
                    </div>

                    <!-- Calendar (Holidays) Tab -->
                    <div class="tab-content hidden" id="calendar-tab">
                        <div class="flex justify-between items-center mb-6">
                            <div>
                                <h2 class="text-2xl font-bold text-gray-900">Calendar</h2>
                                <p class="text-sm text-gray-500 mt-1">Add enrollment day, wellness break, Christmas break (from–to), year end, school end, or PH/Dasma holidays</p>
                            </div>
                            <button 
                                onclick="toggleForm('holiday-form')" 
                                class="flex items-center gap-2 px-5 py-3 bg-gradient-to-r from-indigo-600 to-purple-600 text-white font-bold rounded-xl hover:from-indigo-700 hover:to-purple-700 transition-all duration-200 shadow-lg hover:shadow-xl transform hover:-translate-y-0.5"
                            >
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                                    <line x1="12" y1="5" x2="12" y2="19"></line>
                                    <line x1="5" y1="12" x2="19" y2="12"></line>
                                </svg>
                                <span>Add Calendar Entry</span>
                            </button>
                        </div>

                        <!-- Calendar Entry Form -->
                        <div id="holiday-form" class="hidden bg-gradient-to-br from-gray-50 to-white border-2 border-gray-200 rounded-2xl p-8 mb-6 shadow-lg">
                            <h3 id="holiday-form-title" class="text-xl font-bold text-gray-900 mb-6 flex items-center gap-3">
                                <span class="w-1.5 h-8 bg-gradient-to-b from-indigo-500 via-purple-500 to-pink-500 rounded-full"></span>
                                Add Calendar Entry
                            </h3>
                            <form id="holidayForm" method="POST" action="<?php echo ADMIN_URL; ?>/holidays_handler.php" class="space-y-6">
                                <?php if (function_exists('generateCSRFToken')): ?>
                                <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                                <?php endif; ?>
                                <input type="hidden" id="hol-id" name="id">
                                <input type="hidden" id="hol-action" name="action" value="create">
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div>
                                        <label for="hol-date" class="block text-sm font-bold text-gray-700 mb-2">Start date *</label>
                                        <input type="date" id="hol-date" name="date" required class="w-full px-4 py-3.5 border-2 border-gray-200 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all duration-200 bg-white">
                                    </div>
                                    <div>
                                        <label for="hol-end-date" class="block text-sm font-bold text-gray-700 mb-2">End date (optional)</label>
                                        <input type="date" id="hol-end-date" name="end_date" class="w-full px-4 py-3.5 border-2 border-gray-200 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all duration-200 bg-white">
                                        <p class="mt-1 text-xs text-gray-500">For multi-day (e.g. Christmas break from–to, wellness break for a week)</p>
                                    </div>
                                    <div class="md:col-span-2">
                                        <label for="hol-name" class="block text-sm font-bold text-gray-700 mb-2">Name *</label>
                                        <input type="text" id="hol-name" name="name" required placeholder="e.g. Enrollment Day, Christmas Break, Wellness Break" class="w-full px-4 py-3.5 border-2 border-gray-200 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all duration-200 bg-white">
                                    </div>
                                    <div>
                                        <label for="hol-type" class="block text-sm font-bold text-gray-700 mb-2">Type</label>
                                        <select id="hol-type" name="type" class="w-full px-4 py-3.5 border-2 border-gray-200 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all duration-200 bg-white">
                                            <optgroup label="School calendar">
                                                <option value="school">School (enter type below)</option>
                                                <option value="enrollment">Enrollment day</option>
                                                <option value="start_of_school">Start of school</option>
                                                <option value="wellness_break">Wellness break</option>
                                                <option value="christmas_break">Christmas break</option>
                                                <option value="year_end">Year end</option>
                                                <option value="school_end">School end</option>
                                            </optgroup>
                                            <optgroup label="PH / Dasma">
                                                <option value="regular">Regular Holiday</option>
                                                <option value="special_non_working">Special Non-Working Day</option>
                                                <option value="special_working">Special Working Day</option>
                                                <option value="dasma">Dasma (Dasmariñas)</option>
                                            </optgroup>
                                        </select>
                                    </div>
                                    <div class="md:col-span-2 hidden" id="hol-type-label-wrap">
                                        <label for="hol-type-label" id="hol-type-label-label" class="block text-sm font-bold text-gray-700 mb-2">Event type</label>
                                        <input type="text" id="hol-type-label" name="type_label" maxlength="255" placeholder="e.g. Enrollment, Wellness break, Finals week, Start of school" class="w-full px-4 py-3.5 border-2 border-gray-200 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all duration-200 bg-white">
                                        <p class="mt-1 text-xs text-gray-500">When &quot;School (enter type below)&quot; is selected, enter the type here. For other school types this overrides the default label.</p>
                                    </div>
                                    <div>
                                        <label for="hol-region" class="block text-sm font-bold text-gray-700 mb-2">Region</label>
                                        <select id="hol-region" name="region" class="w-full px-4 py-3.5 border-2 border-gray-200 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all duration-200 bg-white">
                                            <option value="PH">Philippines (PH)</option>
                                            <option value="Dasma">Dasmariñas (Dasma)</option>
                                        </select>
                                    </div>
                                    <div class="md:col-span-2" id="hol-description-wrap">
                                        <label for="hol-description" id="hol-description-label" class="block text-sm font-bold text-gray-700 mb-2">Description (optional)</label>
                                        <textarea id="hol-description" name="description" rows="3" placeholder="e.g. Enrollment day details, wellness break info" class="w-full px-4 py-3.5 border-2 border-gray-200 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all duration-200 bg-white resize-y"></textarea>
                                        <p id="hol-description-hint" class="mt-1 text-xs text-gray-500 hidden">For school calendar: describe what the event is (e.g. Enrollment for Grade 11, Wellness break for all students).</p>
                                    </div>
                                </div>
                                <div class="flex gap-3 pt-4">
                                    <button type="submit" class="px-8 py-3.5 bg-gradient-to-r from-indigo-600 to-purple-600 text-white font-bold rounded-xl hover:from-indigo-700 hover:to-purple-700 transition-all duration-200 shadow-lg hover:shadow-xl transform hover:-translate-y-0.5" id="hol-submit-btn">Add Calendar Entry</button>
                                    <button type="button" onclick="cancelHolidayEdit()" class="px-8 py-3.5 bg-gray-100 text-gray-700 font-bold rounded-xl hover:bg-gray-200 transition-colors">Cancel</button>
                                </div>
                            </form>
                        </div>

                        <!-- Holidays List: menu bar + content -->
                        <div class="bg-white border-2 border-gray-200 rounded-xl overflow-hidden">
                            <nav id="holidays-list-menu" class="hidden px-4 py-3 bg-gray-100 border-b border-gray-200 flex flex-wrap items-center gap-2" aria-label="Jump to calendar section">
                                <span class="text-sm font-semibold text-gray-600 mr-2">Jump to:</span>
                                <a href="#calendar-section-holidays" class="calendar-list-jump px-3 py-1.5 text-sm font-medium text-amber-800 bg-amber-100 rounded-lg hover:bg-amber-200 transition-colors">Holidays (PH / Dasma)</a>
                                <a href="#calendar-section-school" class="calendar-list-jump px-3 py-1.5 text-sm font-medium text-indigo-800 bg-indigo-100 rounded-lg hover:bg-indigo-200 transition-colors">School calendar</a>
                            </nav>
                            <div id="holidays-list" class="p-6 min-h-[200px]">
                                <p class="text-gray-500 text-center py-8">Loading calendar...</p>
                            </div>
                        </div>
                    </div>

                    <!-- Documents Tab -->
                    <div class="tab-content hidden" id="documents-tab">
                        <div class="flex justify-between items-center mb-6">
                            <div>
                                <h2 class="text-2xl font-bold text-gray-900">Documents</h2>
                                <p class="text-sm text-gray-500 mt-1">Manage document archives</p>
                            </div>
                            <button 
                                id="createDocBtn" 
                                onclick="toggleForm('document-form')" 
                                class="flex items-center gap-2 px-5 py-3 bg-gradient-to-r from-indigo-600 to-purple-600 text-white font-bold rounded-xl hover:from-indigo-700 hover:to-purple-700 transition-all duration-200 shadow-lg hover:shadow-xl transform hover:-translate-y-0.5"
                            >
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                                    <line x1="12" y1="5" x2="12" y2="19"></line>
                                    <line x1="5" y1="12" x2="19" y2="12"></line>
                                </svg>
                                <span>Create New</span>
                            </button>
                        </div>

                        <!-- Document Form -->
                        <div id="document-form" class="hidden bg-gradient-to-br from-gray-50 to-white border-2 border-gray-200 rounded-2xl p-8 mb-6 shadow-lg">
                            <h3 id="document-form-title" class="text-xl font-bold text-gray-900 mb-6 flex items-center gap-3">
                                <span class="w-1.5 h-8 bg-gradient-to-b from-indigo-500 via-purple-500 to-pink-500 rounded-full"></span>
                                Create Document Entry
                            </h3>
                            <form id="documentForm" method="POST" action="<?php echo ADMIN_URL; ?>/documents.php" enctype="multipart/form-data" class="space-y-6">
                                <?php if (function_exists('generateCSRFToken')): ?>
                                <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                                <?php endif; ?>
                                <input type="hidden" id="doc-id" name="id">
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div>
                                        <label for="doc-title" class="block text-sm font-bold text-gray-700 mb-2">Title *</label>
                                        <input type="text" id="doc-title" name="title" required class="w-full px-4 py-3.5 border-2 border-gray-200 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all duration-200 bg-white" onblur="clearFieldError('doc-title')">
                                    </div>
                                    <div>
                                        <label for="doc-category" class="block text-sm font-bold text-gray-700 mb-2">Category *</label>
                                        <select id="doc-category" name="category" required class="w-full px-4 py-3.5 border-2 border-gray-200 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all duration-200 bg-white" onchange="updateDocumentSubcategory()">
                                            <option value="01">01 - OFFICES REPORT</option>
                                            <option value="02">02 - EXECUTIVE ORDER</option>
                                            <option value="03">03 - ORDINANCE</option>
                                            <option value="04">04 - RESOLUTION</option>
                                            <option value="05">05 - OTHER</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6" id="doc-subcategory-group">
                                    <div>
                                        <label for="doc-subcategory" class="block text-sm font-bold text-gray-700 mb-2">Subcategory *</label>
                                        <select id="doc-subcategory" name="subcategory" required class="w-full px-4 py-3.5 border-2 border-gray-200 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all duration-200 bg-white">
                                            <option value="">Select subcategory...</option>
                                        </select>
                                        <p class="mt-1 text-xs text-gray-500">Select a category first to load subcategories</p>
                                    </div>
                                    <div id="doc-document-type-group" style="display: none;">
                                        <label for="doc-document-type" class="block text-sm font-bold text-gray-700 mb-2">Document Type</label>
                                        <select id="doc-document-type" name="document_type" class="w-full px-4 py-3.5 border-2 border-gray-200 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all duration-200 bg-white">
                                            <option value="">Select type...</option>
                                            <option value="executive_order">Executive Order</option>
                                            <option value="administrative_order">Administrative Order</option>
                                            <option value="memorandum">Memorandum</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div>
                                        <label for="doc-series-year" class="block text-sm font-bold text-gray-700 mb-2">Series Year</label>
                                        <input type="text" id="doc-series-year" name="series_year" placeholder="e.g., 2025" class="w-full px-4 py-3.5 border-2 border-gray-200 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all duration-200 bg-white">
                                    </div>
                                    <div>
                                        <label for="doc-academic-year" class="block text-sm font-bold text-gray-700 mb-2">Academic Year</label>
                                        <input type="text" id="doc-academic-year" name="academic_year" placeholder="e.g., 2024-2025" class="w-full px-4 py-3.5 border-2 border-gray-200 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all duration-200 bg-white" onblur="if(this.value && !validateAcademicYear(this.value)) { showFieldError('doc-academic-year', 'Invalid format. Use YYYY-YYYY'); } else { clearFieldError('doc-academic-year'); }">
                                    </div>
                                </div>
                                <div>
                                    <label for="doc-description" class="block text-sm font-bold text-gray-700 mb-2">Description</label>
                                    <textarea id="doc-description" name="description" rows="4" class="w-full px-4 py-3.5 border-2 border-gray-200 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all duration-200 bg-white resize-y"></textarea>
                                </div>
                                
                                <!-- File Upload Section -->
                                <div>
                                    <label for="doc-file" class="block text-sm font-bold text-gray-700 mb-2">Upload PDF File (Max 50MB) *</label>
                                    <input type="file" id="doc-file" name="file" accept=".pdf" class="block w-full text-sm text-gray-500 file:mr-4 file:py-2.5 file:px-4 file:rounded-xl file:border-0 file:text-sm file:font-bold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100 transition-colors" onchange="clearFieldError('doc-file')">
                                    <p class="mt-2 text-xs text-gray-500">Choose a PDF file to upload (Maximum size: 50MB)</p>
                                    <div id="upload-progress" class="hidden mt-4">
                                        <div class="bg-gray-200 rounded-xl overflow-hidden h-6">
                                            <div id="progress-bar" class="bg-gradient-to-r from-indigo-500 to-purple-600 h-full transition-all duration-300 rounded-xl" style="width: 0%;"></div>
                                        </div>
                                        <p id="progress-text" class="mt-2 text-xs text-gray-600 font-medium"></p>
                                    </div>
                                </div>
                                <div id="doc-status-wrap">
                                    <label for="doc-status" class="block text-sm font-bold text-gray-700 mb-2">Status</label>
                                    <select id="doc-status" name="status" class="w-full px-4 py-3.5 border-2 border-gray-200 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all duration-200 bg-white">
                                        <option value="draft">Draft</option>
                                        <option value="pending_review">Pending Review</option>
                                        <option value="approved">Approved</option>
                                        <option value="published">Published</option>
                                        <option value="archived">Archived</option>
                                    </select>
                                    <p class="mt-1 text-xs text-gray-500" id="doc-status-hint">Editors can save as Draft or submit for Review. Administrators can approve and publish.</p>
                                </div>
                                <div class="flex gap-3 pt-4">
                                    <button type="submit" class="px-8 py-3.5 bg-gradient-to-r from-indigo-600 to-purple-600 text-white font-bold rounded-xl hover:from-indigo-700 hover:to-purple-700 transition-all duration-200 shadow-lg hover:shadow-xl transform hover:-translate-y-0.5" id="doc-submit-btn">Create Document</button>
                                    <button type="button" onclick="cancelDocumentEdit()" class="px-8 py-3.5 bg-gray-100 text-gray-700 font-bold rounded-xl hover:bg-gray-200 transition-colors">Cancel</button>
                                </div>
                            </form>
                        </div>

                        <!-- Documents List -->
                        <div id="documents-list" class="bg-white border-2 border-gray-200 rounded-xl p-6 min-h-[200px]">
                            <p class="text-gray-500 text-center py-8">Loading documents...</p>
                        </div>

                        <!-- Subcategories Management Section -->
                        <div class="mt-8 bg-gradient-to-br from-gray-50 to-white border-2 border-gray-200 rounded-2xl p-8 shadow-lg">
                            <div class="flex items-center justify-between mb-6">
                                <h3 class="text-xl font-bold text-gray-900 flex items-center gap-3">
                                    <span class="w-1.5 h-8 bg-gradient-to-b from-indigo-500 via-purple-500 to-pink-500 rounded-full"></span>
                                    Manage Subcategories
                                </h3>
                                <button onclick="toggleForm('subcategory-form')" class="flex items-center gap-2 px-6 py-3 bg-gradient-to-r from-indigo-600 to-purple-600 text-white font-bold rounded-xl hover:from-indigo-700 hover:to-purple-700 transition-all duration-200 shadow-lg hover:shadow-xl transform hover:-translate-y-0.5">
                                    <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                                        <path d="M12 5v14M5 12h14"></path>
                                    </svg>
                                    <span>Add Subcategory</span>
                                </button>
                            </div>

                            <!-- Subcategory Form -->
                            <div id="subcategory-form" class="hidden mb-6 bg-white border border-gray-200 rounded-xl p-6">
                                <h4 id="subcategory-form-title" class="text-lg font-bold text-gray-900 mb-4">Add New Subcategory</h4>
                                <form id="subcategoryForm" class="space-y-4">
                                    <?php if (function_exists('generateCSRFToken')): ?>
                                    <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                                    <?php endif; ?>
                                    <input type="hidden" id="subcat-id" name="id">
                                    <input type="hidden" id="subcat-action" name="action" value="create">
                                    
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        <div>
                                            <label for="subcat-category" class="block text-sm font-semibold text-gray-700 mb-2">Category *</label>
                                            <select id="subcat-category" name="category" required class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                                                <option value="">Select category...</option>
                                                <option value="01">01 - OFFICES REPORT</option>
                                                <option value="02">02 - EXECUTIVE ORDER</option>
                                                <option value="03">03 - ORDINANCE</option>
                                                <option value="04">04 - RESOLUTION</option>
                                                <option value="05">05 - OTHER</option>
                                            </select>
                                        </div>
                                        <div>
                                            <label for="subcat-name" class="block text-sm font-semibold text-gray-700 mb-2">Subcategory Name *</label>
                                            <input type="text" id="subcat-name" name="name" required maxlength="100" placeholder="e.g., OTP, OVIA, OVPEA" class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                                        </div>
                                    </div>
                                    
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        <div>
                                            <label for="subcat-status" class="block text-sm font-semibold text-gray-700 mb-2">Status</label>
                                            <select id="subcat-status" name="status" class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                                                <option value="active">Active</option>
                                                <option value="inactive">Inactive</option>
                                            </select>
                                        </div>
                                        <div>
                                            <label for="subcat-order" class="block text-sm font-semibold text-gray-700 mb-2">Display Order</label>
                                            <input type="number" id="subcat-order" name="display_order" value="0" min="0" class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                                        </div>
                                    </div>
                                    
                                    <div class="flex gap-3 pt-2">
                                        <button type="submit" id="subcat-submit-btn" class="px-8 py-3.5 bg-gradient-to-r from-indigo-600 to-purple-600 text-white font-bold rounded-xl hover:from-indigo-700 hover:to-purple-700 transition-all duration-200 shadow-lg hover:shadow-xl transform hover:-translate-y-0.5">Create Subcategory</button>
                                        <button type="button" onclick="cancelSubcategoryEdit()" class="px-8 py-3.5 bg-gray-100 text-gray-700 font-bold rounded-xl hover:bg-gray-200 transition-colors">Cancel</button>
                                    </div>
                                </form>
                            </div>

                            <!-- Subcategories List -->
                            <div id="subcategories-list" class="bg-white border border-gray-200 rounded-xl p-6 min-h-[200px]">
                                <p class="text-gray-500 text-center py-8">Select a category to view subcategories</p>
                            </div>
                        </div>
                    </div>

                    <!-- Inquiries Tab -->
                    <div class="tab-content hidden" id="inquiries-tab">
                        <div class="mb-6">
                            <h2 class="text-2xl font-bold text-gray-900">Contact Inquiries</h2>
                            <p class="text-sm text-gray-500 mt-1">View and respond to messages from the contact form (Editors: view only; Administrators: respond and close)</p>
                        </div>
                        <div id="inquiries-list" class="bg-white border-2 border-gray-200 rounded-xl p-6 min-h-[200px]">
                            <p class="text-gray-500 text-center py-8">Loading inquiries...</p>
                        </div>
                    </div>

                    <!-- Sponsors Tab -->
                    <?php if ($canPublish): ?>
                    <div class="tab-content hidden" id="sponsors-tab">
                        <div class="flex justify-between items-center mb-6">
                            <div>
                                <h2 class="text-2xl font-bold text-gray-900">Sponsors / Ads</h2>
                                <p class="text-sm text-gray-500 mt-1">Manage sponsors and advertisements shown in popup</p>
                            </div>
                            <button 
                                onclick="toggleForm('sponsor-form')" 
                                class="flex items-center gap-2 px-5 py-3 bg-gradient-to-r from-indigo-600 to-purple-600 text-white font-bold rounded-xl hover:from-indigo-700 hover:to-purple-700 transition-all duration-200 shadow-lg hover:shadow-xl transform hover:-translate-y-0.5"
                            >
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                                    <line x1="12" y1="5" x2="12" y2="19"></line>
                                    <line x1="5" y1="12" x2="19" y2="12"></line>
                                </svg>
                                <span>Add Sponsor</span>
                            </button>
                        </div>

                        <!-- Sponsor Form -->
                        <div id="sponsor-form" class="hidden bg-gradient-to-br from-gray-50 to-white border-2 border-gray-200 rounded-2xl p-8 mb-6 shadow-lg">
                            <h3 id="sponsor-form-title" class="text-xl font-bold text-gray-900 mb-6 flex items-center gap-3">
                                <span class="w-1.5 h-8 bg-gradient-to-b from-indigo-500 via-purple-500 to-pink-500 rounded-full"></span>
                                <span>Add New Sponsor</span>
                            </h3>
                            <form id="sponsorForm" method="POST" enctype="multipart/form-data" onsubmit="submitSponsor(event)">
                                <input type="hidden" name="action" value="create" id="sponsor-action">
                                <input type="hidden" name="id" id="sponsor-id">
                                <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                                <input type="hidden" name="old_image" id="sponsor-old-image">
                                
                                <div class="space-y-6">
                                    <div>
                                        <label for="sponsor-title" class="block text-sm font-bold text-gray-700 mb-2">Title *</label>
                                        <input type="text" id="sponsor-title" name="title" required class="w-full px-4 py-3.5 border-2 border-gray-200 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all duration-200 bg-white" onblur="clearFieldError('sponsor-title')">
                                    </div>
                                    
                                    <div>
                                        <label for="sponsor-link-url" class="block text-sm font-bold text-gray-700 mb-2">Link URL (Optional)</label>
                                        <input type="url" id="sponsor-link-url" name="link_url" placeholder="https://example.com" class="w-full px-4 py-3.5 border-2 border-gray-200 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all duration-200 bg-white">
                                        <p class="mt-1 text-xs text-gray-500">If provided, clicking the sponsor image will open this URL</p>
                                    </div>
                                    
                                    <div>
                                        <label for="sponsor-image" class="block text-sm font-bold text-gray-700 mb-2">Image *</label>
                                        <input type="file" id="sponsor-image" name="image" accept="image/jpeg,image/jpg,image/png,image/gif,image/webp" required class="block w-full text-sm text-gray-500 file:mr-4 file:py-2.5 file:px-4 file:rounded-xl file:border-0 file:text-sm file:font-bold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100 transition-colors" onchange="clearFieldError('sponsor-image')">
                                        <p class="mt-2 text-xs text-gray-500">Max size: 5MB. Formats: JPEG, PNG, GIF, WebP.</p>
                                        <div id="sponsor-image-preview" class="mt-4"></div>
                                    </div>
                                    
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                        <div>
                                            <label for="sponsor-order" class="block text-sm font-bold text-gray-700 mb-2">Display Order</label>
                                            <input type="number" id="sponsor-order" name="display_order" value="0" min="0" class="w-full px-4 py-3.5 border-2 border-gray-200 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all duration-200 bg-white">
                                        </div>
                                        <div>
                                            <label class="flex items-center gap-3 cursor-pointer mt-8">
                                                <input type="checkbox" name="active" value="1" id="sponsor-active" checked class="w-5 h-5 text-indigo-600 border-2 border-gray-300 rounded">
                                                <span class="text-sm font-bold text-gray-700">Active (show in popup)</span>
                                            </label>
                                        </div>
                                    </div>
                                    
                                    <div class="flex gap-3 pt-4">
                                        <button type="submit" class="px-8 py-3.5 bg-gradient-to-r from-indigo-600 to-purple-600 text-white font-bold rounded-xl hover:from-indigo-700 hover:to-purple-700 transition-all duration-200 shadow-lg hover:shadow-xl transform hover:-translate-y-0.5" id="sponsor-submit-btn">Create Sponsor</button>
                                        <button type="button" onclick="cancelSponsorEdit()" class="px-8 py-3.5 bg-gray-100 text-gray-700 font-bold rounded-xl hover:bg-gray-200 transition-colors">Cancel</button>
                                    </div>
                                </div>
                            </form>
                        </div>

                        <!-- Sponsors List -->
                        <div id="sponsors-list" class="bg-white border-2 border-gray-200 rounded-xl p-6 min-h-[200px]">
                            <p class="text-gray-500 text-center py-8">Loading sponsors...</p>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Inquiry Respond Modal (Administrators only) -->
    <div id="inquiry-respond-modal" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50">
        <div class="bg-white rounded-2xl shadow-xl max-w-lg w-full p-6">
            <h3 class="text-xl font-bold text-gray-900 mb-4">Respond to inquiry</h3>
            <form id="inquiry-respond-form" onsubmit="submitInquiryResponse(event)">
                <input type="hidden" id="inquiry-respond-id" name="id">
                <div class="mb-4">
                    <label for="inquiry-respond-text" class="block text-sm font-bold text-gray-700 mb-2">Response</label>
                    <textarea id="inquiry-respond-text" name="response_text" rows="4" class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500" placeholder="Your response to the sender..."></textarea>
                </div>
                <div class="mb-4">
                    <label for="inquiry-respond-status" class="block text-sm font-bold text-gray-700 mb-2">Status</label>
                    <select id="inquiry-respond-status" name="status" class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                        <option value="closed">Closed</option>
                        <option value="open">Keep open</option>
                        <option value="archived">Archived</option>
                    </select>
                </div>
                <div class="flex gap-3">
                    <button type="submit" class="px-6 py-3 bg-indigo-600 text-white font-bold rounded-xl hover:bg-indigo-700">Save response</button>
                    <button type="button" onclick="closeInquiryRespondModal()" class="px-6 py-3 bg-gray-200 text-gray-700 font-bold rounded-xl hover:bg-gray-300">Cancel</button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
/* ============================================
   CRITICAL ADMIN PANEL STYLES - FORCE LIGHT THEME
   These styles MUST override all dark theme CSS
   ============================================ */

/* Force light background on body and html */
html.admin-panel-page,
body.admin-panel-page,
html body.admin-panel-page {
    background: linear-gradient(to bottom right, #f8fafc, #e0e7ff, #e9d5ff) !important;
    background-attachment: fixed !important;
    color: #1f2937 !important;
    margin: 0 !important;
    padding: 0 !important;
    min-height: 100vh !important;
}

/* Hide all old admin container styles */
body.admin-panel-page .admin-container,
body.admin-panel-page .admin-header:not(.dashboard-header),
body.admin-panel-page .admin-content,
body.admin-panel-page .container:not(.admin-panel-wrapper):not(.max-w-7xl):not(.max-w-md),
body.admin-panel-page .header:not(.site-header),
body.admin-panel-page .content {
    display: none !important;
    visibility: hidden !important;
}

/* Ensure admin wrapper is visible and light */
.admin-panel-wrapper {
    display: block !important;
    width: 100% !important;
    min-height: 100vh !important;
    background: linear-gradient(to bottom right, #f8fafc, #e0e7ff, #e9d5ff) !important;
    color: #1f2937 !important;
    margin: 0 !important;
    padding: 0 !important;
}

/* Force light colors on all text */
body.admin-panel-page,
body.admin-panel-page * {
    color: inherit !important;
}

body.admin-panel-page .text-gray-900,
body.admin-panel-page .text-gray-800,
body.admin-panel-page .text-gray-700 {
    color: #111827 !important;
}

body.admin-panel-page .text-gray-600 {
    color: #4b5563 !important;
}

body.admin-panel-page .text-gray-500 {
    color: #6b7280 !important;
}

/* Force white backgrounds */
body.admin-panel-page .bg-white {
    background-color: #ffffff !important;
}

body.admin-panel-page .bg-gray-50 {
    background-color: #f9fafb !important;
}

body.admin-panel-page .bg-gray-100 {
    background-color: #f3f4f6 !important;
}

/* Override any dark borders */
body.admin-panel-page .border-gray-200,
body.admin-panel-page .border-gray-300 {
    border-color: #e5e7eb !important;
}

/* Ensure site header is hidden */
body.admin-panel-page .site-header {
    display: none !important;
    visibility: hidden !important;
}

@keyframes slide-in {
    from {
        opacity: 0;
        transform: translateY(-10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}
.animate-slide-in {
    animation: slide-in 0.3s ease-out;
}
.border-b-3 {
    border-bottom-width: 3px;
}
</style>

<?php include '../includes/footer.php'; ?>
