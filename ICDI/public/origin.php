<?php
require_once '../includes/config.php';
require_once '../includes/database.php';
require_once '../includes/upload.php';

$pageTitle = 'ORIGIN - PROWLWAY';
$bodyClass = 'origin-page';
include '../includes/header.php';

// Fetch institute origin content (About, Mission, Vision, Logo)
$about  = dbFetchOne("SELECT * FROM institute_info WHERE section = 'about' AND status = 'published'");
$mission = dbFetchOne("SELECT * FROM institute_info WHERE section = 'mission' AND status = 'published'");
$vision  = dbFetchOne("SELECT * FROM institute_info WHERE section = 'vision' AND status = 'published'");
$logo    = dbFetchOne("SELECT * FROM institute_info WHERE section = 'logo' AND status = 'published'");

// Fetch student organizations for sidebar/directory
$organizations = dbFetchAll("SELECT * FROM student_organizations WHERE status = 'active' ORDER BY display_order ASC");
?>

<div class="origin-page-container">
    <!-- Main Content Area -->
    <div class="origin-main-content">
        <div class="origin-content-panel">
            <!-- Inner white panel to match Figma layout -->
            <div class="origin-inner-panel">
                <!-- Top Hero Image -->
                <div class="origin-hero">
                    <img src="<?php echo ASSETS_URL; ?>/images/BANNER.png" alt="ICDI Banner" class="origin-hero-image">
                </div>

                <!-- About Section -->
                <section class="origin-section origin-section-about">
                    <h2 class="origin-section-title">About</h2>
                    <div class="origin-text">
                        <?php
                        if ($about && !empty($about['content'])) {
                            echo nl2br(htmlspecialchars($about['content']));
                        } else {
                            echo 'Origin details are not available yet.';
                        }
                        ?>
                    </div>
                </section>

                <div class="origin-divider"></div>

                <!-- Mission & Vision Side by Side -->
                <section class="origin-section origin-section-split">
                    <div class="origin-split-column">
                        <h2 class="origin-section-title">Mission</h2>
                        <div class="origin-text">
                            <?php
                            if ($mission && !empty($mission['content'])) {
                                echo nl2br(htmlspecialchars($mission['content']));
                            } else {
                                echo 'Mission content is not available yet.';
                            }
                            ?>
                        </div>
                    </div>
                    <div class="origin-split-column">
                        <h2 class="origin-section-title">Vision</h2>
                        <div class="origin-text">
                            <?php
                            if ($vision && !empty($vision['content'])) {
                                echo nl2br(htmlspecialchars($vision['content']));
                            } else {
                                echo 'Vision content is not available yet.';
                            }
                            ?>
                        </div>
                    </div>
                </section>

                <div class="origin-divider"></div>

                <!-- Logo Section -->
                <section class="origin-section origin-section-logo">
                    <h2 class="origin-section-title">Logo</h2>
                    <div class="origin-logo-layout">
                        <div class="origin-logo-block">
                            <div class="origin-logo-frame">
                                <?php if ($logo && !empty($logo['image'])): ?>
                                    <img src="<?php echo getImageUrl($logo['image']); ?>" alt="ICDI Logo" class="origin-logo-image">
                                <?php else: ?>
                                    <img src="<?php echo ASSETS_URL; ?>/images/ICDI.png" alt="ICDI Logo" class="origin-logo-image">
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="origin-logo-text">
                            <?php
                            if ($logo && !empty($logo['content'])) {
                                echo nl2br(htmlspecialchars($logo['content']));
                            } else {
                                echo 'Logo description will be added soon.';
                            }
                            ?>
                        </div>
                    </div>
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
                    <li><a href="<?php echo PUBLIC_URL; ?>/institute.php?section=faculty">Faculty Unit</a></li>
                    <li><a href="<?php echo PUBLIC_URL; ?>/institute.php?section=admin">Admin Representative</a></li>
                    <li><a href="<?php echo PUBLIC_URL; ?>/institute.php?section=program">Program</a></li>
                </ul>
            </div>
            
            <!-- STUDENT ORGANIZATION Section -->
            <div class="sidebar-section">
                <h2 class="sidebar-title">STUDENT ORGANIZATION</h2>
                <ul class="sidebar-links">
                    <?php foreach ($organizations as $org): ?>
                        <li>
                            <a href="<?php echo PUBLIC_URL; ?>/organization.php?id=<?php echo $org['id']; ?>">
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

