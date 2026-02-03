<?php
require_once '../includes/config.php';
require_once '../includes/database.php';
require_once '../includes/upload.php';

$bodyClass = 'origin-page';
$pageTitle = 'Data Science - Program - PROWLWAY';
include '../includes/header.php';

// Fetch organizations list for sidebar directory
$organizationsList = dbFetchAll("SELECT * FROM student_organizations WHERE status = 'active' ORDER BY display_order ASC");

// Fetch faculty subcategories for sidebar
$facultySubcategories = dbFetchAll("SELECT * FROM institute_sections WHERE type = 'faculty_subcategory' AND status = 'published' ORDER BY display_order ASC, title ASC");
?>

<div class="origin-page-container">
    <!-- Main Content Area -->
    <div class="origin-main-content">
        <div class="origin-content-panel">
            <div class="batches-inner-panel batch-detail-inner-panel">
                <div class="mb-4">
                    <a href="<?php echo PUBLIC_URL; ?>/institute.php?section=about" class="text-sm text-black inline-flex items-center gap-1">
                        ← Back to Institute
                    </a>
                </div>
                
                <!-- Page title -->
                <div class="mb-6 md:mb-8">
                    <h1 class="text-2xl md:text-3xl font-bold text-gray-900 px-2 md:px-0">Data Science</h1>
                </div>

                <!-- Program Content (Coming Soon) -->
                <section class="batch-detail-section mt-8 md:mt-12 lg:mt-16 px-2 md:px-0">
                    <h2 class="batch-section-heading mb-3 md:mb-4 text-xl md:text-2xl lg:text-[32px]">Coming Soon</h2>
                    <div class="text-sm md:text-base text-black leading-relaxed max-w-3xl mx-auto text-center">
                        <p class="mb-2 font-semibold">We’re preparing the Data Science program details.</p>
                        <p>Please check back soon for the full overview, career outlook, and program requirements.</p>
                    </div>
                </section>
            </div>
        </div>
    </div>

    <!-- Sidebar / Directory -->
    <aside class="origin-sidebar">
        <div class="sidebar-panel">
            <!-- INSTITUTE Section -->
            <div class="sidebar-section">
                <h2 class="sidebar-title">INSTITUTE</h2>
                <ul class="sidebar-links">
                    <li><a href="<?php echo PUBLIC_URL; ?>/institute.php?section=about">About</a></li>
                    <li class="org-item-with-batch">
                        <span class="sidebar-label cursor-default">Faculty Unit</span>
                        <!-- Subcategories - Show below Faculty Unit on hover -->
                        <?php if (!empty($facultySubcategories)): ?>
                        <ul class="sidebar-sublinks batch-hover-menu">
                            <?php foreach ($facultySubcategories as $subcat): ?>
                            <li>
                                <a href="<?php echo PUBLIC_URL; ?>/faculty-detail.php?id=<?php echo (int)$subcat['id']; ?>" class="sidebar-sublink">
                                    <?php echo htmlspecialchars($subcat['title']); ?>
                                </a>
                            </li>
                            <?php endforeach; ?>
                        </ul>
                        <?php endif; ?>
                    </li>
                    <li><a href="<?php echo PUBLIC_URL; ?>/admin-representative-detail.php">Admin Representative</a></li>
                    <li class="org-item-with-batch">
                        <span class="sidebar-label cursor-default">Program</span>
                        <!-- Program submenu - Show below Program on hover -->
                        <ul class="sidebar-sublinks batch-hover-menu">
                            <li>
                                <a href="<?php echo PUBLIC_URL; ?>/program-detail-2.php" class="sidebar-sublink">
                                    Information Systems
                                </a>
                            </li>
                            <li>
                                <a href="<?php echo PUBLIC_URL; ?>/program-detail-1.php" class="sidebar-sublink">
                                    Computer Science
                                </a>
                            </li>
                            <li>
                                <a href="<?php echo PUBLIC_URL; ?>/program-detail-3.php" class="sidebar-sublink active">
                                    Data Science
                                </a>
                            </li>
                        </ul>
                    </li>
                </ul>
            </div>

            <!-- STUDENT ORGANIZATION Section -->
            <div class="sidebar-section">
                <h2 class="sidebar-title">STUDENT ORGANIZATION</h2>
                <ul class="sidebar-links">
                    <?php foreach ($organizationsList as $org): ?>
                        <li>
                            <a href="<?php echo PUBLIC_URL; ?>/batches.php?org_id=<?php echo $org['id']; ?>">
                                <?php echo htmlspecialchars($org['name']); ?> Batches
                            </a>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>
    </aside>
</div>

<?php include '../includes/footer.php'; ?>


