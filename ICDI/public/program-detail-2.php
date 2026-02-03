<?php
require_once '../includes/config.php';
require_once '../includes/database.php';
require_once '../includes/upload.php';

$bodyClass = 'origin-page';
$pageTitle = 'Information Systems - Program - PROWLWAY';
include '../includes/header.php';

// Fetch organizations list for sidebar directory
$organizationsList = dbFetchAll("SELECT * FROM student_organizations WHERE status = 'active' ORDER BY display_order ASC");

// Fetch faculty subcategories for sidebar
$facultySubcategories = dbFetchAll("SELECT * FROM institute_sections WHERE type = 'faculty_subcategory' AND status = 'published' ORDER BY display_order ASC, title ASC");
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
                    <h1 class="text-2xl md:text-3xl font-bold text-gray-900 px-2 md:px-0">Information Systems</h1>
                </div>

                <!-- Program Content -->
                <section class="batch-detail-section mt-8 md:mt-12 lg:mt-16 px-2 md:px-0">
                    <h2 class="batch-section-heading mb-4 md:mb-6 text-xl md:text-2xl lg:text-[32px]">BACHELOR OF SCIENCE IN INFORMATION SYSTEMS</h2>
                    <div class="text-sm md:text-base text-black leading-relaxed max-w-3xl mx-auto">
                        <p class="mb-4">
                            The BS in Information Systems (BSIS) is a four-year degree program that builds a bridge between technology and business. It trains students to understand how organizations operate and how technology can support, enhance, and transform business processes.
                        </p>
                        <p class="mb-6">
                            Through this program, students gain a strong foundation in systems analysis and design, database management, project coordination, and IT strategy—equipping them to solve real-world challenges and drive innovation in an increasingly digital world.
                        </p>
                    </div>

                    <!-- Career Outlook -->
                    <div class="mt-10 md:mt-12">
                        <h3 class="text-lg md:text-xl font-extrabold text-black text-center tracking-wide mb-4">CAREER OUTLOOK</h3>
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
                                                <li>Organizational Process Analyst</li>
                                                <li>Data Analyst</li>
                                                <li>Solutions Specialist</li>
                                                <li>Systems Analyst</li>
                                                <li>IS Project Management Personnel</li>
                                            </ul>
                                        </td>
                                        <td class="border border-black px-4 py-3 align-top">
                                            <ul class="list-disc pl-5 space-y-1">
                                                <li>Applications Developer</li>
                                                <li>End User Trainer</li>
                                                <li>Documentation Specialist</li>
                                                <li>Quality Assurance Specialist</li>
                                            </ul>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Program Requirements -->
                    <div class="mt-12 md:mt-14">
                        <h3 class="text-lg md:text-xl font-extrabold text-black text-center tracking-wide mb-4">PROGRAM REQUIREMENTS</h3>
                        <div class="overflow-x-auto max-w-4xl mx-auto">
                            <table class="w-full border border-black text-sm md:text-base text-black">
                                <thead>
                                    <tr>
                                        <th class="border border-black px-4 py-3 text-left font-extrabold uppercase">Category</th>
                                        <th class="border border-black px-4 py-3 text-left font-extrabold uppercase w-[140px]">Units</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr><td class="border border-black px-4 py-2">General Education Courses (GEE)</td><td class="border border-black px-4 py-2 font-bold">9</td></tr>
                                    <tr><td class="border border-black px-4 py-2">General Education Electives (GEC)</td><td class="border border-black px-4 py-2 font-bold">33</td></tr>
                                    <tr><td class="border border-black px-4 py-2">Physical Education (PE)</td><td class="border border-black px-4 py-2 font-bold">8</td></tr>
                                    <tr><td class="border border-black px-4 py-2">National Service Training Program (NSTP)</td><td class="border border-black px-4 py-2 font-bold">6</td></tr>
                                    <tr><td class="border border-black px-4 py-2">KLD Studies</td><td class="border border-black px-4 py-2 font-bold">0</td></tr>
                                    <tr><td class="border border-black px-4 py-2">Common Computing Courses (CCIS)</td><td class="border border-black px-4 py-2 font-bold">16</td></tr>
                                    <tr><td class="border border-black px-4 py-2">IS Professional Courses (PCIS)</td><td class="border border-black px-4 py-2 font-bold">48</td></tr>
                                    <tr><td class="border border-black px-4 py-2">Professional Electives</td><td class="border border-black px-4 py-2 font-bold">12</td></tr>
                                    <tr><td class="border border-black px-4 py-2">Capstone</td><td class="border border-black px-4 py-2 font-bold">6</td></tr>
                                    <tr><td class="border border-black px-4 py-2">Practicum</td><td class="border border-black px-4 py-2 font-bold">6</td></tr>
                                    <tr>
                                        <td class="border border-black px-4 py-2 font-extrabold">Total No. of Units</td>
                                        <td class="border border-black px-4 py-2 font-extrabold">144</td>
                                    </tr>
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
                                <a href="<?php echo PUBLIC_URL; ?>/program-detail-2.php" class="sidebar-sublink active">
                                    Information Systems
                                </a>
                            </li>
                            <li>
                                <a href="<?php echo PUBLIC_URL; ?>/program-detail-1.php" class="sidebar-sublink">
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

