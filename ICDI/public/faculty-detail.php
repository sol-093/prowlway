<?php
require_once '../includes/config.php';
require_once '../includes/database.php';
require_once '../includes/upload.php';

$facultyId = isset($_GET['id']) ? (int) $_GET['id'] : 0;

// Fetch faculty subcategory (or faculty_unit) by id — dynamic page like batch-detail.php?id=
$faculty = null;
if ($facultyId > 0) {
    $faculty = dbFetchOne("SELECT * FROM institute_sections WHERE id = ? AND type IN ('faculty_subcategory', 'faculty_unit') AND status = 'published'", [$facultyId]);
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

// Fetch members for this faculty section (like batch-detail members)
$facultyMembers = [];
if ($faculty && $facultyId > 0) {
    $facultyMembers = dbFetchAll("SELECT * FROM faculty_section_members WHERE institute_section_id = ? ORDER BY display_order ASC, id ASC", [$facultyId]);
}
?>

<div class="origin-page-container">
    <!-- Main Content Area -->
    <div class="origin-main-content">
        <div class="origin-content-panel">
            <div class="batches-inner-panel batch-detail-inner-panel">
                <?php if (!$faculty): ?>
                    <h1 class="origin-page-title">Faculty Unit</h1>
                    <p>Faculty section not found.</p>
                <?php else: ?>
                    <div class="mb-4">
                        <a href="<?php echo PUBLIC_URL; ?>/institute.php?section=about" class="text-sm text-black inline-flex items-center gap-1">
                            ← Back to Institute
                        </a>
                    </div>
                    
                    <!-- Page title (subcategory name) -->
                    <div class="mb-6 md:mb-8">
                        <h1 class="text-2xl md:text-3xl font-bold text-gray-900 px-2 md:px-0"><?php echo htmlspecialchars($faculty['title']); ?></h1>
                        <?php if (!empty($faculty['description'])): ?>
                            <p class="text-base md:text-lg text-gray-600 mt-2 px-2 md:px-0 font-semibold"><?php echo htmlspecialchars($faculty['description']); ?></p>
                        <?php endif; ?>
                    </div>

                    <!-- Section heading (editable in admin, e.g. EXECUTIVE OFFICERS) + Members grid -->
                    <?php
                    $sectionHeading = !empty($faculty['position_title']) ? $faculty['position_title'] : 'MEMBERS';
                    ?>
                    <section class="batch-detail-section mt-6 md:mt-8 px-2 md:px-0">
                        <h2 class="batch-section-heading mb-4 md:mb-6 text-xl md:text-2xl lg:text-[32px]"><?php echo htmlspecialchars(strtoupper($sectionHeading)); ?></h2>
                        <?php if (empty($facultyMembers)): ?>
                            <p class="batch-section-empty text-sm md:text-base">No members added for this section yet.</p>
                        <?php else:
                            $firstRow = array_slice($facultyMembers, 0, 2);
                            $restRows = array_slice($facultyMembers, 2);
                        ?>
                            <?php if (!empty($firstRow)): ?>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 md:gap-8 justify-items-center mb-6 md:mb-8">
                                <?php foreach ($firstRow as $member): ?>
                                <div class="text-center w-full max-w-[205px]">
                                    <div class="flex justify-center mb-3 w-full aspect-[196/182] max-w-[196px] mx-auto">
                                        <?php if (!empty($member['image'])): ?>
                                            <img src="<?php echo getImageUrl($member['image']); ?>" alt="<?php echo htmlspecialchars($member['name']); ?>" class="w-full h-full rounded-[30px] object-cover bg-[#D9D9D9]">
                                        <?php else: ?>
                                            <div class="w-full h-full rounded-[30px] bg-[#D9D9D9]"></div>
                                        <?php endif; ?>
                                    </div>
                                    <div class="w-full max-w-[205px] mx-auto font-['Inter'] font-semibold text-sm md:text-[15px] leading-[18px] text-center capitalize text-[#0F181D] truncate px-2" title="<?php echo htmlspecialchars($member['name']); ?>">
                                        <?php echo htmlspecialchars($member['name']); ?>
                                    </div>
                                    <div class="text-xs md:text-sm text-black italic truncate w-full max-w-[205px] mx-auto mt-0.5 px-2" title="<?php echo htmlspecialchars($member['position_title']); ?>">
                                        <?php echo htmlspecialchars($member['position_title']); ?>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            </div>
                            <?php endif; ?>
                            <?php if (!empty($restRows)): ?>
                            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6 md:gap-8 justify-items-center">
                                <?php foreach ($restRows as $member): ?>
                                <div class="text-center w-full max-w-[205px]">
                                    <div class="flex justify-center mb-3 w-full aspect-[196/182] max-w-[196px] mx-auto">
                                        <?php if (!empty($member['image'])): ?>
                                            <img src="<?php echo getImageUrl($member['image']); ?>" alt="<?php echo htmlspecialchars($member['name']); ?>" class="w-full h-full rounded-[30px] object-cover bg-[#D9D9D9]">
                                        <?php else: ?>
                                            <div class="w-full h-full rounded-[30px] bg-[#D9D9D9]"></div>
                                        <?php endif; ?>
                                    </div>
                                    <div class="w-full max-w-[205px] mx-auto font-['Inter'] font-semibold text-sm md:text-[15px] leading-[18px] text-center capitalize text-[#0F181D] truncate px-2" title="<?php echo htmlspecialchars($member['name']); ?>">
                                        <?php echo htmlspecialchars($member['name']); ?>
                                    </div>
                                    <div class="text-xs md:text-sm text-black italic truncate w-full max-w-[205px] mx-auto mt-0.5 px-2" title="<?php echo htmlspecialchars($member['position_title']); ?>">
                                        <?php echo htmlspecialchars($member['position_title']); ?>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            </div>
                            <?php endif; ?>
                        <?php endif; ?>
                    </section>

                    <!-- Optional faculty section image (for faculty_unit style) -->
                    <?php if (!empty($faculty['image'])): ?>
                        <section class="batch-detail-section px-2 md:px-0 mb-6 md:mb-8 mt-8">
                            <div class="flex justify-center">
                                <div class="w-full max-w-[400px] aspect-[196/182]">
                                    <img src="<?php echo getImageUrl($faculty['image']); ?>" alt="<?php echo htmlspecialchars($faculty['title']); ?>" class="w-full h-full rounded-[30px] object-cover bg-[#D9D9D9]">
                                </div>
                            </div>
                        </section>
                    <?php endif; ?>

                    <!-- Faculty Content (optional about text) -->
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
                        <span class="sidebar-label cursor-default">Faculty Unit</span>
                        <!-- Subcategories - Show below Faculty Unit on hover -->
                        <?php if (!empty($facultySubcategories)): ?>
                        <ul class="sidebar-sublinks batch-hover-menu">
                            <?php foreach ($facultySubcategories as $subcat): ?>
                            <li>
                                <a href="<?php echo PUBLIC_URL; ?>/faculty-detail.php?id=<?php echo (int)$subcat['id']; ?>" class="sidebar-sublink <?php echo ($faculty && (int)$subcat['id'] === (int)$faculty['id']) ? 'active' : ''; ?>">
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
                                <a href="<?php echo PUBLIC_URL; ?>/program-detail-3.php" class="sidebar-sublink">
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
