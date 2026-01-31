<?php
require_once '../includes/config.php';
require_once '../includes/database.php';
require_once '../includes/upload.php';

// Fetch admin representatives
$adminReps = dbFetchAll("SELECT * FROM institute_sections WHERE type = 'admin_representative' AND status = 'published' ORDER BY display_order ASC");

// Fetch faculty subcategories for sidebar
$facultySubcategories = dbFetchAll("SELECT * FROM institute_sections WHERE type = 'faculty_subcategory' AND status = 'published' ORDER BY display_order ASC, title ASC");

$pageTitle = 'Admin Representative - Institute - PROWLWAY';
$bodyClass = 'origin-page';
include '../includes/header.php';

// Fetch organizations list for sidebar
$organizationsList = dbFetchAll("SELECT * FROM student_organizations WHERE status = 'active' ORDER BY display_order ASC");
?>

<div class="origin-page-container">
    <!-- Main Content Area -->
    <div class="origin-main-content">
        <div class="origin-content-panel">
            <div class="batches-inner-panel batch-detail-inner-panel">
                <section class="batch-detail-section px-2 md:px-0">
                    <div class="mb-4">
                        <a href="<?php echo PUBLIC_URL; ?>/institute.php?section=about" class="text-sm text-black inline-flex items-center gap-1">
                            ← Back to Institute
                        </a>
                    </div>
                    
                    <h2 class="batch-section-heading mb-4 md:mb-6 text-xl md:text-2xl lg:text-[32px]">ADMIN REPRESENTATIVE</h2>
                    
                    <?php if (empty($adminReps)): ?>
                        <p class="batch-section-empty text-sm md:text-base">No admin representative information available.</p>
                    <?php else: ?>
                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6 md:gap-8 justify-items-center">
                            <?php foreach ($adminReps as $rep): ?>
                                <div class="text-center w-full max-w-[205px]">
                                    <div class="flex justify-center mb-3 w-full aspect-[196/182] max-w-[196px] mx-auto">
                                        <?php if (!empty($rep['image'])): ?>
                                            <img src="<?php echo getImageUrl($rep['image']); ?>" alt="<?php echo htmlspecialchars($rep['title']); ?>" class="w-full h-full rounded-[30px] object-cover bg-[#D9D9D9]">
                                        <?php else: ?>
                                            <div class="w-full h-full rounded-[30px] bg-[#D9D9D9]"></div>
                                        <?php endif; ?>
                                    </div>
                                    <div class="w-full max-w-[205px] mx-auto font-['Inter'] font-semibold text-sm md:text-[15px] leading-[18px] text-center capitalize text-[#0F181D] truncate px-2" title="<?php echo htmlspecialchars($rep['title']); ?>">
                                        <?php echo htmlspecialchars($rep['title']); ?>
                                    </div>
                                    <?php if (!empty($rep['position_title'])): ?>
                                        <div class="text-xs md:text-sm text-black italic truncate w-full max-w-[205px] mx-auto mt-0.5 px-2" title="<?php echo htmlspecialchars($rep['position_title']); ?>">
                                            <?php echo htmlspecialchars($rep['position_title']); ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
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
                        <a href="<?php echo PUBLIC_URL; ?>/faculty.php">Faculty Unit</a>
                        
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
                    <li><a href="<?php echo PUBLIC_URL; ?>/admin-representative.php" class="active">Admin Representative</a></li>
                    <li><a href="<?php echo PUBLIC_URL; ?>/institute.php?section=program">Program</a></li>
                </ul>
            </div>

            <!-- STUDENT ORGANIZATION Section -->
            <div class="sidebar-section">
                <h2 class="sidebar-title">STUDENT ORGANIZATION</h2>
                <ul class="sidebar-links">
                    <?php 
                    // Check if we're currently on the batches page
                    $isBatchesPage = (basename($_SERVER['PHP_SELF']) === 'batches.php' && isset($_GET['org_id']));
                    $currentBatchOrgId = $isBatchesPage ? (int)$_GET['org_id'] : null;
                    
                    foreach ($organizationsList as $org): 
                    ?>
                        <li class="org-item-with-batch">
                            <a href="<?php echo PUBLIC_URL; ?>/organization.php?id=<?php echo $org['id']; ?>"><?php echo htmlspecialchars($org['name']); ?></a>
                            
                            <!-- BATCH Subcategory - Show below organization on hover -->
                            <ul class="sidebar-sublinks batch-hover-menu">
                                <li>
                                    <a href="<?php echo PUBLIC_URL; ?>/batches.php?org_id=<?php echo $org['id']; ?>" 
                                       class="sidebar-sublink <?php echo ($isBatchesPage && $currentBatchOrgId == $org['id']) ? 'active' : ''; ?>">
                                        BATCH
                                    </a>
                                </li>
                            </ul>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>
    </aside>
</div>

<?php include '../includes/footer.php'; ?>
