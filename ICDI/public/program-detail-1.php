<?php
require_once '../includes/config.php';
require_once '../includes/database.php';
require_once '../includes/upload.php';

$bodyClass = 'origin-page';
$pageTitle = 'Computer Science - Program - PROWLWAY';
include '../includes/header.php';

// Fetch organizations list for sidebar directory
$organizationsList = dbFetchAll("SELECT * FROM student_organizations WHERE status = 'active' ORDER BY display_order ASC");

// Fetch faculty subcategories for sidebar
$facultySubcategories = dbFetchAll("SELECT * FROM institute_sections WHERE type = 'faculty_subcategory' AND status = 'published' ORDER BY display_order ASC, title ASC");

// Program content (static)
$degreeTitle = 'Bachelor of Science in Computer Science';
$overview = [
    'The BS in Computer Science (BSCS) is a four-year degree program that equips students with the technical expertise and analytical skills needed to design, develop, and maintain complex computing systems.',
    'This program emphasizes both theoretical foundations and practical applications in programming, algorithms, software engineering, computer architecture, artificial intelligence, and cybersecurity. Students learn to solve real-world problems through computational thinking, research, and innovation, preparing them for careers in the rapidly evolving tech industry.'
];
$careerPrimary = [
    'Systems Analyst',
    'Software Developer / Engineer',
    'AI / Machine Learning Engineer',
    'Cybersecurity Analyst',
    'Network Administrator',
    'Database Administrator',
];
$careerSecondary = [
    'UX/UI Designer',
    'Mobile / Web Application Developer',
    'IT Consultant',
    'Cloud Solutions Specialist',
    'Researcher in Computing',
];
$requirements = [
    ['General Education Courses (GEE)', '9'],
    ['General Education Electives (GEC)', '33'],
    ['Physical Education (PE)', '8'],
    ['National Service Training Program (NSTP)', '6'],
    ['KLD/Institute Studies', '0'],
    ['Common Computing Courses (CCCS)', '18'],
    ['CS Professional Courses (PCCS)', '60'],
    ['Professional Electives', '12'],
    ['Capstone / Thesis', '6'],
    ['Practicum', '3'],
    ['Total No. of Units', '155'],
];
?>

<div class="origin-page-container">
    <!-- Main Content Area -->
    <div class="origin-main-content">
        <div class="origin-content-panel">
            <div class="batches-inner-panel batch-detail-inner-panel">
                <div class="mb-4">
                    <a href="<?php echo PUBLIC_URL; ?>/institute.php?section=about" class="text-sm text-black inline-flex items-center gap-1">
                        ← Back to Institute
                    </a>
                </div>
                
                <!-- Page title -->
                <div class="mb-6 md:mb-8">
                    <h1 class="text-2xl md:text-3xl font-bold text-gray-900 px-2 md:px-0">Computer Science</h1>
                </div>

                <!-- Program Content -->
                <section class="batch-detail-section mt-8 md:mt-12 lg:mt-16 px-2 md:px-0">
                    <h2 class="batch-section-heading mb-3 md:mb-4 text-xl md:text-2xl lg:text-[32px]">
                        <?php echo htmlspecialchars($degreeTitle); ?>
                    </h2>
                    <div class="text-sm md:text-base text-black leading-relaxed max-w-3xl mx-auto">
                        <?php foreach ($overview as $p): ?>
                            <p class="mb-4"><?php echo htmlspecialchars($p); ?></p>
                        <?php endforeach; ?>
                    </div>

                    <!-- Career Outlook -->
                    <div class="mt-10 md:mt-12">
                        <h3 class="text-lg md:text-xl font-extrabold text-black text-center tracking-wide mb-4">Career Outlook</h3>
                        <div class="overflow-x-auto max-w-4xl mx-auto">
                            <table class="w-full border border-black text-sm md:text-base text-black">
                                <thead>
                                    <tr>
                                        <th class="border border-black px-4 py-3 text-left font-extrabold uppercase">Primary Job Roles</th>
                                        <th class="border border-black px-4 py-3 text-left font-extrabold uppercase">Secondary Job Roles</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td class="border border-black px-4 py-3 align-top">
                                            <ul class="list-disc pl-5 space-y-1">
                                                <?php foreach ($careerPrimary as $item): ?>
                                                    <li><?php echo htmlspecialchars($item); ?></li>
                                                <?php endforeach; ?>
                                            </ul>
                                        </td>
                                        <td class="border border-black px-4 py-3 align-top">
                                            <ul class="list-disc pl-5 space-y-1">
                                                <?php foreach ($careerSecondary as $item): ?>
                                                    <li><?php echo htmlspecialchars($item); ?></li>
                                                <?php endforeach; ?>
                                            </ul>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Program Requirements -->
                    <div class="mt-12 md:mt-14">
                        <h3 class="text-lg md:text-xl font-extrabold text-black text-center tracking-wide mb-4">Program Requirements</h3>
                        <div class="overflow-x-auto max-w-4xl mx-auto">
                            <table class="w-full border border-black text-sm md:text-base text-black">
                                <thead>
                                    <tr>
                                        <th class="border border-black px-4 py-3 text-left font-extrabold uppercase">Category</th>
                                        <th class="border border-black px-4 py-3 text-left font-extrabold uppercase w-[140px]">Units</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($requirements as $row): ?>
                                        <?php $isTotal = stripos($row[0], 'total') !== false; ?>
                                        <tr>
                                            <td class="border border-black px-4 py-2 <?php echo $isTotal ? 'font-extrabold' : ''; ?>">
                                                <?php echo htmlspecialchars($row[0]); ?>
                                            </td>
                                            <td class="border border-black px-4 py-2 <?php echo $isTotal ? 'font-extrabold' : 'font-bold'; ?>">
                                                <?php echo htmlspecialchars($row[1]); ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
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
                    <li class="org-item-with-batch">
                        <span class="sidebar-label cursor-default">Faculty Unit</span>
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
                    <li class="org-item-with-batch">
                        <span class="sidebar-label cursor-default">Program</span>
                        <!-- Program submenu - Show below Program on hover -->
                        <ul class="sidebar-sublinks batch-hover-menu">
                            <li>
                                <a href="<?php echo PUBLIC_URL; ?>/program-detail-2.php" class="sidebar-sublink">
                                    Information Systems
                                </a>
                            </li>
                            <li>
                                <a href="<?php echo PUBLIC_URL; ?>/program-detail-1.php" class="sidebar-sublink active">
                                    Computer Science
                                </a>
                            </li>
                            <li>
                                <a href="<?php echo PUBLIC_URL; ?>/program-detail-3.php" class="sidebar-sublink">
                                    Data Science
                                </a>
                            </li>
                        </ul>
                    </li>
                </ul>
            </div>

            <!-- STUDENT ORGANIZATION Section -->
            <div class="sidebar-section">
                <h2 class="sidebar-title">STUDENT ORGANIZATION</h2>
                <ul class="sidebar-links">
                    <?php foreach ($organizationsList as $org): ?>
                        <li>
                            <a href="<?php echo PUBLIC_URL; ?>/batches.php?org_id=<?php echo $org['id']; ?>">
                                <?php echo htmlspecialchars($org['name']); ?> Batches
                            </a>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>
    </aside>
</div>

<?php include '../includes/footer.php'; ?>

