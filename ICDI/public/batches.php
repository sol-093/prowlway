<?php
require_once '../includes/config.php';
require_once '../includes/database.php';
require_once '../includes/upload.php';

$pageTitle = $activeOrg ? ('ORIGIN: ' . ($activeOrg['acronym'] ?: $activeOrg['name']) . ' - BATCH - PROWLWAY') : 'ORIGIN: ICDISG - BATCH - PROWLWAY';
$bodyClass = 'origin-page';
include '../includes/header.php';

// Optional filter by organization
$orgId = isset($_GET['org_id']) ? (int) $_GET['org_id'] : 0;
$activeOrg = null;
if ($orgId > 0) {
    $activeOrg = dbFetchOne("SELECT id, name, acronym FROM student_organizations WHERE id = ? AND status != 'archived'", [$orgId]);
}

// Fetch active batches for grid
$batches = dbFetchAll(
    "SELECT b.* FROM batches b WHERE b.status = 'active' " . ($activeOrg ? "AND b.organization_id = ? " : "") . "ORDER BY b.display_order DESC, b.start_year DESC",
    $activeOrg ? [$activeOrg['id']] : []
);

// Fetch organizations list for sidebar directory
$organizationsList = dbFetchAll("SELECT * FROM student_organizations WHERE status = 'active' ORDER BY display_order ASC");
?>

<div class="origin-page-container flex flex-col lg:flex-row min-h-screen">
    <!-- Main Content Area -->
    <div class="origin-main-content flex-1 w-full lg:w-auto">
        <div class="origin-content-panel p-4 md:p-6 lg:p-8">
            <div class="batches-inner-panel w-full max-w-full lg:max-w-6xl mx-auto px-4 md:px-6 lg:px-10 py-4 md:py-6 lg:py-8">
                <div class="batches-grid-header mb-6 md:mb-8">
                    <h1 class="origin-page-title text-xl md:text-2xl lg:text-3xl font-bold mb-3 md:mb-4">
                        ORIGIN: <?php echo htmlspecialchars($activeOrg ? ($activeOrg['acronym'] ?: $activeOrg['name']) : 'ICDISG'); ?> - BATCH
                    </h1>
                    <?php if ($activeOrg): ?>
                        <p class="text-xs md:text-sm text-black mt-2 leading-relaxed">
                            Showing batches for <strong><?php echo htmlspecialchars($activeOrg['name']); ?></strong>
                            <a href="<?php echo PUBLIC_URL; ?>/organization.php?id=<?php echo $activeOrg['id']; ?>" class="text-black hover:text-gray-800 ml-1 md:ml-2 inline-block">(← Back to organization)</a>
                        </p>
                    <?php endif; ?>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4 md:gap-6 lg:gap-8">
                    <?php if (empty($batches)): ?>
                        <div class="col-span-full text-center py-12">
                            <p class="text-gray-600 text-lg">No batches available yet.</p>
                        </div>
                    <?php else: ?>
                        <?php foreach ($batches as $batch): ?>
                            <div class="flex justify-center">
                                <a href="<?php echo PUBLIC_URL; ?>/batch-detail.php?id=<?php echo $batch['id']; ?>" class="w-full max-w-[240px] block">
                                    <div class="batch-grid-card-inner w-full hover:shadow-xl transition-all duration-200">
                                        <div class="batch-grid-image-frame mx-auto">
                                            <?php if (!empty($batch['image'])): ?>
                                                <img src="<?php echo getImageUrl($batch['image']); ?>" alt="<?php echo htmlspecialchars($batch['academic_year']); ?>" class="w-full h-full object-cover">
                                            <?php else: ?>
                                                <div class="batch-placeholder w-full h-full">
                                                    <div class="batch-logo-placeholder">🐾</div>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                        <div class="batch-grid-year">
                                            <?php echo htmlspecialchars($batch['academic_year']); ?>
                                        </div>
                                        <div class="batch-grid-view-btn">VIEW</div>
                                    </div>
                                </a>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
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
                    <li><a href="<?php echo PUBLIC_URL; ?>/institute.php?section=faculty">Faculty Unit</a></li>
                    <li><a href="<?php echo PUBLIC_URL; ?>/institute.php?section=admin">Admin Representative</a></li>
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
                               class="<?php echo $activeOrg && $org['id'] == $activeOrg['id'] ? 'active' : ''; ?>">
                                <?php echo htmlspecialchars($org['name']); ?> Batches
                            </a>
                        </li>
                    <?php endforeach; ?>
                    <?php if ($activeOrg): ?>
                        <li><a href="<?php echo PUBLIC_URL; ?>/organization.php?id=<?php echo $activeOrg['id']; ?>">← Back to <?php echo htmlspecialchars($activeOrg['name']); ?></a></li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </aside>
</div>

<?php include '../includes/footer.php'; ?>

