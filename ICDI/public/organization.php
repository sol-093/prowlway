<?php
require_once '../includes/config.php';
require_once '../includes/database.php';
require_once '../includes/upload.php';

$orgId = isset($_GET['id']) ? (int) $_GET['id'] : 0;

// Fetch organization
$organization = null;
if ($orgId > 0) {
    $organization = dbFetchOne("SELECT * FROM student_organizations WHERE id = ? AND status = 'active'", [$orgId]);
}

if ($organization) {
    $pageTitle = ($organization['acronym'] ? $organization['acronym'] . ' - ' : '') . $organization['name'] . ' · Student Organization - PROWLWAY';
} else {
    $pageTitle = 'Student Organization - PROWLWAY';
}

$bodyClass = 'origin-page';
include '../includes/header.php';

// Decode social media JSON if present
$socialMedia = [];
if ($organization && !empty($organization['social_media'])) {
    $decoded = json_decode($organization['social_media'], true);
    if (is_array($decoded)) {
        $socialMedia = $decoded;
    }
}

// Load core values from organization_core_values table (icon, title, description)
$coreValues = [];
if ($organization && $orgId > 0) {
    $coreValues = dbFetchAll("SELECT icon, title, description FROM organization_core_values WHERE organization_id = ? ORDER BY display_order ASC, id ASC", [$orgId]);
}

// Fetch organizations list for sidebar
$organizationsList = dbFetchAll("SELECT * FROM student_organizations WHERE status = 'active' ORDER BY display_order ASC");

// Fetch faculty subcategories for sidebar
$facultySubcategories = dbFetchAll("SELECT * FROM institute_sections WHERE type = 'faculty_subcategory' AND status = 'published' ORDER BY display_order ASC, title ASC");
?>

