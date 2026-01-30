<?php
require_once '../includes/config.php';
require_once '../includes/database.php';
require_once '../includes/upload.php';

$bodyClass = 'institute-page';

// Which page: about | faculty | admin | program (one section per page)
$section = isset($_GET['section']) ? $_GET['section'] : 'about';
$allowed = ['about', 'faculty', 'admin', 'program'];
if (!in_array($section, $allowed, true)) {
    $section = 'about';
}

$sectionTitles = [
    'about' => 'About',
    'faculty' => 'Faculty Unit',
    'admin' => 'Admin Representative',
    'program' => 'Program'
];
$pageTitle = $sectionTitles[$section] . ' - Institute - PROWLWAY';

// Fetch data needed for all pages (sidebar)
$organizations = dbFetchAll("SELECT * FROM student_organizations WHERE status = 'active' ORDER BY display_order ASC");

// Fetch data per section
$about = $mission = $vision = $logo = $banner = null;
$facultyUnits = $adminReps = $programs = [];

if ($section === 'about') {
    $about = dbFetchOne("SELECT * FROM institute_info WHERE section = 'about' AND status = 'published'");
    $mission = dbFetchOne("SELECT * FROM institute_info WHERE section = 'mission' AND status = 'published'");
    $vision = dbFetchOne("SELECT * FROM institute_info WHERE section = 'vision' AND status = 'published'");
    $logo = dbFetchOne("SELECT * FROM institute_info WHERE section = 'logo' AND status = 'published'");
    $banner = dbFetchOne("SELECT * FROM institute_info WHERE section = 'banner' AND status = 'published'");
} else {
    $facultyUnits = dbFetchAll("SELECT * FROM institute_sections WHERE type = 'faculty_unit' AND status = 'published' ORDER BY display_order ASC");
    $adminReps = dbFetchAll("SELECT * FROM institute_sections WHERE type = 'admin_representative' AND status = 'published' ORDER BY display_order ASC");
    $programs = dbFetchAll("SELECT * FROM institute_sections WHERE type = 'program' AND status = 'published' ORDER BY display_order ASC");
}

$bannerSrc = ($banner && !empty($banner['image'])) ? getImageUrl($banner['image']) : (ASSETS_URL . '/images/BANNER.png');
$logoSrc = ($logo && !empty($logo['image'])) ? getImageUrl($logo['image']) : (ASSETS_URL . '/images/ICDI.png');

include '../includes/header.php';
?>

