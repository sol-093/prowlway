<?php
require_once '../includes/config.php';
require_once '../includes/database.php';
require_once '../includes/upload.php';

$batchId = isset($_GET['id']) ? (int) $_GET['id'] : 0;

// Fetch batch
$batch = null;
if ($batchId > 0) {
    $batch = dbFetchOne("SELECT * FROM batches WHERE id = ? AND status != 'archived'", [$batchId]);
}

// If batch has an org, fetch it for display
$batchOrg = null;
if ($batch && !empty($batch['organization_id'])) {
    $batchOrg = dbFetchOne("SELECT id, name, acronym FROM student_organizations WHERE id = ? AND status != 'archived'", [(int)$batch['organization_id']]);
}

if ($batch) {
    $orgLabel = $batchOrg ? ($batchOrg['acronym'] ?: $batchOrg['name']) : 'ICDISG';
    $pageTitle = $batch['academic_year'] . ' · ' . $orgLabel . ' Batch - PROWLWAY';
} else {
    $pageTitle = 'ICDISG Batch - PROWLWAY';
}

$bodyClass = 'origin-page';
include '../includes/header.php';

// Fetch batch members grouped by role
$advisers = $batch
    ? dbFetchAll("SELECT * FROM batch_members WHERE batch_id = ? AND group_type = 'adviser' ORDER BY display_order ASC, id ASC", [$batch['id']])
    : [];
$coAdvisers = $batch
    ? dbFetchAll("SELECT * FROM batch_members WHERE batch_id = ? AND group_type = 'co_adviser' ORDER BY display_order ASC, id ASC", [$batch['id']])
    : [];
$executiveOfficers = $batch
    ? dbFetchAll("SELECT * FROM batch_members WHERE batch_id = ? AND group_type = 'executive_officer' ORDER BY display_order ASC, id ASC", [$batch['id']])
    : [];
$executiveAssociates = $batch
    ? dbFetchAll("SELECT * FROM batch_members WHERE batch_id = ? AND group_type = 'executive_associate' ORDER BY display_order ASC, id ASC", [$batch['id']])
    : [];

$targetGroup = $batch['target_group'] ?? 'all';

// Fetch organizations list for sidebar directory
$organizationsList = dbFetchAll("SELECT * FROM student_organizations WHERE status = 'active' ORDER BY display_order ASC");
// Fetch faculty subcategories for sidebar (Faculty Unit hover)
$facultySubcategories = dbFetchAll("SELECT * FROM institute_sections WHERE type = 'faculty_subcategory' AND status = 'published' ORDER BY display_order ASC, title ASC");
?>

