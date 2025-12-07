<?php
require_once '../includes/config.php';
require_once '../includes/database.php';
require_once '../includes/upload.php';

$pageTitle = 'ORIGIN: ICDISG - BATCH - PROWLWAY';
$bodyClass = 'origin-page';
include '../includes/header.php';

// Fetch batches
$batches = dbFetchAll("SELECT * FROM batches WHERE status = 'active' ORDER BY display_order DESC, start_year DESC");
?>

<div class="origin-page-container">
    <!-- Main Content Area -->
    <div class="origin-main-content">
        <div class="origin-content-panel">
            <h1 class="origin-page-title">ORIGIN: ICDISG - BATCH</h1>
            
            <!-- Batches Grid (2x2) -->
            <div class="batches-grid">
                <?php if (empty($batches)): ?>
                    <div class="no-batches">
                        <p>No batches available yet.</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($batches as $batch): ?>
                        <div class="batch-card">
                            <div class="batch-card-image">
                                <?php if ($batch['image']): ?>
                                    <img src="<?php echo getImageUrl($batch['image']); ?>" alt="<?php echo htmlspecialchars($batch['academic_year']); ?>">
                                <?php else: ?>
                                    <div class="batch-placeholder">
                                        <div class="batch-logo-placeholder">🐾</div>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <div class="batch-card-info">
                                <div class="batch-academic-year"><?php echo htmlspecialchars($batch['academic_year']); ?></div>
                                <a href="<?php echo PUBLIC_URL; ?>/batch-detail.php?id=<?php echo $batch['id']; ?>" class="batch-view-btn">VIEW</a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <!-- Sidebar -->
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
                    <?php
                    $organizations = dbFetchAll("SELECT * FROM student_organizations WHERE status = 'active' ORDER BY display_order ASC");
                    foreach ($organizations as $org):
                    ?>
                        <li><a href="<?php echo PUBLIC_URL; ?>/organization.php?id=<?php echo $org['id']; ?>"><?php echo htmlspecialchars($org['name']); ?></a></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>
    </aside>
</div>

<?php include '../includes/footer.php'; ?>

