<?php
require_once '../includes/config.php';
require_once '../includes/database.php';
require_once '../includes/upload.php';

$facultyId = isset($_GET['id']) ? (int) $_GET['id'] : 0;

// Fetch faculty member
$faculty = null;
if ($facultyId > 0) {
    $faculty = dbFetchOne("SELECT * FROM institute_sections WHERE id = ? AND type = 'faculty_unit' AND status = 'published'", [$facultyId]);
}

if ($faculty) {
    $pageTitle = htmlspecialchars($faculty['title']) . ' - Faculty Unit - PROWLWAY';
} else {
    $pageTitle = 'Faculty Unit - PROWLWAY';
}

$bodyClass = 'origin-page';
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
                <?php if (!$faculty): ?>
                    <h1 class="origin-page-title">Faculty Unit</h1>
                    <p>Faculty member not found.</p>
                <?php else: ?>
                    <div class="mb-4">
                        <a href="<?php echo PUBLIC_URL; ?>/faculty.php" class="text-sm text-black inline-flex items-center gap-1">
                            ← Back to Faculty Unit
                        </a>
                    </div>
                    
                    <!-- Faculty Title -->
                    <div class="mb-6 md:mb-8">
                        <h1 class="text-2xl md:text-3xl font-bold text-gray-900 px-2 md:px-0"><?php echo htmlspecialchars($faculty['title']); ?></h1>
                        <?php if (!empty($faculty['position_title'])): ?>
                            <p class="text-lg md:text-xl text-gray-700 mt-2 px-2 md:px-0"><?php echo htmlspecialchars($faculty['position_title']); ?></p>
                        <?php endif; ?>
                        <?php if (!empty($faculty['description'])): ?>
                            <p class="text-base md:text-lg text-gray-600 mt-2 px-2 md:px-0 font-semibold"><?php echo htmlspecialchars($faculty['description']); ?></p>
                        <?php endif; ?>
                    </div>

                    <!-- Faculty Image -->
                    <?php if (!empty($faculty['image'])): ?>
                        <section class="batch-detail-section px-2 md:px-0 mb-6 md:mb-8">
                            <div class="flex justify-center">
                                <div class="w-full max-w-[400px] aspect-[196/182]">
                                    <img src="<?php echo getImageUrl($faculty['image']); ?>" alt="<?php echo htmlspecialchars($faculty['title']); ?>" class="w-full h-full rounded-[30px] object-cover bg-[#D9D9D9]">
                                </div>
                            </div>
                        </section>
                    <?php endif; ?>

                    <!-- Faculty Content -->
                    <?php if (!empty($faculty['content'])): ?>
                        <section class="batch-detail-section mt-8 md:mt-12 lg:mt-16 px-2 md:px-0">
                            <h2 class="batch-section-heading mb-4 md:mb-6 text-xl md:text-2xl lg:text-[32px]">ABOUT</h2>
                            <div class="text-sm md:text-base text-black leading-relaxed">
                                <?php echo nl2br(htmlspecialchars($faculty['content'])); ?>
                            </div>
                        </section>
                    <?php endif; ?>
                <?php endif; ?>
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
                        <a href="<?php echo PUBLIC_URL; ?>/faculty.php" class="active">Faculty Unit</a>
                        
                        <!-- Subcategories - Show below Faculty Unit on hover -->
                        <?php if (!empty($facultySubcategories)): ?>
                        <ul class="sidebar-sublinks batch-hover-menu">
                            <?php foreach ($facultySubcategories as $subcat): ?>
                            <li>
                                <a href="<?php echo htmlspecialchars($subcat['description'] ?: '#'); ?>" class="sidebar-sublink">
                                    <?php echo htmlspecialchars($subcat['title']); ?>
                                </a>
                            </li>
                            <?php endforeach; ?>
                        </ul>
                        <?php endif; ?>
                    </li>
                    <li><a href="<?php echo PUBLIC_URL; ?>/admin-representative.php">Admin Representative</a></li>
                    <li><a href="<?php echo PUBLIC_URL; ?>/institute.php?section=program">Program</a></li>
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