<div class="origin-page-container">
    <!-- Main Content Area -->
    <div class="origin-main-content">
        <div class="origin-content-panel">
            <div class="batches-inner-panel batch-detail-inner-panel">
                <?php if (!$batch): ?>
                    <h1 class="origin-page-title">ICDISG Batch</h1>
                    <p>Batch not found.</p>
                <?php else: ?>
                    <?php if ($batchOrg): ?>
                        <div class="mb-4">
                            <a href="<?php echo PUBLIC_URL; ?>/organization.php?id=<?php echo $batchOrg['id']; ?>" class="text-sm text-black inline-flex items-center gap-1">
                                ← Back to <?php echo htmlspecialchars($batchOrg['name']); ?>
                            </a>
                        </div>
                    <?php endif; ?>
                    
                    <!-- Batch Title -->
                    <div class="mb-6 md:mb-8">
                        <h1 class="text-2xl md:text-3xl font-bold text-gray-900 px-2 md:px-0"><?php echo htmlspecialchars($batch['academic_year']); ?></h1>
                    </div>

                    <!-- Advisers & Co-Advisers (2 Profile Cards) -->
                    <?php if ($targetGroup === 'all' || $targetGroup === 'adviser' || $targetGroup === 'co_adviser'): ?>
                        <?php if (!empty($advisers) || !empty($coAdvisers)): ?>
                            <section class="batch-detail-section advisers-section">
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 md:gap-12 lg:gap-16 justify-items-center mb-6 md:mb-8 px-2 md:px-0">
                                    <!-- Adviser Card -->
                                    <?php if (!empty($advisers)): ?>
                                        <?php foreach ($advisers as $adviser): ?>
                                        <div class="text-center w-full max-w-[205px]">
                                            <div class="flex justify-center mb-3 w-full aspect-[196/182] max-w-[196px] mx-auto">
                                                <?php if (!empty($adviser['image'])): ?>
                                                    <img src="<?php echo getImageUrl($adviser['image']); ?>" alt="<?php echo htmlspecialchars($adviser['name']); ?>" class="w-full h-full rounded-[30px] object-cover bg-[#D9D9D9]">
                                                <?php else: ?>
                                                    <div class="w-full h-full rounded-[30px] bg-[#D9D9D9]"></div>
                                                <?php endif; ?>
                                            </div>
                                            <div class="w-full max-w-[205px] mx-auto font-['Inter'] font-semibold text-[15px] leading-[18px] text-center capitalize text-[#0F181D] truncate px-2" title="<?php echo htmlspecialchars($adviser['name']); ?>">
                                                <?php echo htmlspecialchars($adviser['name']); ?>
                                            </div>
                                            <div class="text-xs md:text-sm text-black italic truncate w-full max-w-[205px] mx-auto mt-0.5 px-2" title="<?php echo htmlspecialchars($adviser['position_title']); ?>">
                                                <?php echo htmlspecialchars($adviser['position_title']); ?>
                                            </div>
                                        </div>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                    
                                    <!-- Co-Adviser Card -->
                                    <?php if (!empty($coAdvisers)): ?>
                                        <?php foreach ($coAdvisers as $coAdviser): ?>
                                        <div class="text-center w-full max-w-[205px]">
                                            <div class="flex justify-center mb-3 w-full aspect-[196/182] max-w-[196px] mx-auto">
                                                <?php if (!empty($coAdviser['image'])): ?>
                                                    <img src="<?php echo getImageUrl($coAdviser['image']); ?>" alt="<?php echo htmlspecialchars($coAdviser['name']); ?>" class="w-full h-full rounded-[30px] object-cover bg-[#D9D9D9]">
                                                <?php else: ?>
                                                    <div class="w-full h-full rounded-[30px] bg-[#D9D9D9]"></div>
                                                <?php endif; ?>
                                            </div>
                                            <div class="w-full max-w-[205px] mx-auto font-['Inter'] font-semibold text-[15px] leading-[18px] text-center capitalize text-[#0F181D] truncate px-2" title="<?php echo htmlspecialchars($coAdviser['name']); ?>">
                                                <?php echo htmlspecialchars($coAdviser['name']); ?>
                                            </div>
                                            <div class="text-xs md:text-sm text-black italic truncate w-full max-w-[205px] mx-auto mt-0.5 px-2" title="<?php echo htmlspecialchars($coAdviser['position_title']); ?>">
                                                <?php echo htmlspecialchars($coAdviser['position_title']); ?>
                                            </div>
                                        </div>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </div>
                            </section>
                        <?php endif; ?>
                    <?php endif; ?>

                    <!-- Executive Officers -->
                    <?php if ($targetGroup === 'all' || $targetGroup === 'executive_officer'): ?>
                        <section class="batch-detail-section mt-8 md:mt-12 lg:mt-16 px-2 md:px-0">
                            <h2 class="batch-section-heading mb-4 md:mb-6 text-xl md:text-2xl lg:text-[32px]">EXECUTIVE OFFICERS</h2>
                            <?php if (empty($executiveOfficers)): ?>
                                <p class="batch-section-empty text-sm md:text-base">No executive officers added for this batch yet.</p>
                            <?php else: ?>
                                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6 md:gap-8 justify-items-center">
                                    <?php foreach ($executiveOfficers as $member): ?>
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
                        </section>
                    <?php endif; ?>

                    <!-- Executive Associates -->
                    <?php if ($targetGroup === 'all' || $targetGroup === 'executive_associate'): ?>
                        <section class="batch-detail-section mt-8 md:mt-12 lg:mt-16 px-2 md:px-0">
                            <h2 class="batch-section-heading mb-4 md:mb-6 text-xl md:text-2xl lg:text-[32px]">EXECUTIVE ASSOCIATES</h2>
                            <?php if (empty($executiveAssociates)): ?>
                                <p class="batch-section-empty text-sm md:text-base">No executive associates added for this batch yet.</p>
                            <?php else: ?>
                                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6 md:gap-8 justify-items-center">
                                    <?php foreach ($executiveAssociates as $member): ?>
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
                        </section>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Sidebar / Directory -->
    <aside class="origin-sidebar w-full lg:w-80 flex-shrink-0 bg-gray-900 lg:bg-transparent border-t lg:border-t-0 border-gray-800 lg:border-0">
        <div class="sidebar-panel p-4 lg:p-6">
            <!-- INSTITUTE Section -->
            <div class="sidebar-section">
                <h2 class="sidebar-title">INSTITUTE</h2>
                <ul class="sidebar-links">
                    <li><a href="<?php echo PUBLIC_URL; ?>/institute.php?section=about">About</a></li>
                    <li class="org-item-with-batch">
                        <span class="sidebar-label">Faculty Unit</span>
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
                    <li><a href="<?php echo PUBLIC_URL; ?>/institute.php?section=program">Program</a></li>
                </ul>
            </div>

            <!-- STUDENT ORGANIZATION Section -->
            <div class="sidebar-section">
                <h2 class="sidebar-title">STUDENT ORGANIZATION</h2>
                <ul class="sidebar-links">
                    <?php foreach ($organizationsList as $org): ?>
                        <li>
                            <a href="<?php echo PUBLIC_URL; ?>/batches.php?org_id=<?php echo $org['id']; ?>"
                               class="<?php echo $batchOrg && $org['id'] == $batchOrg['id'] ? 'active' : ''; ?>">
                                <?php echo htmlspecialchars($org['name']); ?> Batches
                            </a>
                        </li>
                    <?php endforeach; ?>
                    <?php if ($batchOrg): ?>
                        <li><a href="<?php echo PUBLIC_URL; ?>/organization.php?id=<?php echo $batchOrg['id']; ?>" class="text-black">← Back to <?php echo htmlspecialchars($batchOrg['name']); ?></a></li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </aside>
</div>

<?php include '../includes/footer.php'; ?>