<div class="institute-page-container px-4 md:px-6 lg:px-8">
    <div class="institute-main-content">
        <div class="institute-content-panel px-4 md:px-6 lg:px-8 py-6 md:py-8">
            <?php if ($section === 'about'): ?>
                <!-- PAGE 1: ABOUT -->
                <div class="institute-hero-banner mb-6 md:mb-8 rounded-lg overflow-hidden">
                    <img src="<?php echo htmlspecialchars($bannerSrc); ?>" alt="ICDI Banner" class="w-full h-auto">
                </div>
                <section class="institute-section mb-6 md:mb-8">
                    <h2 class="section-title text-xl md:text-2xl lg:text-3xl font-bold mb-4 md:mb-6">About</h2>
                    <div class="section-content">
                        <?php echo $about ? nl2br(htmlspecialchars($about['content'])) : 'Content not available.'; ?>
                    </div>
                    <?php if ($mission): ?>
                    <div class="institute-subsection">
                        <h3 class="subsection-title">Mission</h3>
                        <div class="section-content"><?php echo nl2br(htmlspecialchars($mission['content'])); ?></div>
                    </div>
                    <?php endif; ?>
                    <?php if ($vision): ?>
                    <div class="institute-subsection">
                        <h3 class="subsection-title">Vision</h3>
                        <div class="section-content"><?php echo nl2br(htmlspecialchars($vision['content'])); ?></div>
                    </div>
                    <?php endif; ?>
                    <?php if ($logo): ?>
                    <div class="institute-subsection logo-subsection">
                        <h3 class="subsection-title">Logo</h3>
                        <div class="logo-section-content">
                            <div class="logo-badge-display">
                                <img src="<?php echo htmlspecialchars($logoSrc); ?>" alt="ICDI Logo" class="logo-display-image">
                            </div>
                            <div class="logo-description">
                                <p><?php echo nl2br(htmlspecialchars($logo['content'])); ?></p>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>
                </section>

            <?php elseif ($section === 'faculty'): ?>
                <!-- PAGE 2: FACULTY UNIT -->
                <section class="institute-section mb-6 md:mb-8">
                    <h2 class="section-title text-xl md:text-2xl lg:text-3xl font-bold mb-4 md:mb-6">Faculty Unit</h2>
                    <div class="institute-list grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 md:gap-6">
                        <?php if (empty($facultyUnits)): ?>
                            <p>No faculty information available.</p>
                        <?php else: ?>
                            <?php foreach ($facultyUnits as $faculty): ?>
                                <div class="institute-item">
                                    <?php if (!empty($faculty['image'])): ?>
                                        <img src="<?php echo getImageUrl($faculty['image']); ?>" alt="<?php echo htmlspecialchars($faculty['title']); ?>" class="institute-item-image">
                                    <?php endif; ?>
                                    <div class="institute-item-content">
                                        <h3><?php echo htmlspecialchars($faculty['title']); ?></h3>
                                        <?php if (!empty($faculty['description'])): ?>
                                            <p><?php echo nl2br(htmlspecialchars($faculty['description'])); ?></p>
                                        <?php endif; ?>
                                        <?php if (!empty($faculty['content'])): ?>
                                            <div class="institute-item-details"><?php echo nl2br(htmlspecialchars($faculty['content'])); ?></div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </section>

            <?php elseif ($section === 'admin'): ?>
                <!-- PAGE 3: ADMIN REPRESENTATIVE -->
                <section class="institute-section">
                    <h2 class="section-title">Admin Representative</h2>
                    <div class="institute-list">
                        <?php if (empty($adminReps)): ?>
                            <p>No admin representative information available.</p>
                        <?php else: ?>
                            <?php foreach ($adminReps as $rep): ?>
                                <div class="institute-item">
                                    <?php if (!empty($rep['image'])): ?>
                                        <img src="<?php echo getImageUrl($rep['image']); ?>" alt="<?php echo htmlspecialchars($rep['title']); ?>" class="institute-item-image">
                                    <?php endif; ?>
                                    <div class="institute-item-content">
                                        <h3><?php echo htmlspecialchars($rep['title']); ?></h3>
                                        <?php if (!empty($rep['description'])): ?>
                                            <p><?php echo nl2br(htmlspecialchars($rep['description'])); ?></p>
                                        <?php endif; ?>
                                        <?php if (!empty($rep['content'])): ?>
                                            <div class="institute-item-details"><?php echo nl2br(htmlspecialchars($rep['content'])); ?></div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </section>

            <?php elseif ($section === 'program'): ?>
                <!-- PAGE 4: PROGRAM -->
                <section class="institute-section institute-animate institute-animate-delay-1">
                    <h2 class="section-title institute-animate-title">Program</h2>
                    <div class="institute-list institute-list-animate">
                        <?php if (empty($programs)): ?>
                            <p class="institute-empty">No program information available.</p>
                        <?php else: ?>
                            <?php foreach ($programs as $i => $program): ?>
                                <div class="institute-item institute-animate-item" style="--item-index: <?php echo $i; ?>">
                                    <?php if (!empty($program['image'])): ?>
                                        <img src="<?php echo getImageUrl($program['image']); ?>" alt="<?php echo htmlspecialchars($program['title']); ?>" class="institute-item-image">
                                    <?php else: ?>
                                        <div class="institute-item-image-placeholder" aria-hidden="true"><span>PR</span></div>
                                    <?php endif; ?>
                                    <div class="institute-item-content">
                                        <h3><?php echo htmlspecialchars($program['title']); ?></h3>
                                        <?php if (!empty($program['description'])): ?>
                                            <p><?php echo nl2br(htmlspecialchars($program['description'])); ?></p>
                                        <?php endif; ?>
                                        <?php if (!empty($program['content'])): ?>
                                            <div class="institute-item-details"><?php echo nl2br(htmlspecialchars($program['content'])); ?></div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </section>
            <?php endif; ?>

        </div>
    </div>

    <!-- Sidebar -->
    <aside class="institute-sidebar">
        <div class="sidebar-panel">
            <div class="sidebar-section">
                <h2 class="sidebar-title">INSTITUTE</h2>
                <ul class="sidebar-links">
                    <li><a href="<?php echo PUBLIC_URL; ?>/institute.php?section=about" class="<?php echo $section === 'about' ? 'active' : ''; ?>">About</a></li>
                    <li><a href="<?php echo PUBLIC_URL; ?>/institute.php?section=faculty" class="<?php echo $section === 'faculty' ? 'active' : ''; ?>">Faculty Unit</a></li>
                    <li><a href="<?php echo PUBLIC_URL; ?>/institute.php?section=admin" class="<?php echo $section === 'admin' ? 'active' : ''; ?>">Admin Representative</a></li>
                    <li><a href="<?php echo PUBLIC_URL; ?>/institute.php?section=program" class="<?php echo $section === 'program' ? 'active' : ''; ?>">Program</a></li>
                </ul>
            </div>
            <div class="sidebar-section">
                <h2 class="sidebar-title">STUDENT ORGANIZATION</h2>
                <ul class="sidebar-links">
                    <?php foreach ($organizations as $org): ?>
                        <li><a href="<?php echo PUBLIC_URL; ?>/organization.php?id=<?php echo $org['id']; ?>"><?php echo htmlspecialchars($org['name']); ?></a></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>
    </aside>
</div>

<?php include '../includes/footer.php'; ?>
