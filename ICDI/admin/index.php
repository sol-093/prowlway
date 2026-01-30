<?php
session_start();
require_once '../includes/config.php';
$pageTitle = 'PROWLWAY Admin Panel';
$hideHeader = true;
$bodyClass = 'admin-panel-page';
include '../includes/header.php';

// Check if logged in
$isLoggedIn = isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'];
?>
<script>
    // Make ADMIN_URL available to JavaScript
    window.ADMIN_URL = '<?php echo ADMIN_URL; ?>';
    
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

                    <div class="flex items-center justify-between pt-2">
                        <label class="flex items-center">
                            <input type="checkbox" class="w-4 h-4 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500">
                            <span class="ml-2 text-sm text-gray-600">Remember me</span>
                        </label>
                        <a href="#" class="text-sm font-medium text-indigo-600 hover:text-indigo-700 transition-colors">
                            Forgot password?
                        </a>
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
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
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
                                onclick="toggleForm('announcement-form')" 
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
                                        <label for="ann-image" class="block text-sm font-bold text-gray-700 mb-2">Image (optional)</label>
                                        <div id="ann-image-preview" class="hidden mb-3">
                                            <img id="ann-image-preview-img" src="" alt="Preview" class="max-w-xs max-h-48 rounded-xl border-2 border-gray-200 shadow-md">
                                            <input type="hidden" id="ann-old-image" name="old_image">
                                        </div>
                                        <input type="file" id="ann-image" name="image" accept="image/jpeg,image/jpg,image/png,image/gif,image/webp" class="block w-full text-sm text-gray-500 file:mr-4 file:py-2.5 file:px-4 file:rounded-xl file:border-0 file:text-sm file:font-bold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100 transition-colors">
                                        <p class="mt-2 text-xs text-gray-500">Max size: 5MB. Formats: JPEG, PNG, GIF, WebP</p>
                                    </div>
                                    <div class="flex items-center">
                                        <label class="flex items-center gap-3 cursor-pointer">
                                            <input type="checkbox" id="ann-pinned" name="pinned" class="w-5 h-5 text-indigo-600 border-2 border-gray-300 rounded focus:ring-indigo-500">
                                            <span class="text-sm font-semibold text-gray-700">Pin this announcement</span>
                                        </label>
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
                                <input type="hidden" id="evt-id" name="id">
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div>
                                        <label for="evt-title" class="block text-sm font-bold text-gray-700 mb-2">Title *</label>
                                        <input type="text" id="evt-title" name="title" required class="w-full px-4 py-3.5 border-2 border-gray-200 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all duration-200 bg-white">
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
                                    <input type="text" id="evt-caption" name="caption" required class="w-full px-4 py-3.5 border-2 border-gray-200 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all duration-200 bg-white">
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
                                        <input type="file" id="evt-image" name="image" accept="image/jpeg,image/jpg,image/png,image/gif,image/webp" class="block w-full text-sm text-gray-500 file:mr-4 file:py-2.5 file:px-4 file:rounded-xl file:border-0 file:text-sm file:font-bold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100 transition-colors">
                                        <p class="mt-2 text-xs text-gray-500">Max size: 5MB. Formats: JPEG, PNG, GIF, WebP</p>
                                    </div>
                                    <div>
                                        <label for="evt-location" class="block text-sm font-bold text-gray-700 mb-2">Location</label>
                                        <input type="text" id="evt-location" name="location" class="w-full px-4 py-3.5 border-2 border-gray-200 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all duration-200 bg-white">
                                    </div>
                                </div>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div>
                                        <label for="evt-date" class="block text-sm font-bold text-gray-700 mb-2">Event Date *</label>
                                        <input type="date" id="evt-date" name="date" required class="w-full px-4 py-3.5 border-2 border-gray-200 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all duration-200 bg-white">
                                    </div>
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
                                <input type="hidden" id="doc-id" name="id">
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div>
                                        <label for="doc-title" class="block text-sm font-bold text-gray-700 mb-2">Title *</label>
                                        <input type="text" id="doc-title" name="title" required class="w-full px-4 py-3.5 border-2 border-gray-200 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all duration-200 bg-white">
                                    </div>
                                    <div>
                                        <label for="doc-category" class="block text-sm font-bold text-gray-700 mb-2">Category *</label>
                                        <select id="doc-category" name="category" required class="w-full px-4 py-3.5 border-2 border-gray-200 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all duration-200 bg-white">
                                            <option value="01">01 - OFFICES REPORT</option>
                                            <option value="02">02 - EXECUTIVE ORDER</option>
                                            <option value="03">03 - ORDINANCE</option>
                                            <option value="04">04 - RESOLUTION</option>
                                            <option value="05">05 - OTHER</option>
                                        </select>
                                    </div>
                                </div>
                                <div>
                                    <label for="doc-description" class="block text-sm font-bold text-gray-700 mb-2">Description</label>
                                    <textarea id="doc-description" name="description" rows="4" class="w-full px-4 py-3.5 border-2 border-gray-200 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all duration-200 bg-white resize-y"></textarea>
                                </div>
                                
                                <!-- File Upload Section -->
                                <div>
                                    <label for="doc-file" class="block text-sm font-bold text-gray-700 mb-2">Upload PDF File (Max 50MB) *</label>
                                    <input type="file" id="doc-file" name="file" accept=".pdf" class="block w-full text-sm text-gray-500 file:mr-4 file:py-2.5 file:px-4 file:rounded-xl file:border-0 file:text-sm file:font-bold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100 transition-colors">
                                    <p class="mt-2 text-xs text-gray-500">Choose a PDF file to upload (Maximum size: 50MB)</p>
                                    <div id="upload-progress" class="hidden mt-4">
                                        <div class="bg-gray-200 rounded-xl overflow-hidden h-6">
                                            <div id="progress-bar" class="bg-gradient-to-r from-indigo-500 to-purple-600 h-full transition-all duration-300 rounded-xl" style="width: 0%;"></div>
                                        </div>
                                        <p id="progress-text" class="mt-2 text-xs text-gray-600 font-medium"></p>
                                    </div>
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
                    </div>
                </div>
            </div>
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