<div class="origin-page-container">
    <!-- Main Content Area -->
    <div class="origin-main-content">
        <div class="origin-content-panel">
            <?php if (!$organization): ?>
                <h1 class="origin-page-title">Student Organization</h1>
                <p>No organization found for this ID.</p>
            <?php else: ?>
                <div class="origin-inner-panel org-inner-panel px-4 md:px-6 lg:px-8 py-6 md:py-8">
                    <!-- Top hero / banner (landscape only; different from logo; from admin) -->
                    <div class="origin-hero origin-hero-banner mb-6 md:mb-8 rounded-lg overflow-hidden">
                        <?php
                        $batchPageUrl = PUBLIC_URL . '/batches.php?org_id=' . $organization['id'];
                        $heroSrc = !empty($organization['banner_image'])
                            ? getImageUrl($organization['banner_image'])
                            : (!empty($organization['logo']) ? getImageUrl($organization['logo']) : (ASSETS_URL . '/IMG/ICONS/OFFICE.png'));
                        ?>
                        <a href="<?php echo htmlspecialchars($batchPageUrl); ?>" class="org-banner-link">
                            <img src="<?php echo htmlspecialchars($heroSrc); ?>" alt="<?php echo htmlspecialchars($organization['name']); ?> banner" class="origin-hero-image">
                        </a>
                    </div>

                    <!-- ABOUT -->
                    <section class="origin-section org-section-about mb-6 md:mb-8">
                        <h2 class="origin-section-title text-xl md:text-2xl lg:text-3xl font-bold mb-3 md:mb-4">About</h2>
                        <div class="origin-text">
                            <?php if (!empty($organization['description'])): ?>
                                <?php echo nl2br(htmlspecialchars($organization['description'])); ?>
                            <?php else: ?>
                                This organization does not have an about description yet.
                            <?php endif; ?>
                        </div>
                    </section>

                    <div class="origin-divider"></div>

                    <!-- MISSION & VISION -->
                    <section class="origin-section origin-section-split org-section-mission-vision flex flex-col md:flex-row gap-6 md:gap-8 mb-6 md:mb-8">
                        <div class="origin-split-column flex-1">
                            <h2 class="origin-section-title text-xl md:text-2xl lg:text-3xl font-bold mb-3 md:mb-4">Mission</h2>
                            <div class="origin-text">
                                <?php if (!empty($organization['mission'])): ?>
                                    <?php echo nl2br(htmlspecialchars($organization['mission'])); ?>
                                <?php else: ?>
                                    Mission statement will be added soon.
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="origin-split-column flex-1">
                            <h2 class="origin-section-title text-xl md:text-2xl lg:text-3xl font-bold mb-3 md:mb-4">Vision</h2>
                            <div class="origin-text">
                                <?php if (!empty($organization['vision'])): ?>
                                    <?php echo nl2br(htmlspecialchars($organization['vision'])); ?>
                                <?php else: ?>
                                    Vision statement will be added soon.
                                <?php endif; ?>
                            </div>
                        </div>
                    </section>

                    <div class="origin-divider"></div>

                    <!-- CORE VALUES: 1:1 icon + short title + description -->
                    <section class="origin-section org-section-core-values mb-6 md:mb-8">
                        <h2 class="origin-section-title text-xl md:text-2xl lg:text-3xl font-bold mb-4 md:mb-6">Core Values</h2>
                        <?php if (empty($coreValues)): ?>
                            <p class="origin-text text-sm md:text-base">Core values for this organization have not been added yet.</p>
                        <?php else: ?>
                            <div class="org-core-values-list">
                                <?php foreach ($coreValues as $value):
                                    $title = $value['title'] ?? '';
                                    $description = $value['description'] ?? '';
                                    $iconPath = $value['icon'] ?? null;
                                    $initial = mb_substr($title, 0, 1);
                                ?>
                                    <div class="org-core-item">
                                        <div class="org-core-icon" aria-hidden="true">
                                            <?php if (!empty($iconPath)): ?>
                                                <img src="<?php echo getImageUrl($iconPath); ?>" alt="">
                                            <?php else: ?>
                                                <span class="org-core-icon-letter"><?php echo htmlspecialchars($initial); ?></span>
                                            <?php endif; ?>
                                        </div>
                                        <div class="org-core-content">
                                            <div class="org-core-title"><?php echo htmlspecialchars($title); ?></div>
                                            <?php if ($description !== ''): ?>
                                                <div class="org-core-text"><?php echo nl2br(htmlspecialchars($description)); ?></div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </section>

                    <div class="origin-divider"></div>

                    <!-- LOGO + EXTRA DESCRIPTION -->
                    <section class="origin-section origin-section-logo org-section-logo">
                        <div class="origin-logo-layout">
                            <div class="origin-logo-frame">
                                <a href="<?php echo PUBLIC_URL; ?>/batches.php?org_id=<?php echo $organization['id']; ?>" class="org-logo-link">
                                    <?php if (!empty($organization['logo'])): ?>
                                        <img src="<?php echo getImageUrl($organization['logo']); ?>" alt="<?php echo htmlspecialchars($organization['name']); ?>" class="origin-logo-image">
                                    <?php else: ?>
                                        <img src="<?php echo ASSETS_URL; ?>/images/ICDI.png" alt="<?php echo htmlspecialchars($organization['name'] ?? 'Organization'); ?>" class="origin-logo-image">
                                    <?php endif; ?>
                                </a>
                            </div>
                            <div class="origin-logo-text">
                                <?php if (!empty($organization['description'])): ?>
                                    <?php echo nl2br(htmlspecialchars($organization['description'])); ?>
                                <?php else: ?>
                                    Logo description will be added soon.
                                <?php endif; ?>
                            </div>
                        </div>
                    </section>

                    <!-- Website & Socials -->
                    <?php if (!empty($organization['website']) || !empty($socialMedia)): ?>
                        <section class="origin-section org-section-links">
                            <div class="origin-text">
                                <?php if (!empty($organization['website'])): ?>
                                    <p><strong>Website:</strong>
                                        <a href="<?php echo htmlspecialchars($organization['website']); ?>" target="_blank" rel="noopener noreferrer">
                                            <?php echo htmlspecialchars($organization['website']); ?>
                                        </a>
                                    </p>
                                <?php endif; ?>

                                <?php if (!empty($socialMedia)): ?>
                                    <p style="margin-top: 8px;"><strong>Socials:</strong></p>
                                    <ul style="margin-top: 4px; padding-left: 18px;">
                                        <?php foreach ($socialMedia as $platform => $url): ?>
                                            <?php if (!empty($url)): ?>
                                                <li>
                                                    <a href="<?php echo htmlspecialchars($url); ?>" target="_blank" rel="noopener noreferrer">
                                                        <?php echo htmlspecialchars(ucfirst($platform)); ?>
                                                    </a>
                                                </li>
                                            <?php endif; ?>
                                        <?php endforeach; ?>
                                    </ul>
                                <?php endif; ?>
                            </div>
                        </section>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Sidebar (directory, Figma style similar to origin) -->
    <aside class="origin-sidebar">
        <div class="sidebar-panel">
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
                    <?php 
                    // Check if we're currently on the batches page
                    $isBatchesPage = (basename($_SERVER['PHP_SELF']) === 'batches.php' && isset($_GET['org_id']));
                    $currentBatchOrgId = $isBatchesPage ? (int)$_GET['org_id'] : null;
                    
                    foreach ($organizationsList as $org): 
                        $isCurrentOrg = $organization && $org['id'] == $organization['id'];
                    ?>
                        <li class="org-item-with-batch">
                            <a href="<?php echo PUBLIC_URL; ?>/organization.php?id=<?php echo $org['id']; ?>"
                               class="<?php echo $isCurrentOrg ? 'active' : ''; ?>">
                                <?php echo htmlspecialchars($org['name']); ?>
                            </a>
                            
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

