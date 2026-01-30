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
$executiveOfficers = $batch
    ? dbFetchAll("SELECT * FROM batch_members WHERE batch_id = ? AND group_type = 'executive_officer' ORDER BY display_order ASC, id ASC", [$batch['id']])
    : [];
$executiveAssociates = $batch
    ? dbFetchAll("SELECT * FROM batch_members WHERE batch_id = ? AND group_type = 'executive_associate' ORDER BY display_order ASC, id ASC", [$batch['id']])
    : [];

$targetGroup = $batch['target_group'] ?? 'all';

// Fetch organizations list for sidebar directory
$organizationsList = dbFetchAll("SELECT * FROM student_organizations WHERE status = 'active' ORDER BY display_order ASC");
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
                            <a href="<?php echo PUBLIC_URL; ?>/organization.php?id=<?php echo $batchOrg['id']; ?>" class="text-sm text-gray-400 hover:text-gray-300 inline-flex items-center gap-1">
                                ← Back to <?php echo htmlspecialchars($batchOrg['name']); ?>
                            </a>
                        </div>
                    <?php endif; ?>
                    <!-- Top: Batch hero / logo -->
                    <div class="batch-detail-hero">
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
                        </div>
                    </div>

                    <!-- Advisers -->
                    <?php if (($targetGroup === 'all' || $targetGroup === 'adviser') && !empty($advisers)): ?>
                        <section class="batch-detail-section advisers-section">
                            <div class="batch-members-row advisers-row">
                                <?php foreach ($advisers as $member): ?>
                                    <div class="batch-member-card advisers-card">
                                        <div class="batch-member-avatar">
                                            <?php if (!empty($member['image'])): ?>
                                                <img src="<?php echo getImageUrl($member['image']); ?>" alt="<?php echo htmlspecialchars($member['name']); ?>">
                                            <?php endif; ?>
                                        </div>
                                        <div class="batch-member-name">
                                            <?php echo htmlspecialchars($member['name']); ?>
                                        </div>
                                        <div class="batch-member-position">
                                            <?php echo htmlspecialchars($member['position_title']); ?>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </section>
                    <?php endif; ?>

                    <!-- Executive Officers -->
                    <?php if ($targetGroup === 'all' || $targetGroup === 'executive_officer'): ?>
                        <section class="batch-detail-section">
                            <h2 class="batch-section-heading">EXECUTIVE OFFICERS</h2>
                            <?php if (empty($executiveOfficers)): ?>
                                <p class="batch-section-empty">No executive officers added for this batch yet.</p>
                            <?php else: ?>
                                <div class="batch-members-row">
                                    <?php foreach ($executiveOfficers as $member): ?>
                                        <div class="batch-member-card">
                                            <div class="batch-member-avatar">
                                                <?php if (!empty($member['image'])): ?>
                                                    <img src="<?php echo getImageUrl($member['image']); ?>" alt="<?php echo htmlspecialchars($member['name']); ?>">
                                                <?php endif; ?>
                                            </div>
                                            <div class="batch-member-name">
                                                <?php echo htmlspecialchars($member['name']); ?>
                                            </div>
                                            <div class="batch-member-position">
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
                        <section class="batch-detail-section">
                            <h2 class="batch-section-heading">EXECUTIVE ASSOCIATES</h2>
                            <?php if (empty($executiveAssociates)): ?>
                                <p class="batch-section-empty">No executive associates added for this batch yet.</p>
                            <?php else: ?>
                                <div class="batch-members-row">
                                    <?php foreach ($executiveAssociates as $member): ?>
                                        <div class="batch-member-card">
                                            <div class="batch-member-avatar">
                                                <?php if (!empty($member['image'])): ?>
                                                    <img src="<?php echo getImageUrl($member['image']); ?>" alt="<?php echo htmlspecialchars($member['name']); ?>">
                                                <?php endif; ?>
                                            </div>
                                            <div class="batch-member-name">
                                                <?php echo htmlspecialchars($member['name']); ?>
                                            </div>
                                            <div class="batch-member-position">
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
                               class="<?php echo $batchOrg && $org['id'] == $batchOrg['id'] ? 'active' : ''; ?>">
                                <?php echo htmlspecialchars($org['name']); ?> Batches
                            </a>
                        </li>
                    <?php endforeach; ?>
                    <?php if ($batchOrg): ?>
                        <li><a href="<?php echo PUBLIC_URL; ?>/organization.php?id=<?php echo $batchOrg['id']; ?>">← Back to <?php echo htmlspecialchars($batchOrg['name']); ?></a></li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </aside>
</div>

<?php include '../includes/footer.php'; ?>

