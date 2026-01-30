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

<div class="origin-page-container">
    <!-- Main Content Area -->
    <div class="origin-main-content">
        <div class="origin-content-panel">
            <div class="batches-inner-panel">
                <div class="batches-grid-header">
                    <h1 class="origin-page-title">
                        ORIGIN: <?php echo htmlspecialchars($activeOrg ? ($activeOrg['acronym'] ?: $activeOrg['name']) : 'ICDISG'); ?> - BATCH
                    </h1>
                    <?php if ($activeOrg): ?>
                        <p class="text-sm text-gray-400 mt-2">
                            Showing batches for <strong><?php echo htmlspecialchars($activeOrg['name']); ?></strong>
                            <a href="<?php echo PUBLIC_URL; ?>/organization.php?id=<?php echo $activeOrg['id']; ?>" class="text-indigo-400 hover:text-indigo-300 ml-2">(← Back to organization)</a>
                        </p>
                    <?php endif; ?>
                </div>

                <div class="batches-grid-cards">
                    <?php if (empty($batches)): ?>
                        <div class="no-batches">
                            <p>No batches available yet.</p>
                        </div>
                    <?php else: ?>
                        <?php foreach ($batches as $batch): ?>
                            <div class="batch-grid-card">
                                <a href="<?php echo PUBLIC_URL; ?>/batch-detail.php?id=<?php echo $batch['id']; ?>" class="batch-grid-card-link">
                                    <div class="batch-grid-card-inner">
                                        <div class="batch-grid-image-frame">
                                            <?php if (!empty($batch['image'])): ?>
                                                <img src="<?php echo getImageUrl($batch['image']); ?>" alt="<?php echo htmlspecialchars($batch['academic_year']); ?>">
                                            <?php else: ?>
                                                <div class="batch-placeholder">
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
    <aside class="origin-sidebar">
        <div class="sidebar-panel">
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

