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

// Fetch faculty subcategories for sidebar
$facultySubcategories = dbFetchAll("SELECT * FROM institute_sections WHERE type = 'faculty_subcategory' AND status = 'published' ORDER BY display_order ASC, title ASC");

// Fetch data per section
$about = $mission = $vision = $logo = $banner = null;
$facultyUnits = $adminReps = $programs = [];
$selectedFacultyUnit = $_GET['unit'] ?? null;
$facultyUnitOptions = ['IS', 'CS', 'DS', 'Higher Ups'];

if ($section === 'about') {
    $about = dbFetchOne("SELECT * FROM institute_info WHERE section = 'about' AND status = 'published'");
    $mission = dbFetchOne("SELECT * FROM institute_info WHERE section = 'mission' AND status = 'published'");
    $vision = dbFetchOne("SELECT * FROM institute_info WHERE section = 'vision' AND status = 'published'");
    $logo = dbFetchOne("SELECT * FROM institute_info WHERE section = 'logo' AND status = 'published'");
    $banner = dbFetchOne("SELECT * FROM institute_info WHERE section = 'banner' AND status = 'published'");
} else {
    // For faculty section, filter by unit if selected
    if ($section === 'faculty') {
        $facultyQuery = "SELECT * FROM institute_sections WHERE type = 'faculty_unit' AND status = 'published'";
        $facultyParams = [];
        if ($selectedFacultyUnit && in_array($selectedFacultyUnit, $facultyUnitOptions, true)) {
            $facultyQuery .= " AND description = ?";
            $facultyParams[] = $selectedFacultyUnit;
        }
        $facultyQuery .= " ORDER BY display_order ASC";
        $facultyUnits = dbFetchAll($facultyQuery, $facultyParams);
    } else {
        $facultyUnits = dbFetchAll("SELECT * FROM institute_sections WHERE type = 'faculty_unit' AND status = 'published' ORDER BY display_order ASC");
    }
    $adminReps = dbFetchAll("SELECT * FROM institute_sections WHERE type = 'admin_representative' AND status = 'published' ORDER BY display_order ASC");
    
    // For program section, fetch all programs
    if ($section === 'program') {
        $programs = dbFetchAll("SELECT * FROM institute_sections WHERE type = 'program' AND status = 'published' ORDER BY display_order ASC");
    } else {
        $programs = dbFetchAll("SELECT * FROM institute_sections WHERE type = 'program' AND status = 'published' ORDER BY display_order ASC");
    }
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
                        
                        <!-- Faculty Unit Selection Dropdown -->
                        <div class="mb-6 md:mb-8">
                            <label for="faculty-unit-select" class="block text-sm font-bold text-gray-700 mb-2">Select Faculty Unit</label>
                            <select 
                                id="faculty-unit-select" 
                                class="w-full max-w-md px-4 py-3 border-2 border-gray-200 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 bg-white"
                                onchange="if(this.value) { window.location.href='<?php echo PUBLIC_URL; ?>/institute.php?section=faculty&unit=' + encodeURIComponent(this.value); } else { window.location.href='<?php echo PUBLIC_URL; ?>/institute.php?section=faculty'; }"
                            >
                                <option value="">-- Select a Faculty Unit --</option>
                                <option value="IS" <?php echo $selectedFacultyUnit === 'IS' ? 'selected' : ''; ?>>IS (Information Systems)</option>
                                <option value="CS" <?php echo $selectedFacultyUnit === 'CS' ? 'selected' : ''; ?>>CS (Computer Science)</option>
                                <option value="DS" <?php echo $selectedFacultyUnit === 'DS' ? 'selected' : ''; ?>>DS (Data Science)</option>
                                <option value="Higher Ups" <?php echo $selectedFacultyUnit === 'Higher Ups' ? 'selected' : ''; ?>>Higher Ups</option>
                            </select>
                            <?php if ($selectedFacultyUnit): ?>
                                <div class="mt-2">
                                    <a href="<?php echo PUBLIC_URL; ?>/institute.php?section=faculty" class="text-indigo-600 hover:text-indigo-800 font-semibold text-sm">← Clear selection</a>
                                </div>
                            <?php endif; ?>
                        </div>
                        
                        <?php if (!$selectedFacultyUnit): ?>
                            <p class="batch-section-empty text-sm md:text-base">Please select a faculty unit from the dropdown above to view faculty members.</p>
                        <?php elseif (empty($facultyUnits)): ?>
                            <p class="batch-section-empty text-sm md:text-base">No faculty members found for the selected unit.</p>
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
                <div class="batches-inner-panel batch-detail-inner-panel">
                    <section class="batch-detail-section px-2 md:px-0">
                        <h2 class="batch-section-heading mb-4 md:mb-6 text-xl md:text-2xl lg:text-[32px]">PROGRAM</h2>
                        
                        <?php if (empty($programs)): ?>
                            <p class="batch-section-empty text-sm md:text-base">No programs found.</p>
                        <?php else: ?>
                            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6 md:gap-8 justify-items-center">
                                <?php foreach ($programs as $program): ?>
                                    <div class="text-center w-full max-w-[300px]">
                                        <?php if (!empty($program['image'])): ?>
                                            <div class="flex justify-center mb-3 w-full aspect-[196/182] max-w-[196px] mx-auto">
                                                <img src="<?php echo getImageUrl($program['image']); ?>" alt="<?php echo htmlspecialchars($program['title']); ?>" class="w-full h-full rounded-[30px] object-cover bg-[#D9D9D9]">
                                            </div>
                                        <?php endif; ?>
                                        <div class="w-full mx-auto font-['Inter'] font-semibold text-base md:text-lg leading-[22px] text-center text-[#0F181D] mb-2 px-2">
                                            <?php echo htmlspecialchars($program['title']); ?>
                                        </div>
                                        <?php if (!empty($program['content'])): ?>
                                            <div class="text-sm md:text-base text-black text-center px-2">
                                                <?php echo nl2br(htmlspecialchars($program['content'])); ?>
                                            </div>
                                        <?php elseif (!empty($program['description'])): ?>
                                            <div class="text-sm md:text-base text-black text-center px-2">
                                                <?php echo nl2br(htmlspecialchars($program['description'])); ?>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
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
                    <li class="org-item-with-batch">
                        <a href="<?php echo PUBLIC_URL; ?>/faculty.php" class="<?php echo $section === 'faculty' ? 'active' : ''; ?>">Faculty Unit</a>
                        
                        <!-- Subcategories - Show below Faculty Unit on hover -->
                        <?php if (!empty($facultySubcategories)): ?>
                        <ul class="sidebar-sublinks batch-hover-menu">
                            <?php foreach ($facultySubcategories as $subcat): ?>
                            <li>
                                <a href="<?php echo htmlspecialchars($subcat['description'] ?: '#'); ?>" class="sidebar-sublink">
                                    <?php echo htmlspecialchars($subcat['title']); ?>
                                </a>
                            </li>
                            <?php endforeach; ?>
                        </ul>
                        <?php endif; ?>
                    </li>
                    <li><a href="<?php echo PUBLIC_URL; ?>/admin-representative.php" class="<?php echo $section === 'admin' ? 'active' : ''; ?>">Admin Representative</a></li>
                    <li><a href="<?php echo PUBLIC_URL; ?>/institute.php?section=program" class="<?php echo $section === 'program' ? 'active' : ''; ?>">Program</a></li>
                </ul>
            </div>
            <div class="sidebar-section">
                <h2 class="sidebar-title">STUDENT ORGANIZATION</h2>
                <ul class="sidebar-links">
                    <?php 
                    // Check if we're currently on the batches page
                    $isBatchesPage = (basename($_SERVER['PHP_SELF']) === 'batches.php' && isset($_GET['org_id']));
                    $currentBatchOrgId = $isBatchesPage ? (int)$_GET['org_id'] : null;
                    
                    foreach ($organizations as $org): 
                    ?>
                        <li class="org-item-with-batch">
                            <a href="<?php echo PUBLIC_URL; ?>/organization.php?id=<?php echo $org['id']; ?>"><?php echo htmlspecialchars($org['name']); ?></a>
                            
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
