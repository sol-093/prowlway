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
        <?php if ($section === 'faculty'): ?>
            <div class="institute-content-panel">
        <?php else: ?>
            <div class="institute-content-panel">
        <?php endif; ?>
            <?php if ($section === 'about'): ?>
                <!-- PAGE 1: ABOUT -->
                <div class="institute-inner-panel px-4 md:px-6 lg:px-8 py-6 md:py-8">
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
                    </section>
                </div>

            <?php elseif ($section === 'faculty'): ?>
                <!-- PAGE 2: FACULTY UNIT -->
                <div class="batches-inner-panel batch-detail-inner-panel">
                    <section class="batch-detail-section px-2 md:px-0">
                        <h2 class="batch-section-heading mb-4 md:mb-6 text-xl md:text-2xl lg:text-[32px]">FACULTY UNIT</h2>
                        <?php if (empty($facultyUnits)): ?>
                            <p class="batch-section-empty text-sm md:text-base">No faculty information available.</p>
                        <?php else: ?>
                            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6 md:gap-8 justify-items-center">
                                <?php foreach ($facultyUnits as $faculty): ?>
                                    <div class="text-center w-full max-w-[205px]">
                                        <div class="flex justify-center mb-3 w-full aspect-[196/182] max-w-[196px] mx-auto">
                                            <?php if (!empty($faculty['image'])): ?>
                                                <img src="<?php echo getImageUrl($faculty['image']); ?>" alt="<?php echo htmlspecialchars($faculty['title']); ?>" class="w-full h-full rounded-[30px] object-cover bg-[#D9D9D9]">
                                            <?php else: ?>
                                                <div class="w-full h-full rounded-[30px] bg-[#D9D9D9]"></div>
                                            <?php endif; ?>
                                        </div>
                                        <div class="w-full max-w-[205px] mx-auto font-['Inter'] font-semibold text-sm md:text-[15px] leading-[18px] text-center capitalize text-[#0F181D] truncate px-2" title="<?php echo htmlspecialchars($faculty['title']); ?>">
                                            <?php echo htmlspecialchars($faculty['title']); ?>
                                        </div>
                                        <?php if (!empty($faculty['position_title'])): ?>
                                            <div class="text-xs md:text-sm text-black italic truncate w-full max-w-[205px] mx-auto mt-0.5 px-2" title="<?php echo htmlspecialchars($faculty['position_title']); ?>">
                                                <?php echo htmlspecialchars($faculty['position_title']); ?>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </section>
                </div>

            <?php elseif ($section === 'admin'): ?>
                <!-- PAGE 3: ADMIN REPRESENTATIVE -->
                <div class="batches-inner-panel batch-detail-inner-panel">
                    <section class="batch-detail-section px-2 md:px-0">
                        <h2 class="batch-section-heading mb-4 md:mb-6 text-xl md:text-2xl lg:text-[32px]">ADMIN REPRESENTATIVE</h2>
                        <?php if (empty($adminReps)): ?>
                            <p class="batch-section-empty text-sm md:text-base">No admin representative information available.</p>
                        <?php else: ?>
                            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6 md:gap-8 justify-items-center">
                                <?php foreach ($adminReps as $rep): ?>
                                    <div class="text-center w-full max-w-[205px]">
                                        <div class="flex justify-center mb-3 w-full aspect-[196/182] max-w-[196px] mx-auto">
                                            <?php if (!empty($rep['image'])): ?>
                                                <img src="<?php echo getImageUrl($rep['image']); ?>" alt="<?php echo htmlspecialchars($rep['title']); ?>" class="w-full h-full rounded-[30px] object-cover bg-[#D9D9D9]">
                                            <?php else: ?>
                                                <div class="w-full h-full rounded-[30px] bg-[#D9D9D9]"></div>
                                            <?php endif; ?>
                                        </div>
                                        <div class="w-full max-w-[205px] mx-auto font-['Inter'] font-semibold text-sm md:text-[15px] leading-[18px] text-center capitalize text-[#0F181D] truncate px-2" title="<?php echo htmlspecialchars($rep['title']); ?>">
                                            <?php echo htmlspecialchars($rep['title']); ?>
                                        </div>
                                        <?php if (!empty($rep['position_title'])): ?>
                                            <div class="text-xs md:text-sm text-black italic truncate w-full max-w-[205px] mx-auto mt-0.5 px-2" title="<?php echo htmlspecialchars($rep['position_title']); ?>">
                                                <?php echo htmlspecialchars($rep['position_title']); ?>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </section>
                </div>

            <?php elseif ($section === 'program'): ?>
                <!-- PAGE 4: PROGRAM -->
                <div class="institute-inner-panel px-4 md:px-6 lg:px-8 py-6 md:py-8">
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
                </div>
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
