<?php
require_once '../includes/config.php';
require_once '../includes/database.php';
require_once '../includes/upload.php';

$pageTitle = 'Institute - PROWLWAY';
$bodyClass = 'institute-page';
$section = $_GET['section'] ?? 'about';
include '../includes/header.php';

// Fetch institute information
$instituteInfo = dbFetchOne("SELECT * FROM institute_info WHERE section = ? AND status = 'published'", [$section]);
if (!$instituteInfo && $section !== 'about') {
    $instituteInfo = dbFetchOne("SELECT * FROM institute_info WHERE section = 'about' AND status = 'published'");
    $section = 'about';
}

// Fetch institute sections
$facultyUnits = dbFetchAll("SELECT * FROM institute_sections WHERE type = 'faculty_unit' AND status = 'published' ORDER BY display_order ASC");
$adminReps = dbFetchAll("SELECT * FROM institute_sections WHERE type = 'admin_representative' AND status = 'published' ORDER BY display_order ASC");
$programs = dbFetchAll("SELECT * FROM institute_sections WHERE type = 'program' AND status = 'published' ORDER BY display_order ASC");
?>

<div class="institute-page-container">
    <!-- Main Content Area -->
    <div class="institute-main-content">
        <div class="institute-content-panel">
            <?php if ($section === 'about'): ?>
                <!-- About Section -->
                <?php
                $about = dbFetchOne("SELECT * FROM institute_info WHERE section = 'about' AND status = 'published'");
                $mission = dbFetchOne("SELECT * FROM institute_info WHERE section = 'mission' AND status = 'published'");
                $vision = dbFetchOne("SELECT * FROM institute_info WHERE section = 'vision' AND status = 'published'");
                $logo = dbFetchOne("SELECT * FROM institute_info WHERE section = 'logo' AND status = 'published'");
                ?>
                
                <!-- Hero Banner -->
                <div class="institute-hero-banner">
                    <img src="<?php echo ASSETS_URL; ?>/images/BANNER.png" alt="ICDI Banner">
                </div>
                
                <!-- About -->
                <section class="institute-section">
                    <h2 class="section-title">About</h2>
                    <div class="section-content">
                        <?php echo $about ? nl2br(htmlspecialchars($about['content'])) : 'Content not available.'; ?>
                    </div>
                </section>
                
                <!-- Mission -->
                <section class="institute-section">
                    <h2 class="section-title">Mission</h2>
                    <div class="section-content">
                        <?php echo $mission ? nl2br(htmlspecialchars($mission['content'])) : 'Content not available.'; ?>
                    </div>
                </section>
                
                <!-- Vision -->
                <section class="institute-section">
                    <h2 class="section-title">Vision</h2>
                    <div class="section-content">
                        <?php echo $vision ? nl2br(htmlspecialchars($vision['content'])) : 'Content not available.'; ?>
                    </div>
                </section>
                
                <!-- Logo -->
                <section class="institute-section">
                    <h2 class="section-title">Logo</h2>
                    <div class="logo-section-content">
                        <div class="logo-badge-display">
                            <img src="<?php echo ASSETS_URL; ?>/images/ICDI.png" alt="ICDI Logo" class="logo-display-image">
                        </div>
                        <div class="logo-description">
                            <?php if ($logo): ?>
                                <p><?php echo nl2br(htmlspecialchars($logo['content'])); ?></p>
                            <?php else: ?>
                                <p>Lorem Ipsum Dolor Sit Amet, Consectetur Adipiscing Elit, Sed Do Eiusmod Tempor Incididunt Ut Labore Et Dolore Magna Aliqua.</p>
                            <?php endif; ?>
                        </div>
                    </div>
                </section>
                
            <?php elseif ($section === 'faculty'): ?>
                <!-- Faculty Unit Section -->
                <h2 class="section-title">Faculty Unit</h2>
                <div class="institute-list">
                    <?php if (empty($facultyUnits)): ?>
                        <p>No faculty information available.</p>
                    <?php else: ?>
                        <?php foreach ($facultyUnits as $faculty): ?>
                            <div class="institute-item">
                                <?php if ($faculty['image']): ?>
                                    <img src="<?php echo getImageUrl($faculty['image']); ?>" alt="<?php echo htmlspecialchars($faculty['title']); ?>" class="institute-item-image">
                                <?php endif; ?>
                                <div class="institute-item-content">
                                    <h3><?php echo htmlspecialchars($faculty['title']); ?></h3>
                                    <?php if ($faculty['description']): ?>
                                        <p><?php echo nl2br(htmlspecialchars($faculty['description'])); ?></p>
                                    <?php endif; ?>
                                    <?php if ($faculty['content']): ?>
                                        <div class="institute-item-details"><?php echo nl2br(htmlspecialchars($faculty['content'])); ?></div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
                
            <?php elseif ($section === 'admin'): ?>
                <!-- Admin Representative Section -->
                <h2 class="section-title">Admin Representative</h2>
                <div class="institute-list">
                    <?php if (empty($adminReps)): ?>
                        <p>No admin representative information available.</p>
                    <?php else: ?>
                        <?php foreach ($adminReps as $admin): ?>
                            <div class="institute-item">
                                <?php if ($admin['image']): ?>
                                    <img src="<?php echo getImageUrl($admin['image']); ?>" alt="<?php echo htmlspecialchars($admin['title']); ?>" class="institute-item-image">
                                <?php endif; ?>
                                <div class="institute-item-content">
                                    <h3><?php echo htmlspecialchars($admin['title']); ?></h3>
                                    <?php if ($admin['description']): ?>
                                        <p><?php echo nl2br(htmlspecialchars($admin['description'])); ?></p>
                                    <?php endif; ?>
                                    <?php if ($admin['content']): ?>
                                        <div class="institute-item-details"><?php echo nl2br(htmlspecialchars($admin['content'])); ?></div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
                
            <?php elseif ($section === 'program'): ?>
                <!-- Program Section -->
                <h2 class="section-title">Program</h2>
                <div class="institute-list">
                    <?php if (empty($programs)): ?>
                        <p>No program information available.</p>
                    <?php else: ?>
                        <?php foreach ($programs as $program): ?>
                            <div class="institute-item">
                                <?php if ($program['image']): ?>
                                    <img src="<?php echo getImageUrl($program['image']); ?>" alt="<?php echo htmlspecialchars($program['title']); ?>" class="institute-item-image">
                                <?php endif; ?>
                                <div class="institute-item-content">
                                    <h3><?php echo htmlspecialchars($program['title']); ?></h3>
                                    <?php if ($program['description']): ?>
                                        <p><?php echo nl2br(htmlspecialchars($program['description'])); ?></p>
                                    <?php endif; ?>
                                    <?php if ($program['content']): ?>
                                        <div class="institute-item-details"><?php echo nl2br(htmlspecialchars($program['content'])); ?></div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
    
    <!-- Sidebar -->
    <aside class="institute-sidebar">
        <div class="sidebar-panel">
            <!-- INSTITUTE Section -->
            <div class="sidebar-section">
                <h2 class="sidebar-title">INSTITUTE</h2>
                <ul class="sidebar-links">
                    <li><a href="<?php echo PUBLIC_URL; ?>/institute.php?section=about" class="<?php echo $section === 'about' ? 'active' : ''; ?>">About</a></li>
                    <li><a href="<?php echo PUBLIC_URL; ?>/institute.php?section=faculty" class="<?php echo $section === 'faculty' ? 'active' : ''; ?>">Faculty Unit</a></li>
                    <li><a href="<?php echo PUBLIC_URL; ?>/institute.php?section=admin" class="<?php echo $section === 'admin' ? 'active' : ''; ?>">Admin Representative</a></li>
                    <li><a href="<?php echo PUBLIC_URL; ?>/institute.php?section=program" class="<?php echo $section === 'program' ? 'active' : ''; ?>">Program</a></li>
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

