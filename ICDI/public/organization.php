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

// Helper: parse core values from content (one value per line)
$coreValues = [];
if ($organization && !empty($organization['content'])) {
    $lines = preg_split('/\r\n|\r|\n/', $organization['content']);
    foreach ($lines as $line) {
        $trimmed = trim($line, " \t\n\r\0\x0B-");
        if ($trimmed !== '') {
            $coreValues[] = $trimmed;
        }
    }
}

// Fetch organizations list for sidebar
$organizationsList = dbFetchAll("SELECT * FROM student_organizations WHERE status = 'active' ORDER BY display_order ASC");
?>

<div class="origin-page-container">
    <!-- Main Content Area -->
    <div class="origin-main-content">
        <div class="origin-content-panel">
            <?php if (!$organization): ?>
                <h1 class="origin-page-title">Student Organization</h1>
                <p>No organization found for this ID.</p>
            <?php else: ?>
                <div class="origin-inner-panel org-inner-panel">
                    <!-- Top hero image / banner -->
                    <div class="origin-hero">
                        <?php
                        $batchPageUrl = PUBLIC_URL . '/batches.php';
                        ?>
                        <a href="<?php echo htmlspecialchars($batchPageUrl); ?>" class="org-logo-link">
                            <?php if (!empty($organization['logo'])): ?>
                                <img src="<?php echo getImageUrl($organization['logo']); ?>" alt="<?php echo htmlspecialchars($organization['name']); ?>" class="origin-hero-image">
                            <?php else: ?>
                                <img src="<?php echo ASSETS_URL; ?>/images/ICDI.png" alt="<?php echo htmlspecialchars($organization['name'] ?? 'Organization'); ?>" class="origin-hero-image">
                            <?php endif; ?>
                        </a>
                    </div>

                    <!-- ABOUT -->
                    <section class="origin-section org-section-about">
                        <h2 class="origin-section-title">About</h2>
                        <div class="origin-text">
                            <?php if (!empty($organization['description'])): ?>
                                <?php echo nl2br(htmlspecialchars($organization['description'])); ?>
                            <?php else: ?>
                                This organization does not have an about description yet.
                            <?php endif; ?>
                        </div>
                    </section>

                    <div class="origin-divider"></div>

                    <!-- MISSION & VISION (optional, reused as long-form details if present) -->
                    <?php if (!empty($organization['content'])): ?>
                        <section class="origin-section origin-section-split org-section-mission-vision">
                            <div class="origin-split-column">
                                <h2 class="origin-section-title">Mission</h2>
                                <div class="origin-text">
                                    <?php echo nl2br(htmlspecialchars($organization['content'])); ?>
                                </div>
                            </div>
                            <div class="origin-split-column">
                                <h2 class="origin-section-title">Vision</h2>
                                <div class="origin-text">
                                    <?php echo nl2br(htmlspecialchars($organization['content'])); ?>
                                </div>
                            </div>
                        </section>

                        <div class="origin-divider"></div>
                    <?php endif; ?>

                    <!-- CORE VALUES LIST -->
                    <section class="origin-section org-section-core-values">
                        <h2 class="origin-section-title">Core Values</h2>
                        <?php if (empty($coreValues)): ?>
                            <p class="origin-text">Core values for this organization have not been added yet.</p>
                        <?php else: ?>
                            <div class="org-core-values-list">
                                <?php foreach ($coreValues as $value): ?>
                                    <div class="org-core-item">
                                        <div class="org-core-icon"></div>
                                        <div class="org-core-content">
                                            <div class="org-core-title"><?php echo htmlspecialchars($value); ?></div>
                                            <?php if (!empty($organization['description'])): ?>
                                                <div class="org-core-text">
                                                    <?php echo nl2br(htmlspecialchars($organization['description'])); ?>
                                                </div>
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
                        <h2 class="origin-section-title">Logo</h2>
                        <div class="origin-logo-layout">
                            <div class="origin-logo-frame">
                                <a href="<?php echo htmlspecialchars($batchPageUrl); ?>" class="org-logo-link">
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
                            <a href="<?php echo PUBLIC_URL; ?>/organization.php?id=<?php echo $org['id']; ?>"
                               class="<?php echo $organization && $org['id'] == $organization['id'] ? 'active' : ''; ?>">
                                <?php echo htmlspecialchars($org['name']); ?>
                            </a>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>
    </aside>
</div>

<?php include '../includes/footer.php'; ?>

